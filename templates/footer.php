<div class="bodyfooter">
	<?
		if ( ! Session::user()) include_once("login-modal.php"); //User Accounts
		if ( control_content::isAuthed()) {
			echo "&mdash; <A HREF=\"/content/logout\">Logout</A> &mdash; <A HREF=\"#meta\" data-toggle=\"modal\">Meta</A>";
			include_once("meta-modal.php");
		} else {
			echo "&mdash; <A HREF=\"/content/auth\">Login</A>";
		}
	?>
</div>
<?= $footer; ?>
</div>
<script type="text/javascript">
<?= $javascript; ?>
</script>
<script type="text/javascript">
	$(document).ready( function() {
		$('[rel=tooltip]').tooltip()
	});
</script>
</div>
</body>