<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="margin: 0; padding: 20px; background-color: #f5f5f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background: #ffffff; border-radius: 8px; padding: 30px;">
                    <!-- Header -->
                    <tr>
                        <td style="border-bottom: 2px solid #f0f0f0; padding-bottom: 20px; margin-bottom: 25px;">
                            <h1 style="color: #1d2327; margin: 0 0 10px 0; font-size: 24px;"><?php echo esc_html($data['site_name']); ?></h1>
                            <p style="color: #646970; margin: 0; font-size: 16px;"><?php _e('Plugin Deactivation Feedback', 'jwcfe'); ?></p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td>
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <!-- User Info -->
                                <tr>
                                    <td style="padding: 15px 0; border-bottom: 1px solid #f0f0f0;">
                                        <strong style="color: #2c3338; display: block; margin-bottom: 5px;"><?php _e('User Email:', 'jwcfe'); ?></strong>
                                        <span style="color: #646970;"><?php echo esc_html($data['user_email']); ?></span>
                                    </td>
                                </tr>

                                <!-- Reason -->
                                <tr>
                                    <td style="padding: 15px 0; border-bottom: 1px solid #f0f0f0;">
                                        <strong style="color: #2c3338; display: block; margin-bottom: 5px;"><?php _e('Deactivation Reason:', 'jwcfe'); ?></strong>
                                        <span style="color: #646970;"><?php echo esc_html($data['reason']); ?></span>
                                    </td>
                                </tr>

                                <?php if (!empty($data['other'])) : ?>
                                <tr>
                                    <td style="padding: 15px 0;">
                                        <strong style="color: #2c3338; display: block; margin-bottom: 5px;"><?php _e('Additional Comments:', 'jwcfe'); ?></strong>
                                        <div style="color: #646970; line-height: 1.6; white-space: pre-wrap;"><?php echo nl2br(esc_html($data['other'])); ?></div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding-top: 25px; border-top: 2px solid #f0f0f0; color: #646970; font-size: 13px;">
                            <p style="margin: 5px 0;"><?php printf(__('Date: %s', 'jwcfe'), $data['date']); ?></p>
                            <p style="margin: 5px 0;"><?php printf(__('Site: %s', 'jwcfe'),
                                '<a href="' . esc_url($data['site_url']) . '" style="color: #3858e9; text-decoration: none;">' . esc_html($data['site_url']) . '</a>'
                            ); ?></p>
                            <p style="margin: 5px 0; font-size: 12px; color: #8c8f94;"><?php _e('This is an automated notification. Please do not reply directly to this email.', 'jwcfe'); ?></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>