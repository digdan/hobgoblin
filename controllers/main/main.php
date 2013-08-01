<?php
	class control_main {
		static function HGinit() { //Called before loaded
			HG::map("GET","/", "control_main#main");
			HG::map("GET","/about", "control_main#about");
		}

		function __construct() {

		}

		function main() {
			HG::active('home')->
			display("main.php",true);
		}

		function about() {
			HG::active('about')->
			display("about.php",true);
		}

	}
?>
