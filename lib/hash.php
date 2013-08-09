<?php
class H {
	public static function base62 ($number, $from_base = 10, $to_base=62) {
		if($to_base > 62 || $to_base < 2) {
			trigger_error("Invalid base (".$to_base."). Max base can be 62. Min base can be 2.", E_USER_ERROR);
		}
		//OPTIMIZATION: no need to convert 0
		if("{$number}" === '0') return 0;

		//OPTIMIZATION: if to and from base are same.
		if($from_base == $to_base)	return $number;

		//OPTIMIZATION: if base is lower than 36, use PHP internal function
		if($from_base <= 36 && $to_base <= 36) return base_convert($number, $from_base, $to_base);

		// char list starts from 0-9 and then small alphabets and then capital alphabets
		// to make it compatible with eixisting base_convert function
		$charlist = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		if($from_base < $to_base) {
			// if converstion is from lower base to higher base
			// first get the number into decimal and then convert it to higher base from decimal;

			if($from_base != 10){
				$decimal = self::base62($number, $from_base, 10);
			} else {
				$decimal = intval($number);
			}

			//get the list of valid characters
			$charlist = substr($charlist, 0, $to_base);

			if($number == 0) {
				return 0;
			}
			$converted = '';
			while($number > 0) {
				$converted = $charlist{($number % $to_base)} . $converted;
				$number = floor($number / $to_base);
			}
			return $converted;
		} else {
			// if conversion is from higher base to lower base;
			// first convert it into decimal and the convert it to lower base with help of same function.
			$number = "{$number}";
			$length = strlen($number);
			$decimal = 0;
			$i = 0;
			while($length > 0) {
				$char = $number{$length-1};
				$pos = strpos($charlist, $char);
				if($pos === false){
					trigger_error("Invalid character in the input number: ".($char), E_USER_ERROR);
				}
				$decimal += $pos * pow($from_base, $i);
				$length --;
				$i++;
			}
			return self::base62($decimal, 10, $to_base);
		}
	}

	public static function salt ($len = 16) {
		$salt = mcrypt_create_iv($len, MCRYPT_DEV_URANDOM);
		$salt = base64_encode($salt);
		$salt = substr($salt, 0, ($len - strlen($salt)));
		return $salt;
	}

	public static function token ($len = 64, $force_secure = false, $try = 0) {

		$token = base64_encode(openssl_random_pseudo_bytes($len/2*1.5, $strong));

		// try to create a secure token 3 times
		if ($force_secure === true && $strong === false)
		{
			if ($try > 3)
			{
				throw new Exception("Could not generate secure token");
			}
			else
			{
				return self::token($len, $try++);
			}
		}

		return $token;
	}

	public static function hash ($str, $salt = "") {
		return crypt($str, $salt);
	}

	public static function hashPassword ($pw, $salt = null) {
		if(is_null($salt)) $salt = self::salt(22);
		$pw = crypt($pw, $salt);
		$pw .= $salt;
		return $pw;
	}

	public static function verifyPassword ($pw, $hash) {
		$salt = substr($hash, strlen($hash) - 22, 22);
		$g = self::hashPassword($pw,$salt);
		return $hash == self::hashPassword($pw,$salt);
	}

	public static function shortHash ($str, $salt = VAULT_SECRET) {
		return substr(self::hash($str, $salt), 0, 12);
	}

	public static function fiddleProtect ($str) {
		return $str . self::shortHash($str);
	}

	public static function fiddleCheck ($str) {
		// check the hash
		$hash  = substr($str, strlen($str) - 12);
		$plain = substr($str, 0, -12);

		if (self::shortHash($plain) !== $hash)
		{
			$plain = null;
		}
		return $plain;
	}

	public static function encrypt ($plain, $password, $alg = "rijndael-256", $mode = "ofb") {
		// append the plain texts hash
		$plain = self::fiddleProtect($plain);

		// prepare cipher, iv, password
		$cipher = mcrypt_module_open($alg, "", $mode, "");
		$vector = mcrypt_create_iv(mcrypt_get_iv_size($alg, $mode), MCRYPT_DEV_URANDOM);
		$password = substr(md5($password), 0 , mcrypt_get_key_size($alg, $mode));

		// initialize
		mcrypt_generic_init($cipher, $password, $vector);

		// encrypt
		$encrypted = mcrypt_generic($cipher, $plain);

		// terminate
		mcrypt_generic_deinit($cipher);

		// append iv to encrypted string
		$encrypted = base64_encode($vector) . "." . base64_encode($encrypted);

		return $encrypted;
	}

	public static function decrypt ($encrypted, $password, $alg = "rijndael-256", $mode = "ofb") {
		// prepare cipher and password
		$cipher = mcrypt_module_open($alg, "", $mode, "");
		$password = substr(md5($password), 0 , mcrypt_get_key_size($alg, $mode));

		// get our encrypted text and the iv
		list($vector, $encrypted) = explode(".", $encrypted, 2);
		$vector = base64_decode($vector);
		$encrypted = base64_decode($encrypted);

		// initialize
		mcrypt_generic_init($cipher, $password, $vector);

		// decrypt
		$decrypted = mdecrypt_generic($cipher, $encrypted);

		// terminate
		mcrypt_generic_deinit($cipher);

		return self::fiddleCheck($decrypted);

	}
}
?>
