<!-- Modals -->
<div id="login" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
	<form method="post" action="/auth/login" id="loginForm">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Login</h3>
		</div>
		<div class="modal-body">
			<fieldset>
				<div class="control-group">
					<label class="control-label" for="email">Email</label>
					<div class="controls">
						<input type="text" class="input-large" id="email" name="email" rel="popover" data-original-title="Email">
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="password">Password</label>
					<div class="controls">
						<input type="password" class="input-large" id="password" name="password" rel="popover" data-original-title="Password">
					</div>
				</div>
				<A href="/auth/forgot" class="pull-right">Forgot Password?</A>
			</fieldset>

			<div class="container-fluid">
				<div id="loginReason"></div>
			</div>
		</div>

		<div class="modal-footer">
			<button class="btn btn-primary" id="login-button">Login</button>
			<button class="btn btn-primary" id="login-register">Register</button>
			<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
		</div>
	</form>
</div>

<div id="register" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
	<form method="post" action="/auth/register" id="registerForm">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Register</h3>
		</div>
		<div id="loginReason"></div>
		<div class="modal-body">
			<fieldset>
				<div class="control-group">
					<label class="control-label" for="user_email">Email</label>
					<div class="controls">
						<input type="text" class="input-large" id="email" name="email" rel="popover" data-original-title="Email">
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="pwd">Password</label>
					<div class="controls">
						<input type="password" class="input-large" id="password" name="password" rel="popover" data-original-title="Password">
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="tos">Terms of Service</label>
					<div class="controls">
						<input type="checkbox" id="tos" name="tos" rel="popover" data-original-title="Terms of Service">
					</div>
				</div>
			</fieldset>

			<div class="container-fluid">
				<div id="registerReason"></div>
			</div>
		</div>
		<div class="modal-footer">
			<button class="btn btn-primary">Register</button>
			<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
		</div>
	</form>
</div>
<script>
	$(document).ready( function () {
		$("#login-register").click( function() {
		    $("#login").modal('hide');
		    $("#register").modal('show');
		    return false;
		});

		$("#login").on('shown', function () {
			$('input:text:visible:first', this).focus();
		});
		$("#register").on('shown', function () {
			$('input:text:visible:first', this).focus();
		});

		$("#loginForm").ajaxForm({
			dataType: 'json',
			success: function(data) {
				if (data.error) {
					$("#loginReason").html("<div class='alert alert-error'><button type='button' class='close' data-dismiss='alert'>&times;</button>" + data.error + "</div>");
				}
				if (data.ok) {
					location.reload();
				}
			}
		});

		$("#registerForm").ajaxForm({
			dataType: 'json',
			success: function(data) {
				if (data.error) {
					$("#registerReason").html("<div class='alert alert-error'><button type='button' class='close' data-dismiss='alert'>&times;</button>" + data.error + "</div>");
				}
				if (data.ok) {
					location.href='<?=$config["register_redirect"];?>';
				}
			}
		});

	});
</script>