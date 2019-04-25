	<?php include 'head.php'?>
	<title>RHEM Web Tool: Contact Us</title>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
	<script language="javascript" src="//ajax.microsoft.com/ajax/jquery.validate/1.7/jquery.validate.js" ></script>
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	<title>RHEM Web Tool: Contact Us</title>
	<script type="text/javascript">
		$().ready(function() {
			$("#contactform").validate({
				rules: {
					requesttype: {required: true},
					firstname: {required: true},
					lastname: {required: true},
					email: {required:true,email:true},
					comments: {required: true}
				},
				messages: {
					requesttype: "Please select a request type from the list."
				}
			});
		});
		
		//var RecaptchaOptions = {theme : 'clean',tabindex:5};
	</script>
</head>
<body>
	<div class="content">
		<?php  include 'intro.php';?>    
		<div class="subheader"></div>
		<div class="subcontent">
			<div class="subcontent_item">
				<h2>Contact <span class="blue">Us</span></h2>
				<form action="<?php echo base_url(); ?>contact/send" method="post" id="contactform" class="styled_form">
					<p class="description">For any technical questions, please contact Gerardo Armendariz at gerardo.armendariz@ars.usda.gov</p><br/>
					<p class="description">Let us know any comments or concerns about this web application:</p>
					<?php echo (isset($errormessage)?$errormessage:'');?>
					<p>
						<label for="firstname">First name: </label>
						<input type="text" id="firstname" name="firstname" tabindex="1">
					</p>
					<p>
						<label for="lastname">Last name: </label> 
						<input type="text" id="lastname" name="lastname" tabindex="2">
					</p>
					<p>
						<label for="email">Email: </label>
						<input type="text" id="email" name="email" tabindex="3">
					</p>
					<p>
						<label for="requesttype">Request type: </label>
						<select id="requesttype" name="requesttype" tabindex="4" style="margin-left:10px;">
							<option></option>
							<option value="website">Technical/Website</option>
							<option value="model">RHEM Model</option>
						</select>
					</p>
					<p>
						<label for="comments">Comments: </label>
						<textarea id="comments" name="comments" rows="7" cols="81" tabindex="4"></textarea>
					</p>
					<?php 
                        //$publickey = "6LeWmMESAAAAAGW_llYQIDc2TvVeKdzsPluOILOc";
                        //echo recaptcha_get_html($publickey,NULL,true); 
                    ?>
                    <div class="g-recaptcha" data-sitekey="6Lc8m1EUAAAAADziU1LZwqPbol1kvOsgxUjvgbKZ"></div>
					<p>
						<br/>
						<label for="submit"></label>
						<button type="submit" id="submit" class="btn btn-warning" tabindex="6">Send</button>
					</p>
                    
				</form>
			</div>	
		</div>	
		<?php include 'right.php'; ?>
		<?php include 'footer.php'; ?>	
</body>
</html>