<?php
/**
*	General Utility Class
*/
class Util {

	//Check if it is a UUID
	static function is_uuid($uuid) {
		return preg_match('/^\{?[0-9a-f]{8}[0-9a-f]{4}[0-9a-f]{4}[0-9a-f]{4}[0-9a-f]{12}\}?$/i', trim((String) @$uuid));
	}

	//Build a UUID
	static function uuid() {
		return sprintf( '%04x%04x%04x%04x%04x%04x%04x%04x',
		mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0x0fff ) | 0x4000,
		mt_rand( 0, 0x3fff ) | 0x8000,
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff )
		);
	}

	//Key (private/public/common) functions
	static function key_gen_private($bits=64) {
		return bin2hex(rand(0,pow(2,$bits)));
	}

	static function key_gen_public($private_key,$generator=3,$modulus=0x7FFFFFFF) {
		return bin2hex ( ( pow($generator,$private_key) % $modulus ) );
	}

	static function key_gen_common($client,$private_key,$modulus=0x7FFFFFFF) {
		return bin2hex ( ( pow($client,$private_key) % $modulus ) );
	}

	// Encrypt Function
	static function encrypt($encrypt, $mc_key) {
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
		$passcrypt = trim(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $mc_key, trim($encrypt), MCRYPT_MODE_ECB, $iv));
		$encode = base64_encode($passcrypt);
		return $encode;
	}

	// Decrypt Function
	static function decrypt($decrypt, $mc_key) {
		$decoded = base64_decode($decrypt);
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
		$decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $mc_key, trim($decoded), MCRYPT_MODE_ECB, $iv));
		return $decrypted;
	}

	//Create a SHA1 hash of two variables, used in storing 1 way info
	static function hash($email,$password) {
		$em_hash = sprintf("%u",crc32($email));
		return hash('sha1',$em_hash.$password);
	}


	static function timeAgo($from_time, $include_seconds = true) {
		$to_time = time();
		$mindist = round(abs($to_time - $from_time) / 60);
		$secdist = round(abs($to_time - $from_time));
		if ($mindist >= 0 and $mindist <= 1) {
			if (!$include_seconds) {
				return ($mindist == 0) ? 'less than a minute' : '1 minute';
			} else {
				if ($secdist >= 0 and $secdist <= 4) {
					return 'less than 5 seconds';
				} elseif ($secdist >= 5 and $secdist <= 9) {
					return 'less than 10 seconds';
				} elseif ($secdist >= 10 and $secdist <= 19) {
					return 'less than 20 seconds';
				} elseif ($secdist >= 20 and $secdist <= 39) {
					return 'half a minute';
				} elseif ($secdist >= 40 and $secdist <= 59) {
					return 'less than a minute';
				} else {
					return '1 minute';
				}
			}
		} elseif ($mindist >= 2 and $mindist <= 44) {
			return $mindist . ' minutes';
		} elseif ($mindist >= 45 and $mindist <= 89) {
			return 'about 1 hour';
		} elseif ($mindist >= 90 and $mindist <= 1439) {
			return 'about ' . round(floatval($mindist) / 60.0) . ' hours';
		} elseif ($mindist >= 1440 and $mindist <= 2879) {
			return '1 day';
		} elseif ($mindist >= 2880 and $mindist <= 43199) {
			return 'about ' . round(floatval($mindist) / 1440) . ' days';
		} elseif ($mindist >= 43200 and $mindist <= 86399) {
			return 'about 1 month';
		} elseif ($mindist >= 86400 and $mindist <= 525599) {
			return round(floatval($mindist) / 43200) . ' months';
		} elseif ($mindist >= 525600 and $mindist <= 1051199) {
			return 'about 1 year';
		} else {
			return 'over ' . round(floatval($mindist) / 525600) . ' years';
		}
	}
}

//Outside of Static, for debugging code quickly
function dump(&$var, $var_name = NULL, $indent = NULL, $reference = NULL) {
	$do_dump_indent = "<span style='color:#666666;'>|</span> &nbsp;&nbsp; ";
	$reference = $reference.$var_name;
	$keyvar = 'the_do_dump_recursion_protection_scheme'; $keyname = 'referenced_object_name';
	$output = "";
	// So this is always visible and always left justified and readable
	$output .=  "<div style='text-align:left; background-color:white; font: 100% monospace; color:black;'>";

	if (is_array($var) && isset($var[$keyvar])) {
	    $real_var = &$var[$keyvar];
	    $real_name = &$var[$keyname];
	    $type = ucfirst(gettype($real_var));
	    $output .=  "$indent$var_name <span style='color:#666666'>$type</span> = <span style='color:#e87800;'>&amp;$real_name</span><br>";
	} else {
	    $var = array($keyvar => $var, $keyname => $reference);
	    $avar = &$var[$keyvar];

	    $type = ucfirst(gettype($avar));
	    if($type == "String") $type_color = "<span style='color:green'>";
	    elseif($type == "Integer") $type_color = "<span style='color:red'>";
	    elseif($type == "Double"){ $type_color = "<span style='color:#0099c5'>"; $type = "Float"; }
	    elseif($type == "Boolean") $type_color = "<span style='color:#92008d'>";
	    elseif($type == "NULL") $type_color = "<span style='color:black'>";

	    if(is_array($avar)) {
	        $count = count($avar);
	        $output .=  "$indent" . ($var_name ? "$var_name => ":"") . "<span style='color:#666666'>$type ($count)</span><br>$indent(<br>";
	        $keys = array_keys($avar);
	        foreach($keys as $name) {
	            $value = &$avar[$name];
	            dump($value, "['$name']", $indent.$do_dump_indent, $reference);
	        }
	        $output .=  "$indent)<br>";
	    } elseif(is_object($avar)) {
	        $output .=  "$indent$var_name <span style='color:#666666'>$type</span><br>$indent(<br>";
	        foreach($avar as $name=>$value) dump($value, "$name", $indent.$do_dump_indent, $reference);
	        $output .=  "$indent)<br>";
	    } elseif(is_int($avar)) $output .=  "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> $type_color".htmlentities($avar)."</span><br>";
	    elseif(is_string($avar)) $output .=  "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> $type_color\"".htmlentities($avar)."\"</span><br>";
	    elseif(is_float($avar)) $output .=  "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> $type_color".htmlentities($avar)."</span><br>";
	    elseif(is_bool($avar)) $output .=  "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> $type_color".($avar == 1 ? "TRUE":"FALSE")."</span><br>";
	    elseif(is_null($avar)) $output .=  "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> {$type_color}NULL</span><br>";
	    else $output .=  "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> ".htmlentities($avar)."<br>";
	    $var = $var[$keyvar];
	}

	$output .=  "</div>";
	return $output;
}


?>