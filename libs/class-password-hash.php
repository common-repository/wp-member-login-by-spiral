<?php

class WPMLS_Password_Hash
{

	public function wpmls_encrypt_setting_field($plaintext, $key)
	{
		// already jak soar or invalid lek somngat
		if (strlen($plaintext) === 152 || strlen($plaintext) === 128) {
			return $plaintext;
		}

		if (current_user_can('administrator')) {
			$lekjbol = get_user_meta(1, 'soarsomngat', true);
			$lekderm = $plaintext;
			$ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
			$iv = random_bytes($ivlen);
			$lek_kae_rouch_raw = openssl_encrypt($lekderm, $cipher, $key . $lekjbol, $options = OPENSSL_RAW_DATA, $iv);
			$hmac = hash_hmac('sha256', $lek_kae_rouch_raw, $key . $lekjbol, $as_binary = true);
			$lek_kae_rouch = base64_encode($iv . $hmac . $lek_kae_rouch_raw);
			return $lek_kae_rouch;
		}
	}

	public function wpmls_decrypt_setting_field($plaintext, $key)
	{
		// already jak soar or invalid lek somngat
		if (strlen($plaintext) !== 152 && strlen($plaintext) !== 128) {
			return $plaintext;
		}
		$lekjbol = get_user_meta(1, 'soarsomngat', true);
		$c = base64_decode($plaintext);
		$ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
		$iv = substr($c, 0, $ivlen);
		$hmac = substr($c, $ivlen, $sha2len = 32);
		$lek_kae_rouch_raw = substr($c, $ivlen + $sha2len);
		$original_lekderm = openssl_decrypt($lek_kae_rouch_raw, $cipher, $key . $lekjbol, $options = OPENSSL_RAW_DATA, $iv);
		$calcmac = hash_hmac('sha256', $lek_kae_rouch_raw, $key . $lekjbol, $as_binary = true);
		if ($c) {
			if (hash_equals($hmac, $calcmac)) {
				return $original_lekderm;
			}
		}
	}

	function encrypt($string, $key = 5)
	{
		$result = '';
		for ($i = 0, $k = strlen($string); $i < $k; $i++) {
			$char = substr($string, $i, 1);
			$keychar = substr($key, ($i % strlen($key)) - 1, 1);
			$char = chr(ord($char) + ord($keychar));
			$result .= $char;
		}
		return base64_encode($result);
	}

	function decrypt($string, $key = 5)
	{
		$result = '';
		$string = base64_decode($string);
		for ($i = 0, $k = strlen($string); $i < $k; $i++) {
			$char = substr($string, $i, 1);
			$keychar = substr($key, ($i % strlen($key)) - 1, 1);
			$char = chr(ord($char) - ord($keychar));
			$result .= $char;
		}
		return $result;
	}

	function encrypt_key($data, $key)
	{
		$cipher = "aes-256-cbc";
		$iv_length = openssl_cipher_iv_length($cipher);
		$iv = openssl_random_pseudo_bytes($iv_length);
		$encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);
		return base64_encode($iv . $encrypted);
	}

	function decrypt_key($data, $key)
	{
		$cipher = "aes-256-cbc";
		$data = base64_decode($data);
		$iv_length = openssl_cipher_iv_length($cipher);
		$iv = substr($data, 0, $iv_length);
		$data = substr($data, $iv_length);
		return openssl_decrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);
	}


	// Function to encrypt array to string
	function encrypt_array_to_String($array, $key)
	{
		$serialized = serialize($array);
		$encrypted = openssl_encrypt($serialized, 'AES-256-CBC', $key, 0, substr(md5($key), 0, 16));
		return base64_encode($encrypted);
	}

	// Function to decrypt string to array
	function decrypt_string_to_array($string, $key)
	{
		$decrypted = openssl_decrypt(base64_decode($string), 'AES-256-CBC', $key, 0, substr(md5($key), 0, 16));
		return unserialize($decrypted);
	}
}
