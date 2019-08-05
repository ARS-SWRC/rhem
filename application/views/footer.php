		<div class="footer">
        	<div class="inside">
        		<p><a href=".">Home</a> | <a href="about">About</a> | <a href="docs">Documentation</a> | <a href="contact">Contact Us</a> <br />
        		&copy; Copyright <a href="https://www.tucson.ars.ag.gov/"><b>SWRC-ARS-USDA</b></a></p>
        	</div>
        	<div class="tr"></div>
        	<div class="tl"></div>
        	<div class="br"></div>
        	<div class="bl"></div>
		</div>
	</div>
    <script type="text/javascript" src="//www.google.com/jsapi"></script>
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="//cdn.rawgit.com/BorisChumichev/everpolate/master/everpolate.browserified.min.js"></script>

    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA-41803167-1', 'auto');
        ga('send', 'pageview');
    
        //google.load("visualization", "1.1", {packages:["table"]});
        $("#username").focus();
    </script>

    <div id="loginModal" class="modal hide fade in" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
        <form action="<?php echo base_url(); ?>login/in" method="post" id="userform" class="styled">
            <div class="modal-header">
            	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            	<h3 id="myModalLabel">Log<span class="blue">in</span></h3> 
            </div>
            <div class="modal-body">
                <fieldset>
                    <p>Please login to begin using this tool: </p>
                    <?php echo (isset($errormessage)?$errormessage:'');?>
                    <p>
                        <label for="username" accesskey="l">Username: </label>
                        <input type="text" id="username" name="username" tabindex="1" autofocus="autofocus" >
                    </p>
                    <p>
                        <label for="password" accesskey="l">Password: </label>
                        <input type="password" id="password" name="password" tabindex="2" onkeydown="if(event.keyCode == 13)this.form.submit();">
                    </p>
                    <p><a href="recover">Forgot your password?</a></p>
                    <p><br/><a href="register"><b>New user?</b> Register to use this tool.</a></p>
                </fieldset>
            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                <button type="submit" id="login" class="btn btn-warning" tabindex="3">Login</button>
            </div>
        </form>
    </div>