<?php
/**
*	Hobgoblin Framework - used with ReadBeans and AltoRouter
*/

class HG {
	public static $db; //PDO DB
	public static $config;
	public static $scope; //Variables for template

	private static $stem; //Active Controller
	private static $faces; //Multiple objects to facade
	private static $containers;

	private static $hooks; //List of hooks to catch

	const HOOK_CLEAR=0;
	const HOOK_BEFORE=1;
	const HOOK_AFTER=2;


	static function init() { //Construct
		global $config,$db,$router;
		session_start();
		include_once("router.php"); //Request router
		include_once("util.php"); //Collection of utility functions
		include_once("validate.php"); //Request validation library
		include_once("rb.php"); //Readbean ORM
		include_once("request.php");
		include_once("cache.php"); //Cache Subsystem
		include_once("session.php"); //Session Handler

		R::setup($config["db"]["dsn"], $config["db"]["user"],$config["db"]["password"]);

		//Build the faces
		self::face("router", new router() );
		self::face("cache", Cache::getInstance() );		

		self::initContainers(
			array('javascript','css','script','footer')
		); //Build out containers
		self::$hooks = array();

		self::initScope(); //Start new scope
		self::controllers_load(); //Bootstrap controllers
	}

	static function face($name,$face=NULL) { //Push a function to the HG Poly Face
		if (is_object($face)) {
			if ($face instanceof Closure) {
				if ( ! isset(self::$faces["poly"]) ) self::$faces["poly"] = array();
				self::$faces["poly"][$functionName] = $function;				
			} else {
				self::$faces[$name] = $face;				
			}			
		}		
	}

	//*****************************[ Scope/Template System

	static function v($name=NULL,$val=NULL) { //Get/Set scope variables, used with templating
		HG::callHook(__FUNCTION__,HG::HOOK_BEFORE,func_get_args());
		if (is_null($val)) return self::$scope[$name];
		if (strstr($name,".")) {
			$name_parts = explode(".",$name);
			$name = $name_parts[0];
			if (isset(self::$scope[$name]) AND (is_array(self::$scope[$name]))) {
				$sub = self::$scope[$name];
			} else {
				$sub = array();
			}
			$sub[$name_parts[1]] = $val;
			$val = $sub;
		}
		self::$scope[$name] = $val;
		HG::callHook(__FUNCTION__,HG::HOOK_AFTER,func_get_args());
		return new static;
	}

	static function initScope() {
		self::$scope["meta"] = array(
			"title"=>"",
			"description"=>"",
			"keywords"=>""
		);
		self::$scope["active"] = array(
			'current'=>'',
		);
	}

	static function buildScope() {
		global $config;
		self::$scope["config"] = $config;
		foreach(self::$containers as $containerName=>$container) {
			self::$scope[$containerName] = "";
			foreach($container as $line) {
				switch($containerName) {
					case 'css' : self::$scope[$containerName] .= "<link href=\"{$line}\" rel=\"stylesheet\">\n"; break;
					case 'script' : self::$scope[$containerName] .= "<script src=\"{$line}\" type=\"text/javascript\"></script>\n";break;
					default : self::$scope[$containerName] .= "{$line}\n"; break;
				}
			}
		}
	}

	static function initContainers( $containerList ) {
		foreach($containerList as $containerName) {
			self::$containers[$containerName] = array();
		}
	}

	static function url($path,$params) { //Reverse Route Lookup
		self::callHook(__FUNCTION__,HG::HOOK_BEFORE,func_get_args());
		return self::$router->url($path,$params);
		self::callHook(__FUNCTION__,HG::HOOK_AFTER,func_get_args());
	}

	static function display($template,$headerfooter=false) { //Display a template file, extract scope vars into template
		HG::callHook(__FUNCTION__,HG::HOOK_BEFORE,func_get_args());
		self::buildScope();
		extract(self::$scope);
		if ($headerfooter === true) {
			include_once($config["templates"]."/header.php");
			include_once($config["templates"]."/".$template);
			include_once($config["templates"]."/footer.php");
		} else {
			include_once($config["templates"]."/".$template);
		}
		HG::callHook(__FUNCTION__,HG::HOOK_AFTER,func_get_args());
	}

	//*****************************[ Hooks
	static function hook($target,$function=NULL,$priority=HG::HOOK_BEFORE) {
		if ($priority == HG::HOOK_CLEAR) {
			foreach(self::$hooks as $callTarget=>$callHook) {
				foreach($callHook as $pos=>$hook) {
					if (($target == $callTarget) AND ($hook["f"] == $function)) unset(self::$hooks[$callTarget][$pos]);
				}
			}
		} else {
			self::$hooks[$target][] = array("p"=>$priority,"f"=>$function);
		}
		return new static;
	}

