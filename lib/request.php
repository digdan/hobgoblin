<?
/**
 * Class Request
 * 		Request handles the wrangling and to help sanitize input data
 * 		Also used to format ajax messages
 */
class Request {
	static $log;
	static $error;
	static $json='';
	static $method='';
	static $ajax=false;

	function __construct( ) {
		self::isAjax();
		self::$method = $_SERVER["HTTP_REQUEST_METHOD"];
	}

	static function isAjax() {
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			self::$ajax = true;
		} else {
			self::$ajax = false;
		}
		return self::$ajax;
	}

	static function method($test=NULL) {
		if (is_null($test)) return self::$method;
		return (strtolower(trim(self::$method)) == trim(strtolower($test)));
	}

	static function r($name=NULL) {
		if (! is_null($name) ) {
			if (isset($_REQUEST[$name])) {
				return $_REQUEST[$name];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	static function populate( $obj, array $params, $filter='none') { // Filter will filter the results accordingly. Can be formmated as list : "html,xss"
		foreach($params as $k=>$v) {
			if (is_numeric($k)) { //Non-associative, use matching request variable
				$data = self::r($v);
				if (strstr($filter,'html')) {
					$data = self::filterHTML(self::r($v));
				}
				if (strstr($filter,'xss')) {
					$data = self::filterXSS(self::r($v));
				}
				$obj->$v = $data;
			} else {
				if (property_exists($obj,$k)) {
					$obj->$k = $v;
				}
			}
		}
	}

	static function filterHTML( $data ) {
		$data = strip_tags($data);
		$data = filter_var($data, FILTER_SANITIZE_STRING);
		return $data;
	}

	static function filterXSS( $data ) {
		// Fix &entity\n;
		$data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
		$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
		$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
		$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

		// Remove any attribute starting with "on" or xmlns
		$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

		// Remove javascript: and vbscript: protocols
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

		// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

		// Remove namespaced elements (we do not need them)
		$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
		do {
			// Remove really unwanted tags
			$old_data = $data;
			$data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
		}
		while ($old_data !== $data);
		return $data;
	}

	static function md5($name=NULL,$cat=NULL) {
		if ( is_null($cat)) $cat = "";
		return md5($cat.self::r($name));
	}

	static function json($ok = false, $values = NULL) {
		$barray = $values;
		$barray["ok"] = $ok;
		$barray["time"] = time();
		if (isset($values["error"])) {
			self::$error = $values["error"];
		}
		self::$json = json_encode($barray);
		return self::$json;
	}
}
?>
