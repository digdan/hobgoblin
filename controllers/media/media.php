<?php
	class control_media {
		static function HGinit() {
			HG::map("PUT","/media", "control_media#add");
		}


		function add() { //Add media to server
		}
	}
?>