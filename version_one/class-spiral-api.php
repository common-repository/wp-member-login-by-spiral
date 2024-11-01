<?php

/**
 * SPIRAL API class
 *
 * @package   Spiral_Member_Login
 * @author    PIPED BITS Co.,Ltd.
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;
if (!class_exists('WPMLS_Spiral_Api')) :
	/**
	 *
	 * SPIRAL API class.
	 *
	 * @package WPMLS_Spiral_Member_Login
	 * @author  PIPED BITS Co.,Ltd.
	 */
	class WPMLS_Spiral_Api extends WPMLS_Spiral_Member_Login_Base
	{

		private $token 			= null;
		private $token_secret 	= null;
		private $api_url 		= null;
		private $user_data 		= null;
		private $area_status 	= null;
		private $users = [];
		public $db_title = NULL;
		public $identification_key =  NULL;
		private $db_field_title = [];
		private $db_field_type = [];
		private $allow_select_fields = ["mm_alternative", "mm_multiple", "mm_multiple128"];
		private $user_id = null;

		public function __construct($token, $token_secret, $db_title, $identification_key)
		{
			$this->token = $token;
			$this->token_secret = $token_secret;
			$this->db_title = $db_title;
			$this->identification_key = $identification_key;
			$this->set_db_fields();
		}

		public function set_user_id($id)
		{
			$this->user_id = $id;
		}

		public function get_user_id()
		{
			return $this->user_id;
		}

		public function set_users($login_id)
		{
			$parameters = array();
			$parameters["search_condition"] = [
				[
					"name" => $this->identification_key,
					"value" => $login_id, "operator" => "="
				]
			];

			$parameters["db_title"] = $this->db_title;
			$columns = $this->get_db_columns($this->db_title);
			$parameters["select_columns"] = $columns;
			$this->_sign_params($parameters);
			$result = $this->request_spiral_api($this->get_api_url(), "database/select", $parameters);
			if (empty($result["data"])) {
				return null;
			}
			$data_values = $result["data"][0];
			$id = $data_values[0];
			$this->set_user_id($id);
			unset($data_values[0]);
			$data_values = array_values($data_values);
			$this->users = array_combine($this->db_field_title, $data_values);
		}

		public function set_users_mapper($area_title,$area_session_id,$search_title)
		{
			$identifier = $this->get_id($area_title,$area_session_id,$search_title);
		
			$parameters = array();
			$parameters["search_condition"] = [
				[
					"name" => $this->identification_key,
					"value" => $identifier, "operator" => "="
				]
			];

			$parameters["db_title"] = $this->db_title;
			$columns = $this->get_db_columns($this->db_title);
			$parameters["select_columns"] = $columns;
			$this->_sign_params($parameters);
			$result = $this->request_spiral_api($this->get_api_url(), "database/select", $parameters);
			if (empty($result["data"])) {
				return null;
			}
			$data_values = $result["data"][0];
			$id = $data_values[0];
			$this->set_user_id($id);
			unset($data_values[0]);
			$data_values = array_values($data_values);
			$data_labels = $result['label'];
			foreach ($this->db_field_type as $key => $value) {

				if (in_array($value, $this->allow_select_fields)) {
					$mapper = $this->map_selectable_fields($data_labels[$key], $data_values[$key]);
					$data_values[$key] = $mapper;
				}
			}
			$this->users = array_combine($this->db_field_title, $data_values);
		}

		public function get_user_option_key_value($fields_name)
		{
			if (!array_key_exists($fields_name, $this->users)) {
				return null;
			}
			$user_data = $this->users[$fields_name];
			if (is_array($user_data)) {
				return $this->to_string($user_data);
			}
			return $user_data;
		}

		public function get_username()
		{
			if (isset($this->users['firstName']) && isset($this->users['lastName'])) {
				return $this->users['firstName'] . '&nbsp;' . $this->users['lastName'];
			} else if (isset($this->users['name'])) {
				return $this->users['name'];
			} else {
				return null;
			}
		}

		public function is_multi_select_fields($text)
		{
			if (strpos($text, ',') !== false) {
				return true;
			}
			return false;
		}

		private function to_array($string)
		{
			$array = explode(",", $string);
			return $array;
		}

		private function to_string($array)
		{
			$string = implode(",", $array);
			return $string;
		}

		public function get_users_data($field_name, $options = '')
		{
			if (empty($this->users) || empty($field_name)) {
				return null;
			}
			switch ($options) {
				case 'key':
					if (is_null($this->users[$field_name]))
						return null;
					return array_keys($this->users[$field_name]);
					break;
				case 'key_value':
					if ($this->is_multi_select_fields($field_name)) {
						$final_data = [];
						foreach ($this->to_array($field_name) as $key_field => $value_field) {
							if (strtolower($value_field) == "name") {
								$final_data[$value_field] = $this->get_username();
							} else {
								if (is_array($this->users[$value_field])) {
									$temp = [];
									foreach ($this->users[$value_field] as $key_field_arr => $value_field_arr) {
										$temp[] = $value_field_arr;
									}
									$final_data[$value_field] = $temp;
								} else {
									$final_data[$value_field] = $this->users[$value_field];
								}
							}
						}
						$queryString = "";
						foreach ($final_data as $key => $value) {
							if (is_array($value)) {
								$value = implode(',', $value);
							}
							$queryString .= "$key=$value&";
						}

						// Remove the trailing "&" from the string
						$queryString = rtrim($queryString, '&');

						return $queryString;
					} else {
						$final_data = [];
						foreach ($this->to_array($field_name) as $key_field => $value_field) {
							if (strtolower($value_field) == "name") {
								$final_data[$value_field] = $this->get_username();
							} else {
								if (is_array($this->users[$value_field])) {
									$temp = [];
									foreach ($this->users[$value_field] as $key_field_arr => $value_field_arr) {
										$temp[] = $value_field_arr;
									}
									$final_data[$value_field] = $temp;
								} else {
									$final_data[$value_field] = $this->users[$value_field];
								}
							}
						}
						$queryString = "";
						foreach ($final_data as $key => $value) {
							if (is_array($value)) {
								$value = implode(',', $value);
							}
							$queryString .= "$key=$value&";
						}

						// Remove the trailing "&" from the string
						$queryString = rtrim($queryString, '&');

						return $queryString;
					}
					break;
				default:
					/**
					 * In case, Multi Select Ex. "name,email,multiselect"
					 * return : "fistname lastname,a@email.com,select1,select2"
					 */
					if ($this->is_multi_select_fields($field_name)) {
						$final_data = [];
						foreach ($this->to_array($field_name) as $key_field => $value_field) {
							if (strtolower($value_field) == "name") {
								$final_data[] = $this->get_username();
							} else {
								if (is_array($this->users[$value_field])) {
									$temp = [];
									foreach ($this->users[$value_field] as $key_field_arr => $value_field_arr) {
										$temp[] = $value_field_arr;
									}
									$final_data[] = $temp;
								} else {
									$final_data[] = $this->users[$value_field];
								}
							}
						}
						$result = '';
						foreach ($final_data as $value) {
							if (is_array($value)) {
								$result .= implode(',', $value) . ',';
							} else {
								$result .= $value . ',';
							}
						}

						// Remove the trailing comma
						$result = rtrim($result, ',');

						return $result;
					}
					/**
					 * Case Single : Ex. "email"
					 */
					else {
						$final_data = [];
						foreach ($this->to_array($field_name) as $key_field => $value_field) {
							if (strtolower($value_field) == "name") {
								$final_data[] = $this->get_username();
							} else {
								if (is_array($this->users[$value_field])) {
									$temp = [];
									foreach ($this->users[$value_field] as $key_field_arr => $value_field_arr) {
										$temp[] = $value_field_arr;
									}
									$final_data[] = $temp;
								} else {
									$final_data[] = $this->users[$value_field];
								}
							}
						}
						$result = '';
						foreach ($final_data as $value) {
							if (is_array($value)) {
								$result .= implode(',', $value) . ',';
							} else {
								$result .= $value . ',';
							}
						}

						// Remove the trailing comma
						$result = rtrim($result, ',');

						return $result;
					}
					break;
			}
		}

		public function get_users()
		{
			return $this->users;
		}

		public function set_db_fields()
		{
			$parameters = array();
			$parameters["db_title"] = $this->db_title;
			$this->_sign_params($parameters);
			$result = $this->request_spiral_api($this->get_api_url(), "database/get", $parameters);
			$db_field_title = [];
			$db_field_type = [];
			if (isset($result["schema"]["fieldList"])) {
				foreach ($result["schema"]["fieldList"] as $item) {

					$db_field_title[] = $item["title"];
					$db_field_type[] = $item["type"];
				}
				$this->db_field_title = $db_field_title;
				$this->db_field_type = $db_field_type;
			}
		}

		public function request_spiral_api($api_url, $api_path, $params)
		{
			if ($api_url === null) {
				return null;
			}

			$api_headers = array(
				"X-SPIRAL-API" => "${api_path}/request",
				"Content-Type" => "application/json; charset=UTF-8"
			);

			$args = array(
				'headers' => $api_headers,
				'body' => json_encode($params)
			);

			$response = wp_remote_post($api_url, $args);

			if (is_wp_error($response)) {
				return null;
			}

			$result = json_decode($response["body"], true);
			return $result;
		}

		protected function _sign_params(&$params)
		{
			$params["spiral_api_token"] = $this->token;
			$params["passkey"] = time();
			$key = $params["spiral_api_token"] . "&" . $params["passkey"];
			$params["signature"] = hash_hmac('sha1', $key, $this->token_secret, false);
		}

		public function get_api_url()
		{
			if ($this->api_url === null) {
				$locator_url = "https://www.pi-pe.co.jp/api/locator";

				$params = array();
				$params["spiral_api_token"] = $this->token;

				$result = $this->request_spiral_api($locator_url, "locator/apiserver", $params);

				if (!$result or $result["code"] != "0") {
					return null;
				}
				$this->api_url = $result["location"];
			}

			return $this->api_url;
		}

		public function login_area($area_title, $id = null, $key = null, $password = null)
		{
			$parameters = array();
			$parameters["my_area_title"] = $area_title;
			$parameters["url_type"] = 2;

			if ($id) {
				$parameters["id"] = $id;
			}
			if ($key) {
				$parameters["key"] = $key;
			}
			if ($password) {
				$parameters["password"] = $password;
			}

			$this->_sign_params($parameters);

			$result = $this->request_spiral_api($this->get_api_url(), "area/login", $parameters);
			return $result;
		}

		public function logout_area($area_title, $session_id)
		{
			$parameters = array();
			$parameters["my_area_title"] = $area_title;
			$parameters["jsessionid"] = $session_id;

			$this->_sign_params($parameters);

			$result = $this->request_spiral_api($this->get_api_url(), "area/logout", $parameters);
			$this->area_status = null;
			if ($result !== null && isset($result['code']) && (int)$result['code'] === 0) {
				return $result['url'];
			} else {
				return null;
			}
		}

		public function get_area_status($area_title, $session_id)
		{
			// To prevent on many request
			if ($this->area_status !== null) {
				return $this->area_status;
			}
			$parameters = array();
			$parameters["my_area_title"] = $area_title;
			$parameters["jsessionid"] = $session_id;

			$this->_sign_params($parameters);

			$result = $this->request_spiral_api($this->get_api_url(), "area/status", $parameters);
			if ($result !== null && isset($result['code']) && (int)$result['code'] === 0) {
				$this->area_status = true;
				return (int)$result['status'] === 1;
			} else {
				$this->area_status = false;
				return null;
			}
		}

		public function get_area_mypage($area_title, $session_id, $mypage_id)
		{
			$parameters = array();
			$parameters["my_area_title"] = $area_title;
			$parameters["jsessionid"] = $session_id;
			$parameters["my_page_id"] = $mypage_id;
			$parameters["url_type"] = 2;


			$this->_sign_params($parameters);

			$result = $this->request_spiral_api($this->get_api_url(), "area/mypage", $parameters);

			if ($result !== null && isset($result['code']) && (int)$result['code'] === 0) {
				return $result['url'];
			} else {
				return null;
			}
		}

		public function map_selectable_fields($labels, $data_values)
		{
			$data_values_arr = str_getcsv($data_values);
			$mapper = [];
			foreach ($data_values_arr as $value) {
				if (array_key_exists($value, $labels)) {
					$mapper[$value] = $labels[$value];
				}
			}
			return $mapper;
		}

		public function get_db_columns($db_title)
		{
			$parameters = array();
			$parameters["db_title"] = $db_title;
			$this->_sign_params($parameters);
			$result = $this->request_spiral_api($this->get_api_url(), "database/get", $parameters);

			if ($result !== null && isset($result['code']) && (int)$result['code'] === 0) {
				$columns = ["id"];
				for ($i = 0; $i < count($result["schema"]["fieldList"]); $i++) {
					$column = $result["schema"]["fieldList"][$i];
					$columns[] = $column["title"];
				}
				return $columns;
			} else {
				return null;
			}
		}

		public function get_db_columns_types($db_title)
		{
			$parameters = array();
			$parameters["db_title"] = $db_title;
			$this->_sign_params($parameters);
			$result = $this->request_spiral_api($this->get_api_url(), "database/get", $parameters);
			if ($result !== null && isset($result['code']) && (int)$result['code'] === 0) {
				$columnsTypes = ['id'];
				for ($i = 0; $i < count($result["schema"]["fieldList"]); $i++) {
					$columnTypes = $result["schema"]["fieldList"][$i];
					$columnsTypes[] = $columnTypes["type"];
				}
				return $columnsTypes;
			} else {
				return null;
			}
		}

		public function get_extraction_rule($area_title, $db_title, $session_id, $id, $select_name)
		{
			$parameters = array();
			$parameters["search_condition"] = array(array("name" => "id", "value" => $id, "operator" => "="));
			$parameters["jsessionid"] = $session_id;
			$parameters["select_name"] = $select_name;
			$parameters["db_title"] = $db_title;

			$this->_sign_params($parameters);

			$result = $this->request_spiral_api($this->get_api_url(), "database/select", $parameters);
			if ($result !== null && isset($result['code']) && (int)$result['code'] === 0) {
				return $result['count'];
			} else {
				return null;
			}
		}

		public function get_table_data($area_title, $session_id, $search_title, $options = null)
		{
			// If already has user data
			if ($this->user_data != null) {
				return $this->user_data;
			}
			$parameters = array();
			if ($options && is_array($options)) {
				$parameters = $options;
			}
			$parameters["my_area_title"] = $area_title;
			$parameters["jsessionid"] = $session_id;
			$parameters["search_title"] = $search_title;

			$this->_sign_params($parameters);

			$result = $this->request_spiral_api($this->get_api_url(), "table/data", $parameters);

			if ($result !== null && isset($result['code']) && (int)$result['code'] === 0) {
				$this->user_data = $result;
				return $result;
			} else {
				return null;
			}
		}

		public function get_id($area_title,$area_session_id,$search_title)
		{
			
			// Get table data and handle potential null values
			$response = $this->get_table_data($area_title, $area_session_id, $search_title);

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
			return $result[$this->identification_key];
		}
	}

endif; // Class exists