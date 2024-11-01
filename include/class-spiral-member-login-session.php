<?php

/**
 * WPMLS_Spiral_Member_Login Session
 *
 * This is a wrapper class for WPMLS_Session / PHP $_SESSION and handles the storage
 *
 * Partly based on WPMLS_Session by Eric Mann.
 *
 * @package   Spiral_Member_Login
 * @author    PIPED BITS Co.,Ltd.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

if (!class_exists('WPMLS_Spiral_Member_Login_Session')) :
	/**
	 * WPMLS_Spiral_Member_Login_Session Class
	 *
	 * @since 1.0.0
	 */
	class WPMLS_Spiral_Member_Login_Session
	{

		/**
		 * Holds our session data
		 *
		 * @var array
		 * @access private
		 * @since 1.0.0
		 */
		private $session = array();


		/**
		 * Whether to use PHP $_SESSION or WPMLS_Session
		 *
		 * PHP $_SESSION is opt-in only by defining the SML_USE_PHP_SESSIONS constant
		 *
		 * @var bool
		 * @access private
		 * @since 1.0.0
		 */
		private $use_php_sessions = false;


		/**
		 * Get things started
		 *
		 * Defines our WPMLS_Session constants, includes the necessary libraries and
		 * retrieves the WP Session instance
		 *
		 * @since 1.0.0
		 */
		public function __construct()
		{

			// Use WPMLS_Session (default)
			if (!defined('WP_SESSION_COOKIE'))
				define('WP_SESSION_COOKIE', 'sml_wp_session');

			if (!class_exists('WPMLS_Recursive_ArrayAccess'))
				require_once plugin_dir_path(__DIR__) . 'libs/class-recursive-arrayaccess.php';

			if (!class_exists('WPMLS_Session')) {
				require_once plugin_dir_path(__DIR__) . 'libs/class-wp-session.php';
				require_once plugin_dir_path(__DIR__) . 'libs/wp-session.php';
			}

			add_filter('wp_session_expiration_variant', function () {
				return 5 * 60;
			}, 10, 1);
			add_filter('wp_session_expiration', function () {
				return 60 * 60;
			}, 10, 1);

			add_action('init', array($this, 'init'), -1);
		}


		/**
		 * Setup the WPMLS_Session instance
		 *
		 * @access public
		 * @since 1.0.0
		 * @return void
		 */
		public function init()
		{
			$this->session = WPMLS_Session::get_instance();
			return $this->session;
		}


		/**
		 * Retrieve session ID
		 *
		 * @access public
		 * @since 1.6
		 * @return string Session ID
		 */
		public function get_id()
		{
			return $this->session->session_id;
		}


		/**
		 * Retrieve a session variable
		 *
		 * @access public
		 * @since 1.0.0
		 * @param string $key Session key
		 * @return string Session variable
		 */
		public function get($key)
		{
			$key = sanitize_key($key);
			return isset($this->session[$key]) ? maybe_unserialize($this->session[$key]) : false;
		}

		/**
		 * Set a session variable
		 *
		 * @since 1.0.0
		 *
		 * @param $key Session key
		 * @param $value Session variable
		 * @return mixed Session variable
		 */
		public function set($key, $value)
		{
			$key = sanitize_key($key);

			if (is_array($value))
				$this->session[$key] = serialize($value);
			else
				$this->session[$key] = $value;

			return $this->session[$key];
		}

		/**
		 * Regenerate session id
		 *
		 * @since 1.0.5
		 *
		 * @param bool $delete_old Flag whether or not to delete the old session data from the server.
		 */
		public function regenerate_id($delete_old_session = false)
		{
			$this->session->regenerate_id($delete_old_session);
		}

		public function wpmls_remove_session_and_db_catches()
		{
			$session_id = $this->get_id();
			delete_transient('wpmls_auth_' . $session_id);
			delete_option('_wp_session_expires_' . $session_id);
			delete_option('_wp_session_' . $session_id);

			global $wpdb;
			
			$page_option_name = '_transient_' . 'wpmls_'. $session_id . '%';
			$page_option_timeout = '_transient_timeout_' . 'wpmls_'. $session_id . '%';
			$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",  $page_option_name));
			$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",  $page_option_timeout));
		}
	}

endif;
