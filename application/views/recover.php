	<?php include 'head.php'?>
	<title>RHEM Web Tool: Register</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
	<script language="javascript" src="http://ajax.microsoft.com/ajax/jquery.validate/1.7/jquery.validate.js" ></script>
	<title>RHEM Web Tool: Retrieve Forgotten Password</title>
	<script type="text/javascript">
		$().ready(function() {
			$("#userform").validate({
				rules: {
					email: {required:true,email:true}
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
				<h2>Forgot<span class="blue"> password?</span></h2>	
				<form action="<?php echo base_url(); ?>login/recover" method="post" id="userform" class="styled">
					<fieldset>
						<p>Please enter your email address and a new password will be sent to you. </p>
						<?php echo (isset($errormessage)?$errormessage:'');?>
						<p>
							<label for="email" accesskey="e">Email: </label>
							<input type="text" id="email" name="email" tabindex="6">
							<?php echo (isset($testmsg)?$testmsg:'');?>
						</p>
						<p>
							<label for="recover"></label>
							<button type="submit" id="recover" class="btn btn-warning" tabindex="3">Recover</button>
						</p>
					</fieldset>
				</form>
            </div>  
        </div>  
        <?php include 'right.php'; ?>
		<?php include 'footer.php'; ?>	
</body>
</html>