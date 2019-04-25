	<?php include 'head.php'?>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
	<script language="javascript" src="//ajax.microsoft.com/ajax/jquery.validate/1.7/jquery.validate.js" ></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
	<title>RHEM Web Tool: Register</title>
	<script type="text/javascript">
    //<![CDATA[
		$().ready(function() {
			$("#userform").validate({
				rules: {
					firstname: {required: true},
					lastname: {required: true},
					username: {required: true},
					password: {required:true,minlength:5},
					password2: {required:true,minlength:5,equalTo:'#password'}, 
					email: {required:true,email:true},
					usage: {required: true}
				},
				messages: {
					password: {
						required: "Please provide a password",
						minlength: "Your password must be at least 5 characters long"
					},
					password2: {
						required: "Please provide a password",
						minlength: "Your password must be at least 5 characters long",
						equalTo: "Please enter the same password as above"
					}
				}
			});
		});
		//var RecaptchaOptions = {theme : 'clean',tabindex : 8};
        //]]>
	</script>
</head>
<body>
	<div class="content">
		<?php  include 'intro.php'; ?>
        <div class="subheader"></div>
        <div class="subcontent">
            <div class="subcontent_item">
                <h2>Sign <span class="blue">Up</span></h2>
                <form action="<?php echo base_url(); ?>register/confirm" method="post" id="userform" class="styled_form">
                    <fieldset>
                        <p>Please register to start using the RHEM Web Tool.</p>
                        <p>
                            <label for="firstname">First Name: </label>
                            <input type="text" id="firstname" name="firstname" tabindex="1"/>
                        </p>
                        <p>
                            <label for="lastname">Last Name: </label>
                            <input type="text" id="lastname" name="lastname" tabindex="2"/>
                        </p>
                        <p>
                            <label for="username">Username: </label>
                            <input type="text" id="username" name="username"  tabindex="3"/>
                        </p>
                        <p>
                            <label for="password">Password: </label>
                            <input type="password" id="password" name="password" tabindex="4"/>
                        </p>
                        <p>
                            <label for="password2">Confirm Password: </label>
                            <input type="password" id="password2" name="password2" tabindex="5"/>
                        </p>
                        <p>
                            <label for="email">Email: </label>
                            <input type="text" id="email" name="email" tabindex="6"/>
                        </p>
                        <p> 
                            <label for="usage">Please tell us how you intend to use this tool: </label>
                            <textarea cols="83" rows="5" id="usage" name="usage" tabindex="7"></textarea>
                        </p>
                        <?php 
                            //$publickey = "6Lfj4L4SAAAAADInlpd70-ZZE6IGhTzxmnL6uaMd";
                            //echo recaptcha_get_html($publickey,NULL,true); 
                        ?>
                        <div class="g-recaptcha" data-sitekey="6Lc8m1EUAAAAADziU1LZwqPbol1kvOsgxUjvgbKZ"></div>
                        <p>
                            <br/>
                            <label for="register"></label>
                            <button type="submit" id="register" class="btn btn-warning" tabindex="9">Register</button>
                        </p>
                    </fieldset>
                </form>	
            </div>  
        </div>  
        <?php include 'right.php'; ?>
		<?php include 'footer.php'; ?>	
</body>
</html>