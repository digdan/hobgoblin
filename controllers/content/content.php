<?
/**
*	Content Controller
*
* 		Custom Content controller to generate live WYSIWYG editing for clients - Used with $config and HG integration
*
* sqlite3 ../content.txt
*
* CREATE TABLE content ( content_id INTEGER PRIMARY KEY AUTOINCREMENT, content_name VARCHAR(255), content TEXT NOT NULL, updated INT NOT NULL );
* CREATE TABLE meta ( meta_id INTEGER PRIMARY KEY AUTOINCREMENT, page_name VARCHAR(255) NOT NULL, page_title VARCHAR(255), page_keywords VARCHAR(255), page_description VARCHAR(255), updated INT NOT NULL );
*
*/
	class control_content {
		var $active=true;

		static function HGInit() {
			HG::map( "GET|POST" , "/content/auth" , "control_content#auth");
			HG::map( "GET|POST" , "/content/post" , "control_content#post");
			HG::map( "GET|POST" , "/content/meta" , "control_content#meta");
			HG::map( "GET|POST" , "/content/logout" , "control_content#logout");

			HG::map( "GET|POST" , "/content/assets/[**:target]" , "control_content#assets");

			self::check(); // Check Content Authentication & Define Meta

			HG::face('content', function($id) { return control_content::content($id); } ); //Build face for content

			//Define
			HG::css("/controllers/content/assets/wysiwyg.css");
			HG::script("/controllers/content/assets/jquery.wysiwyg.js");
			HG::script("/controllers/content/assets/jquery.jeditable.js");
			HG::script("/controllers/content/assets/jquery.jeditable.wysiwyg.js");
		}

		function auth() {
			global $config;
			if (self::isAuthed()) {
				$_SESSION["content_digest"] = array($_SERVER["PHP_AUTH_USER"]=>md5( $_SERVER["PHP_AUTH_PW"])); //Digestable
				header("Location: {$config["content"]["landingURL"]}"); die();
			} else {
				header('WWW-Authenticate: Basic realm="'.$config["content"]["realm"].'"');
				header('HTTP/1.0 401 Unauthorized');
				echo 'Authorized access only.';
				exit;
			}
		}

		static function logout() {
			global $config;
			$_SESSION["content_digest"] = array();
			header("Location: {$config["content"]["landingURL"]}"); die();
		}

		static function isAuthed() {
			global $config;
			if ( isset($_SERVER["PHP_AUTH_USER"]) ) { //Authed
				if ( isset($config["content"]["users"][$_SERVER["PHP_AUTH_USER"]]) and ($config["content"]["users"][$_SERVER["PHP_AUTH_USER"]] == $_SERVER["PHP_AUTH_PW"])) {
					return true;
				}
			}

			if ( isset($_SESSION["content_digest"])) {
				$name = key($_SESSION["content_digest"]);
				if (strlen($name) > 2) {
					if (@md5($config["content"]["users"][$name]) == @$_SESSION["content_digest"][$name]) { //Authed
						return true;
					}
				}
			}

			return false;
		}

		static function check() {
			global $config;
			if (self::isAuthed()) {
				HG::javascript("
					\$('.editable').editable('{$config["content"]["post"]}', {
					    type      : 'wysiwyg',
						event	  : 'dblclick',
					    onblur    : 'ignore',
					    submit    : 'OK',
					    cancel    : 'Cancel',
					    wysiwyg   : { controls : {
						                insertOrderedList   : { visible : true },
						                insertUnorderedList : { visible : true },
						                html  : { visible: true },
					                }
					    }
					});
				");
			}
		}

		public static function content($id="front") {
			global $config;
			if (isset($config["content"]["db"])) { //Do we have a content database?
				$s = new SQLite3( $config["content"]["db"]);
				$result = $s->querySingle("SELECT `content` FROM `content` WHERE content_name = '{$id}' ORDER by `updated` DESC");
				if (is_null($result)) {
					$result = "<I>EDITABLE</I>";
				}
				$s->close();
				return "<DIV class=\"editable\" id=\"{$id}\">{$result}</DIV>";
			} else {
				return false;
			}
		}

		static function metaTarget($target) { //Define meta tags
			global $config;
			if (isset($config["content"]["db"])) { //Do we have a content database?
				$s = new SQLite3( $config["content"]["db"]);
				$query = "SELECT * FROM meta WHERE page_name = '{$target}' ORDER by `updated` DESC";
				$res = $s->query($query)->fetchArray(SQLITE3_ASSOC);
				$s->close();

				if ( is_array($res) and (count($res) > 0) ) {
					HG::v("meta",array(
						'title'=>$res['page_title'],
						'keywords'=>$res['page_keywords'],
						'description'=>$res['page_description']
					));
				}
			} else {
				return false;
			}
		}

		function post() {
			global $config;
			/** Save Content Posted to this function **/
			if (isset($config["content"]["db"])) { //Do we have a content database?
				if (self::isAuthed()) {
					$s = new SQLite3( $config["content"]["db"]);
					$c = SQLite3::escapeString($_POST['value']);
					$id = SQLite3::escapeString($_POST['id']);
					$tn = time();
					$query = "INSERT INTO `content` (content_name,content,updated) VALUES ('{$id}','{$c}',{$tn})";
					$s->exec($query);
					$s->close();
					echo $_POST['value'];
				}
			}
		}

		function meta() {
			global $config;
			/** Save Content Posted to this function **/
			if (isset($config["content"]["db"])) { //Do we have a content database?
				if (self::isAuthed()) {
					$s = new SQLite3( $config["content"]["db"]);
					$pn = SQLite3::escapeString($_POST['page_name']);
					$t = SQLite3::escapeString($_POST['title']);
					$k = SQLite3::escapeString($_POST['keywords']);
					$d = SQLite3::escapeString($_POST['description']);
					$tn = time();
					$s->exec("INSERT INTO `meta` (page_name,page_title,page_keywords,page_description,updated) VALUES ('{$pn}','{$t}','{$k}','{$d}',{$tn})");
					$s->close();
				}
			}
			header("Location: ".$_POST["page_source"]);
		}
	}
?>
