<?php
/**
*
*	Hobgoblin API Engine
*
* 	For more information visit : http://www.danmorgan.net/hobgoblin
*
*/
//Framework Libraries
include("config.php");
include("lib/hg.php");

HG::init();
HG::execute( HG::match( $_SERVER["REQUEST_URI"] ) ); //Execute matched routes

?>
