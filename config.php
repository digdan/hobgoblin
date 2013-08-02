<?php
	$config = array(
		'templates'=>array(
			'directory'=>'templates',
			'default_header'=>'header.php',
			'default_footer'=>'footer.php'
		),
		'db'=>array(
			'dsn'=>'mysql:dbname=db;host=127.0.0.1',
			'user'=>'user',
			'password'=>'pass',
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
		'auth'=>array(
			'register_redirect'=>'/manage',
			'user_table'=>'users'
		),		
		'content'=>array(
			'db'=>'../content.sqlite',
			'users'=>array(
				'admin'=>'pass',
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
