<?php
	$config = array(
		'templates'=>'templates',
		'db'=>array(
			'dsn'=>'mysql:dbname=ebi;host=127.0.0.1',
			'user'=>'ebi',
			'password'=>'aGzvLWwt4yVKApYE',
		),
		'encryption'=>array(
			'salt'=>'se',
			'iterations'=>10
		),
		'sessions'=>array(
			'threshold'=>3600,
		),
		'users'=>array(
			'forgot_token_life'=>360, //How often a requested token can change
			'forgot_token_size'=>10, //Up to 32
		),
		'register_redirect'=>'/books',

		'content'=>array(
			'db'=>'../content.sqlite',
			'users'=>array(
				'dan'=>'r0tation',
			),
			'post'=>'/content/post',
			'realm'=>'Content Editor',
			'landingURL'=>'/'
		),
	);

	//For Caching
	define('CACHE_TYPE', 'file');
	define('CACHE_FOLDER', dirname(__FILE__).'/cache/');
?>
