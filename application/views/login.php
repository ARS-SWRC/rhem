	<?php include 'head.php'?>
	<title>RHEM Web Tool: Register</title>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
	<script language="javascript" src="//ajax.microsoft.com/ajax/jquery.validate/1.7/jquery.validate.js" ></script>
	<title>RHEM Web Tool: Register</title>
	<script type="text/javascript">
		$().ready(function() {
			$("#userform").validate({
				rules: {
					username: {required: true},
					password: {required:true,minLength:5}
				},
				messages: {
				}
			});
		});
	</script>
</head>
<body>
	<div class="content">
		<?php  include 'intro.php'; ?>
		<div class="subheader"></div>
		<div class="subcontent">
            <div class="subcontent_item">
				<h2>Log<span class="blue">in</span></h2>
				<form action="<?php echo base_url(); ?>login/in" method="post" id="userform" class="styled">
				<fieldset>
					<p>Please login to begin using this tool: </p>
					<?php echo (isset($errormessage)?$errormessage:'');?>
					<p>
						<label for="username" accesskey="l">Username: </label>
						<input type="text" id="username" name="username" tabindex="1">
					</p>
					<p>
						<label for="password" accesskey="l">Password: </label>
						<input type="password" id="password" name="password" tabindex="2">
					</p>
					<p>
						<label for="login"></label>
						<button type="submit" id="login" class="btn btn-warning" tabindex="3">Login</button>
						<a href="recover">Forgot your password?</a>
					</p>
					<p><br/>
						<a href="register"><b>New user?</b> Register to use this tool.</a>
					</p>
				</fieldset>
				</form>
            </div>  
        </div>  
        <?php include 'right.php'; ?>
		<?php include 'footer.php'; ?>	
</body>
</html>