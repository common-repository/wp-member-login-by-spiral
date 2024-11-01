<?php

/**
 * Holds the Spiral Member Login Template class
 *
 * @package   Spiral_Member_Login
 * @author    PIPED BITS Co.,Ltd.
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;
if (!class_exists('WPMLS_Spiral_Member_Login_Template')) :
	/*
 * Spiral Member Login Template class
 *
 * This class contains properties and methods common to displaying output.
 *
 * @since 1.0.0
 */
	class WPMLS_Spiral_Member_Login_Template extends WPMLS_Spiral_Member_Login_Base
	{
		/**
		 * Holds active instance flag
		 *
		 * @since 1.0.0
		 * @access private
		 * @var bool
		 */
		private $is_active = false;

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param array $options Instance options
		 */
		public function __construct($options = '')
		{
			$options = wp_parse_args($options);
			$options = shortcode_atts(self::default_options(), $options);

			$this->set_options($options);
		}

		/**
		 * Retrieves default options
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @return array Default options
		 */
		public static function default_options()
		{
			return array(
				'template_num'          => 0,
				'default_action'        => '',
				'login_template'        => '',
				'user_template'         => '',
				'name_key'              => 'name',
				'show_name'             => true,
				'show_title'            => true,
				'show_reg_link'         => true,
				'show_pass_link'        => true,
				'show_profile_link'     => true,
				'show_resetpass_link'   => true,
				'show_withdrawal_link'  => true,
				'enable_blank_tab'      => false,
				'logged_in_widget'      => true,
				'logged_out_widget'     => true,
				'before_widget'         => '',
				'after_widget'          => '',
				'before_title'          => '',
				'after_title'           => '',
				'is_target_blank'		=> false,
				'show_logout_link'		=> false
			);
		}

		/**
		 * Displays output according to current action
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @return string HTML output
		 */
		public function display($action = '')
		{
			// Validate and normalize $action
			$action = $this->normalize_action($action);

			// Check if settings are incomplete and user has manage_options capability
			if ($this->is_settings_incomplete() && !current_user_can('manage_options')) {
				return;
			}

			// Start output buffering
			ob_start();

			// Display before_widget option
			echo $this->get_option('before_widget');

			// Display title if enabled and appropriate for action
			if ($this->get_option('show_title')) {
				$this->display_title($action);
			}

			// Handle logged-in and logged-out scenarios
			if (WPMLS_Spiral_Member_Login::get_instance()->is_logged_in()) {
				$this->display_user_templates($action);
			} else {
				$this->display_login_templates($action);
			}

			// Display after_widget option
			echo $this->get_option('after_widget') . "\n";

			// Get output and clean buffer
			$output = ob_get_contents();
			ob_end_clean();

			// Apply filters
			return apply_filters_ref_array('sml_display', array($output, $action, &$this));
		}

		// Helper functions for improved readability and maintainability

		private function normalize_action($action)
		{
			if (empty($action)) {
				$action = $this->get_option('default_action');
			}
			return $action;
		}

		private function is_settings_incomplete()
		{
			$spiral_member_login = WPMLS_Spiral_Member_Login::get_instance();
			return $spiral_member_login->is_settings_imcomplete();
		}

		private function display_title($action)
		{
			if (isset($action['is_widget'])) {
				echo '<h1 class="widget-title subSection-title">' . esc_html_e('Log In', 'spiral-member-login') . '</h1>';
			} else {
				echo '<p class="sml-user-links-title">' . esc_html_e('Log In', 'spiral-member-login') . '</p>';
			}
		}

		private function display_user_templates($action)
		{
			$templates = [];

			// Add user-defined template if available
			if ($user_template = $this->get_option('user_template')) {
				$templates[] = $user_template;
			}

			// Determine which default templates to include based on action parameters
			if (isset($action['is_widget'])) {
				$templates[] = 'user_info_widget.php';
			} else {
				if(isset($action['showname']) && $action['showname'] == 'off'){
					$templates[] = 'show_username.php';
				} else {
					$templates[] = 'user_info.php';
				}
			}

			// Load the templates
			$this->get_template($templates);
		}


		private function display_login_templates($action)
		{
			$templates = array();

			if ($this->get_option('login_template')) {
				$templates[] = $this->get_option('login_template');
			}

			if (isset($action['is_widget'])) {
				$templates[] = 'login_widget.php';
			} else {
				$templates[] = 'login.php';
			}

			$this->get_template($templates);
		}

		/**
		 * Returns action title
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param string $action The action to retrieve. Defaults to current action.
		 * @return string Title of $action
		 */
		public function get_title($action = '')
		{
			if (empty($action))
				$action = $this->get_option('default_action');

			if (is_admin())
				return;

			$spiral_member_login = WPMLS_Spiral_Member_Login::get_instance();
			if ($spiral_member_login->is_logged_in() && 'login' == $action && $action == $this->get_option('default_action')) {
				$title = __('Log In', 'spiral-member-login');
			} else {
				if ($page_id = WPMLS_Spiral_Member_Login::get_page_id($action)) {
					$title = get_post_field('post_title', $page_id);
				} else {
					switch ($action) {
						case 'register':
							$title = __('Register', 'spiral-member-login');
							break;
						case 'lostpassword':
							$title = __('Lost Password', 'spiral-member-login');
							break;
						case 'profile':
							$title = __('Profile', 'spiral-member-login');
							break;
						case 'resetpass':
							$title = __('Reset Password', 'spiral-member-login');
							break;
						case 'withdrawal':
							$title = __('Withdrawal', 'spiral-member-login');
							break;
						case 'login':
						default:
							$title = '<div id="sml-user-links-block">';
							$title .= '<p class="sml-user-links-title">' . esc_html_e('Log In', 'spiral-member-login') . "</p>";
					}
				}
			}
			return apply_filters('sml_title', $title, $action);
		}

		/**
		 * Outputs action title
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param string $action The action to retieve. Defaults to current action.
		 */
		public function the_title($action = '')
		{
			echo $this->get_title($action);
		}

		/**
		 * Returns plugin errors
		 *
		 * @since 1.0.0
		 * @access public
		 */
		public function get_errors()
		{
			global $error;

			$spiral_member_login = WPMLS_Spiral_Member_Login::get_instance();

			$wp_error = &$spiral_member_login->errors;

			if (empty($wp_error))
				$wp_error = new WP_Error();

			// Incase a plugin uses $error rather than the $errors object
			if (!empty($error)) {
				$wp_error->add('error', $error);
				unset($error);
			}

			$output = '';
			if ($this->is_active()) {
				if ($wp_error->get_error_code()) {
					$errors = '';
					$messages = '';
					foreach ($wp_error->get_error_codes() as $code) {
						$severity = $wp_error->get_error_data($code);
						foreach ($wp_error->get_error_messages($code) as $error) {
							if ('message' == $severity)
								$messages .= '    ' . $error . "<br />\n";
							else
								$errors .= '    ' . $error . "<br />\n";
						}
					}
					if (!empty($errors))
						$output .= '<p class="error" id="sml-login-error-message">' . apply_filters('login_errors', $errors) . "</p>\n";
					if (!empty($messages))
						$output .= '<p class="message">' . apply_filters('login_messages', $messages) . "</p>\n";
				}
			}
			return $output;
		}

		/**
		 * Prints plugin errors
		 *
		 * @since 1.0.0
		 * @access public
		 */
		public function the_errors()
		{
			echo $this->get_errors();
		}

		/**
		 * Returns requested action URL
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param string $action Action to retrieve
		 * @return string The requested action URL
		 */
		public function get_action_url($action = '')
		{
			$template_num = $this->get_option('template_num');

			if ($action == $this->get_option('default_action')) {
				$args = array();
				if ($template_num)
					$args['template_num'] = $template_num;
				$url = WPMLS_Spiral_Member_Login::get_current_url($args);
			} else {
				$url = WPMLS_Spiral_Member_Login::get_page_link($action);
			}

			// Respect FORCE_SSL_LOGIN
			if ('login' == $action && force_ssl_login())
				$url = preg_replace('|^http://|', 'https://', $url);

			return apply_filters('sml_action_url', $url, $action, $template_num);
		}

		/**
		 * Outputs requested action URL
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param string $action Action to retrieve
		 */
		public function the_action_url($action = 'login')
		{
			echo esc_url($this->get_action_url($action));
		}

		/**
		 * Returns the action links
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param array $args Optionally specify which actions to include/exclude. By default, all are included.
		 */
		public function get_action_links($args = '')
		{
			$args = wp_parse_args($args, array(
				'register'     => true,
				'lostpassword' => true
			));

			$action_links = array();
			if ($args['register'] && $this->get_option('show_reg_link')) {
				$action_links[] = array(
					'title' => $this->get_title('register'),
					'url'   => $this->get_action_url('register'),
					'class'	=> "register"
				);
			}
			if ($args['lostpassword'] && $this->get_option('show_pass_link')) {
				$action_links[] = array(
					'title' => $this->get_title('lostpassword'),
					'url'   => $this->get_action_url('lostpassword'),
					'class'	=> "lostpassword"
				);
			}
			return apply_filters('sml_action_links', $action_links, $args);
		}

		/**
		 * Outputs the action links for shortcode
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param array $args Optionally specify which actions to include/exclude. By default, all are included.
		 */
		public function the_action_links($args = '')
		{
			$is_target_blank = $args['is_target_blank'] ? 'target="_blank"' : '';
			if ($action_links = $this->get_action_links($args)) {

				$html = '<div>';
				$html .= '<ul>';
				foreach ((array) $action_links as $link) {
					$title = sprintf(
						esc_html(
							'%s'
						),
						esc_html($link['title']),
					);

					$html .= '<li class="sml-action-links-"' . esc_attr($link['class']) . '>';
					$html .= '<a ' . $is_target_blank . ' href="' . esc_url($link['url']) . '" rel="nofollow">' . __($title, 'spiral-member-login') . '</a></li>';
				}
				$html .= '</ul>';
				$html .= '</div>';

				echo $html;
			}
		}

		/**
		 * Returns logged-in user links
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @return array Logged-in user links
		 */
		public function get_user_links()
		{
			$user_links = array();
			if ($this->get_option('show_profile_link')) {
				$user_links[] = array(
					'title' => self::get_title('profile'),
					'url'   => $this->get_action_url('profile'),
					'class' => "sml-user-links-profile"
				);
			}
			if ($this->get_option('show_resetpass_link')) {
				$user_links[] = array(
					'title' => $this->get_title('resetpass'),
					'url'   => $this->get_action_url('resetpass'),
					'class' => "sml-user-links-reset-pw"
				);
			}
			if ($this->get_option('show_withdrawal_link')) {
				$user_links[] = array(
					'title' => $this->get_title('withdrawal'),
					'url'   => $this->get_action_url('withdrawal'),
					'class'	=> "sml-user-links-withdrawal"
				);
			}
			return apply_filters('sml_user_links', $user_links);
		}

		/**
		 * Outputs logged-in user links shrotcode
		 *
		 * @since 1.0.0
		 * @access public
		 */
		public function the_user_links($args = '')
		{
			$show_logout_link = $args['show_logout_link'] ?? false;
			$is_target_blank = $args['is_target_blank'] ? 'target="_blank"' : '';
			$html = '<ul class="sml-user-links">';
			foreach ((array) $this->get_user_links() as $link) {
				$title = sprintf(
					esc_html(
						'%s'
					),
					esc_html($link['title']),
				);
				$html .= '<li class="' . esc_attr($link['class']) . '"><a ' . $is_target_blank . ' href="' . esc_url($link['url']) . '">' . __($title, 'spiral-member-login') . '</a></li>';
			}
			if ($show_logout_link == false) {
				$html .=  '<li class="sml-user-links-logout"><a href="' . esc_url(WPMLS_Spiral_Member_Login::get_page_link('logout')) . '">' .  __('Log Out', 'spiral-member-login') . '</a></li>' . "\n";
			}
			$html .= '</ul>';

			echo $html;
		}

		/**
		 * Outputs logged-in user name
		 *
		 * @since 1.0.0
		 * @access public
		 */
		public function the_user_name()
		{
			if (!$this->get_option('show_name')) {
				return;
			}
			$spiral_member_login = WPMLS_Spiral_Member_Login::get_instance();
			$user_name = $spiral_member_login->get_user_prop($this->get_option('name_key'));
			if ($user_name) {
				echo '<p class="sml-user-name">' . esc_html($user_name) . '</p>' . "\n";
			}
		}

		/**
		 * URL for authentication form
		 *
		 * @since 1.0.0
		 * @access public
		 */
		public function the_auth_form_url()
		{
			$spiral_member_login = WPMLS_Spiral_Member_Login::get_instance();
			$option_name = $spiral_member_login->get_option('auth_form_url');
			if ($option_name) {
				if (WPMLS_VERSION == 1)
					echo esc_attr($option_name) . '&site=' . esc_attr(get_home_url()) . '/';
				if (WPMLS_VERSION == 2)
					echo esc_attr($option_name) . '?site=' . esc_attr(get_home_url()) . '/';
			}
		}

		/**
		 * Locates specified template
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param string|array $template_names The template(s) to locate
		 * @param bool $load If true, the template will be included if found
		 * @param array $args Array of extra variables to make available to template
		 * @return string|bool Template path if found, false if not
		 */
		public function get_template($template_names, $load = true, $args = array())
		{
			$spiral_member_login = WPMLS_Spiral_Member_Login::get_instance();

			// User friendly access to this
			$template = &$this;

			// Easy access to current user
			$current_user = wp_get_current_user();

			extract(apply_filters_ref_array('sml_template_args', array($args, &$this)));

			if (!is_array($template_names))
				$template_names = array($template_names);

			if (!$found_template = locate_template($template_names)) {
				foreach ($template_names as $template_name) {
					if (file_exists(plugin_dir_path(__DIR__) . 'views/' . $template_name)) {
						$found_template = plugin_dir_path(__DIR__) . 'views/' . $template_name;
						break;
					}
				}
			}

			$found_template = apply_filters_ref_array('sml_template', array($found_template, $template_names, &$this));

			if ($load && $found_template) {
				include($found_template);
			}

			return $found_template;
		}

		/**
		 * Returns the proper redirect URL according to action
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param string $action The action
		 * @return string The redirect URL
		 */
		public function get_redirect_url($action = '')
		{
			if (empty($action))
				$action = $this->get_option('default_action');

			$url = WPMLS_Spiral_Member_Login::get_current_path();
			$new_url = str_replace("?expired=true", "", $url);

			return apply_filters('sml_redirect_url', $new_url, $action);
		}

		/**
		 * Outputs redirect URL
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param string $action The action
		 */
		public function the_redirect_url($action = '')
		{
			echo esc_attr($this->get_redirect_url($action));
		}

		/**
		 * Outputs current template instance ID
		 *
		 * @since 1.0.0
		 * @access public
		 */
		public function the_template_num()
		{
			if ($this->get_option('template_num'))
				echo esc_attr($this->get_option('template_num'));
		}
		/**
		 * Outputs current template instance ID
		 *
		 * @since 1.0.0
		 * @access public
		 */
		public function full_site_url()
		{
			$domin_name = parse_url(get_site_url())["scheme"] . '://' . parse_url(get_site_url())["host"];
			$full_url = $domin_name . WPMLS_Spiral_Member_Login::get_current_path();
			echo esc_url($full_url);
		}

		/**
		 * Returns requested $value
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param string $value The value to retrieve
		 * @return string|bool The value if it exists, false if not
		 */
		public function get_posted_value($value)
		{
			if ($this->is_active() && isset($_REQUEST[$value]))
				return stripslashes($_REQUEST[$value]);
			return false;
		}

		/**
		 * Outputs requested value
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param string $value The value to retrieve
		 */
		public function the_posted_value($value)
		{
			echo esc_attr($this->get_posted_value($value));
		}

		/**
		 * Returns active status
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @return bool True if instance is active, false if not
		 */
		public function is_active()
		{
			return $this->is_active;
		}

		/**
		 * Sets active status
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param bool $active Active status
		 */
		public function set_active($active = true)
		{
			$this->is_active = $active;
		}
	}

endif; // Class exists