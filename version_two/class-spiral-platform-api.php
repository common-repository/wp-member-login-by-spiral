<?php

/**
 * SPIRAL Platform API class
 *
 * @package   Spiral_Member_Login
 * @author    PIPED BITS Co.,Ltd.
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;
if (!class_exists('WPMLS_Spiral_Platform_Api')) :
  /**
   * =======================================
   * WPMLS_Spiral_Platform_Api
   * =======================================
   */
  class WPMLS_Spiral_Platform_Api
  {
    private $token = null;

    private $api_url = 'https://api.spiral-platform.com/v1';

    private $x_spiral_api_version = '1.1';

    public $app_id        = null;
    public $db_id         = null;
    public $wpmls_site_id       = null;
    public $wpmls_authentication_id   = null;
    private $user_data     = null;
    private $area_status     = null;
    private $users = [];

    public function __construct($token)
    {
      $this->token         = $token;
    }

    public function set_options($app_id, $db_id, $wpmls_site_id, $wpmls_authentication_id)
    {
      $this->app_id        = $app_id;
      $this->db_id         = $db_id;
      $this->wpmls_site_id        = $wpmls_site_id;
      $this->wpmls_authentication_id   = $wpmls_authentication_id;
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

    public function set_users($record_id)
    {
      $identifier = $this->get_id($record_id);

      $app_id = $this->app_id;
      $db_id = $this->db_id;

      $result = $this->request_spiral_api('get', "/apps/$app_id/dbs/$db_id/records/$identifier");
      if (!isset($result['item'])) {
        return null;
      }
      $this->users =  $result['item'];
    }

    public function set_users_mapper($record_id)
    {
      $identifier = $this->get_id($record_id);
      $app_id = $this->app_id;
      $db_id = $this->db_id;

      $result = $this->request_spiral_api('get', "/apps/$app_id/dbs/$db_id/records/$identifier");
      $users_data = $result;
      if (!isset($users_data['item'])) {
        return null;
      }
      // Modify item array based on options
      foreach ($users_data['item'] as $key => $value) {

        // Check if the key exists in options
        if (isset($users_data['options'][$key])) {
          // If it's an array, map each value
          if (is_array($value)) {
            $mapped_values = [];
            foreach ($value as $v) {
              $mapped_values[] = $users_data['options'][$key][$v] ?? $v;
            }
            $users_data['item'][$key] = $mapped_values;
          } else {
            // If it's a single value, map it directly
            $users_data['item'][$key] = [$value => $users_data['options'][$key][$value]];
          }
        }
      }
      unset($users_data['options']);
      $this->users = $users_data['item'];
    }

    public function get_id($login_id)
    {
      $app_id = $this->app_id;
      $db_id = $this->db_id;

      $result = $this->request_spiral_api('get', "/apps/$app_id/dbs/$db_id/records");
      $users_data = $result;

      $data = $users_data['items'];
      $email = $login_id;

      $foundId = null;

      foreach ($data as $item) {
        if ($item['email'] === $email) {
          $foundId = $item['_id'];
          break; // Exit the loop after finding the first match
        }
      }

      if (isset($foundId))
        return $foundId;
    }

    public function get_users()
    {
      return $this->users;
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
    /**
     * Get User data with key => value
     */
    public function get_users_data($field_name, $options = '')
    {
      if (empty($this->users) || empty($field_name)) {
        return null;
      }
      switch ($options) {
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

    public function request_spiral_api($method, $api_path, $params = null)
    {
      if ($api_path === null || $api_path === '') {
        return null;
      }

      $api_headers = array(
        "Authorization" => "Bearer $this->token",
        'Content-Type' => 'application/json',
      );

      if ($this->x_spiral_api_version !== null) {
        $api_headers['X-Spiral-Api-Version'] = $this->x_spiral_api_version;
      }

      $args = array(
        'headers' => $api_headers
      );

      $response = null;
      $full_path = $this->get_api_url($api_path);
      // for console checkc only
      // d("Request ... $method $full_path");
      switch (strtoupper($method)) {
        case 'GET':
          if ($params != null) {
            $full_path .= $this->generate_paramers($params);
          }

          $response = wp_remote_get($full_path, $args);
          break;
        case 'POST':
          if ($params != null) {
            $args['body'] = json_encode($params);
          }
          $response = wp_remote_post($full_path, $args);
          break;
        default:
          break;
      }

      if ($response == null || is_wp_error($response)) {
        return null;
      }

      $result = json_decode($response["body"], true);
      return $result;
    }

    public function get_api_url($path = '')
    {
      return  $this->api_url . $path;
    }

    public function login_area($wpmls_site_id, $wpmls_authentication_id, $id = null, $password = null)
    {
      $parameters = array();

      if ($id) {
        $parameters["id"] = $id;
      }
      if ($password) {
        $parameters["password"] = $password;
      }

      $result = $this->request_spiral_api('POST', "/sites/$wpmls_site_id/authentications/$wpmls_authentication_id/login", $parameters);
      return $result;
    }

    public function logout($token = null)
    {
      $parameters = array();
      if ($token) {
        $parameters["token"] = $token;
      }
      $wpmls_site_id = $this->wpmls_site_id;
      $wpmls_authentication_id = $this->wpmls_authentication_id;

      $this->request_spiral_api('POST', "/sites/$wpmls_site_id/authentications/$wpmls_authentication_id/logout", $parameters);
      $this->area_status = null;
    }

    public function get_area_status($wpmls_site_id, $wpmls_authentication_id, $session_token = null)
    {
      // To prevent on many request
      if ($this->area_status !== null) {
        return $this->area_status;
      }
      $parameters = array();
      if ($session_token) {
        $parameters["token"] = $session_token;
      }

      $result = $this->request_spiral_api('POST', "/sites/$wpmls_site_id/authentications/$wpmls_authentication_id/status", $parameters);

      if ($result !== null && isset($result['status'])) {
        $this->area_status = $result['status'];
      }
      return $this->area_status;
    }

    public function get_user_action_url($token, $path)
    {
      $parameters = array();
      if ($token) {
        $parameters["token"] = $token;
      }
      if ($token) {
        $parameters["path"] = $path;
      }

      $wpmls_site_id = $this->wpmls_site_id;
      $wpmls_authentication_id = $this->wpmls_authentication_id;

      $result = $this->request_spiral_api('POST', "/sites/$wpmls_site_id/authentications/$wpmls_authentication_id/oneTimeLogin", $parameters);

      if (isset($result['url'])) {
        return $result['url'];
      }
      return null;
    }


    public function get_user($appid, $dbid, $record_id)
    {
      // If already has user data
      if ($this->user_data != null) {
        return $this->user_data;
      }
      if (!isset($dbid))
        return null;
      if (!isset($dbid))
        return null;
      if (!isset($record_id))
        return null;

      $result = $this->request_spiral_api('get', "/apps/$appid/dbs/$dbid/records/$record_id");

      if ($result !== null) {
        $this->user_data = $result;
        return $result;
      } else {
        return null;
      }
    }

    public function get_db_columns($appid, $dbid)
    {
      $result = $this->request_spiral_api('get', "/apps/$appid/dbs/$dbid");

      if ($result !== null && isset($result["fields"])) {
        $fields = $result["fields"];
        $count_fields = count($result["fields"]);
        $columns = [];
        for ($i = 0; $i < $count_fields; $i++) {
          $columns[] = $fields[$i]["name"];
        }
        return $columns;
      } else {
        return null;
      }
    }

    /**
     * Generate parameters to string path
     * @return string
     */
    private function generate_paramers($parameters)
    {
      if ($parameters == null || $parameters == '' || !isset($parameters)) {
        return '';
      }
      $parameter_search_str = '';
      $args['body'] = json_encode($parameters);
      foreach ($parameters as $key => $value) {
        $parameter_search_str .= $parameter_search_str === '' ? '?' : '&';
        $parameter_search_str .= "$key=$value";
      }
      return $parameter_search_str;
    }


    public function check_selectable_field($appid, $dbid, $select_field)
    {
      $allow_select_fields = ["select", "multiselect", "integer"];

      $result = $this->request_spiral_api('get', "/apps/$appid/dbs/$dbid");

      if ($result !== null && isset($result["fields"])) {
        $fields = $result["fields"];
        $count_fields = count($result["fields"]);
        $field_name = [];
        $field_type = [];
        for ($i = 0; $i < $count_fields; $i++) {
          $field_name[] = $fields[$i]["name"];
          $field_type[] = $fields[$i]["type"];
        }
        $fields =  array_combine($field_name, $field_type);

        if (isset($fields[$select_field])) {

          $field_value =  $fields[$select_field];
          if (in_array($field_value, $allow_select_fields)) {
            return true;
          } else {
            return false;
          }
        } else {
          return null;
        }
      } else {
        return null;
      }
    }
  }

endif; // Class exists
