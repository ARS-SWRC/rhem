<html>
	<head>
		<script type="text/javascript" src="http://www.google.com/jsapi"></script>
		<script type="text/javascript" src="<?php echo base_url(); ?>assets/scripts/jquery.editable-1.3.3.js"></script>
		<script>
			 // set the editable functionality fo reach one of the values in the PAR input file
			 $(document).ready(function() {
				$('span.editable').editable({
	        		 indicator : 'Saving...', 
	        		 tooltip   : 'Click to edit...',
					 cancel    : 'Cancel', 
			         submit    : 'OK',
					 onSubmit  :function(content){
					 	// If user updates the slopelength in the PAR file, update
					 	// the CLEN by multiplying it by 2.5
					 	//console.log(content.current);
					 	if($(this).attr("id") == "slopelength"){
					 		$("#clen").text(content.current * 2.5);	
					 	}

					 }
	     		});
			 });
			 
			 // allows the user to save the soil input file opened based on an existing scenario
			 function saveInputFile(saveoroverwrite)
			 {
				// get the current scenario's name and description
				var scenarioname = $("#scenarioname", window.parent.document).val();
				var scenariodescription = $("#scenariodescription", window.parent.document).val();
				// build a date string to be appended to the scneario name
				var myDate = new Date();
				var displayDate = (myDate.getMonth()+1) + '' + (myDate.getDate()) + '' + myDate.getFullYear() + '' + myDate.getMinutes() + '' + myDate.getSeconds();
				var displayDateFormatted = (myDate.getMonth()+1) + '-' + (myDate.getDate()) + '-' + myDate.getFullYear() + ',' + myDate.getHours() + ':' + myDate.getMinutes() + ':' + myDate.getSeconds();

				// set the new name and description for the modified scenario
				var scenarioNameParts = $("#scenarioname", window.parent.document).val().split("___");
				var scenarioDescriptionParts = $("#scenariodescription", window.parent.document).val().split("---");
				if(scenarioNameParts.length == 1){
					$("#scenarioname", window.parent.document).val(scenarioname + "___v1");
					$("#scenariodescription", window.parent.document).val(scenariodescription + "\n---\nThe parameters input file has been manually edited(" + displayDateFormatted + ").");
				}
				else{
					// save the current input file as a new version or overwrite the current version
					if(saveoroverwrite == 'save'){
						var version = scenarioNameParts[1]; 
						$("#scenarioname", window.parent.document).val(scenarioNameParts[0] + "___v" + eval(parseInt(version.split('v')[1]) + 1));
						$("#scenariodescription", window.parent.document).val(scenarioDescriptionParts[0] + "---\nThe parameters input file has been manually edited(" + displayDateFormatted + "). This is a new version.");
					}
					else if(saveoroverwrite == 'overwrite'){
						$("#scenarioname", window.parent.document).val(scenarioNameParts[0] + "___" + scenarioNameParts[1]);
						$("#scenariodescription", window.parent.document).val(scenarioDescriptionParts[0] + "---\nThe parameters input file has been manually edited(" + displayDateFormatted + "). This is a modified version.");
					}
				}
				
				// set the modified hidden variable flag to true
				$("#modifiedPARfileflag", window.parent.document).val("true");

				// disable the soil texture, slope, and vegetation panels when modifying the input files manually
				hidePanels();

				// send a true flag to the editPARfile controller in order for it to know to open the modified PAR file instead of the current scenario's
				var modifiedPARfileurl = $("#editPARfilelink").attr('href').replace("false","true");
				$("#editPARfilelink").attr('href', modifiedPARfileurl);
				
				var inputhtml = $("#PARinputfile").html();
				
				// send the modified html to the server and save it under a generic PAR file name for this user
				$.post("editPARfile/save/",{PARfileinputhtml: inputhtml}, function(data){
					if(data.length > 0) {
						//alert("PAR File Saved Successfully.");
					}
				});
			 }
			 
			 /* This function will be used to hide the vegetation panels when 
			    the PAR file has been manually modified*/
			 function hidePanels()
			 {
				$("#soil_texture_panel", window.parent.document).unbind("click");
				$("#slope_panel", window.parent.document).unbind("click");
				$("#cover_characteristics_panel", window.parent.document).unbind("click");
				$("#soil_texture_panel", window.parent.document).unbind("hover");
				$("#slope_panel", window.parent.document).unbind("hover");
				$("#cover_characteristics_panel", window.parent.document).unbind("hover");
			 }
		</script>
	    <style>
			#PARinputfile{
				font-family:monospace;
				font-size:12px;
			}
			#PARinputfile input{
				width:40px;
			}
			.closeDOMWindow[alt]{
				visibility:visible;
			}
			span.editable{
				margin-right:20px;
				font-weight: bold;
				color: #D4812F;
			}
		</style>
	</head>
	<body>
		<p style="text-align:center;color:#D4812F;font-weight:bold;"> Parameters in orange are modifiable </p>
		<p style="text-align:center;color:#D4812F;"> <b>Note:</b> after modifying this file, the Soil Texture Class, Slope, and Cover Characteristic panels will be disabled </p>
	<div id="PARinputfile">
		<?php
	        echo $parfile;
	    ?>
	</div>
	<p style="text-align:center;margin-top:30px;"> 
		<input class="closeDOMWindow" type="button" value="Save Input File" onClick="saveInputFile('save');" alt="Forces the scenario to be saved under a new name."/>
	    <input class="closeDOMWindow" type="button" value="Cancel"/>
	</p>
	</body>
</html>
