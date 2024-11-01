<?php

/**
 * WP Member Login by SPIRAL.
 *
 * @package   Spiral_Member_Login
 * @author    PIPED BITS Co.,Ltd.
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;
if (!class_exists('WPMLS_Spiral_Member_Login')) :
	/**
	 * Plugin class.
	 *
	 * @package WPMLS_Spiral_Member_Login
	 * @author  PIPED BITS Co.,Ltd.
	 */
	class WPMLS_Spiral_Member_Login extends WPMLS_Spiral_Member_Login_Base
	{

		/**
		 * Plugin version
		 *
		 * @since   2.0.0
		 *
		 * @const     string
		 */
		const version = '2.0.0';

		/**
		 * Plugin slug
		 *
		 * @since   2.0.0
		 * @var     string
		 */
		protected $plugin_slug = 'spiral-member-login';

		/**
		 * Holds options key
		 *
		 * @access protected
		 * @var string
		 */
		protected $options_key = 'spiral_member_login';

		/**
		 * Unique identifier for your plugin.
		 *
		 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
		 * match the Text Domain file header in the main plugin file.
		 *
		 * @since    1.0.0
		 *
		 * @const      string
		 */
		const domain = 'spiral-member-login';

		/**
		 * Instance of this class.
		 *
		 * @since    1.0.0
		 *
		 * @var      object
		 */
		protected static $instance = null;

		/**
		 * Slug of the plugin screen.
		 *
		 * @since    1.0.0
		 *
		 * @var      string
		 */
		protected $plugin_screen_hook_suffix = null;

		/**
		 * Holds errors object
		 *
		 * @access public
		 * @var object
		 */
		public $errors;

		/**
		 * Holds current page being requested
		 *
		 * @access public
		 * @var string
		 */
		public $request_page;

		/**
		 * Holds current action being requested
		 *
		 * @access public
		 * @var string
		 */
		public $request_action;

		/**
		 * Holds current template being requestedif ( ! defined( 'ABSPATH' ) ) exit;
		 * @access public
		 * @var int
		 */
		public $request_template_num;

		/**
		 * Holds loaded template instances
		 *
		 * @access protected
		 * @var array
		 */
		protected $loaded_templates = array();

		/**
		 * WP Session for SML
		 */
		public $session;

		/**
		 * SPIRAL API
		 */
		public $spiral;

		public $hasher;

		/**
		 * Initialize the plugin by setting localization, filters, and administration functions.
		 *
		 */
		private function __construct()
		{
			$this->load_options();
			$this->load_template();
			$this->load_plugin_textdomain();
			// wp actions
			add_action('init', [$this, 'init']);
			add_action('admin_init', [$this, 'admin_init']);
			add_action('admin_menu', [$this, 'admin_menu']);
			add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
			add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
			add_action('admin_post_switch_version_form_action', [$this, 'wpmls_switch_version_form']);
			add_action('admin_post_clear_setting_action', [$this, 'wpmls_clear_setting_form']);
			add_action('admin_post_clear_cache_action', [$this, 'wpmls_clear_cache_form']);
			// style
			add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
			add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
			add_action('widgets_init', [$this, 'widgets_init']);
			add_action('wp', [$this, 'wp']);
			add_action('template_redirect', [$this, 'template_redirect']);
			add_action('wp_head', [$this, 'wp_head']);
			add_action('wp_footer', [$this, 'wp_footer']);
			add_action('wp_print_footer_scripts', [$this, 'wp_print_footer_scripts']);
			// wp filters
			add_filter('wp_setup_nav_menu_item', [$this, 'wp_setup_nav_menu_item']);
			add_filter('wp_list_pages_excludes', [$this, 'wp_list_pages_excludes']);
			add_filter('page_link', [$this, 'page_link'], 10, 2);
			// wp shortcodes
			add_shortcode('sml-show-template', [$this, 'shortcode_show_template']);
			add_shortcode('sml-is-logged-in', [$this, 'shortcode_is_logged_in']);
			add_shortcode('sml-is-logged-mypage', [$this, 'shortcode_mypage_url']);
			add_shortcode('sml-is-logged-in-hide', [$this, 'shortcode_is_logged_in_hide']);
			add_shortcode('sml-user-prop', [$this, 'shortcode_user_prop']);
			add_shortcode('sml-is-logged-in-type', [$this, 'shortcode_is_logged_in_type']);
			add_shortcode('sml-is-logged-in-rule', [$this, 'shortcode_is_logged_in_rule']);
			add_shortcode('sml-link', [$this, 'shortcode_user_link']);

			// setup session
			$this->session = new WPMLS_Spiral_Member_Login_Session();

			$this->hasher = new WPMLS_Password_Hash();
			if ($this->is_settings_imcomplete()) {
				return null;
			}
			$api_token_key =  $this->hasher->wpmls_decrypt_setting_field($this->get_option('api_token'), SECURE_AUTH_KEY);
			$api_token_secret_key =  $this->hasher->wpmls_decrypt_setting_field($this->get_option('api_token_secret'), SECURE_AUTH_KEY);
			$db_title = $this->get_option('member_db_title');
			$member_identification_key = $this->get_option('member_identification_key');
			$this->spiral = new WPMLS_Spiral_Api($api_token_key, $api_token_secret_key, $db_title, $member_identification_key);
		}

		/**
		 * Register the administration menu for this plugin into the WordPress Dashboard menu.
		 *
		 * @since    1.0.0
		 */
		public function admin_menu()
		{
			$this->plugin_screen_hook_suffix = add_options_page(
				__('WP Member Login by SPIRAL', 'spiral-member-login'),
				__('WP Member Login by SPIRAL', 'spiral-member-login'),
				'read',
				$this->options_key,
				array($this, 'display_plugin_admin_page')
			);
			add_settings_section('api', __('API Agent API Key', 'spiral-member-login'), '__return_false', $this->options_key);
			add_settings_section('auth', __('Authentication Setting', 'spiral-member-login'), '__return_false', $this->options_key);
			add_settings_section('userprop', __('Member Information Setting', 'spiral-member-login'), '__return_false', $this->options_key);
			add_settings_section('link', __('Each Link Setting', 'spiral-member-login'), '__return_false', $this->options_key);
			add_settings_section('logout', __('Page URL After Logout Setting', 'spiral-member-login'), '__return_false', $this->options_key);
			add_settings_section('web', __('Relate Web no Ashiato', 'spiral-member-login'), '__return_false', $this->options_key);

			// api
			add_settings_field('api_token', __('API Key', 'spiral-member-login'), [$this, 'settings_field_api_token'], $this->options_key, 'api', ["class" => "basic-config-label api-token"]);
			add_settings_field('api_token_secret', __('API Token Secret', 'spiral-member-login'), [$this, 'settings_field_api_token_secret'], $this->options_key, 'api', ["class" => "basic-config-label"]);

			// auth
			add_settings_field('member_db_title', __('DB Title', 'spiral-member-login'), [$this, 'settings_field_member_db_title'], $this->options_key, 'auth', ["class" => "advance-config hidden"]);
			add_settings_field('wpmls_member_identification_key', __('My Area Identification Key', 'spiral-member-login'), [$this, 'settings_field_member_identification_key'], $this->options_key, 'auth', ["class" => "advance-config hidden"]);
			add_settings_field('area_title', __('My Area Title', 'spiral-member-login'), [$this, 'settings_field_area_title'], $this->options_key, 'auth', ["class" => "advance-config hidden"]);
			add_settings_field('wpmls_auth_form_url', __('Authentication Form URL', 'spiral-member-login'), [$this, 'settings_field_auth_form_url'], $this->options_key, 'auth', ["class" => "basic-config-label"]);

			// userprop
			add_settings_field('member_list_search_title', __("Member List's Search Title", "spiral-member-login"), [$this, 'settings_field_member_list_search_title'], $this->options_key, 'userprop', ["class" => "advance-config hidden"]);
			add_settings_field('default_name_key', __('Default Name Key', 'spiral-member-login'), [$this, 'settings_field_default_name_key'], $this->options_key, 'userprop', ["class" => "basic-config-label"]);
			add_settings_field('wpmls_login_id_label', __('ログインフォームのIDラベル', 'spiral-member-login'), [$this, 'settings_field_login_id_label'], $this->options_key, 'userprop', ["class" => "basic-config-label"]);

			// link
			add_settings_field('register_url', __('Register URL', 'spiral-member-login'), [$this, 'settings_field_register_url'], $this->options_key, 'link', ["class" => "basic-config-label link-setting"]);
			add_settings_field('lostpassword_url', __('Lost Password URL', 'spiral-member-login'), [$this, 'settings_field_lostpassword_url'], $this->options_key, 'link', ["class" => "basic-config-label link-setting"]);
			add_settings_field('profile_page_id', __('Profile Page ID', 'spiral-member-login'), [$this, 'settings_field_profile_page_id'], $this->options_key, 'link', ["class" => "basic-config-label link-setting"]);
			add_settings_field('resetpass_page_id', __('Reset Password Page ID', 'spiral-member-login'), [$this, 'settings_field_resetpass_page_id'], $this->options_key, 'link', ["class" => "basic-config-label link-setting"]);
			add_settings_field('withdrawal_page_id', __('Withdrawal Page ID', 'spiral-member-login'), [$this, 'settings_field_withdrawal_page_id'], $this->options_key, 'link', ["class" => "basic-config-label link-setting"]);

			// Logout
			add_settings_field('wpmls_logout_url', __('URL After Logout', 'spiral-member-login'), [$this, 'settings_field_logout_url'], $this->options_key, 'logout', ["class" => "advance-config hidden"]);
			// Web section
			add_settings_field('is_enable', __('Use this Function', 'spiral-member-login'), [$this, 'settings_field_is_enable'], $this->options_key, 'web', ["class" => "advance-config"]);
			add_settings_field('param_name', __('Parameter Name', 'spiral-member-login'), [$this, 'settings_field_param_name'], $this->options_key, 'web', ["class" => "advance-config"]);
			add_settings_field('wpmls_filed_name', __('Field Name', 'spiral-member-login'), [$this, 'settings_field_filed_name'], $this->options_key, 'web', ["class" => "advance-config"]);
		}

		function wpmls_clear_setting_form()
		{
			if (!isset($_POST['wpmls_clear_setting_nonce']) || !wp_verify_nonce(
				sanitize_text_field(wp_unslash($_POST['wpmls_clear_setting_nonce'])),
				'wpmls_clear_setting_nonce'
			)) {
				wp_die(__('Nonce Failed.', 'spiral-member-login'));
			}
			$this->options = self::default_options();
			update_option('spiral_member_login', null);
			$this->session->wpmls_remove_session_and_db_catches();
			wp_redirect('options-general.php?page=spiral_member_login&setup=true');
			exit();
		}

		function wpmls_clear_cache_form()
		{
			// wp_nonce verification
			if (!isset($_POST['wpmls_clear_cache_nonce']) || !wp_verify_nonce(
				sanitize_text_field(wp_unslash($_POST['wpmls_clear_cache_nonce'])),
				'wpmls_clear_cache_nonce'
			)) {
				wp_die(__('Nonce Failed.', 'spiral-member-login'));
			}
			// Clear Spiral V1 cache
			$sml_sid = $this->hasher->decrypt_key($this->session->get('sml_sid'), SECURE_AUTH_KEY);
			$area_title = $this->get_option('area_title');
			if ($sml_sid) {
				$this->spiral->logout_area($area_title, $sml_sid);
				$this->session->wpmls_remove_session_and_db_catches();
			}
			$this->session->wpmls_remove_session_and_db_catches();
			wp_redirect('options-general.php?page=spiral_member_login&status=true');
			exit();
		}

		function wpmls_switch_version_form()
		{
			$version = isset($_POST['show_on_front']) ? sanitize_text_field($_POST['show_on_front']) : '';

			if (empty($version) || !in_array($version, array("1", "2"))) {
				wp_die("不正な値です");
			}
			// wp_nonce verification
			if (!isset($_POST['spiral_version']) || !wp_verify_nonce(
				sanitize_text_field(wp_unslash($_POST['spiral_version'])),
				'spiral_version'
			)) {
				print 'Verification failed.';
				exit;
			} else {
				//Delete Old Setting
				update_option('is_setup', false);
				update_option('version', $version);

				$setting_version = get_option('version');
				if ($setting_version == 1) {
					wp_redirect('options-general.php?page=spiral_member_login&version=1');
					exit();
				}
				if ($setting_version == 2) {
					wp_redirect('options-general.php?page=spiral_member_login&version=2');
					exit();
				}
			}
		}

		/************************************************************************************************************************
		 * Hooks
		 ************************************************************************************************************************/

		/**
		 * Fired when the plugin is activated.
		 *
		 * @since    1.0.0
		 *
		 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
		 */
		public static function activate($network_wide)
		{
		}

		/**
		 * Fired when the plugin is deactivated.
		 *
		 * @since    1.0.0
		 *
		 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
		 */
		public static function deactivate($network_wide)
		{
		}

		/**
		 * Uninstall hook
		 *
		 * @access public
		 */
		public static function uninstall()
		{
			global $wpdb;

			if (is_multisite()) {
				$get_networkwide = self::get_query_param('networkwide');
				if (!is_null($get_networkwide) && ($get_networkwide == 1)) {
					$blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
					foreach ($blogids as $blog_id) {
						switch_to_blog($blog_id);
						self::_uninstall();
					}
					restore_current_blog();
					return;
				}
			}
			self::_uninstall();
		}


		/************************************************************************************************************************
		 * Actions
		 ************************************************************************************************************************/

		/**
		 * Initilizes the plugin
		 *
		 * @since    1.0.0
		 */
		public function init()
		{
			$this->errors = new WP_Error();
		}

		/**
		 * Register plugin's setting and Install
		 *
		 * @since    1.0.0
		 */
		public function admin_init()
		{
			register_setting($this->options_key, $this->options_key, [$this, 'wpmls_save_settings']);
			if (version_compare($this->get_option('version', 0), self::version, '<')) {
				$this->install();
			}
		}

		/**
		 * Register and enqueue admin-specific style sheet.
		 *
		 *
		 * @return    null    Return early if no settings page is registered.
		 */
		public function enqueue_admin_styles()
		{
			if (!isset($this->plugin_screen_hook_suffix)) {
				return;
			}

			$screen = get_current_screen();
			if ($screen->id == $this->plugin_screen_hook_suffix) {
				wp_enqueue_style($this->plugin_slug . '-admin-styles', plugins_url('css/admin.css', __DIR__), false, self::version);
			}
		}

		/**
		 * Register and enqueue admin-specific JavaScript.
		 *
		 * @return    null    Return early if no settings page is registered.
		 */
		public function enqueue_admin_scripts()
		{
			if (!isset($this->plugin_screen_hook_suffix)) {
				return;
			}

			$screen = get_current_screen();
			if ($screen->id == $this->plugin_screen_hook_suffix) {
				wp_enqueue_script($this->plugin_slug . '-admin-script', plugins_url('js/admin.js', __DIR__), array('jquery'), self::version);
			}
		}
		/**
		 * Register and enqueue public-facing style sheet.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_styles()
		{
			wp_enqueue_style($this->plugin_slug . '-plugin-styles', plugins_url('css/public.css', __FILE__), false, self::version);
		}

		/**
		 * Register and enqueues public-facing JavaScript files.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_scripts()
		{
			wp_enqueue_script($this->plugin_slug . '-plugin-script', plugins_url('js/public.js', __FILE__), array('jquery'), self::version);
		}

		public function settings_field_is_enable()
		{
			$is_enable  = $this->get_option('related_web') ? $this->get_option('related_web')['is_enable'] : false;
			$is_checked = $is_enable ? 'checked' : '';
?>
			<div>
				<input id="is_enable" type="checkbox" name="spiral_member_login[is_enable]" type="text" class="sml_url_field sml_member_logout_url_field advance-config" value="<?php echo esc_attr($is_enable); ?>" <?php echo esc_attr($is_checked); ?> />
			</div>
			<script>
				const cb = document.querySelector("#is_enable");

				cb.addEventListener("click", function() {
					if (cb.checked) {
						cb.value = 1;
					} else {
						cb.value = 0;
					}
				});
			</script>
		<?php
		}
		public function settings_field_param_name()
		{
			$param_name = isset($this->get_option('related_web')['atts']) ? $this->get_option('related_web')['atts']['param_name'] : '';
		?>
			<div>
				<div class="" id="web_id">
					<input name="spiral_member_login[param_name]" type="text" class="sml_login_id_label_jp basic_config" value="<?php echo esc_attr($param_name); ?>" />
				</div>
			</div>
		<?php
		}
		public function settings_field_filed_name()
		{
			$field_name = isset($this->get_option('related_web')['atts']) ? $this->get_option('related_web')['atts']['field_name'] : '';
		?>
			<div>
				<div class="" id="web_id">
					<input name="spiral_member_login[field_name]" type="text" class="sml_login_id_label_en basic_config" value="<?php echo esc_attr($field_name); ?>" />
				</div>
			</div>

		<?php
		}


		/**
		 * Registers the widget
		 *
		 * @access public
		 */
		public function widgets_init()
		{
			if (class_exists('WPMLS_Spiral_Member_Login_Widget')) {
				register_widget('WPMLS_Spiral_Member_Login_Widget');
			}
		}

		/**
		 * Used to add/remove filters from login page
		 *
		 * @access public
		 */
		public function wp()
		{
			if (self::is_sml_page()) {
				do_action('login_init');

				remove_action('wp_head', 'feed_links', 2);
				remove_action('wp_head', 'feed_links_extra', 3);
				remove_action('wp_head', 'rsd_link');
				remove_action('wp_head', 'wlwmanifest_link');
				remove_action('wp_head', 'parent_post_rel_link', 10);
				remove_action('wp_head', 'start_post_rel_link', 10);
				remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
				remove_action('wp_head', 'rel_canonical');

				// Don't index any of these forms
				add_action('login_head', 'wp_no_robots');

				if (force_ssl_admin() && !is_ssl()) {
					if (0 === strpos(WPMLS_Spiral_Member_Login::get_current_path(), 'http')) {
						wp_redirect((preg_replace('|^http://|', 'https://', WPMLS_Spiral_Member_Login::get_current_path())));
						exit;
					} else {
						wp_redirect(esc_url('https://' . getenv('HTTP_HOST') . WPMLS_Spiral_Member_Login::get_current_path()));
						exit;
					}
				}
			}
		}

		/**
		 * Proccesses the request
		 *
		 * Callback for "template_redirect" hook in template-loader.php
		 *
		 * @access public
		 */
		public function template_redirect()
		{
			$this->request_action = isset($_REQUEST['action']) ? sanitize_key($_REQUEST['action']) : '';
			if (!$this->request_action && self::is_sml_page()) {
				$this->request_action = self::get_page_action(get_the_ID());
			}
			$this->request_template_num = isset($_REQUEST['template_num']) ? sanitize_key($_REQUEST['template_num']) : 0;
			if ($this->is_settings_imcomplete()) {
				if ($this->request_action) {
					wp_redirect(esc_url(get_home_url('/')));
					exit;
				}
				return;
			}

			do_action_ref_array('sml_request', array(&$this));

			if (has_action('sml_request_' . $this->request_action)) {
				do_action_ref_array('sml_request_' . $this->request_action, array(&$this));
			} else {
				switch ($this->request_action) {
					case 'logout':
						$session_id = $this->session->get_id();
						$sml_sid = $this->hasher->decrypt_key($this->session->get('sml_sid'), SECURE_AUTH_KEY);
						$area_title = $this->get_option('area_title');
						if ($sml_sid) {
							$result = $this->spiral->logout_area($area_title, $sml_sid);
							$this->session->wpmls_remove_session_and_db_catches();
						}

						if ($this->get_option('member_logout_url')) {
							$logout_setting_url = $this->get_option('member_logout_url');
							wp_redirect(esc_url($logout_setting_url));
							exit;
						}
						wp_safe_redirect(wp_get_referer());
						exit;
					case 'register':
						$page_id = $this->get_id_from_url(WPMLS_Spiral_Member_Login::get_current_url());
						if (!is_null($page_id)) {
							$session_id = $this->session->get_id();
							$sml_sid = $this->hasher->decrypt_key($this->session->get('sml_sid'), SECURE_AUTH_KEY);
							$option_name = 'wpmls_' . $session_id . '_' . $page_id;

							if (get_transient($option_name)) {
								$shortcode_mypage_url = get_transient($option_name);
								$param = $this->get_param_from_url(WPMLS_Spiral_Member_Login::get_current_url());
								if (!is_null($param)) {
									wp_redirect($this->hasher->decrypt_key($shortcode_mypage_url, SECURE_AUTH_KEY) . '&' . $param);
									exit;
								}
								wp_redirect(($this->hasher->decrypt_key($shortcode_mypage_url, SECURE_AUTH_KEY)));
								exit;
							} else {

								$area_title = $this->get_option('area_title');
								$result = $this->spiral->get_area_mypage($area_title, $sml_sid, $page_id);

								if (is_null($result)) {
									wp_redirect(esc_url(get_home_url('/')));
									exit;
								} else {
									set_transient($option_name, $this->hasher->encrypt_key($result, SECURE_AUTH_KEY), 3600);
									if (isset($param)) {
										wp_redirect($result . '&' . $param);
										exit;
									}
									wp_redirect($result);
									exit;
								}
							}
						}
						if ($register_url = $this->get_option('register_url')) {
							wp_redirect(esc_url($register_url));
							exit;
						} else {
							wp_redirect(esc_url(get_home_url('/')));
							exit;
						}
						break;
					case 'lostpassword':
						if ($lostpassword_url = $this->get_option('lostpassword_url')) {
							wp_redirect(esc_url($lostpassword_url));
							exit;
						} else {
							wp_redirect(esc_url(get_home_url('/')));
							exit;
						}
						break;
					case 'resetpass':
					case 'withdrawal':
						$this->get_area_mypage_url($this->request_action);
						break;
					case 'profile':
						$this->get_area_mypage_url($this->request_action);
						break;
					case 'login':
					default:
						$form_data = $this->handle_post_form();

						if (!is_null($form_data)) {
							if (!isset($form_data['is_error'])) {
								$this->handle_successful_login($form_data);
							} elseif ($form_data['is_error']) {
								$this->handle_login_error($form_data);
							}

							if (!$this->is_logged_in()) {
								$this->handle_non_logged_in_state();
							}

							if ($this->is_loggedout()) {
								$this->handle_logged_out_state();
							} elseif ($this->is_expired()) {
								$this->handle_expired_state();
							}
						}
						break;
				} // end switch
			}
		}

		private function handle_successful_login($form_data)
		{
			$this->regenerate_session($form_data);
			update_option('wpmls_clear_cached', "unclear");

			$is_enable = $this->get_option('related_web')['is_enable'];
			$param_name = $this->get_option('related_web')['atts']['param_name'];
			$field_name = $this->get_option('related_web')['atts']['field_name'];

			$url = $this->remove_message_query_param($form_data['redirect_to']);
			$param_exist = (strpos($url, "?") !== false) ? "&" : "?";
			$param = null;

			if ($is_enable == '1') {
				$this->spiral->set_users($this->hasher->decrypt_key($this->session->get('login_id'), SECURE_AUTH_KEY));
				$user_data = $this->spiral->get_users_data($field_name);
				$param = $param_exist . $param_name . '=' . $user_data;
			}

			$current_page_url = $url . $param;
			wp_redirect($current_page_url);
			exit;
		}

		private function handle_login_error($form_data)
		{
			$error_code = $form_data['code'];
			$error_messages = [
				10 => 'error_occurred',
				20 => 'username_failed',
				21 => 'password_failed',
				30 => 'auth_failed',
				121 => 'pass_failed'
			];

			$url = $this->remove_message_query_param($form_data['redirect_to']);
			$param_exist = (strpos($url, "?") !== false) ? "&" : "?";
			$message_param = isset($error_messages[$error_code]) ? $error_messages[$error_code] : '';

			wp_redirect($url . $param_exist . "message=$message_param");
			exit;
		}

		private function handle_non_logged_in_state()
		{
			if (self::is_member_page(get_the_ID())) {
				// Redirect to login page for member page
				$args = [
					'memberpage' => 'true',
					'redirect_to' => self::get_current_path()
				];
				wp_redirect(esc_url(self::get_page_link('login', $args)));
				exit;
			}
		}

		private function handle_logged_out_state()
		{
			$session_id = $this->session->get_id();
			$this->session->wpmls_remove_session_and_db_catches();
			$this->errors->add('loggedout', __('You are now logged out.'), 'message');
		}

		private function handle_expired_state()
		{
			$this->session->wpmls_remove_session_and_db_catches();
			$this->errors->add('expired', __('Session expired. Please log in again. You will not move away from this page.'), 'message');
		}

		public function get_query_param($name)
		{
			if (!isset($name)) {
				return null;
			}
			$current_url = WPMLS_Spiral_Member_Login::get_current_url();
			$parsed_url = parse_url($current_url);

			if (!isset($parsed_url['query'])) {
				return null; // No query string
			}
			$query_args = wp_parse_args($parsed_url['query']);
			return (isset($query_args[$name])) ? $query_args[$name] : null;
		}
		
		function is_loggedout()
		{
			$current_url = WPMLS_Spiral_Member_Login::get_current_url();
			if (!$current_url) {
				return false; // Handle invalid URL
			}

			$parsed_url = parse_url($current_url);
			if (!isset($parsed_url['query'])) {
				return false; // No query string
			}
			$query_args = wp_parse_args($parsed_url['query']);
			return (isset($query_args['loggedout']) && ($query_args['loggedout'] == true)) ? true : false;
		}

		function is_expired()
		{
			$current_url = WPMLS_Spiral_Member_Login::get_current_url();
			if (!$current_url) {
				return false; // Handle invalid URL
			}

			$parsed_url = parse_url($current_url);
			if (!isset($parsed_url['query'])) {
				return false; // No query string
			}
			$query_args = wp_parse_args($parsed_url['query']);
			return (isset($query_args['expired']) && ($query_args['expired'] == true)) ? true : false;
		}
		function get_id_from_url($url)
		{
			if (!$url) {
				return null; // Handle invalid URL
			}

			$parsed_url = parse_url($url);
			if (!isset($parsed_url['query'])) {
				return null; // No query string
			}

			$query_args = wp_parse_args($parsed_url['query']);
			return isset($query_args['id']) ? $query_args['id'] : null;
		}

		function get_param_from_url($param)
		{
			if (!$param) {
				return null; // Handle invalid URL
			}

			$parsed_url = parse_url($param);
			if (!isset($parsed_url['query'])) {
				return null; // No query string
			}

			$query_args = wp_parse_args($parsed_url['query']);
			return isset($query_args['param']) ? $query_args['param'] : null;
		}

		private function regenerate_session($form_data)
		{
			$sml_sid    = $this->hasher->encrypt_key($form_data['sml_sid'], SECURE_AUTH_KEY);
			$login_id   = $this->hasher->encrypt_key($form_data['login_id'], SECURE_AUTH_KEY);
			$this->session->regenerate_id(true);
			$this->session->set('sml_sid', $sml_sid);
			$this->session->set('login_id', $login_id);
		}

		private function handle_post_form()
		{
			// Check if it's a POST request
			if ($_SERVER['REQUEST_METHOD'] === 'POST') {

				// Validate site and redirect_to
				$maxLength = 8177;
				if (strlen($_POST['site']) > $maxLength || strlen($_POST['redirect_to']) > $maxLength) {
					wp_safe_redirect(home_url()."/?message=unauthorized");
					exit;
				}

				// Validate login_id
				$maxLength = 129;
				if (strlen($_POST['login_id']) > $maxLength) {
					wp_safe_redirect(home_url()."/?message=unauthorized");
					exit;
				}

				// Validate template_num
				$maxLength = 1;
				if (strlen($_POST['template_num']) > $maxLength) {
					wp_safe_redirect(home_url()."/?message=unauthorized");
					exit;
				}
				// Retrieving Input Data
				$sml_sid = isset($_POST['sml-sid']) ? sanitize_text_field($_POST['sml-sid']) : '';
				$site = isset($_POST['site']) ? esc_url_raw($_POST['site']) : '';
				$login_id = isset($_POST['login_id']) ? sanitize_text_field($_POST['login_id']) : '';
				$redirect_to = isset($_POST['redirect_to']) ? esc_url_raw($_POST['redirect_to']) : '';
				$is_error = isset($_POST['sml-error']) ? rest_sanitize_boolean($_POST['sml-error']) : false;
				$error_code = isset($_POST['code']) ? sanitize_text_field($_POST['code']) : '';

				if(!$this->checkDomainNames($redirect_to) || $_SERVER['HTTPS'] != "on"){
					wp_safe_redirect(home_url()."/?message=unauthorized");
					exit;
				}
				// If there's an error, perform validation checks
				if ($is_error) {
					if (empty($error_code) || empty($is_error)) {
						wp_redirect(home_url()."/?message=unauthorized");
						exit;
					}
					if(!$this->checkDomainNames($redirect_to) || $_SERVER['HTTPS'] != "on"){
						wp_redirect(home_url()."/?message=unauthorized");
						exit;
					}
					return [
						'is_error' => $is_error,
						'code' => $error_code,
						'redirect_to' => $redirect_to
					];
				}

				// Check if required fields are empty
				if (empty($sml_sid) || empty($site) || empty($login_id) || empty($redirect_to)) {
					wp_redirect(home_url()."/?message=unauthorized");
					exit;
				}
				
				return [
					'sml_sid' => $sml_sid,
					'site' => $site,
					'login_id' => $login_id,
					'redirect_to' => $redirect_to
				];
			}

			return null; // Return null if not a POST request
		}

		private function checkDomainNames($site)
		{

			$websiteDomain = parse_url(get_home_url(), PHP_URL_HOST);
			$siteDomain = parse_url($site, PHP_URL_HOST);

			if (!is_null($siteDomain)) {
				return $websiteDomain === $siteDomain;
			}

			return true;
		}

		private function get_area_mypage_url($request_action)
		{
			$page_id = $this->get_option('' . $request_action . '_page_id');
			$session_id = $this->session->get_id();

			if ($this->is_logged_in()) {
				if ($page_id) {

					$session_id = $this->session->get_id();
					$option_name = 'wpmls_' . $session_id . '_' . $page_id;

					if (get_transient($option_name)) {
						$profile_url = get_transient($option_name);
						wp_redirect(esc_url($this->hasher->decrypt_key($profile_url, SECURE_AUTH_KEY)));
						exit;
					} else {
						$sml_sid = $this->hasher->decrypt_key($this->session->get('sml_sid'), SECURE_AUTH_KEY);
						$area_title = $this->get_option('area_title');
						$result = $this->spiral->get_area_mypage($area_title, $sml_sid, $page_id);
						if ($result) {
							set_transient($option_name, $this->hasher->encrypt_key($result, SECURE_AUTH_KEY), 3600);
							wp_redirect(esc_url($result));
							exit;
						}
					}
				}
				wp_redirect(esc_url(get_home_url('/')));
				exit;
			} else {
				if ($page_id) {
					wp_redirect(esc_url(self::get_page_link('login', 'expired=true')));
				} else {
					wp_redirect(esc_url(get_home_url('/')));
				}
				exit;
			}
		}

		public function remove_message_query_param($url)
		{
			// Parse the URL
			$parsedUrl = parse_url($url);

			// Ensure 'query' key exists in parsed URL
			if (isset($parsedUrl['query'])) {
				// Parse the query string into an array
				parse_str($parsedUrl['query'], $queryParams);

				// Remove the 'message' and 'expired' parameters
				unset($queryParams['message']);
				unset($queryParams['expired']);

				// Build the new query string safely
				$newQuery = http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);

				// Construct the new URL
				$newUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];
				if (!empty($newQuery)) {
					$newUrl .= '?' . $newQuery;
				}
				if (isset($parsedUrl['fragment'])) {
					$newUrl .= '#' . $parsedUrl['fragment'];
				}

				return $newUrl;
			}

			// If there's no query string, return the original URL
			return $url;
		}

		/**
		 * Calls "login_head" hook on login page
		 *
		 * Callback for "wp_head" hook
		 *
		 * @access public
		 */
		public function wp_head()
		{
			if (self::is_sml_page()) {
				// This is already attached to "wp_head"
				remove_action('login_head', 'wp_print_head_scripts', 9);

				do_action('login_head');
			}
		}

		/**
		 * Calls "login_footer" hook on login page
		 *
		 * Callback for "wp_footer" hook
		 *
		 */
		public function wp_footer()
		{
			if (self::is_sml_page()) {
				// This is already attached to "wp_footer"
				remove_action('login_footer', 'wp_print_footer_scripts', 20);

				do_action('login_footer');
			}
		}

		/**
		 * Prints javascript in the footer
		 *
		 * @access public
		 */
		public function wp_print_footer_scripts()
		{
			if (!self::is_sml_page()) {
				return;
			}
		}


		/************************************************************************************************************************
		 * Filters
		 ************************************************************************************************************************/

		/**
		 * Alters menu item title & link according to whether user is logged in or not
		 *
		 * Callback for "wp_setup_nav_menu_item" hook in wp_setup_nav_menu_item()
		 *
		 * @see wp_setup_nav_menu_item()
		 * @access public
		 *
		 * @param object $menu_item The menu item
		 * @return object The (possibly) modified menu item
		 */
		public function wp_setup_nav_menu_item($menu_item)
		{
			if (is_admin())
				return $menu_item;

			if ('page' == $menu_item->object && self::is_sml_page('login', $menu_item->object_id)) {
				if ($this->is_logged_in()) {
					$menu_item->title = $this->get_template()->get_title('logout');
					$menu_item->url   = self::get_page_link('logout');
				}
			}
			return $menu_item;
		}

		/**
		 * Excludes pages from wp_list_pages
		 *
		 *
		 * @param array $exclude Page IDs to exclude
		 * @return array Page IDs to exclude
		 */
		public function wp_list_pages_excludes($exclude)
		{
			$pages = get_posts(array(
				'post_type'      => 'page',
				'post_status'    => 'any',
				'meta_key'       => '_sml_action',
				'posts_per_page' => -1
			));
			$pages = wp_list_pluck($pages, 'ID');

			return array_merge($exclude, $pages);
		}

		/**
		 * Adds nonce to logout link
		 *
		 * @param string $link Page link
		 * @param int $post_id Post ID
		 * @return string Page link
		 */
		public function page_link($link, $post_id)
		{
			if (self::is_sml_page('logout', $post_id))
				$link = add_query_arg('_wpnonce', wp_create_nonce('log-out'), $link);
			return $link;
		}


		/************************************************************************************************************************
		 * Utilities
		 ************************************************************************************************************************/

		/**
		 * Is this plugin with imcomplete settings
		 *
		 * @access public
		 *
		 * @return bool True if settings is imcomplete
		 */
		public function is_settings_imcomplete()
		{
			if (strlen($this->get_option('api_token')) != 52) {
				$token =  $this->hasher->wpmls_decrypt_setting_field($this->get_option('api_token'), SECURE_AUTH_KEY);
			} elseif (strlen($this->get_option('api_token')) == 52) {
				$token = $this->get_option('api_token');
			}

			if (strlen($this->get_option('api_token_secret')) != 40) {
				$token_secret =  $this->hasher->wpmls_decrypt_setting_field($this->get_option('api_token_secret'), SECURE_AUTH_KEY);
			} elseif (strlen($this->get_option('api_token_secret')) == 40) {
				$token_secret = $this->get_option('api_token_secret');
			}
			$wpmls_auth_form_url = $this->get_option('auth_form_url');
			$wpmls_indentify_key = $this->get_option('member_identification_key');
			return (empty($token) || empty($token_secret) || empty($wpmls_auth_form_url) || empty($wpmls_indentify_key));
		}

		/**
		 * Handler for "sml-show-template" shortcode
		 *
		 * Optional $atts contents:
		 *
		 * - template_num - A unqiue template number for this instance.
		 * - default_action - The action to display. Defaults to "login".
		 * - login_template - The template used for the login form. Defaults to "login-form.php".
		 * - user_template - The templated used for when a user is logged in. Defalts to "user-panel.php".
		 * - show_title - True to display the current title, false to hide. Defaults to true.
		 * - show_reg_link - True to display the register link, false to hide. Defaults to true.
		 * - show_pass_link - True to display the lost password link, false to hide. Defaults to true.
		 * - logged_in_widget - True to display the widget when logged in, false to hide. Defaults to true.
		 * - logged_out_widget - True to display the widget when logged out, false to hide. Defaults to true.
		 *
		 * @access public
		 *
		 * @param string|array $atts Attributes passed from the shortcode
		 * @return string HTML output from WPMLS_Spiral_Member_Login_Template->display()
		 */
		public function shortcode_show_template($atts = '')
		{
			static $did_main_template = false;

			$atts = wp_parse_args($atts);

			// Hide title
			if (isset($atts['title']) && $atts['title'] == 'off') {
				$atts['show_title'] = false;
			}

			// Hide all links
			$all_off = isset($atts['all']) && $atts['all'] == 'off';
			$show_name_on = isset($atts['showname']) && $atts['showname'] == 'on';
			if ($all_off) {
				$atts['show_title'] = !$show_name_on;
				$atts['show_reg_link'] = !$show_name_on;
				$atts['show_pass_link'] = !$show_name_on;
				$atts['show_profile_link'] = !$show_name_on;
				$atts['show_resetpass_link'] = !$show_name_on;
				$atts['show_withdrawal_link'] = !$show_name_on;
				$atts['hide_logout_link'] = true;
			}

			// Hide register, lost password, name key, profile, reset password, and withdrawal links
			if (isset($atts['register']) && $atts['register'] == 'off') {
				$atts['show_reg_link'] = false;
			}
			if (isset($atts['lostpw']) && $atts['lostpw'] == 'off') {
				$atts['show_pass_link'] = false;
			}
			if (isset($atts['showname']) && $atts['showname'] == 'off') {
				$atts['name_key'] = false;
			}
			if (isset($atts['profile']) && $atts['profile'] == 'off') {
				$atts['show_profile_link'] = false;
			}
			if (isset($atts['resetpw']) && $atts['resetpw'] == 'off') {
				$atts['show_resetpass_link'] = false;
			}
			if (isset($atts['withdrawal']) && $atts['withdrawal'] == 'off') {
				$atts['show_withdrawal_link'] = false;
			}

			// Set target attribute
			$atts['is_target_blank'] = isset($atts['target']) && $atts['target'] == '_blank';

			// Set show logout link attribute
			$atts['show_logout_link'] = isset($atts['logout']) && $atts['logout'] == 'off';

			// Set default name key if not set
			if (!isset($atts['name_key']) && $this->get_option('default_name_key')) {
				$atts['name_key'] = $this->get_option('default_name_key');
			}

			// Hide links based on option values
			$option_values = [
				'register_url' => 'show_reg_link',
				'lostpassword_url' => 'show_pass_link',
				'profile_page_id' => 'show_profile_link',
				'resetpass_page_id' => 'show_resetpass_link',
				'withdrawal_page_id' => 'show_withdrawal_link'
			];
			foreach ($option_values as $option => $attribute) {
				if (!$this->get_option($option)) {
					$atts[$attribute] = false;
				}
			}

			if (self::is_sml_page() && in_the_loop() && is_main_query() && !$did_main_template) {
				$template = $this->get_template();

				if (!empty($this->request_template_num))
					$template->set_active(false);

				if (!empty($this->request_action))
					$atts['default_action'] = $this->request_action;

				if (!isset($atts['show_title']))
					$atts['show_title'] = false;

				foreach ($atts as $option => $value) {
					$template->set_option($option, $value);
				}

				$did_main_template = true;
			} else {
				$template = $this->load_template($atts);
			}

			return $template->display($atts);
		}


		/**
		 * [sml-is-logged-in]
		 */
		public function shortcode_is_logged_in($atts, $content = null)
		{
			if (!$this->is_logged_in()) {
				return null;
			}
			return do_shortcode($content);
		}
		/**
		 * [sml-is-logged-in-hide]
		 */
		public function shortcode_is_logged_in_hide($atts, $content = null)
		{
			if (!$this->is_logged_in()) {
				return do_shortcode($content);
			}

			return null;
		}
		/**
		 * Handle for [sml-link]
		 */
		public function shortcode_user_link($atts = [])
		{
			$target     = isset($atts['target']) ? 'target="_blank"' : '';
			$key = $atts["key"];

			// Set users using decrypted login ID
			$area_title = $this->get_option('area_title');
			$area_session_id = $this->hasher->decrypt_key($this->session->get('sml_sid'), SECURE_AUTH_KEY);
			$search_title = $this->get_option('member_list_search_title');
			$this->spiral->set_users_mapper($area_title,$area_session_id,$search_title);
			$user_key = isset($atts["key"]) ? $atts["key"] : null;
			$user_data =  $this->spiral->get_users_data($user_key, 'key_value');

			if (isset($atts['link'])) {
				$is_query =  strpos($atts["link"], '?') !== false;
				$link = ($is_query) ? $atts["link"] . '&' : $atts["link"] . '?';
				if (isset($user_key)) {
					if (isset($atts['target'])) {
						return '<p><a href="' . $link . $user_data . '"' . $target . '>' . $atts['link_text'] . '</a></p>';
					} else {
						return '<p><a href="' . $link . $user_data . '">' . $atts['link_text'] . '</a></p>';
					}
				}
			} else {
				return $user_data;
			}
		}
		/**
		 * Handle for [sml-user-prop]
		 */
		public function shortcode_user_prop($atts)
		{
			// Check if user is logged in
			if (!$this->is_logged_in()) {
				return null;
			}

			// Check if 'key' attribute is set
			if (!isset($atts['key'])) {
				return null;
			}

			$key = $atts['key'];

			// Get the decrypted login ID from session
			$login_id = $this->hasher->decrypt_key($this->session->get('login_id'), SECURE_AUTH_KEY);

			// Set users using decrypted login ID
			$area_title = $this->get_option('area_title');
			$area_session_id = $this->hasher->decrypt_key($this->session->get('sml_sid'), SECURE_AUTH_KEY);
			$search_title = $this->get_option('member_list_search_title');
			$this->spiral->set_users_mapper($area_title,$area_session_id,$search_title);

			// Get users data based on the key
			return $this->spiral->get_users_data($key);
		}


		public function to_string($array, $delimiter = ',')
		{
			return implode($delimiter, $array);
		}

		protected function to_arrray($str)
		{

			$arr = preg_split("/\,/", $str);
			return $arr;
		}
		/**
		 * [sml-is-logged-mypage]
		 */
		public function shortcode_mypage_url($atts)
		{
			if (!$this->is_logged_in() || !isset($atts['id'])) {
				return '';
			}

			$page_id = $atts['id'];
			$param = isset($atts['param']) ? '&param=' . $atts['param'] : '';
			$target = isset($atts['target']) ? 'target="_blank"' : '';
			$action = 'register';

			if (isset($atts['title'])) {
				return sprintf('<div><a %s href="%s?id=%s%s">%s</a></div>', $target, $action, $page_id, $param, $atts['title']);
			}

			if (isset($atts['image'])) {
				return sprintf('<div><a %s href="%s?id=%s%s"><img src="%s"></a></div>', $target, $action, $page_id, $param, $atts['image']);
			}

			return '';
		}

		function convert_to_number($number)
		{
			return ctype_digit($number) ? ($number + 0) : FALSE;
		}

		protected function isFilterTypeNumber($atts)
		{
			if (!array_key_exists('fieldtype', $atts)) {
				return false;
			}
			if (!empty($atts['fieldtype'])) {
				if ($atts['fieldtype'] == 'num' &&  is_integer($this->convert_to_number($atts['value']))) {
					return true;
				}
				return false;
			}
			return false;
		}

		protected function isOperator($atts)
		{
			if (array_key_exists('filter', $atts)) {
				if (empty($atts['filter'])) {
					return 'equal';
				}
				return strtolower($atts['filter']);
			}
			return 'equal';
		}
		public function to_array($inputString)
		{
			// Split the input string into an array based on commas
			$array = explode(",", $inputString);

			// Return the resulting array
			return $array;
		}

		function compare_arrays($arr1, $arr2)
		{
			// Check if there is at least one common value between the arrays
			return !empty(array_intersect($arr1, $arr2));
		}
		/**
		 * [sml-is-logged-in-type]
		 */
		public function shortcode_is_logged_in_type($atts, $content = null)
		{
			if (!$this->is_logged_in() || !isset($atts['value']) || !isset($atts['key'])) {
				return null;
			}
			$user_data = null;
			$att_value 			= $atts['value'] == 'null' ? NULL : $atts['value'];
			$session_id = $this->session->get_id();
			$option_name = 'wpmls_' . $session_id . '_' . $atts['key'];
			/**
			 * Check Catch Exist
			 */
			if (!get_transient($option_name)) {
				$login_id = $this->hasher->decrypt_key($this->session->get('login_id'), SECURE_AUTH_KEY);
				$this->spiral->set_users($login_id);
				$user_data  = $this->spiral->get_user_option_key_value($atts['key']);
				$user_data_encrypted 	= $this->hasher->encrypt_key($user_data, SECURE_AUTH_KEY);
				if (!is_null($user_data))
					set_transient($option_name, $user_data_encrypted, 3600);
			} else {
				$user_data =  $this->hasher->decrypt_key(get_transient($option_name), SECURE_AUTH_KEY);
			}

			$data_array = $this->to_array($user_data);
			$attr_array = $this->to_array($att_value);
			switch ($this->isOperator($atts)) {
				case 'equal':
					$is_equal = $this->compare_arrays($data_array, $attr_array);
					if (!$is_equal) {
						return null;
					}
					return do_shortcode($content);

					break;
				case 'unequal':
					$is_equal = $this->compare_arrays($data_array, $attr_array);
					if ($is_equal) {
						return null;
					}
					return do_shortcode($content);

					break;
				case 'less':
					if ($this->isFilterTypeNumber($atts)) {
						if (intval($user_data) < intval($att_value)) {
							return do_shortcode($content);
						}
						break;
					}
				case 'greater':
					if ($this->isFilterTypeNumber($atts)) {
						if (intval($user_data) > intval($att_value)) {
							return do_shortcode($content);
						}
						break;
					}
					return null;
				case 'lessequal':
					if ($this->isFilterTypeNumber($atts)) {
						if (intval($user_data) <= intval($att_value)) {
							return do_shortcode($content);
						}
						break;
					}
					return null;
				case 'greaterequal':
					if ($this->isFilterTypeNumber($atts)) {
						if (intval($user_data) >= intval($att_value)) {
							return do_shortcode($content);
						}
						break;
					}
					return null;
				default:
			}
		}

		private function set_option_name($name, $num)
		{
			$session_id = $this->session->get_id();
			$option_name = $name . '_' . $session_id . 'x' . $num;
			return $option_name;
		}

		public function shortcode_is_logged_in_rule($atts, $content = null)
		{
			if (!$this->is_logged_in()) {
				return null;
			}
			$this->spiral->set_users($this->hasher->decrypt_key($this->session->get('login_id'), SECURE_AUTH_KEY));

			$id = $this->spiral->get_user_id();
			$select_name = $atts['rule_name'];

			if ($select_name == "") {
				return null;
			}
			$login_rule_count = $this->get_user_extraction_rule($id, $select_name);
			if ($login_rule_count > 0) {
				return do_shortcode($content);
			} else {
				return null;
			}
		}

		/**
		 * Return an instance of this class.
		 *
		 * @return    object    A single instance of this class.
		 */
		public static function get_instance()
		{

			// If the single instance hasn't been set, set it now.
			if (null == self::$instance) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Returns default options
		 *
		 * @access public
		 *
		 * @return array Default options
		 */
		public static function default_options()
		{
			$domin_name = parse_url(get_site_url())["scheme"] . '://' . parse_url(get_site_url())["host"] . '/';
			return apply_filters('sml_default_options', array(
				'api_token' => '',
				'api_token_secret' => '',
				'member_db_title' => 'wpmls_member',
				'member_identification_key' => 'email',
				'area_title' => 'wpmls_area',
				'wpmls_auth_form_url' => '',
				'member_list_search_title' => 'wpmls_searchform',
				'default_name_key' => 'name',
				'login_id_label_jp' => 'ユーザー名',
				'login_id_label_en' => 'User Name',
				'register_url' => '',
				'lostpassword_url' => '',
				'member_domain_name' => $domin_name,
				'member_logout_url' => get_home_url(),
				'profile_page_id' => '',
				'resetpass_page_id' => '',
				'withdrawal_page_id' => '',
				'related_web' => [
					'is_enable' => false,
					'atts' => [
						'param_name' => '',
						'field_name' => ''
					]
				]
			));
		}

		/**
		 * Returns default pages
		 *
		 * @access public
		 *
		 * @return array Default pages
		 */
		public static function default_pages()
		{
			return apply_filters('sml_default_pages', array(
				'login'        => __('Log In', 'spiral-member-login'),
				'logout'       => __('Log Out', 'spiral-member-login'),
				'profile'      => __('Profile', 'spiral-member-login'),
				'lostpassword' => __('Lost Password', 'spiral-member-login'),
				'resetpass'    => __('Reset Password', 'spiral-member-login'),
				'register'     => __('Register', 'spiral-member-login'),
				'withdrawal'   => __('Withdrawal', 'spiral-member-login')
			));
		}

		/**
		 * Retrieves active template object
		 *
		 * @access public
		 *
		 * @return object Instance object
		 */
		public function get_active_template()
		{
			return $this->get_template((int) $this->request_template_num);
		}

		/**
		 * Retrieves a loaded template object
		 *
		 * @access public
		 *
		 * @param int $num Instance number
		 * @return object Instance object

		 */
		public function get_template($num = 0)
		{
			if (isset($this->loaded_templates[$num]))
				return $this->loaded_templates[$num];
		}

		/**
		 * Sets an template object
		 *
		 * @access public
		 *
		 * @param object $object Instance object
		 */
		public function set_template($object)
		{
			$this->loaded_templates[] = &$object;
		}

		/**
		 * Instantiates an template
		 *
		 * @access public
		 *
		 * @param array|string $args Array or query string of arguments

		 * @return object Instance object
		 */
		public function load_template($args = '')
		{
			if (!$args && version_compare(phpversion(), '7.1.0', '>=')) {
				$args = array();
			}

			$args['template_num'] = count($this->loaded_templates);

			$template = new WPMLS_Spiral_Member_Login_Template($args);

			if ($args['template_num'] == $this->request_template_num) {
				$template->set_active();
				$template->set_option('default_action', $this->request_action);
			}

			$this->loaded_templates[] = $template;

			return $template;
		}

		/**
		 * Load the plugin text domain for translation.
		 *
		 * @since    1.0.0
		 */
		public function load_plugin_textdomain()
		{
			// $locale = apply_filters('plugin_locale', get_locale(), 'spiral-member-login');

			// load_textdomain('spiral-member-login', WP_LANG_DIR . '/' . 'spiral-member-login' . '/' . 'spiral-member-login' . '-' . $locale . '.mo');
			// load_plugin_textdomain('spiral-member-login', FALSE, dirname(plugin_basename(__DIR__)) . '/lang');

			load_plugin_textdomain(
				'spiral-member-login',
				false,
				dirname(plugin_basename(__DIR__)) . '/languages'
			);
		}

		/**
		 * Save plugin settings
		 *
		 * This is the callback for register_setting()
		 *
		 * @access public
		 *
		 * @param string|array $inputs Settings passed in from filter
		 * @return string|array Sanitized settings
		 */
		public function wpmls_save_settings($inputs)
		{
			// Check if input data is provided
			if (empty($inputs) || empty($inputs['api_token'])) {
				return $this->get_options();
			}

			// Verify nonce
			if (isset($_POST['wpmls_save_setting_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wpmls_save_setting_nonce'])), 'wpmls_save_setting_nonce')) {
				// Initialize error messages array
				$error_messages = [];

				// Get current options
				$options = $this->get_options();

				// Sanitize and validate input data
				$options['api_token'] = sanitize_text_field(trim($this->hasher->wpmls_encrypt_setting_field($inputs['api_token'], SECURE_AUTH_KEY)));
				$options['api_token_secret'] = sanitize_text_field(trim($this->hasher->wpmls_encrypt_setting_field($inputs['api_token_secret'], SECURE_AUTH_KEY)));
				$options['member_db_title'] = sanitize_text_field(trim($inputs['member_db_title']));
				$options['member_identification_key'] = sanitize_text_field(trim($inputs['member_identification_key']));
				$options['area_title'] = sanitize_text_field(trim($inputs['area_title']));
				$options['auth_form_url'] = sanitize_text_field(trim($inputs['auth_form_url']));
				$options['member_logout_url'] = sanitize_url(trim($inputs['member_logout_url']));
				$options['register_url'] = sanitize_url(trim($inputs['register_url']));
				$options['lostpassword_url'] = sanitize_url(trim($inputs['lostpassword_url']));
				$options['profile_page_id'] = !empty($inputs['profile_page_id']) ? absint(trim($inputs['profile_page_id'])) : '';
				$options['resetpass_page_id'] = !empty($inputs['resetpass_page_id']) ? absint(trim($inputs['resetpass_page_id'])) : '';
				$options['withdrawal_page_id'] = !empty($inputs['withdrawal_page_id']) ? absint(trim($inputs['withdrawal_page_id'])) : '';
				$options['member_list_search_title'] = sanitize_text_field(trim($inputs['member_list_search_title']));
				$options['default_name_key'] = sanitize_text_field(trim($inputs['default_name_key']));
				$options['login_id_label_jp'] = sanitize_text_field(trim($inputs['login_id_label_jp']));
				$options['login_id_label_en'] = sanitize_text_field(trim($inputs['login_id_label_en']));

				// Handle related_web
				$options['related_web'] = [
					'is_enable' => isset($inputs['is_enable']) ? rest_sanitize_boolean($inputs['is_enable']) : false,
					'atts' => [
						'param_name' => isset($inputs['param_name']) ? sanitize_text_field($inputs['param_name']) : '',
						'field_name' => isset($inputs['field_name']) ? sanitize_text_field($inputs['field_name']) : ''
					]
				];

				// Define patterns for token and secret validation
				$token_pattern = '/^[0-9a-zA-Z_\-]+$/';
				$secret_pattern = '/^[0-9a-zA-Z_\-]+$/';

				// Validate API token and secret
				if (!preg_match($token_pattern, trim($this->hasher->wpmls_decrypt_setting_field($options['api_token'], SECURE_AUTH_KEY)))) {
					$options['api_token'] = '';
					$error_messages[] = __('Enter a valid API token', 'spiral-member-login');
				}
				if (!preg_match($secret_pattern, trim($this->hasher->wpmls_decrypt_setting_field($options['api_token_secret'], SECURE_AUTH_KEY)))) {
					$options['api_token_secret'] = '';
					$error_messages[] = __('Enter a valid API token secret', 'spiral-member-login');
				}
				
				if (!empty($inputs['auth_form_url']) && !filter_var($inputs['auth_form_url'], FILTER_VALIDATE_URL)) {
					$options['auth_form_url'] = '';
					$error_messages[] = __("Enter authentication form URL", 'spiral-member-login');
				}
				
				if (!empty($inputs['register_url']) && !filter_var($inputs['register_url'], FILTER_VALIDATE_URL)) {
					$options['register_url'] = '';
					$error_messages[] = __("Enter register form URL", 'spiral-member-login');
				}
				
				if (!empty($inputs['lostpassword_url']) && !filter_var($inputs['lostpassword_url'], FILTER_VALIDATE_URL)) {
					$options['lostpassword_url'] = '';
					$error_messages[] = __("Enter last password form URL", 'spiral-member-login');
				}

				// Validate required fields
				$required_fields = [
					'member_db_title' => 'Enter member DB title',
					'member_identification_key' => 'Enter identification key',
					'area_title' => 'Enter area title',
					'member_list_search_title' => 'Enter member list search title',
					'default_name_key' => 'Enter default name key',
					'login_id_label_jp' => 'Enter Login ID Label (JP)',
					'login_id_label_en' => 'Enter Login ID Label (EN)',
				];

				foreach ($required_fields as $field => $error_message) {
					if (empty($options[$field])) {
						$error_messages[] = __($error_message, 'spiral-member-login');
					}
				}

				// Check for error messages
				if (!empty($error_messages)) {
					$error_message = implode('<br/>', $error_messages);
					add_settings_error($this->options_key, $this->plugin_slug, $error_message);
				}

				return $options;
			}
		}


		/**
		 * Install plugin
		 *
		 * @access public
		 */
		public function install()
		{
			// Current version
			$version = $this->get_option('version', self::version);

			// Setup default pages
			foreach (self::default_pages() as $action => $title) {
				if (!$page_id = self::get_page_id($action)) {
					$page_id = wp_insert_post(array(
						'post_title'     => $title,
						'post_name'      => $action,
						'post_status'    => 'publish',
						'post_type'      => 'page',
						'post_content'   => '[sml-show-template]',
						'comment_status' => 'closed',
						'ping_status'    => 'closed'
					));
					update_post_meta($page_id, '_sml_action', $action);
				}
			}

			$this->set_option('version', self::version);

			if (!get_option($this->options_key))
				$this->save_options();
		}

		/**
		 * Returns current URL
		 *
		 * @access public
		 *
		 * @param string $query Optionally append query to the current URL
		 * @return string URL with optional path appended
		 */
		public static function get_current_url($query = '')
		{
			$url = remove_query_arg(array('template_num', 'action', 'error', 'loggedout', 'redirect_to', 'updated', 'key', '_wpnonce', 'login'));

			if (!empty($_REQUEST['template_num']))
				$url = add_query_arg('template_num', $_REQUEST['template_num']);

			if (!empty($query)) {
				$r = wp_parse_args($query);
				foreach ($r as $k => $v) {
					if (strpos($v, ' ') !== false)
						$r[$k] = rawurlencode($v);
				}
				$url = add_query_arg($r, $url);
			}
			return $url;
		}

		public static function get_current_path($query = '')
		{
			$url = self::get_current_url($query);
			$home_url = get_home_url('/');
			return str_replace($home_url, '', $url);
		}

		/**
		 * Returns link for a login page
		 *
		 * @access public
		 *
		 * @param string $action The action
		 * @param string|array $query Optional. Query arguments to add to link
		 * @return string Login page link with optional $query arguments appended
		 */
		public static function get_page_link($action, $query = '')
		{
			$page_id = self::get_page_id($action);

			if ($page_id) {
				$link = get_permalink($page_id);
			} elseif ($page_id = self::get_page_id('login')) {
				$link = add_query_arg('action', $action, get_permalink($page_id));
			} else {
				$link = get_home_url('/');
			}

			if (!empty($query)) {
				$args = wp_parse_args($query);

				if (isset($args['action']) && $action == $args['action']) {
					unset($args['action']);
				}

				$link = add_query_arg(array_map('rawurlencode', $args), $link);
			}

			// Respect FORCE_SSL_LOGIN
			if ('login' == $action && force_ssl_login()) {
				$link = preg_replace('|^http://|', 'https://', $link);
			}

			return apply_filters('sml_page_link', $link, $action, $query);
		}

		/**
		 * Retrieves a page ID for an action
		 *
		 * @param string $action The action
		 * @return int|bool The page ID if exists, false otherwise
		 */
		public static function get_page_id($action)
		{
			global $wpdb;

			if (!$page_id = wp_cache_get($action, 'sml_page_ids')) {
				$page_id = $wpdb->get_var($wpdb->prepare("SELECT p.ID FROM $wpdb->posts p LEFT JOIN $wpdb->postmeta pmeta ON p.ID = pmeta.post_id WHERE p.post_type = 'page' AND pmeta.meta_key = '_sml_action' AND pmeta.meta_value = %s", $action));
				if (!$page_id) {
					return null;
				}
				wp_cache_add($action, $page_id, 'sml_page_ids');
			}
			return $page_id;
		}

		/**
		 * Get the action for a page
		 *
		 * @param int|object Post ID or object
		 * @return string|bool Action name if exists, false otherwise
		 */
		public static function get_page_action($page)
		{
			if (!$page = get_post($page))
				return false;

			return get_post_meta($page->ID, '_sml_action', true);
		}

		/**
		 * Determines if $action is for $page
		 *
		 * @param string $action The action to check
		 * @param int|object Post ID or object
		 * @return bool True if $action is for $page, false otherwise
		 */
		public static function is_sml_page($action = '', $page = '')
		{
			if (!$page = get_post($page))
				return false;

			if ('page' != $page->post_type)
				return false;

			if (!$page_action = self::get_page_action($page->ID))
				return false;

			if (empty($action) || $action == $page_action)
				return true;

			return false;
		}

		public static function is_member_page($page = '')
		{
			if (!$post = get_post($page)) {
				return false;
			}

			if ($post->post_type != 'page') {
				return false;
			}

			return get_post_meta($post->ID, 'sml-member-page', true) == 'true';
		}

		/**
		 * Renders api token settings field
		 *
		 * @access public
		 */
		public function settings_field_api_token()
		{
		?>
			<input name="spiral_member_login[api_token]" type="password" id="spiral_member_login_api_token" class="sml_token_field basic_config" value="<?php esc_attr_e($this->get_option('api_token')); ?>" required />
		<?php
		}
		/**
		 * Renders api token secret settings field
		 *
		 * @access public
		 */
		public function settings_field_api_token_secret()
		{
			$lek_somngat_secret = $this->get_option('api_token_secret');
			$after_encript_secret = $lek_somngat_secret;
		?>
			<input name="spiral_member_login[api_token_secret]" type="password" class="sml_token_field basic_config" value="<?php esc_attr_e($after_encript_secret); ?>" required />
		<?php
		}

		public function settings_field_register_url()
		{
		?>
			<input name="spiral_member_login[register_url]" type="text" class="sml_token_field" value="<?php esc_attr_e($this->get_option('register_url')); ?>" required />
		<?php
		}

		public function settings_field_lostpassword_url()
		{
		?>
			<input name="spiral_member_login[lostpassword_url]" type="text" class="sml_token_field" value="<?php esc_attr_e($this->get_option('lostpassword_url')); ?>" required />
		<?php

		}

		public function settings_field_member_identification_key()
		{
		?>
			<input name="spiral_member_login[member_identification_key]" type="text" class="sml_member_member_identification_key_field advance-config" value="<?php esc_attr_e($this->get_option('member_identification_key')); ?>" required />
		<?php
		}

		public function settings_field_member_db_title()
		{
		?>
			<input name="spiral_member_login[member_db_title]" type="text" class="sml_member_home_url_field advance-config" value="<?php esc_attr_e($this->get_option('member_db_title')); ?>" required />
		<?php
		}

		public function settings_field_logout_url()
		{
		?>
			<input pattern="https?://.+" name="spiral_member_login[member_logout_url]" type="text" class="sml_url_field sml_member_logout_url_field" value="<?php echo esc_url($this->get_option('member_logout_url')); ?>" />
		<?php
		}

		public function settings_field_area_title()
		{
		?>
			<input name="spiral_member_login[area_title]" type="text" class="sml_area_title_field advance-config" value="<?php esc_attr_e($this->get_option('area_title')); ?>" required />
		<?php

		}

		public function settings_field_default_name_key()
		{
		?>
			<input name="spiral_member_login[default_name_key]" type="text" class="sml_title_field basic_config" value="<?php esc_attr_e($this->get_option('default_name_key')); ?>" />
		<?php
		}

		public function settings_field_login_id_label()
		{
		?>
			<div>
				<label for="">日本語</label>
				<input name="spiral_member_login[login_id_label_jp]" type="text" class="sml_login_id_label_jp basic_config" value="<?php echo empty(get_option('spiral_member_login')['login_id_label_jp']) ? "ユーザー名" :  esc_attr(get_option('spiral_member_login')['login_id_label_jp']) ?>" required />
				<br><br>
				<label for="">English</label>
				<input name="spiral_member_login[login_id_label_en]" type="text" class="sml_login_id_label_en basic_config" value="<?php echo empty(get_option('spiral_member_login')['login_id_label_en']) ? "User Name" :  esc_attr(get_option('spiral_member_login')['login_id_label_en']) ?>" required />
			</div>
		<?php

		}

		public function settings_field_profile_page_id()
		{
		?>
			<input name="spiral_member_login[profile_page_id]" type="text" class="sml_id_field basic_config" value="<?php esc_attr_e($this->get_option('profile_page_id')); ?>" required />
		<?php

		}

		public function settings_field_resetpass_page_id()
		{
		?>
			<input name="spiral_member_login[resetpass_page_id]" type="text" class="sml_id_field basic_config" value="<?php esc_attr_e($this->get_option('resetpass_page_id')); ?>" required />
		<?php
		}

		public function settings_field_withdrawal_page_id()
		{
		?>
			<input name="spiral_member_login[withdrawal_page_id]" type="text" class="sml_id_field basic_config" value="<?php esc_attr_e($this->get_option('withdrawal_page_id')); ?>" required />
		<?php
		}

		public function settings_field_member_list_search_title()
		{
		?>
			<input name="spiral_member_login[member_list_search_title]" type="text" class="sml_member_list_search_title_field" value="<?php esc_attr_e($this->get_option('member_list_search_title')); ?>" />
		<?php
		}

		public function settings_field_auth_form_url()
		{
		?>
			<input name="spiral_member_login[auth_form_url]" type="text" class="sml_url_field basic_config" value="<?php esc_attr_e($this->get_option('auth_form_url')); ?>" required />
<?php
		}

		/**
		 * Render the settings page for this plugin.
		 *
		 * @since    1.0.0
		 */
		public function display_plugin_admin_page()
		{
			include_once(plugin_dir_path(__DIR__) . 'views/admins/admin.php');
		}

		/**
		 * Uninstall the plugin
		 *
		 * @access protected
		 */
		protected static function _uninstall()
		{
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');

			// Delete settings
			delete_option('spiral_member_login');
			delete_option('version');
			delete_option('is_setup');

			$pages = get_posts(array(
				'post_type'      => 'page',
				'post_status'    => 'any',
				'meta_key'       => '_sml_action',
				'posts_per_page' => -1
			));

			// Delete pages
			foreach ($pages as $page) {
				wp_delete_post($page->ID, true);
			}
		}

		public function is_logged_in($area_session_id = null)
		{
			if ( $area_session_id == null ) {
				 $area_session_id = $this->hasher->decrypt_key($this->session->get('sml_sid'), SECURE_AUTH_KEY);
				 if ( $area_session_id == null ) {
					 return false;
				 }
			 }
			$area_title = $this->get_option( 'area_title' );
			$result = $this->spiral->get_area_status( $area_title, $area_session_id );
			return $result === true;
		}

		public function get_user_extraction_rule($id, $select_name)
		{
			$area_title = $this->get_option('area_title');
			$db_title = $this->get_option('member_db_title');
			$area_session_id = $this->hasher->decrypt_key($this->session->get('sml_sid'), SECURE_AUTH_KEY);
			$result = $this->spiral->get_extraction_rule($area_title, $db_title, $area_session_id, $id, $select_name);
			$data = $result;
			return $data;
		}

		public function get_user_prop($key = 'name')
		{
			if (!$this->is_logged_in()) {
				return null;
			}

			$area_title = $this->get_option('area_title');
			$area_session_id = $this->hasher->decrypt_key($this->session->get('sml_sid'), SECURE_AUTH_KEY);
			$search_title = $this->get_option('member_list_search_title');

			// Get table data and handle potential null values
			$response = $this->spiral->get_table_data($area_title, $area_session_id, $search_title);
			if (is_null($response) || !isset($response['data'])) {
				return null; // Return null if the response or data is invalid
			}

			$result = array();
			foreach ($response['data'] as $item) {
				$mappedItem = array();
				for ($i = 0; $i < count($response['header']); $i++) {
					$mappedItem[$response['header'][$i]] = $item[$i] ?? null; // Use null coalescing operator for empty values
				}
				$result[] = $mappedItem;
			}

			if (empty($result)) {
				return null;
			}

			$result = $result[0];

			// Access desired property with potential fallbacks
			return $result[$key] ?? $result['name'] ?? $result['firstName'] . '&nbsp;' . $result['lastName'] ?? null;
		}
	}

endif; // Class exists
