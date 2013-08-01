<head>
	<title><?=$meta["title"];?></title>
	<meta name=description content="<?=$meta["description"];?>"/>
	<meta name=keywords content="<?=$meta["keywords"];?>"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
	<link rel="stylesheet" href="/assets/css/bootstrap.css">
	<link rel="stylesheet" href="/assets/css/custom.css">
	<link rel="stylesheet" href="http://netdna.bootstrapcdn.com/font-awesome/3.0.2/css/font-awesome.css">
	<link rel="stylesheet" href="/assets/css/bootstrap-responsive.css">
	<link rel="stylesheet" href="/assets/css/bootstrap-fileupload.min.css">
	<?= $css; ?>


	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js" type="text/javascript"></script>
	<script src="/assets/js/bootstrap.min.js" type="text/javascript"></script>
	<script src="/assets/js/limitCount.js" type="text/javascript"></script>
	<script src="/assets/js/bootstrap-wysiwyg.js" type="text/javascript"></script>
	<script src="/assets/js/jquery.hotkeys.js" type="text/javascript"></script>
	<script src="/assets/js/jquery.form.js" type="text/javascript"></script>
	<script src="/assets/js/jquery.dataTables.js" type="text/javascript"></script>
	<script src="/assets/js/misc.js" type="text/javascript"></script>
	<?= $script; ?>
</head>
<body>
	<div class="container">
		<div class="navbar">
			<div class="navbar-inner">
	  			<div class="container">

                <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse"><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></a>

				      <div class="nav pull-right message-icon">
				      <? if (Session::message()) { ?>
		      			<i class="icon-envelope-alt icon-spin" title="<?= _("You have mail"); ?>"></i>
				      <?} else {?>
		      			<i class="icon-envelope" title="<?= _("No Mail"); ?>"></i>
				      <?}?>
<?php
	if (Session::user()) {
		echo "<IMG SRC=\"http://www.gravatar.com/avatar/".md5(Session::email())."?s=30&d=mm\">";
?>
						<a href="/auth/logout" role="button" class="btn" data-toggle="modal">Logout</a>
<?php
	} else {
?>
						<a href="#login" role="button" class="btn" data-toggle="modal">Login</a>
						<a href="#register" role="button" class="btn" data-toggle="modal">Register</a>
<?php
	}
?>
				      </div>

				<a class="brand" href="/">EBI</a>
                <div class="nav-collapse collapse">
                    <ul class="nav">
<?php
	if (Session::user()) {
?>
                        <li class="<?=@$active["home"];?>"><a href="/">Home</a></li>
                        <li class="divider-vertical"></li>
                        <li class="<?=@$active["about"];?>"><a href="/about">About</a></li>
						<li class="divider-vertical"></li>
						<li class="<?=@$active["tropes"];?>"><a href="/tropes">Story Ideas</a></li>
						<li class="divider-vertical"></li>
                        <li class="<?=@$active["books"];?>"><a href="/books">My Books</a></li>

<?php
	} else {
?>
                        <li class="<?=@$active["home"];?>"><a href="/">Home</a></li>
                        <li class="divider-vertical"></li>
                        <li class="<?=@$active["about"];?>"><a href="/about">About</a></li>

<?php
	}
?>
						<li><form class="navbar-search pull-left">
				        <input class="search-query input-large form-inline" style="height:28px" placeholder="Search" type="text">
   					</form></li>
                    </ul>
	                </div>
	            </div>

	  </div>
	</div>

<?php
if (isset($breadcrumbs)) {
	include('breadcrumb.php');
}
?>
