<?php
/**
*	Session Object
*
* 	Session singleton object
*
*/
	class Session {
		static $vars;
		static $user;

		static function logout() {
			unset($_SESSION["user"]);
			session_destroy();
			session_start();
		}

		static function mustAuth() {
			if ( ! self::user()) {
				HG::force(401,'Authentication Required');
			}
		}

		static function user($user_id=NULL) {
			if (! is_null($user_id) ) {
				$_SESSION["user"] = $user_id;
				return true;
			} else {
				if (isset($_SESSION["user"])) return $_SESSION["user"];
			}
			return false;
		}

		static function email($user_id=NULL) {
			if (is_null($user_id)) {
				$user_id = self::user();
			}
			$user = R::load('users',$user_id);
			if ($user) {
				return $user->email;
			}
			return false;
		}

		static function message($user_id=NULL) {
			if (is_null($user_id)) {
				$user_id = self::user();
			}
			/*
				TODO, check for unread messages
			*/
			return false;
		}

	}
?>