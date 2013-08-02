<?php
	class control_auth {

		static function HGinit() {
			HG::map("POST","/auth/login", "control_auth#authenticate");
			HG::map("POST","/auth/heir", "control_auth#heir");
			HG::map("GET","/auth/logout", "control_auth#logout");
		}

		public function login() {
			if (Request::$ajax) {
				$this->authenticate();
			} else {
				$this->loginForm();
			}
		}

		public function authenticate() {
			global $config;
			if ( Validate::run(
				array(
					"email"=>array(
						"required"=>TRUE,
						"regex"=>"/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})\$/"
					),
					"password"=>array(
						"required"=>TRUE,
						"min"=>4,
						"max"=>32
					),
				)
			)) {
				$users_raw = R::getAll("SELECT * FROM ".$config["auth"]["user_table"]." WHERE email=? AND password=?", array(Request::r("email"),Request::md5("password")));
				if (count($users_raw) > 0) {
					$user = array_shift($users_raw);
					Session::user($user["id"]); //Logged in
					echo Request::json(true,array("user"=>$user["id"]));
				} else {
					echo Request::json(false,array("error"=>_("Invalid username or password")));
				}
			} else {
				echo Request::$json;
			}
		}

		public function logout() {
			Session::logout();
			header("Location: /");
			die();
		}

		public function register() {
			if ( Validate::run (
				array(
					"email"=>array(
						"required"=>TRUE,
						"regex"=>"/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})\$/",
						"callback"=>"this:email_free"
					),
					"tos"=>array(
						"required"=>TRUE,
					),
					"password"=>array(
						"required"=>TRUE,
						"min"=>4,
						"max"=>32
					)
				)
			)
			) {

				$new_user = R::dispense('users');
				$new_user->email = Request::r("email");
				$new_user->password = Request::md5("password");
				$new_user->created = time();
				$new_user->state = 0;
				$id = R::store($new_user);
				if ($id > 0) {
					echo Request::json( true, array("user_id"=>$id));
				} else {
					echo Request::json( false, array("error"=>_("Unable to create account")));
				}
			} else {
				echo Request::$json;
			}
		}

		public function forgot() {
			global $config,$db;
			if ( Validate::run (
				array(
					"email"=>array(
						"required"=>TRUE,
						"regex"=>"/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})\$/",
						"callback"=>"this:email_exists"
					),
				)
			)) {
				//Find UUID based on email
				$email = Request::r("email");
				$search_results = Search::main(
					array(
						array(
							'key'=>'email',
							'value'=>$email,
							'type'=>'user'
						)
					)
				);
				$forgot_uuid = bin2hex($search_results[0]["uuid"]);
				$u = new user($forgot_uuid);
				$forgot_token = substr(md5(floor(time() / $config["users"]["forgot_token_life"])),0,$config["users"]["forgot_token_size"]);
				$u->forgot_token = $forgot_token;
				$u->update();
				Request::json( true, array("forgot_token"=>$forgot_token));
			}
		}

		public function reset() {
			if ( Validate::run (
				array(
					"email"=>array(
						"required"=>TRUE,
						"regex"=>"/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})\$/",
						"callback"=>"this:email_exists"
					),
					"new_password"=>array(
						"required"=>TRUE,
						"min"=>4,
						"max"=>32
					),
					"forgot_token"=>array(
						"required"=>TRUE,
						"min"=>10,
						"max"=>10
					),
				)
			)) {
				$email = Request::r("email");

				$search_results = Search::main(
					array(
						array(
							'key'=>'email',
							'value'=>$email,
							'type'=>'user'
						)
					)
				);

				$reset_uuid = bin2hex($search_results[0]["uuid"]);
				$u = new user($reset_uuid);
				if (Request::r("forgot_token") == $u->forgot_token) {
					$u->auth_hash = Util::hash($u->email,Request::r("new_password"));
					$u->update();
				} else {
					Request::json( false , array("error"=>_("Invalid token"),"errors"=>array("forgot_token"=>_("Invalid token"))));
				}
			}
		}


		public function check() {
			Request::json( true , array("user"=>Session::user()));
		}

	}
?>
