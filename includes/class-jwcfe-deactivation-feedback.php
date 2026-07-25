<?php
if (!defined('ABSPATH')) exit;

class JWCFE_Deactivation_Feedback {
    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_jwcfe_send_feedback', [$this, 'handle_feedback']);
    }

    public function enqueue_scripts($hook) {
        if ($hook !== 'plugins.php') return;

        // Make sure jQuery is present
        wp_enqueue_script('jquery');

        // Build and print our inline JS
        wp_add_inline_script('jquery', $this->get_feedback_script());

        // (Optional) enqueue a tiny style if your template needs it
        // wp_enqueue_style('jwcfe-feedback', plugins_url('admin/assets/feedback.css', dirname(__FILE__)));
    }

    private function get_feedback_script() {
        $nonce = wp_create_nonce('jwcfe_feedback_nonce');

        // Use the MAIN plugin file slug that appears in plugins.php row
        // JWCFE_BASE_NAME should be defined in your main plugin file.
        $plugin_slug = esc_js( defined('JWCFE_BASE_NAME') ? JWCFE_BASE_NAME : plugin_basename(dirname(__DIR__) . '/main.php') );

        // Build correct paths from the plugin ROOT (one level up from /includes)
        $root_path = plugin_dir_path( dirname(__FILE__) );
        $root_url  = plugin_dir_url( dirname(__FILE__) );

        $logo_url = esc_url( $root_url . 'admin/assets/logo-blue.svg' );

        // Load the popup template (ensure the file exists at /admin/feedback-popup-template.php)
        $template_path = $root_path . 'views/feedback-popup-template.php';
        $popup_html = file_exists($template_path) ? file_get_contents($template_path) : '';

        // Replace template vars
        $popup_html = str_replace('{{logo_url}}', $logo_url, $popup_html);

        // Safely encode HTML into a JS string
        $popup_js_html = wp_json_encode($popup_html);

        return "
jQuery(function($){
    const pluginSlug = '{$plugin_slug}';
    let deactivateLink = '';

    // Delegate to handle dynamically rendered rows too
    $(document).on('click', \"tr[data-plugin='\" + pluginSlug + \"'] .deactivate a\", function(e){
        e.preventDefault();
        deactivateLink = $(this).attr('href');

        // Inject popup once
        if ($('#jwcfe-feedback-popup').length === 0) {
            $('body').append({$popup_js_html});
        }

        // Wire buttons (use off/on to avoid double-binding)
        $(document)
            .off('click.jwcfe', '.jwcfd-deactivate')
            .on('click.jwcfe', '.jwcfd-deactivate', function(e){
                e.preventDefault();
                window.location.href = deactivateLink;
            });

        $(document)
            .off('click.jwcfe', '.jwcfd-close')
            .on('click.jwcfe', '.jwcfd-close', function(){
                $('#jwcfe-feedback-popup').remove();
            });

        $(document)
            .off('submit.jwcfe', '#jwcfe-feedback-form')
            .on('submit.jwcfe', '#jwcfe-feedback-form', function(event){
                event.preventDefault();

                const reason = $('input[name=\"reason\"]:checked').val() || '';
                const other  = $('textarea[name=\"other_reason\"]').val() || '';

                $('#jwcfe-loading').show();

                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        action: 'jwcfe_send_feedback',
                        reason: reason,
                        other_reason: other,
                        _ajax_nonce: '{$nonce}'
                    }
                }).done(function(){
                    window.location.href = deactivateLink;
                }).fail(function(){
                    window.location.href = deactivateLink;
                }).always(function(){
                    $('#jwcfe-loading').hide();
                });
            });
    });
});
        ";
    }

    public function handle_feedback() {
        check_ajax_referer('jwcfe_feedback_nonce');

        // Security Check: Only allow users with plugin activation capability
        if (!current_user_can('activate_plugins')) {
            wp_send_json_error('Unauthorized', 403);
            return;
        }

        $reason = sanitize_text_field($_POST['reason'] ?? '');
        $other  = sanitize_textarea_field($_POST['other_reason'] ?? '');

        $user        = wp_get_current_user();
        $user_email  = $user->user_email;
        
        // Sanitize Site Name to prevent Email Header Injection
        $site_name   = wp_strip_all_tags(get_bloginfo('name'));
        $site_name   = str_replace(array("\r", "\n"), '', $site_name);
        
        $site_url    = site_url();
        $date        = current_time('F j, Y \a\t g:i a');

        $additional = '';
        if (!empty($other)) {
            $additional = '<tr><td><strong>Additional Comments:</strong><br>' . nl2br(esc_html($other)) . '</td></tr>';
        }

        $root_path = plugin_dir_path( dirname(__FILE__) );
        $email_template_path = $root_path . 'views/feedback-email-template.php';
        
        $email_template = '';
        if (file_exists($email_template_path)) {
            ob_start();
            include $email_template_path;
            $email_template = ob_get_clean();
        } else {
            wp_send_json_error('Email template not found', 500);
            return;
        }

        $replacements = [
            '{{site_name}}' => esc_html($site_name),
            '{{user_email}}'=> esc_html($user_email),
            '{{reason}}'    => esc_html($reason),
            '{{additional}}'=> $additional,
            '{{site_url}}'  => esc_url($site_url),
            '{{date}}'      => esc_html($date),
        ];

        $email_content = strtr($email_template, $replacements);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <' . sanitize_email(get_option('admin_email')) . '>',
            'Reply-To: ' . sanitize_email(get_option('admin_email')),
        ];

        $sent = wp_mail('support@jcodex.com', 'Plugin Deactivation Feedback: ' . $site_name, $email_content, $headers);

        if (!$sent) {
            wp_send_json_error('Email failed to send', 500);
            return;
        }

        wp_send_json_success(['message' => 'Feedback received']);
    }
}

new JWCFE_Deactivation_Feedback();
