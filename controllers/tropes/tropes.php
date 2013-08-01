<?php
class control_tropes {
	static function HGinit() { //Called before loaded
		HG::map("GET","/tropes", "control_tropes#main");
	}

	function __construct() {

	}

	private function parseTropes() {
		$map = array();
		$trope = array();
		$cache = Cache::getInstance();
		$cont = file("http://tvtropes.org/pmwiki/storygen.php");
		for($i=223;$i<=232;$i++) {
			if (strstr($cont[$i],'twikilink')) {
				$line_parts = explode("'",$cont[$i]);
				$trope["type"] = trim(preg_replace("/[^A-Za-z0-9?!\s]/", "", strip_tags($line_parts[0])));
				$trope["name"] = trim(preg_replace("/[^A-Za-z0-9?!\s]/", "", strip_tags($line_parts[6])));
				$trope["url"] = trim($line_parts[3]);
				$trope["desc"] = "";

				$cacheName = "trope".$trope["url"];
				$dcont = HG::getVar($cacheName);
				if ($dcont === FALSE) {
					$dcont = file($trope["url"]); //TODO - Cache results
					HG::setVar($cacheName,$dcont, (60 * 60 * 24 * 30) ); //Cache for a month
				}

				$record = false;
				foreach($dcont as $v) {
					if ($record === TRUE) {
						$trope["desc"] .= $v;
					}
					if (strstr($v,"<hr />")) {
						$record = false;
					}
					if (strstr($v,"<!--PageText-->")) {
						$record = true;
					}
				}
				$trope["desc"] = str_replace("class='twikilink'","class='twikilink' TARGET='_BLANK'",$trope["desc"]);
				$trope["desc"] = str_replace("class='twikilink'","class='twikilink' TARGET='_BLANK'",$trope["desc"]);
			}
			if (count($trope) > 0)	$map[$trope["type"]] = $trope;
			$trope = array();
		}
		return $map;
	}

	function main() {
		global $config;
		HG::chain()->
		v("meta.title","Story Idea Generator")->
		active('tropes')->
		v("tropes",$this->parseTropes())->
		display("tropes.php",true);
	}

}
?>
