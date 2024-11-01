<?php
	/*
		Plugin Name: Simple Dropdown Filter by Category for WooCommerce
		Description: Add a dropdown on the catalog page to filter products by WooCommerce categories.
		Version: 0.0.1.6
		Author: Inbound Horizons
		Author URI: https://www.inboundhorizons.com
	*/
	
	
	if (!defined('ABSPATH')) {
		exit; // Exit if accessed directly
	}
	
	class SDFBCFW_SIMPLE_CATEGORY_FILTER_FOR_WOOCOMMERCE {
		
		private static $_instance = null;	// Get the static instance variable
		
		public static function Instantiate() {
			if (is_null(self::$_instance)) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}
		
		private function __construct() {
			// Only initialize hooks if WooCommommerce is present
			add_action('woocommerce_init', array($this, 'InitHooks'));
		}
		
		public function InitHooks() {
			
			// Side Menu (which loads the back-end)
			add_action('admin_menu', function() {
				add_submenu_page('woocommerce', 'Category Filter', 'Category Filter', 'manage_options', 'category-filter-for-woocommerce', array($this, 'BackendHTML'));
			}, 95);
			
			
			// Add a settings link on the plugin page
			add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
				$settings_link = '<a href="'.admin_url('admin.php?page=category-filter-for-woocommerce').'">'.__('Settings').'</a>';
				array_unshift($links, $settings_link);
				return ($links);
			});
			
			
			// Save the settings
			add_action('wp_ajax_SDFBCFW_SAVE_SETTINGS', function() {
				$ok = 0;
			
				$nonce_verified = wp_verify_nonce($_POST['nonce'], 'save-sdfbcfw-settings');
				
				if ($nonce_verified) {
					$ok = 1;
				
					// Retrieve and sanitize POSTed data
					$hide_empty = isset($_POST['hide_empty']) ? rest_sanitize_boolean($_POST['hide_empty']) : true;
					$show_count = isset($_POST['show_count']) ? rest_sanitize_boolean($_POST['show_count']) : true;
					$placeholder = isset($_POST['placeholder']) ? sanitize_text_field($_POST['placeholder']) : "Filter by Category";
					$allow_clearing = isset($_POST['allow_clearing']) ? rest_sanitize_boolean($_POST['allow_clearing']) : true;
					$parent_css_class = isset($_POST['parent_css_class']) ? sanitize_text_field($_POST['parent_css_class']) : "";
					$selection_css_class = isset($_POST['selection_css_class']) ? sanitize_text_field($_POST['selection_css_class']) : "";
					$dropdown_css_class = isset($_POST['dropdown_css_class']) ? sanitize_text_field($_POST['dropdown_css_class']) : "";
					$custom_css_rules = isset($_POST['custom_css_rules']) ? sanitize_textarea_field($_POST['custom_css_rules']) : "";
					
					$hide_categories = array();	// Array of category slugs to hide
					if (isset($_POST['hide_categories']) && is_array($_POST['hide_categories'])) {
						$hide_categories = array_map('sanitize_text_field', $_POST['hide_categories']);
					}
					
				
					// Save the settings
					$settings = array(
						'hide_empty' => $hide_empty,
						'hide_categories' => $hide_categories,
						'show_count' => $show_count,
						'placeholder' => $placeholder,
						'allow_clearing' => $allow_clearing,
						
						'parent_css_class' => $parent_css_class,
						'selection_css_class' => $selection_css_class,
						'dropdown_css_class' => $dropdown_css_class,
						'custom_css_rules' => $custom_css_rules,
					);
					
					update_option('sdfbcfw_settings', $settings); 	// SDFBCFW Simple Dropdown Filter By Category For WooCommerce settings
				}
				
				$return = array(
					'ok' => $ok,
				);
			
				wp_send_json($return);
				wp_die(); // This is required to terminate immediately and return a proper response
			});
		
		
			// Show the filters on the SHOP page
			add_action('woocommerce_before_shop_loop', array($this, 'DisplayCategoryDropdownWidget'), 20);
			
			add_action('widget_title', array($this, 'RemoveCategoryWidgetTitle'), 10, 3);
			
		}
		
		public function BackendHTML() {
		
		
			$img_src = plugin_dir_url(__FILE__).'assets/SDFC-internal-header.jpg';
			$plugin_data = get_plugin_data(__FILE__);
			$plugin_version = $plugin_data['Version'];
			$plugin_name = $plugin_data['Name'];
			
			
			
			$nonce = wp_create_nonce('save-sdfbcfw-settings');
			
			
			
			$settings = get_option('sdfbcfw_settings', array());
			$settings = $this->GetDefaultSettings($settings);
			
			$hide_empty_yes = ($settings['hide_empty']) ? "selected" : "";
			$hide_empty_no = ($settings['hide_empty']) ? "" : "selected";
			
			$hide_categories = $settings['hide_categories'];
			
			$show_count_yes = ($settings['show_count']) ? "selected" : "";
			$show_count_no = ($settings['show_count']) ? "" : "selected";
			
			$placeholder = $settings['placeholder'];
			
			$allow_clearing_yes = ($settings['allow_clearing']) ? "selected" : "";
			$allow_clearing_no = ($settings['allow_clearing']) ? "" : "selected";
			
			$parent_css_class = $settings['parent_css_class'];
			$selection_css_class = $settings['selection_css_class'];
			$dropdown_css_class = $settings['dropdown_css_class'];
			$custom_css_rules = $settings['custom_css_rules'];
			
			
			$args = array(
				'taxonomy'   => "product_cat",
				'number'     => 0,	// All
				'orderby'    => 'name',
				'order'      => 'asc',
				'hide_empty' => false,
				//'include'    => $ids
			);
			$product_categories = get_terms($args);
			
			
			$dropdown_options = array();
			foreach ($product_categories as $cat) {
			
				$selected = false;
				if (in_array($cat->slug, $hide_categories)) {
					$selected = true;
				}
			
				array_push($dropdown_options, array(
					'id' => $cat->slug,
					'text' => $cat->name,
					'selected' => $selected,
				));
			}
			
			
			wp_enqueue_script('selectWoo');
			wp_enqueue_style('select2');	// Required for selectWoo
			wp_enqueue_style('woocommerce_admin_styles');	// Required for selectWoo
			
			wc_enqueue_js('
				jQuery("#hide_categories").selectWoo({
					dropdownAutoWidth: true,
					placeholder: "Hide Categories",
					allowClear: 0,
					multiple: true,
					data: '.json_encode($dropdown_options).',
				});
			');
			
			
			
			echo '
			
			
				<div style="text-align:center;">
					<div class="panel" style="margin:0px;">
						<img alt="'.esc_attr($plugin_name).'" src="'.esc_url($img_src).'" />
					</div>
					<div class="wrap panel panel-margin">
						<div class="panel-body">
							<span style="float:left;">
								<b>Version:</b> '.esc_html($plugin_version).'
							</span>
							
							<span>
								<b>Developed By: </b>
								<a href="https://www.inboundhorizons.com/" target="_blank">
									Inbound Horizons
								</a>
							</span>
							
							<span style="float:right;">
								<a href="https://www.inboundhorizons.com/wordpress-plugin-feedback/" target="_blank">
									Get Support
								</a>
							</span>
							
						</div>
					</div>
				</div>
				
				
				<div class="wrap" style="display:flex; flex-direction: column;">			
					
					<h1>
						'.esc_html($plugin_name).'
					</h1>
					
					<input type="hidden" id="nonce" value="'.esc_attr($nonce).'" />
					
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row">
									Hide Empty Categories
								</th>
								<td>
									<select class="regular-text" id="hide_empty">
										<option value="1" '.esc_attr($hide_empty_yes).'>Yes</option>
										<option value="0" '.esc_attr($hide_empty_no).'>No</option>
									</select>
									<p class="description">
										Hide categories that have no products.
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									Hide Categories
								</th>
								<td>
									<select class="regular-text" id="hide_categories">
									</select>
									<p class="description">
										Hide categories in filter that you do not want to show to customers.
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									Show Product Counts
								</th>
								<td>
									<select class="regular-text" id="show_count">
										<option value="1" '.esc_attr($show_count_yes).'>Yes</option>
										<option value="0" '.esc_attr($show_count_no).'>No</option>
									</select>
									<p class="description">
										Show the number of each products in each category.
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									Placeholder
								</th>
								<td>
									<input type="text" id="placeholder" value="'.esc_attr($placeholder).'" class="regular-text" placeholder="&quot;Filter by Category&quot;" />
									<p class="description">
										Placeholder text used.
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									Allow Clearing
								</th>
								<td>
									<select class="regular-text" id="allow_clearing">
										<option value="1" '.esc_attr($allow_clearing_yes).'>Yes</option>
										<option value="0" '.esc_attr($allow_clearing_no).'>No</option>
									</select>
									<p class="description">
										Allow the user to remove a filter.
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									Parent CSS Class
								</th>
								<td>
									<input type="text" id="parent_css_class" value="'.esc_attr($parent_css_class).'" class="regular-text" placeholder="CSS Class" />
									<p class="description">
										Adds additional CSS classes to the selection\'s parent container.
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									Selection CSS Class
								</th>
								<td>
									<input type="text" id="selection_css_class" value="'.esc_attr($selection_css_class).'" class="regular-text" placeholder="CSS Class" />
									<p class="description">
										Adds additional CSS classes to the selection container.
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									Dropdown CSS Class
								</th>
								<td>
									<input type="text" id="dropdown_css_class" value="'.esc_attr($dropdown_css_class).'" class="regular-text" placeholder="CSS Class" />
									<p class="description">
										Adds additional CSS classes to the dropdown container.
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									Custom CSS Rules
								</th>
								<td>
									<textarea id="custom_css_rules" rows="5" class="regular-text">'.esc_textarea($custom_css_rules).'</textarea> 
									<p class="description">
										Custom CSS Styles
									</p>
									
									<code>
										<abbr title="Parent CSS Class">.example_parent_css_class</abbr> {
											font-weight: bold;
										}
									</code>
									<br>
									<code>
										<abbr title="Selection CSS Class">.example_selection_css_class</abbr> {
											font-weight: bold;
										}
									</code>
									<br>
									<code>
										<abbr title="Dropdown CSS Class">.example_dropdown_css_class</abbr> {
											font-weight: bold;
										}
									</code>
									<br>
									<p class="description">
										Example to make placeholder text color black.
									</p>
									<code>
										<abbr title="Selection CSS Class">.example_selection_css_class</abbr> .select2-selection__placeholder {
											color: black;
										}
									</code>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<button id="save_settings_btn" type="button" class="button button-primary" onclick="SaveSettings();">
										Save Changes
									</button>
									<span title="Saving Settings..." class="spinner save_settings_spinner" style="float:none;"></span>
								
								</th>
								<td>
									<span id="ajax_result">
										
									</span>
								</td>
							</tr>
						</tbody>
					</table>
					
					
					<div>
						
						<hr>
						Do you have questions about or feedback on making our plugins even better? 
						We want to hear from you. 
						Please contact us on our site here: 
						<a href="https://www.inboundhorizons.com/wordpress-plugin-feedback/" target="_blank">
							Plugin Feedback Form
						</a>
						
					</div>
					
					<script>
					
					
					
					
					
						function SaveSettings() {
							
							jQuery("#save_settings_btn").prop("disabled", true);	// Disable the button
							jQuery(".save_settings_spinner").addClass("is-active");	// Display spinner
							
							var post_data = {
								"action": "SDFBCFW_SAVE_SETTINGS",
								
								hide_empty: jQuery("#hide_empty").val(),
								"hide_categories[]": jQuery("#hide_categories").val(),
								show_count: jQuery("#show_count").val(),
								placeholder: jQuery("#placeholder").val(),
								allow_clearing: jQuery("#allow_clearing").val(),
								
								parent_css_class: jQuery("#parent_css_class").val(),
								selection_css_class: jQuery("#selection_css_class").val(),
								dropdown_css_class: jQuery("#dropdown_css_class").val(),
								custom_css_rules: css_editor.codemirror.getValue(),
								
								nonce: jQuery("#nonce").val(),
							};
						
							jQuery.post(ajaxurl, post_data, function(response) {
								
								var html = "";
								
								if (response.ok == 0) {
									html += \'<div class="notice notice-error is-dismissible">\';
										html += \'<p><b>Something went wrong.</b> Please reload the page and try again.</p>\';
										html += \'<button type="button" class="notice-dismiss" onclick="jQuery(this).parent().remove();">\';
											html += \'<span class="screen-reader-text">Dismiss this notice.</span>\';
										html += \'</button>\';
									html += \'</div>\';
								}
								else {
									html += \'<div class="notice notice-success is-dismissible">\';
										html += \'<p><b>Success!</b> Settings saved successfully.</p>\';
										html += \'<button type="button" class="notice-dismiss" onclick="jQuery(this).parent().remove();">\';
											html += \'<span class="screen-reader-text">Dismiss this notice.</span>\';
										html += \'</button>\';
									html += \'</div>\';
								}
								
								
								jQuery("#ajax_result").html(html);
								
								jQuery("#save_settings_btn").prop("disabled", false);	// Enable the button
								jQuery(".save_settings_spinner").removeClass("is-active");	// Hide spinner
							});
							
						}
						
						
						var css_editor;
						(function($){
							$(function(){									
								if($("#custom_css_rules").length) {
									var editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};
									editorSettings.codemirror = _.extend(
										{},
										editorSettings.codemirror,
										{
											indentUnit: 2,
											tabSize: 4,
											mode: "css",
										}
									);
									css_editor = wp.codeEditor.initialize($("#custom_css_rules"), editorSettings);
								}
							});
						 })(jQuery);
					
					</script>
					
					
					
					<style>
					
						abbr {
							text-decoration: underline dotted;
							text-underline-offset: 2px;
						}
					
						#wpcontent {
							padding-left: 0 !important;
						}
					
						#wpcontent img {
							max-width:100%; 
							vertical-align: middle; 
							box-shadow: 0px 0px 10px #003165;
						}
						
						.wrap {
							margin: auto;
							max-width: 60rem;
						}
						
						.panel {
							background-color: #fff;
							box-sizing: border-box;
							white-space: normal;
						}
						
						.panel-margin {
							margin-bottom: 20px;
						}
						
						.panel-body {
							padding: 10px;
						}
					</style>
				
				</div>
			';
		}
		
		private function GetDefaultSettings($settings) {
			
			$settings['hide_empty'] = isset($settings['hide_empty']) ? boolval($settings['hide_empty']) : true;
			$settings['show_count'] = isset($settings['show_count']) ? boolval($settings['show_count']) : true;
			$settings['placeholder'] = isset($settings['placeholder']) ? sanitize_text_field($settings['placeholder']) : "Filter by Category";
			$settings['allow_clearing'] = isset($settings['allow_clearing']) ? boolval($settings['allow_clearing']) : true;
			$settings['parent_css_class'] = isset($settings['parent_css_class']) ? sanitize_text_field($settings['parent_css_class']) : "";
			$settings['selection_css_class'] = isset($settings['selection_css_class']) ? sanitize_text_field($settings['selection_css_class']) : "";
			$settings['dropdown_css_class'] = isset($settings['dropdown_css_class']) ? sanitize_text_field($settings['dropdown_css_class']) : "";
			$settings['custom_css_rules'] = isset($settings['custom_css_rules']) ? sanitize_textarea_field($settings['custom_css_rules']) : "";
			
			$settings['hide_categories'] = isset($settings['hide_categories']) ? ($settings['hide_categories']) : array();
			foreach ($settings['hide_categories'] as $indx => $val) {
				$settings['hide_categories'][$indx] = sanitize_textarea_field($settings['hide_categories'][$indx]);
			}
			
			return ($settings);
		}
		
		public function DisplayCategoryDropdownWidget() {
			global $wp_query, $post;
			
			// Helpful links
			// https://select2.org/
			
			// https://developer.wordpress.org/reference/functions/the_widget/
			// https://woocommerce.github.io/code-reference/classes/WC-Widget-Product-Categories.html
			// /wp-content/plugins/woocommerce/includes/widgets/class-wc-widget-product-categories.php
			//the_widget( 'WC_Widget_Product_Categories', 'dropdown=1' );
			
			
			
			// Get the settings
			$settings = get_option('sdfbcfw_settings', array());
			$settings = $this->GetDefaultSettings($settings);
			
			$hide_empty = $settings['hide_empty'] ? 1 : 0;
			$hide_categories = $settings['hide_categories'];
			$show_count = $settings['show_count'] ? 1 : 0;
			$placeholder = $settings['placeholder'];
			$parent_css_class = $settings['parent_css_class'];
			$selection_css_class = $settings['selection_css_class'];
			$dropdown_css_class = $settings['dropdown_css_class'];
			$css = $settings['custom_css_rules'];
			$allow_clearing = ($settings['allow_clearing']) ? 'true' : 'false';
			
			
			// Get the current category if one is selected
			$selected_slug = '';
			if (is_tax('product_cat')) {
				$current_category = $wp_query->queried_object;
				if (is_object($current_category) && isset($current_category->slug)) {
					$selected_slug = $current_category->slug;
				}
			} 
			elseif (is_singular('product')) {
				$terms = wc_get_product_terms(
					$post->ID,
					'product_cat',
					apply_filters(
						'woocommerce_product_categories_widget_product_terms_args',
						array(
							'orderby' => 'parent',
							'order'   => 'ASC',
						)
					)
				);
				
				if ($terms) {
					$current_category = apply_filters('woocommerce_product_categories_widget_main_term', $terms[0], $terms);
					
					if (is_object($current_category) && isset($current_category->slug)) {
						$selected_slug = $current_category->slug;
					}
				}
			}
				
				
				
			$exclude_term_IDs = get_terms( 
				array(
					'slug'     => $hide_categories, 
					'taxonomy' => 'product_cat',
					'fields'   => 'ids',
				)
			);
			
			// https://developer.wordpress.org/reference/functions/wp_dropdown_categories/
			$dropdown_args = array(
				'echo' => 0,
				
				'pad_counts'         => 1,
				'show_count'         => $show_count,
				'hierarchical'       => 1,
				'hide_empty'         => $hide_empty,
				'show_uncategorized' => 0,
				'orderby'            => 'name',
				'selected'           => $selected_slug,
				'show_option_none'   => $placeholder,
				'option_none_value'  => '',
				'value_field'        => 'slug',
				'taxonomy'           => 'product_cat',
				'name'               => '',
				'class'              => 'filter_categories',
				'id'              	 => 'filter_categories',
				//'exclude'            => $exclude_term_IDs,
			);
			
			// Only exclude if there is something to exclude. Otherwise everything is excluded.
			if (count($hide_categories) > 0) {
				$dropdown_args['exclude'] = $exclude_term_IDs;
			}
			
			$allowed_dropdown_tags = array(
				'select' => array('id' => array(), 'class' => array()), 
				'option' => array('class' => array(), 'value' => array(), 'selected' => array())
			);
			
			
			
			$dropdown_html = wp_dropdown_categories($dropdown_args);
			
			echo '
				<span class="woocommerce-ordering sdfbcfw-woocommerce-ordering '.esc_attr($parent_css_class).'">
					<div class="widget woocommerce widget_product_categories">
						'.wp_kses($dropdown_html, $allowed_dropdown_tags).'
					</div>
				</span>
			';
			
			
			
			
			
			//selectionCssClass: "'.esc_attr($selection_css_class).'",
			wp_enqueue_script('selectWoo');
			wp_enqueue_style('select2');
			
			wc_enqueue_js('
			
				jQuery("#filter_categories").selectWoo({
					dropdownAutoWidth: true,
					placeholder: "'.esc_attr($placeholder).'",
					allowClear: '.esc_attr($allow_clearing).',
					containerCssClass: "'.esc_attr($selection_css_class).'",
					selectionCssClass: "'.esc_attr($selection_css_class).'",
					dropdownCssClass: "'.esc_attr($dropdown_css_class).'",
					language: {
						noResults: function() {
							return "' . esc_js(_x('No matches found', 'enhanced select', 'woocommerce')) . '";
						}
					},
				}).on("change", function() {
				
					if (jQuery(this).val() != "") {
						var this_page = "";
						var home_url  = "' . esc_js(home_url('/')) . '";
						if ( home_url.indexOf("?") > 0 ) {
							this_page = home_url + "&product_cat=" + jQuery(this).val();
						} 
						else {
							this_page = home_url + "?product_cat=" + jQuery(this).val();
						}
						location.href = this_page;
					} 
					else {
						location.href = "' . esc_js(wc_get_page_permalink('shop')) . '";
					}
					
				});
				
			');
			
			
			// Output the default CSS
			// This resets a couple things so that it doesn't look ugly when it first loads
			$default_css = '
				.woocommerce-ordering.sdfbcfw-woocommerce-ordering{margin-left:10px; margin-right:10px;}
				.woocommerce-page .select2-container .select2-selection {height:initial; padding:initial;}
				.woocommerce-page .select2-container .select2-selection .select2-selection__arrow {width:20px; height:100%;}
				.woocommerce-page .select2-container .select2-selection .select2-selection__arrow {padding:initial;}
				.woocommerce-page .select2-container .select2-dropdown {padding:initial;}
				.woocommerce-page .select2-container .select2-search__field {padding:initial;}
			';
			wp_register_style('sdfbcfw-default-selectwoo', false);
			wp_enqueue_style('sdfbcfw-default-selectwoo');
			wp_add_inline_style('sdfbcfw-default-selectwoo', $default_css);
		
			
			// Output the custom CSS
			wp_register_style('sdfbcfw-custom-selectwoo', false);
			wp_enqueue_style('sdfbcfw-custom-selectwoo');
			wp_add_inline_style('sdfbcfw-custom-selectwoo', $css);

		}

		public function RemoveCategoryWidgetTitle($title, $widget_instance = '', $widget_id = '') {

			if ($widget_id === 'woocommerce_product_categories') {
				$title = '';
			}
			
			return ($title);
		}
		

	}
	
	SDFBCFW_SIMPLE_CATEGORY_FILTER_FOR_WOOCOMMERCE::Instantiate();	// Instantiate an instance of the class
	
	
	