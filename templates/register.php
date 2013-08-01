<div class="container">
    <div class="row">
        <div class="span12">
            <form class="form-horizontal" id="register" method='post' action='/register'>
				<div id="reason"></div>
                <fieldset>
                    <legend>Registration</legend>
                    <div class="control-group">
                        <label class="control-label" for="user_email">Email</label>
                        <div class="controls">
                            <input type="text" class="input-xlarge" id="email" name="email" rel="popover" data-original-title="Email">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="pwd">Password</label>
                        <div class="controls">
                            <input type="password" class="input-xlarge" id="password" name="password" rel="popover" data-original-title="Password">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="submit"></label>
                        <div class="controls">
                            <button type="submit" class="btn btn-success" rel="tooltip" title="first tooltip">Create My Account</button>
                        </div>
                    </div>

                </fieldset>
            </form>
        </div>
    </div>

	<script>
	$("#register").hgButler({
		reason: '#reason'
	});
	</script>