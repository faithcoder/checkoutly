<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://jcodex.com
 *
 * @package    woo-checkout-regsiter-field-editor-premium
 * @subpackage woo-checkout-regsiter-field-editor-premium/admin
 */

if (!defined('WPINC')) {
	die;
}

if (!class_exists('JWCFE_Admin_Settings_Fields')) :
	class JWCFE_Admin_Settings_Fields extends JWCFE_Admin
	{
		protected static $_instance = null;
		private $plugin_name;
		private $version;
		private $screen_id;

		public function __construct($plugin_name, $version)
		{
			parent::__construct($plugin_name, $version);
			$this->plugin_name = $plugin_name;
			$this->version = $version;
		}

		public static function instance($plugin_name, $version)
		{
			if (is_null(self::$_instance)) {
				self::$_instance = new self($plugin_name, $version);
			}
			return self::$_instance;
		}

		public function checkout_form_field_editor()
		{
			$section = $this->get_current_section();
           
            if ($section == 'account') {
                
                echo '<div class="wrap woocommerce jwcfe-wrap"><div class="icon32 icon32-attributes" id="icon-woocommerce"><br /></div>';
                $this->render_page_header();
                
                $this->render_tabs_and_sections();
                
                if (isset($_POST['save_fields'])) {
                    echo $this->save_options($section);
                }
            
                if (isset($_POST['reset_fields'])) {
                    echo $this->reset_checkout_fields();
                }
            
                global $supress_field_modification;
                $supress_field_modification = false;
            
                // Display Premium Features page
                $pro_page = JWCFE_Admin_Settings_Pro::instance();
                $pro_page->render_page();
            
                echo '</div>';
                $this->jwcfe_field_popup();
            
            }
            else{
                echo '<div class="wrap woocommerce jwcfe-wrap"><div class="icon32 icon32-attributes" id="icon-woocommerce"><br /></div>';
                $this->render_page_header();
                $this->render_tabs_and_sections();
                if (isset($_POST['save_fields']))
                    echo $this->save_options($section);

                if (isset($_POST['reset_fields']))
                    echo $this->reset_checkout_fields();

                global $supress_field_modification;
                $supress_field_modification = false;

                // ── All 3 sections on one page (reference design) ──
                $all_sections = array('billing', 'shipping', 'additional');
                $section_meta = array(
                    'billing'    => array(
                        'label' => __('Billing Details', 'jwcfe'),
                        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#2271b1" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>',
                    ),
                    'shipping'   => array(
                        'label' => __('Shipping Details', 'jwcfe'),
                        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#2271b1" stroke-width="2"><path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
                    ),
                    'additional' => array(
                        'label' => __('Additional Information', 'jwcfe'),
                        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#2271b1" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>',
                    ),
                );
                ?>

                <form method="post" id="jwcfe_checkout_fields_form" action="">
                    <?php wp_nonce_field('woo_checkout_editor_settings', 'woo_checkout_editor_nonce'); ?>

                    <!-- ═══ TOP SAVE BAR ═══ -->
                    <div class="jwcfe-bottom-bar jwcfe-top-bar">
                        <button type="button" class="button" onclick="removeSelectedFields()"><?php esc_html_e('Remove Selected', 'jwcfe'); ?></button>
                        <button type="button" class="button" onclick="enableSelectedFields()"><?php esc_html_e('Show Selected', 'jwcfe'); ?></button>
                        <button type="button" class="button" onclick="disableSelectedFields()"><?php esc_html_e('Hide Selected', 'jwcfe'); ?></button>
                        <div class="jwcfe-save-group">
                            <input type="submit" name="reset_fields" class="button" value="<?php esc_attr_e('Reset to defaults', 'jwcfe'); ?>" onclick="return confirm('<?php esc_attr_e('Are you sure? All changes will be deleted.', 'jwcfe'); ?>');" />
                            <input type="submit" name="save_fields" class="button-primary jwcfe-save-btn" value="<?php esc_attr_e('Save Changes', 'jwcfe'); ?>" />
                        </div>
                    </div>

                    <?php 
                    $i = 0;
                    foreach ($all_sections as $sec_key) :
                        $sec_fields  = JWCFE_Helper::get_fields($sec_key);
                        $total_count = count($sec_fields);
                        $active_count = 0;
                        foreach ($sec_fields as $_f) {
                            if (!isset($_f['enabled']) || $_f['enabled'] == 1) $active_count++;
                        }
                        $sec_label = $section_meta[$sec_key]['label'];
                        $sec_icon  = $section_meta[$sec_key]['icon'];
                    ?>

                    <!-- ═══ SECTION ACCORDION: <?php echo esc_html($sec_key); ?> ═══ -->
                    <div class="jwcfe-accordion-wrapper jwcfe-accordion-open" data-section="<?php echo esc_attr($sec_key); ?>">
                    <div class="jwcfe-section-card jwcfe-accordion-trigger">
                        <div class="jwcfe-section-card-header">
                            <div class="jwcfe-section-card-title">
                                <span class="jwcfe-accordion-chevron">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </span>
                                <span class="jwcfe-section-icon"><?php echo $sec_icon; ?></span>
                                <strong><?php echo esc_html($sec_label); ?></strong>
                                <span class="jwcfe-active-badge"><?php echo esc_html($active_count . '/' . $total_count); ?> <?php esc_html_e('active', 'jwcfe'); ?></span>
                            </div>
                            <div class="jwcfe-section-card-actions" onclick="event.stopPropagation()">
                                <span class="jwcfe-drag-hint"><?php esc_html_e('⠿ Drag rows to reorder', 'jwcfe'); ?></span>
                                <button type="button" class="button jwcfe-add-field-btn" onclick="openNewFieldForm('<?php echo esc_js($sec_key); ?>')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                    <?php esc_html_e('Add field', 'jwcfe'); ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="jwcfe-accordion-body">
                    <table id="jwcfe_checkout_fields_<?php echo esc_attr($sec_key); ?>" class="wc_gateways widefat jwcfe-fields-table" cellspacing="0" data-section="<?php echo esc_attr($sec_key); ?>">
                        <thead>
                            <tr><?php $this->render_checkout_fields_heading_row(); ?></tr>
                        </thead>
                        <tbody class="ui-sortable" id="jwcfe_sortable_<?php echo esc_attr($sec_key); ?>">
                            <?php
                            
                            foreach ($sec_fields as $name => $options) :
                                if (isset($options['custom']) && $options['custom'] == 1) {
                                    $options['custom'] = '1';
                                } else {
                                    $options['custom'] = '0';
                                }
                                if (!isset($options['label'])) {
                                    $options['label'] = '';
                                }
                                if (!isset($options['text'])) {
                                    $options['text'] = '';
                                }
                                if (!isset($options['placeholder'])) {
                                    $options['placeholder'] = '';
                                }
                                
                                if (!isset($options['min_time'])) {
                                    $options['min_time'] = '';
                                }
                                if (!isset($options['max_time'])) {
                                    $options['max_time'] = '';
                                }
                                if (!isset($options['time_step'])) {
                                    $options['time_step'] = '';
                                }
                                if (!isset($options['time_format'])) {
                                    $options['time_format'] = '';
                                }
                                if (!isset($options['rules'])) {
                                    $options['rules'] = '';
                                }
                                if (!isset($options['texteditor'])) {
                                    $options['texteditor'] = '';
                                }
                                if (!isset($options['rules_action'])) {
                                    $options['rules_action'] = '';
                                }
                                if (!isset($options['rules_ajax'])) {
                                    $options['rules_ajax'] = '';
                                }
                                if (!isset($options['rules_action_ajax'])) {
                                    $options['rules_action_ajax'] = '';
                                }
                                if (isset($options['options_json']) && is_array($options['options_json'])) {
                                    $options['options_json'] =  urlencode(json_encode($options['options_json']));
                                } else {
                                    $options['options_json'] = '';
                                }
                                if (isset($options['extoptions']) && is_array($options['extoptions'])) {
                                    $options['extoptions'] = implode(",", $options['extoptions']);
                                } else {
                                    $options['extoptions'] = '';
                                }
                                if (isset($options['class']) && is_array($options['class'])) {
                                    $options['class'] = implode(" ", $options['class']);
                                } else {
                                    $options['class'] = '';
                                }
                                if (isset($options['label_class']) && is_array($options['label_class'])) {
                                    $options['label_class'] = implode(",", $options['label_class']);
                                } else {
                                    $options['label_class'] = '';
                                }
                                if (isset($options['validate']) && is_array($options['validate'])) {
                                    $options['validate'] = implode(",", $options['validate']);
                                } else {
                                    $options['validate'] = '';
                                }
                                if (isset($options['required']) && $options['required'] == 1) {
                                    $options['required'] = '1';
                                } else {
                                    $options['required'] = '0';
                                }
                                if (isset($options['is_include']) && $options['is_include'] == 1) {
                                    $options['is_include'] = '1';
                                } else {
                                    $options['is_include'] = '0';
                                }
                                if (isset($options['access']) && $options['access'] == 1) {
                                    $options['access'] = '1';
                                } else {
                                    $options['access'] = '0';
                                }
                                if (!isset($options['enabled']) || $options['enabled'] == 1) {
                                    $options['enabled'] = '1';
                                } else {
                                    $options['enabled'] = '0';
                                }
                                if (!isset($options['type'])) {
                                    $options['type'] = 'text';
                                }
                                if (isset($options['show_in_email']) && $options['show_in_email'] == 1) {
                                    $options['show_in_email'] = '1';
                                } else {
                                    $options['show_in_email'] = '0';
                                }
                                if (isset($options['show_in_order']) && $options['show_in_order'] == 1) {
                                    $options['show_in_order'] = '1';
                                } else {
                                    $options['show_in_order'] = '0';
                                }
                            ?>
                                <?php
                                $disabled = false;
                                if ($name == 'account_username' || $name == 'account_password') {
                                    $disabled = true;
                                ?>
                                    <tr class="row_<?php echo $i;
                                                    echo ' jwcfe-disabled'; ?>">
                                    <?php } else { ?>
                                    <tr class="row_<?php echo $i;
                                                    echo ($options['enabled'] == 1 ? '' : ' jwcfe-disabled') ?>">
                                    <?php } ?>
                                    <td width="1%" class="sort ui-sortable-handle">
                                     
                                        <input type="hidden" name="rowId[<?php echo $i; ?>]" class="rowId" value="<?php echo $options['custom']; ?>" />
                                        <input type="hidden" name="f_custom[<?php echo $i; ?>]" class="f_custom" value="<?php echo $options['custom']; ?>" />
                                        <input type="hidden" name="f_order[<?php echo $i; ?>]" class="f_order" value="<?php echo $i; ?>" />
                                        <input type="hidden" name="f_name[<?php echo $i; ?>]" class="f_name" value="<?php echo esc_attr($name); ?>" />
                                        <input type="hidden" name="f_name_new[<?php echo $i; ?>]" class="f_name_new" value="" />
                                        <input type="hidden" name="f_type[<?php echo $i; ?>]" class="f_type" value="<?php echo $options['type']; ?>" />
                                        <input type="hidden" name="f_label[<?php echo $i; ?>]" class="f_label" value="<?php echo htmlspecialchars($options['label']); ?>" />
                                        <input type="hidden" name="f_text[<?php echo $i; ?>]" class="f_text" value="<?php echo stripcslashes(stripcslashes($options['text'])); ?>" />
                                                                                
                                        <input type="hidden" name="f_texteditor[<?php echo $i; ?>]" class="f_texteditor" value="<?php echo isset($options['texteditor']) ? stripslashes($options['texteditor']) : ''; ?>" />

                                        <input type="hidden" name="f_extoptions[<?php echo $i; ?>]" class="f_extoptions" value="<?php echo ($options['extoptions']) ?>" />
                                        <input type="hidden" name="f_access[<?php echo $i; ?>]" class="f_access" value="<?php echo ($options['access']) ?>" />
                                        <?php if (isset($options['maxlength'])) { ?>
                                            <input type="hidden" name="f_maxlength[<?php echo $i; ?>]" class="f_maxlength" value="<?php echo $options['maxlength']; ?>" />
                                        <?php } ?>
                                        <input type="hidden" name="f_placeholder[<?php echo $i; ?>]" class="f_placeholder" value="<?php echo $options['placeholder']; ?>" />
                                        <input type="hidden" name="f_default[<?php echo $i; ?>]" class="f_default" value="<?php echo isset($options['default']) ? esc_attr($options['default']) : ''; ?>" />
                                        <input type="hidden" name="i_min_time[<?php echo $i; ?>]" class="i_min_time" value="<?php echo $options['min_time']; ?>" />
                                        <input type="hidden" name="i_max_time[<?php echo $i; ?>]" class="i_max_time" value="<?php echo $options['max_time']; ?>" />
                                        <input type="hidden" name="i_time_step[<?php echo $i; ?>]" class="i_time_step" value="<?php echo $options['time_step']; ?>" />
                                        <input type="hidden" name="i_time_format[<?php echo $i; ?>]" class="i_time_format" value="<?php echo $options['time_format']; ?>" />
                                        <input type="hidden" name="f_rules_action[<?php echo $i; ?>]" class="f_rules_action" value="<?php echo $options['rules_action']; ?>" />
                                        <input type="hidden" name="f_rules[<?php echo $i; ?>]" class="f_rules" value="<?php echo $options['rules']; ?>" />
                                        <input type="hidden" name="f_rules_action_ajax[<?php echo $i; ?>]" class="f_rules_action_ajax" value="<?php echo $options['rules_action_ajax']; ?>" />
                                        <input type="hidden" name="f_rules_ajax[<?php echo $i; ?>]" class="f_rules_ajax" value="<?php echo $options['rules_ajax']; ?>" />
                                        <input type="hidden" name="f_heading_type[<?php echo $i; ?>]" class="f_heading_type" value="<?php echo isset($options['heading_type']) ? $options['heading_type'] : 'h4'; ?>" />
                                        <input type="hidden" name="f_options[<?php echo $i; ?>]" class="f_options" value="<?php echo ($options['options_json']); ?>" />
                                        <input type="hidden" name="f_class[<?php echo $i; ?>]" class="f_class" value="<?php echo $options['class']; ?>" />
                                        <input type="hidden" name="f_label_class[<?php echo $i; ?>]" class="f_label_class" value="<?php echo $options['label_class']; ?>" />

                                        <input type="hidden" name="f_required[<?php echo $i; ?>]" class="f_required" value="<?php echo ($options['required']); ?>" />
                                        <input type="hidden" name="f_is_include[<?php echo $i; ?>]" class="f_is_include" value="<?php echo ($options['is_include']); ?>" />
                                        <input type="hidden" name="f_enabled[<?php echo $i; ?>]" class="f_enabled" value="<?php echo ($options['enabled']); ?>" />
                                        <input type="hidden" name="f_validation[<?php echo $i; ?>]" class="f_validation" value="<?php echo ($options['validate']); ?>" />
                                        <input type="hidden" name="f_show_in_email[<?php echo $i; ?>]" class="f_show_in_email" value="<?php echo ($options['show_in_email']); ?>" />
                                        <input type="hidden" name="f_show_in_order[<?php echo $i; ?>]" class="f_show_in_order" value="<?php echo ($options['show_in_order']); ?>" />
                                        <input type="hidden" name="f_deleted[<?php echo $i; ?>]" class="f_deleted" value="0" />
                                        <input type="hidden" name="f_section[<?php echo $i; ?>]" class="f_section" value="<?php echo esc_attr($sec_key); ?>" />
                                        <!--$properties = array('type', 'label', 'placeholder', 'class', 'required', 'clear', 'label_class', 'options');-->
                                    </td>
                                        <td class="td_select"><input type="checkbox" name="select_field" /></td>
                                        <td class="td_label">
                                            <?php
                                            if ($options['type'] === 'paragraph') {
                                                $allowed_tags = array(
                                                    'p' => array(),
                                                    'br' => array(),
                                                    'strong' => array(),
                                                    'em' => array(),
                                                    'ul' => array(),
                                                    'ol' => array(),
                                                    'i' => array(), 
                                                    'li' => array(),
                                                    'a' => array(
                                                        'href' => array(),
                                                        'title' => array(),
                                                        'target' => array()
                                                    ),
                                                    'span' => array(
                                                        'style' => array()
                                                    ),
                                                    'div' => array(
                                                        'class' => array(),
                                                        'style' => array()
                                                    ),
                                                );
                                                echo !empty($options['texteditor']) ? wp_kses(stripslashes($options['texteditor']), $allowed_tags) : '<em>No content</em>';
                                            } else {
                                                echo esc_html($options['label']);
                                            }
                                            ?>
                                        </td>
                                        <td class="td_name" style="color:#888;font-size:12px;"><?php echo esc_attr($name); ?></td>
                                        <td class="td_type">
                                            <?php
                                            $ftype = esc_html($options['type']);
                                            $type_map = array(
                                                'select'      => 'Select / Dropdown',
                                                'multiselect' => 'Multi-Select',
                                                'radio'       => 'Radio Button',
                                                'checkbox'    => 'Checkbox',
                                                'checkboxgroup' => 'Checkbox Group',
                                                'date'        => 'Date Picker',
                                                'timepicker'  => 'Time Picker',
                                                'week'        => 'Week Picker',
                                                'month'       => 'Month Picker',
                                                'textarea'    => 'Textarea',
                                                'paragraph'   => 'Paragraph',
                                                'heading'     => 'Heading',
                                                'hidden'      => 'Hidden',
                                                'password'    => 'Password',
                                                'number'      => 'Number',
                                                'email'       => 'Email',
                                                'phone'       => 'Phone',
                                                'text'        => 'Text',
                                            );
                                            $type_label = isset($type_map[$ftype]) ? $type_map[$ftype] : ucfirst($ftype);
                                            $badge_class = 'jwcfe-type-' . (isset($type_map[$ftype]) ? $ftype : 'default');
                                            echo '<span class="jwcfe-type-badge ' . esc_attr($badge_class) . '">' . esc_html($type_label) . '</span>';
                                            ?>
                                        </td>
                                        <td class="td_required status">
                                            <?php if ($options['required'] == 1) : ?>
                                                <span class="jwcfe-status-required"><?php esc_html_e('Required', 'jwcfe'); ?></span>
                                            <?php else : ?>
                                                <span class="jwcfe-status-optional"><?php esc_html_e('Optional', 'jwcfe'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="td_enabled status">
                                            <label class="pure-material-switch">
                                                <input type="checkbox" class="toggle-checkbox" <?php echo ($options['enabled'] == 1 ? 'checked' : ''); ?> />
                                                <span class="label">No</span>
                                            </label>
                                            <span class="toggle-label">yes</span>
                                        </td>
                                        <td class="td_edit">
                                            <div class="jwcfe-actions-cell">
                                                <div class="jwcfe-icon-btn edit" <?php echo ($options['enabled'] == 1 ? '' : 'disabled') ?> onclick="openEditFieldForm(this,<?php echo $i; ?>)" title="<?php esc_attr_e('Edit field', 'jwcfe'); ?>">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                </div>
                                                <?php if (isset($options['custom']) && $options['custom'] == 1) : ?>
                                                <div class="jwcfe-icon-btn delete" onclick="jwcfeDeleteSingleField(this)" title="<?php esc_attr_e('Delete field', 'jwcfe'); ?>">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php $i++;
                            endforeach; ?>
                        </tbody>
                    </table>
                    </div><!-- /.jwcfe-accordion-body -->
                    </div><!-- /.jwcfe-accordion-wrapper -->

                    <?php endforeach; // end $all_sections loop ?>

                    <!-- ═══ BOTTOM SAVE BAR ═══ -->
                    <div class="jwcfe-bottom-bar">
                        <button type="button" class="button" onclick="removeSelectedFields()"><?php esc_html_e('Remove Selected', 'jwcfe'); ?></button>
                        <button type="button" class="button" onclick="enableSelectedFields()"><?php esc_html_e('Show Selected', 'jwcfe'); ?></button>
                        <button type="button" class="button" onclick="disableSelectedFields()"><?php esc_html_e('Hide Selected', 'jwcfe'); ?></button>
                        <div class="jwcfe-save-group">
                            <input type="submit" name="reset_fields" class="button" value="<?php esc_attr_e('Reset to defaults', 'jwcfe'); ?>" onclick="return confirm('<?php esc_attr_e('Are you sure? All changes will be deleted.', 'jwcfe'); ?>');" />
                            <input type="submit" name="save_fields" class="button-primary jwcfe-save-btn" value="<?php esc_attr_e('Save Changes', 'jwcfe'); ?>" />
                        </div>
                    </div>

                </form>

                <?php
                
                $this->jwcfe_field_popup();
                ?>

                </div>
            <?php
            }
            
		}

		/**

		 * Reset checkout fields.

		 */

		function reset_checkout_fields()
		{

			delete_option('jwcfe_account_label');
			delete_option('jwcfe_account_sync_fields');
			delete_option('jwcfe_wc_fields_account');
			delete_option('jwcfe_wc_fields_billing');
			delete_option('jwcfe_wc_fields_shipping');
			delete_option('jwcfe_wc_fields_additional');
			echo '<div class="updated"><p>' . esc_html__('SUCCESS: Checkout fields successfully reset', 'jwcfe') . '</p></div>';
		}

		function sort_fields_by_order($a, $b)
		{
			$a_order = isset($a['order']) ? (int)$a['order'] : 0;
			$b_order = isset($b['order']) ? (int)$b['order'] : 0;
			if ($a_order === $b_order) {
				return 0;
			}
			return ($a_order < $b_order) ? -1 : 1;
		}

		function get_field_types()
		{

			return array(
				'text'          => 'Text',
                'email'         => 'Email',
				'number'        => 'Number',
				'password'      => 'Password',
				'phone'         => 'Phone',
                'hidden'            => 'Hidden',
                'select'        => 'Select',
                'multiselect'   => 'Multi-Select',
                'radio'	        => 'Radio Button',
                'checkbox'      => 'Checkbox',
                'checkboxgroup' => 'Checkbox Group',
                'date'	        => 'Date Picker',
                'timepicker'    => 'Time Picker',
                'week'	        => 'Week Picker',
                'month'	        => 'Month Picker',
				'textarea'      => 'Textarea',
				'paragraph'	    => 'Paragraph',
				'heading'           => 'Heading',
                'url'               => 'URL',
				'datetime-local'    => 'Datetime Local',
			);
		}


        public function jwcfe_field_popup(){
            
            $field_types = $this->get_field_types();
            $formTitle = 'Add New Field';
            $addClass = '';
            if (isset($_GET['section']) && $_GET['section'] == 'account') {
                $formTitle = 'Add New Account Field';
                $addClass = 'accountdialog';
            }
            ?>
            <div id="jwcfeModal" class="jwcfemodal" style="display: none;">
                <div class="jwcfe-modal-box">
                    <div id="jwcfe_new_field_form_pp" class="<?php echo $addClass; ?> jwcfe_popup_wrapper">
                        <form method="POST" id="jwcfe_new_field_form" action="">

                            <!-- HEADER -->
                            <div class="jwcfe-modal-header">
                                <div class="jwcfe-modal-header-title">
                                    <span class="jwcfe-modal-plus">+</span>
                                    <h2 class="ui-dialog-title"><?php echo esc_html($formTitle); ?></h2>
                                </div>
                                <button type="button" class="jwcfe-modal-close jwcfecloseBtn" aria-label="Close">&#x2715;</button>
                            </div>

                            <!-- BODY -->
                            <div class="jwcfe-modal-body" id="jwcfe_field_editor_form_new">
                                <input type="hidden" name="i_options" value="" />

                                <!-- Row 1: Field Type + Section -->
                                <div class="jwcfe-modal-row-2col">
                                    <div class="jwcfe-modal-field rowfield">
                                        <label class="jwcfe-modal-label"><?php esc_html_e('Field Type', 'jwcfe'); ?> <span class="jwcfe-required">*</span></label>
                                        <select name="ftype" class="jwcfe-modal-select" onchange="jwcfeFieldTypeChangeListnerblock(this)">
                                            <option value="text"><?php esc_html_e('Text', 'jwcfe'); ?></option>
                                            <option value="number"><?php esc_html_e('Number', 'jwcfe'); ?></option>
                                            <option value="email"><?php esc_html_e('Email', 'jwcfe'); ?></option>
                                            <option value="phone"><?php esc_html_e('Phone', 'jwcfe'); ?></option>
                                            <option value="password"><?php esc_html_e('Password', 'jwcfe'); ?></option>
                                            <option value="hidden"><?php esc_html_e('Hidden', 'jwcfe'); ?></option>
                                            <option value="select"><?php esc_html_e('Select / Dropdown', 'jwcfe'); ?></option>
                                            <option value="multiselect"><?php esc_html_e('Multi-Select', 'jwcfe'); ?></option>
                                            <option value="radio"><?php esc_html_e('Radio Button', 'jwcfe'); ?></option>
                                            <option value="checkbox"><?php esc_html_e('Checkbox', 'jwcfe'); ?></option>
                                            <option value="checkboxgroup"><?php esc_html_e('Checkbox Group', 'jwcfe'); ?></option>
                                            <option value="date"><?php esc_html_e('Date Picker', 'jwcfe'); ?></option>
                                            <option value="timepicker"><?php esc_html_e('Time Picker', 'jwcfe'); ?></option>
                                            <option value="week"><?php esc_html_e('Week Picker', 'jwcfe'); ?></option>
                                            <option value="month"><?php esc_html_e('Month Picker', 'jwcfe'); ?></option>
                                            <option value="url"><?php esc_html_e('URL', 'jwcfe'); ?></option>
                                            <option value="datetime-local"><?php esc_html_e('Datetime Local', 'jwcfe'); ?></option>
                                            <option value="textarea"><?php esc_html_e('Textarea', 'jwcfe'); ?></option>
                                            <option value="heading"><?php esc_html_e('Heading', 'jwcfe'); ?></option>
                                            <option value="paragraph"><?php esc_html_e('Paragraph', 'jwcfe'); ?></option>

                                        </select>
                                    </div>
                                    <div class="jwcfe-modal-field">
                                        <label class="jwcfe-modal-label"><?php esc_html_e('Section', 'jwcfe'); ?></label>
                                        <select name="fsection_display" class="jwcfe-modal-select jwcfe-section-display-select" disabled>
                                            <option value="billing"><?php esc_html_e('Billing Details', 'jwcfe'); ?></option>
                                            <option value="shipping"><?php esc_html_e('Shipping Address', 'jwcfe'); ?></option>
                                            <option value="additional"><?php esc_html_e('Additional Information', 'jwcfe'); ?></option>
                                            <option value="account"><?php esc_html_e('Account / My Account', 'jwcfe'); ?></option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Row 2: Label + Key/Name -->
                                <div class="jwcfe-modal-row-2col">
                                    <div class="jwcfe-modal-field">
                                        <label class="jwcfe-modal-label"><?php esc_html_e('Field Label', 'jwcfe'); ?> <span class="jwcfe-required">*</span></label>
                                        <input type="text" name="flabel" class="jwcfe-modal-input" placeholder="<?php esc_attr_e('e.g. Company Name', 'jwcfe'); ?>" />
                                    </div>
                                    <div class="jwcfe-modal-field">
                                        <label class="jwcfe-modal-label">
                                            <?php esc_html_e('Field Key / Name', 'jwcfe'); ?> <span class="jwcfe-required">*</span>
                                            <span class="jwcfe-modal-tooltip-icon" title="<?php esc_attr_e('Unique identifier. No spaces. Not repeated across sections.', 'jwcfe'); ?>">&#9432;</span>
                                        </label>
                                        <input type="text" name="fname" class="jwcfe-modal-input" placeholder="<?php esc_attr_e('e.g. billing_vat_number', 'jwcfe'); ?>" />
                                        <span class="err_msgs" style="color:red;font-size:11px;display:block;margin-top:3px;"></span>
                                    </div>
                                </div>

                                <!-- Default Value -->
                                <div class="jwcfe-modal-row-1col rowDefault">
                                    <div class="jwcfe-modal-field">
                                        <label class="jwcfe-modal-label"><?php esc_html_e('Default Value', 'jwcfe'); ?></label>
                                        <input type="text" name="fdefault" class="jwcfe-modal-input" placeholder="<?php esc_attr_e('e.g. ABC', 'jwcfe'); ?>" />
                                    </div>
                                </div>

                                <!-- Select/Radio options -->
                                <div class="rowOptions jwcfe-modal-options-block" style="display:none;">
                                    <div class="jwcfe-modal-field">
                                        <label class="jwcfe-modal-label"><?php esc_html_e('Options', 'jwcfe'); ?> <span class="jwcfe-required">*</span></label>
                                        <div class="jwcfe_options">
                                            <span class="err_msgs_options" style="color:red;font-size:12px;display:block;margin-bottom:5px;"></span>
                                            <div class="jwcfe-option-list">
                                                <div class="ui-sortable">
                                                    <div class="jwcfe-opt-container">
                                                        <div class="jwcfe-opt-row">
                                                            <div class="jwcfe-opt-input-wrap"><input type="text" name="i_options_key[]" placeholder="<?php esc_attr_e('Option Value', 'jwcfe'); ?>" /></div>
                                                            <div class="jwcfe-opt-input-wrap"><input type="text" name="i_options_text[]" placeholder="<?php esc_attr_e('Option Text', 'jwcfe'); ?>" /></div>
                                                            <div class="jwcfe-opt-actions">
                                                                <a href="javascript:void(0)" onclick="jwcfeAddNewOptionRow(this)" class="jwcfe-opt-btn jwcfe-opt-btn-add" title="Add option">+</a>
                                                                <a href="javascript:void(0)" onclick="jwcfeRemoveOptionRow(this)" class="jwcfe-opt-btn jwcfe-opt-btn-remove" title="Remove">×</a>
                                                                <span class="jwcfe-opt-btn jwcfe-opt-btn-sort ui-jwcf-sortable-handle" onclick="jwcfe_handler_OptionRow(this)" title="Drag to sort">⇅</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- CSS Class (always visible) -->
                                <div class="jwcfe-modal-row-1col rowCustomClass">
                                    <div class="jwcfe-modal-field">
                                        <label class="jwcfe-modal-label"><?php esc_html_e('CSS Class', 'jwcfe'); ?> <span class="jwcfe-modal-hint"><?php esc_html_e('(optional)', 'jwcfe'); ?></span></label>
                                        <input type="text" name="fcustomclass" class="jwcfe-modal-input" placeholder="e.g. my-class another-class" />
                                    </div>
                                </div>

                                <!-- Heading Level (shown only for heading type) -->
                                <div class="jwcfe-modal-row-1col rowHeadingType" style="display:none;">
                                    <div class="jwcfe-modal-field">
                                        <label class="jwcfe-modal-label"><?php esc_html_e('Heading Level', 'jwcfe'); ?></label>
                                        <select name="fheading_type" class="jwcfe-modal-select">
                                            <option value="h1">H1</option>
                                            <option value="h2">H2</option>
                                            <option value="h3">H3</option>
                                            <option value="h4" selected>H4</option>
                                            <option value="h5">H5</option>
                                            <option value="h6">H6</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Paragraph Editor (shown only for paragraph type) -->
                                <div class="jwcfe-modal-row-1col texteditor" style="display:none;">
                                    <div class="jwcfe-modal-field">
                                        <label class="jwcfe-modal-label"><?php esc_html_e('Paragraph Text', 'jwcfe'); ?></label>
                                        <textarea name="ftexteditor" id="flabel_editor" class="jwcfe-modal-textarea" style="min-height:120px;"></textarea>
                                    </div>
                                </div>

                                <!-- Row 4: Placeholder -->
                                <div class="jwcfe-modal-row-1col rowPlaceholder">
                                    <div class="jwcfe-modal-field">
                                        <label class="jwcfe-modal-label"><?php esc_html_e('Placeholder Text', 'jwcfe'); ?></label>
                                        <input type="text" name="fplaceholder" class="jwcfe-modal-input" placeholder="<?php esc_attr_e('e.g. Enter your company name', 'jwcfe'); ?>" />
                                    </div>
                                </div>

                                <!-- Row 5: Description -->
                                <div class="jwcfe-modal-row-1col rowDescription">
                                    <div class="jwcfe-modal-field">
                                        <label class="jwcfe-modal-label"><?php esc_html_e('Description / Help Text', 'jwcfe'); ?></label>
                                        <textarea name="ftext" class="jwcfe-modal-textarea" placeholder="<?php esc_attr_e('Optional help text shown below the field', 'jwcfe'); ?>"></textarea>
                                    </div>
                                </div>

                                <!-- Character limit -->
                                <div class="jwcfe-modal-row-1col rowMaxlength" style="display:none;">
                                    <div class="jwcfe-modal-field">
                                        <label class="jwcfe-modal-label"><?php esc_html_e('Character limit', 'jwcfe'); ?></label>
                                        <input type="number" name="fmaxlength" class="jwcfe-modal-input" />
                                    </div>
                                </div>

                                <!-- Validation -->
                                <div class="jwcfe-modal-row-1col rowValidate" style="display:none;">
                                    <div class="jwcfe-modal-field">
                                        <label class="jwcfe-modal-label"><?php esc_html_e('Validation', 'jwcfe'); ?></label>
                                        <select multiple="multiple" name="fvalidate" class="jwcfe-enhanced-multi-select jwcfe-modal-select" data-placeholder="Please Select" style="height:42px;">
                                            <option value="email"><?php esc_html_e('Email', 'jwcfe'); ?></option>
                                            <option value="phone"><?php esc_html_e('Phone', 'jwcfe'); ?></option>
                                        </select>
                                    </div>
                                </div>



                                <!-- Timepicker -->
                                <div class="jwcfe-modal-row-2col rowTimepicker" style="display:none;">
                                    <div class="jwcfe-modal-field">
                                        <label class="jwcfe-modal-label"><?php esc_html_e('Min. Time', 'jwcfe'); ?> <span class="jwcfe-modal-hint">(<?php esc_html_e('e.g. 12:30am', 'jwcfe'); ?>)</span></label>
                                        <input type="text" name="i_min_time" value="12:00am" class="jwcfe-modal-input" />
                                    </div>
                                    <div class="jwcfe-modal-field">
                                        <label class="jwcfe-modal-label"><?php esc_html_e('Max. Time', 'jwcfe'); ?> <span class="jwcfe-modal-hint">(<?php esc_html_e('e.g. 11:30pm', 'jwcfe'); ?>)</span></label>
                                        <input type="text" name="i_max_time" value="11:30pm" class="jwcfe-modal-input" />
                                    </div>
                                </div>
                                <div class="jwcfe-modal-row-2col rowTimepicker" style="display:none;">
                                    <div class="jwcfe-modal-field">
                                        <label class="jwcfe-modal-label"><?php esc_html_e('Time Format', 'jwcfe'); ?></label>
                                        <select name="i_time_format" class="jwcfe-modal-select">
                                            <option value="h:i A"><?php esc_html_e('12-hour format', 'jwcfe'); ?></option>
                                            <option value="H:i"><?php esc_html_e('24-hour format', 'jwcfe'); ?></option>
                                        </select>
                                    </div>
                                    <div class="jwcfe-modal-field">
                                        <label class="jwcfe-modal-label"><?php esc_html_e('Time Step', 'jwcfe'); ?> <span class="jwcfe-modal-hint">(<?php esc_html_e('minutes', 'jwcfe'); ?>)</span></label>
                                        <input type="text" name="i_time_step" value="30" class="jwcfe-modal-input" />
                                    </div>
                                </div>



                                <?php if (isset($_GET['section']) && $_GET['section'] == 'account') : ?>
                                <div class="jwcfe-modal-row-1col rowAccess">
                                    <div class="jwcfe-modal-field">
                                        <label class="jwcfe-modal-label"><input type="checkbox" name="faccess" value="yes" /> <?php esc_html_e("User Can't edit this field", 'jwcfe'); ?></label>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Field Width (moved near end) -->
                                <div class="jwcfe-modal-row-1col rowClass">
                                    <div class="jwcfe-modal-field">
                                        <label class="jwcfe-modal-label"><?php esc_html_e('Field Width', 'jwcfe'); ?></label>
                                        <select name="fclass" class="jwcfe-modal-select">
                                            <option value="form-row-wide"><?php esc_html_e('Full width', 'jwcfe'); ?></option>
                                            <option value="form-row-first"><?php esc_html_e('Half width — left', 'jwcfe'); ?></option>
                                            <option value="form-row-last"><?php esc_html_e('Half width — right', 'jwcfe'); ?></option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Toggle rows -->
                                <div class="jwcfe-modal-divider"></div>

                                <div class="jwcfe-modal-toggle-row checkbox-row" id="requiredRow">
                                    <div class="jwcfe-modal-toggle-info">
                                        <span class="jwcfe-modal-toggle-label"><?php esc_html_e('Required field', 'jwcfe'); ?></span>
                                        <span class="jwcfe-modal-toggle-desc"><?php esc_html_e('Customer must fill this out to complete checkout', 'jwcfe'); ?></span>
                                    </div>
                                    <label class="jwcfe-toggle-switch">
                                        <input type="checkbox" id="requiredechk" name="frequired" value="yes" checked />
                                        <span class="jwcfe-toggle-slider"></span>
                                    </label>
                                </div>

                                <div class="jwcfe-modal-toggle-row">
                                    <div class="jwcfe-modal-toggle-info">
                                        <span class="jwcfe-modal-toggle-label"><?php esc_html_e('Enable field', 'jwcfe'); ?></span>
                                        <span class="jwcfe-modal-toggle-desc"><?php esc_html_e('Show this field on the checkout page', 'jwcfe'); ?></span>
                                    </div>
                                    <label class="jwcfe-toggle-switch">
                                        <input type="checkbox" id="enabledchk" name="fenabled" value="yes" checked />
                                        <span class="jwcfe-toggle-slider"></span>
                                    </label>
                                </div>

                                <div class="jwcfe-modal-toggle-row">
                                    <div class="jwcfe-modal-toggle-info">
                                        <span class="jwcfe-modal-toggle-label"><?php esc_html_e('Display in order details', 'jwcfe'); ?></span>
                                        <span class="jwcfe-modal-toggle-desc"><?php esc_html_e('Show this field value in order summary', 'jwcfe'); ?></span>
                                    </div>
                                    <label class="jwcfe-toggle-switch">
                                        <input type="checkbox" id="showinorder" name="fshowinorder" value="order-review" checked />
                                        <span class="jwcfe-toggle-slider"></span>
                                    </label>
                                </div>

                                <div class="jwcfe-modal-toggle-row">
                                    <div class="jwcfe-modal-toggle-info">
                                        <span class="jwcfe-modal-toggle-label"><?php esc_html_e('Display in emails', 'jwcfe'); ?></span>
                                        <span class="jwcfe-modal-toggle-desc"><?php esc_html_e('Include this field value in order emails', 'jwcfe'); ?></span>
                                    </div>
                                    <label class="jwcfe-toggle-switch">
                                        <input type="checkbox" name="fshowinemail" value="email" id="showinemail" checked />
                                        <span class="jwcfe-toggle-slider"></span>
                                    </label>
                                </div>

                            </div><!-- /.jwcfe-modal-body -->

                            <!-- FOOTER -->
                            <div class="jwcfe-modal-footer">
                                <button type="button" id="btncancel" class="jwcfe-modal-btn jwcfe-modal-btn-cancel btncancel">
                                    &#x2715; <?php esc_html_e('Cancel', 'jwcfe'); ?>
                                </button>
                                <button type="button" id="btnaddfield" class="jwcfe-modal-btn jwcfe-modal-btn-primary" value="yes">
                                    &#10003; <?php esc_html_e('Add Field', 'jwcfe'); ?>
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
            <?php
        }
		

		function render_checkout_fields_heading_row()
		{

                ?>

                    <th class="sort"></th>

                    <th class="check-column" style="padding-left:0px !important;"><input type="checkbox" style="margin-left:7px;" onclick="jwcfeSelectAllCheckoutFields(this)" /></th>

                    <th class="name"><?php esc_html_e('Label', 'jwcfe'); ?></th>

                    <th class="id"><?php esc_html_e('Field Key', 'jwcfe'); ?></th>

                    <th><?php esc_html_e('Type', 'jwcfe'); ?></th>

                    <th class="status"><?php esc_html_e('Status', 'jwcfe'); ?></th>

                    <th class="status"><?php esc_html_e('Show / Hide', 'jwcfe'); ?></th>

                    <th class="status"><?php esc_html_e('Actions', 'jwcfe'); ?></th>

                <?php

		}

		function render_actions_row($section) { /* Not used in new layout */ }
	}
endif;