<?php
	class control_main {
		static function HGinit() { //Called before loaded
			HG::map("GET","/", "control_main#main");
			HG::map("GET","/about", "control_main#about");
		}

		function __construct() {

		}

		function main() {
			global $config;
			HG::chain()->
			active('home')->
			v("meta.title","Ebook Interactive &mdash; An Open World")->
			display("main.php",true);
		}

		function about() {
			global $config;
			HG::chain()->
				active('about')->
				v("meta.title","About Ebook Interact")->
				display("about.php",true);
		}

	}
?>