	// ex: HG::callHook(__FUNCTION__,HG::HOOK_BEFORE,func_get_args());
	static function callHook($target,$priority,$params=NULL) {
		foreach(self::$hooks as $callTarget=>$callHook) {
			foreach($callHook as $pos=>$hook) {
				if (($target == $callTarget) AND ($hook["p"]==$priority)) {
					return call_user_func_array($hook["f"],$params);
				}
			}
		}
	}

	//*****************************[ System

	static function trace() {
		echo "<!--";
		print_r(debug_backtrace());
		echo "-->";
	}

	static function force($code=NULL,$message = NULL) { //Force a specific HTTP response
		if ($code == "404") header('HTTP/1.0 404 Not Found');
		if ($code == "401") header('HTTP/1.0 401 Authentication Required');
		if (is_null($message)) {
			echo "<H1>{$code}</H1><quote>Page not found</quote>";
		} else {
			echo "<H1>{$code}</H1><quote>{$message}</quote>";
		}

		die();
	}

	static function chain() { //Starts a chain ( todo : omit )
		return new static;
	}

	static function active($target) {
		HG::callHook(__FUNCTION__,HG::HOOK_BEFORE,func_get_args());
		HG::v("active",array(
			"current"=>$target,
			$target=>"active"
		));
		HG::callHook(__FUNCTION__,HG::HOOK_AFTER,func_get_args());
		return new static;
	}


	static function controllers_load() { //Load and init controllers
		$prefix = "controllers/";
		$controllers = glob($prefix."*");
		foreach($controllers as $controller) {
			$pi = pathinfo($controller);
			$controller_base = $pi["filename"];
			if (file_exists($prefix.$controller_base."/".$controller_base.".php")) {
				include_once($prefix.$controller_base."/".$controller_base.".php");
				$controller_name = "control_".$controller_base;
				$reflection = new ReflectionMethod($controller_name,'HGInit');
				if ($reflection->isStatic()) {
					$controller_name::HGInit();
				}
			}
		}
	}

	static function redirect($location="/") { //Redirect
		HG::callHook(__FUNCTION__,HG::HOOK_BEFORE,func_get_args());
		header("Location: {$location}");
		HG::callHook(__FUNCTION__,HG::HOOK_AFTER,func_get_args());
		die();
	}

	static function execute($match) { //Execute route target
		HG::callHook(__FUNCTION__,HG::HOOK_BEFORE,func_get_args());
		if (is_string($match["target"])) {
			if (strstr($match["target"],"#")) {
				$match["target"] = explode("#",$match["target"]);
			} elseif (is_callable($match["target"])) {
				call_user_func($match["target"],$match["params"]);
			}
		}
		if (is_array($match["target"])) {
			if (class_exists($match["target"][0])) {
				self::$stem = new $match["target"][0]();
				call_user_func(array(self::$stem,$match["target"][1]),$match["params"]);
			}
		}
		HG::callHook(__FUNCTION__,HG::HOOK_AFTER,func_get_args());
	}

	static function __callStatic($func,$params) { //Singleton Facade Overload
		$payload = false;
		foreach(self::$faces as $faceName=>$face) { //Check Faces
			if (is_callable(array($face,$func))) { 
				HG::callHook($func,HG::HOOK_BEFORE,$params);
				$payload = call_user_func_array(array($face,$func),$params);
				HG::callHook($func,HG::HOOK_AFTER,$params);
				return $payload;
			}
		}

		foreach(self::$containers as $containerName=>$container) {
			$declare = $params[0];
			if ($func == $containerName) {
				HG::callHook($containerName,HG::HOOK_BEFORE,func_get_args());
				if ( ! is_null( $declare ) ) self::$containers[$containerName][] = $declare;
				HG::callHook($containerName,HG::HOOK_AFTER,func_get_args());
				return new static;
			}
		}

		if (isset(self::$faces["poly"][$func])) {
			$g = self::$faces["poly"][$func];
			HG::callHook($func,HG::HOOK_BEFORE,$params);
			$payload = call_user_func_array($g,$params);
			HG::callHook($func,HG::HOOK_AFTER,$params);
			return $payload;
		}

		echo "Undefined method : {$func}\n";
	}
}
?>
