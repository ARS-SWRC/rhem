	<?php include 'head.php'?>
	<title>DRHEM Web Tool: Account</title>
    <link href="<?php echo base_url(); ?>assets/bootstrap/css/bootstrapSwitch.css" rel="stylesheet">
    <script type="text/javascript">
        $().ready(function() {
            $("#accountform").validate({
                rules: {
                    confirmNewPassword: {required: function(element) {
                                                return $("#newPassword").val() != '';
                                        }
                                 }
                },
                messages: {
                    newPassword: "Please enter a new password." ,
                    confirmNewPassword: "Please very the new password." 
                }
            });
        });
    </script>
</head>
<body>
	<div class="content">
		<?php  include 'intro.php';?>    
		<div class="subheader"></div> 
		<div class="subcontent">
			<div class="subcontent_item">
				<h2>User Account Options</h2>
                <form action="<?php echo base_url(); ?>account/save" method="post" class="form-horizontal" id="accountform">
                	<h4>Change Password</h4>
                    <div class="control-group">
                        <label class="control-label" for="newPassword"><i>New Password</i></label>
                        <div class="controls">
                            <input type="password" id="newPassword" name="newPassword" placeholder="New Password">
                        </div> 
                    </div> 
                    <div class="control-group">
                        <label class="control-label" for="confirmNewPassword"><i>Confirm New Password</i></label>
                        <div class="controls">
                            <input type="password" id="confirmNewPassword" name="confirmNewPassword" placeholder="Confirm Password">
                        </div>
                    </div>
                     <h4>Parameter File Modifications</h4>
                    <div class="control-group">
                     <label class="control-label" for="modifyInputParametersFlag"><i>Allow me to modify the parameter files for each scenario</i></label>
                        <div class="controls">
                            <div class="switch" data-on="warning">
                                <input type="checkbox" id="modifyInputParametersFlag" name="modifyInputParametersFlag" <?php echo (isset($modify_parameter_file_flag)?$modify_parameter_file_flag:'tt'); ?>/>
                            </div>
                        </div>
                    </div>
                    <h4>Scenario Output</h4>
                    <div class="control-group">
                     <label class="control-label" for="detailedOutputFlag"><i>Create detailed output. (Note: to use the risk assessment tool, this option needs to be enabled)</i></label>
                        <div class="controls">
                            <div class="switch" data-on="warning">
                                <input type="checkbox" id="detailedOutputFlag" name="detailedOutputFlag" <?php echo (isset($detailed_output_flag)?$detailed_output_flag:''); ?>/>
                            </div>
                        </div>
                    </div>
                    <h4>Model Version</h4>
                    <div class="control-group">
                        <label class="control-label" for="optionsRadios1" style="margin-right:15px;"><i>Controls which version of the RHEM model and equations to use when running new scenarios</i></label>
                        <input type="radio" name="modelVersion" id="v23" value="2.3" <?php echo $model_version==2.3?'checked="checked"':'';?>>
                        <label for="v23">Version 2.3</label>
                        
                        <input type="radio" name="modelVersion" id="v21" value="2.1" <?php echo $model_version==2.1?'checked="checked"':'';?> <?php echo $model_version_21_enabled;?>>
                        <label for="v21">Version 2.1</label>

                    </div>
                    <?php echo (isset($accountupdated)?$accountupdated:''); ?>
                    <div class="control-group">
                        <div class="controls">
                            <button id="subitAccountUpdate" type="submit" class="btn">Save Changes</button>
                        </div>
                    </div>
                </form>
			</div>	
		</div>	
		<?php include 'right.php'; ?>
    	<div class="footer">
                <div class="inside">
                    <p><a href=".">Home</a> | <a href="about">About</a> | <a href="links">Links</a> | <a href="contact">Contact Us</a> <br />
                    &copy; Copyright 2008 <a href="http://tucson.ars.ag.gov/"><b>SWRC-ARS-USDA</b></a></p>
                </div>
                <div>
                    <div class="tr"></div>
                    <div class="tl"></div>
                    <div class="br"></div>
                    <div class="bl"></div>
                </div>
        </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/2.3.1/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/1.3/bootstrapSwitch.min.js"></script>
    <script language="javascript" src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
        <script type="text/javascript">
          var _gaq = _gaq || [];
          _gaq.push(['_setAccount', 'UA-41803167-1']);
          _gaq.push(['_trackPageview']);

          (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
          })();

          $("#username").focus();
        </script>
</body>
</html>