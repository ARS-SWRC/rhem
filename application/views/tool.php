	<?php include 'head.php';?>
    <link rel="stylesheet" href="assets/css/impromptu.css" type="text/css" media="screen" charset="utf-8" />
</head>
<body>
	<div class="content">
		<?php include 'intro.php'; ?>
		<div class="subheader"></div>
		<div id="left_panel_header">
			<span style="margin-right: 400px; margin-left: 90px;">INPUT PARAMETERS</span>
			<span>RESULTS</span>
		</div>
		<div class="subcontent_expanded">
		<form action="run"  method="post" id="rhemForm">
		<div id="left_panel">
		    <!-- DEFINE SCENARIO -->
			<h2>
				<span class="orange">1. </span> Define Scenario 
                <a class="help_lnk" rel="popover" style="margin-right: 105px;" data-poload="help/getstring/define_scenario_panel" data-original-title="Define Scenario Panel"></a>
			</h2>
			<div>
				<a href="#" id="clearContent" style="padding-left:20px;"><i class="icon-refresh" style="opacity:0.8;"></i>Clear Scenario</a>
				<a class="help_lnk" rel="popover" data-poload="help/getstring/clear_scenario" data-original-title="Clear Scenario"></a>
				<br/><br/>
				<label for="scenarioname">Name:</label>
				<input type="text" id="scenarioname" style="width: 253px;" class="alphanumericCheck2 tb" value="<?php echo $this->session->userdata('scenarioname');?>" tabindex="1" />
				<a class="help_lnk" rel="popover" data-poload="help/getstring/session_name" data-original-title="Scenario Name"></a>
                
				<label for="scenariodescription">Description:</label>
				<textarea cols="39" rows="4" id="scenariodescription" tabindex="2" style="width: 255px;"><?php echo $this->session->userdata('scenariodescription');?></textarea>
                <a class="help_lnk" rel="popover" data-poload="help/getstring/session_description" data-original-title="Scenario Description"></a>
				<br/>
				<br/>
				<div class="radios" id="unitsbox">
					<label><b>Select units:</b></label>
					<label for="metricunits">Metric:</label><input id="metricunits" name="units" value="metric" type="radio" tabindex="3" <?php echo $this->session->userdata('units')=='metric' || $this->session->userdata('units') == ''?'checked="checked"':'';?> />
					<label for="englishunits">English:</label><input id="englishunits" name="units" value="english" type="radio" <?php echo $this->session->userdata('units')=='english'?'checked="checked"':'';?> />
                    <a class="help_lnk" rel="popover" data-poload="help/getstring/units_selection" data-original-title="Units Selection"></a>
				</div>
				<br/>
				<a href="#" id="showScenarios" style="margin-left:30px;font-size: 1.1em;font-weight: bold;"><i class="icon-tasks"></i>Manage User Scenarios</a> 
                <a class="help_lnk" rel="popover" data-poload="help/getstring/show_scenarios" data-original-title="Manage User Scenarios"></a>
				<br/>
                <input type="hidden" id="modifiedPARfileflag" value="false"/>
				<br/>
                <?php
					// if a scenario is open and the user has allowed to modify input parameter files
					if($this->session->userdata('scenarioid') and $user_account_settings_rs->modify_parameter_file_flag == 1)
						echo '<a id="editPARfilelink" style="margin-left:20px;" href="' . base_url() . 'editPARfile/open/false" class="editSoilFileDialog"><i class="icon-edit" style="opacity:0.8"></i>Manually Edit Model Input File</a><a class="help_lnk" rel="popover" data-poload="help/getstring/manual_inputs_modification" data-original-title="Manual Inputs Modification"></a><br/><br/>';
				?>
			</div>
		    <!-- DEFINE CLIMATE STATION -->
			<h2 class="toggle_off">
				<span class="orange">2. </span>Climate Station
                <a class="help_lnk" rel="popover" style="margin-right: 110px;" data-poload="help/getstring/climate_station_panel" data-original-title="Climate Station Panel"></a>
			</h2>
			<div class="panel">
				<ul class="nav nav-tabs" id="station_tabs">
					<li id="national_tab" <?php if($this->session->userdata('stateid') != "INL"){echo 'class="active"';} ?> ><a data-toggle="tab" href="#national">National</a></li>
					<li id="international_tab"  <?php if($this->session->userdata('stateid') == "INL"){echo 'class="active"';} ?> ><a data-toggle="tab" href="#international">International</a></li>
				</ul>


				<div class="tab-content">
					<!-- National -->
				    <div id="national" class="tab-pane fade in <?php if($this->session->userdata('stateid') != "INL"){echo 'active';} ?>">
				    	<br/>
						<label class="rhem_label_short" for="stateid">State:</label>
						<select name="stateid" id="stateid" size="1" tabindex="2">
							<option value="">-----</option>
							<?php
							foreach ($states_rs as $row)
							{
							   echo '<option value="' . $row->state_id . '"';
							   if($this->session->userdata('stateid') == $row->state_id)
							   		echo ' selected="selected"';
							   echo '>';
							   echo $row->state_name;
							   echo '</option>';
							}
							?>
						</select>
						<a class="help_lnk" rel="popover" data-poload="help/getstring/state" data-original-title="State"></a>
						<br/>
						<a class="help_lnk" rel="popover" data-poload="help/getstring/station" data-original-title="Climate Station"></a>
						<label class="rhem_label_short"  for="climatestationid">Station:</label>
						<select class="rhem_select" name="climatestationid" id="climatestationid" size="1" tabindex="3" style="width:160px">
							<option value="">-----</option>
						</select>
						<br/>
						<a href="#" id="showClimateStation" style="margin-left:95px;"><i class="icon-map-marker" style="opacity:0.7"></i>Show Map</a> 
				     
					</div>
				    <!-- National end -->

				    <!-- International -->
				    <div id="international" class="tab-pane fade in <?php if($this->session->userdata('stateid') == "INL"){echo 'active';} ?>">
				      <br/>
				      <label class="rhem_label_short" for="stateid" style="display:none;">State:</label>
					  <select name="stateid" id="stateid" size="1" tabindex="2" style="display:none;">
						<option value="INL">INL</option>
					  </select>
					  <br/>
				      <label class="rhem_label_short"  for="climatestationid">Station:</label>
						<select class="rhem_select" name="climatestationid" id="climatestationid" size="1" tabindex="3" style="width:160px">
							<option value="">-----</option>
							<option value="40340" <?php if($this->session->userdata('climatestationid') == "40340") echo ' selected="selected"'; ?> >Aqaba Airport JOR</option>
							<option value="40270" <?php if($this->session->userdata('climatestationid') == "40270") echo ' selected="selected"'; ?> >Amman JOR</option>
							<option value="40250" <?php if($this->session->userdata('climatestationid') == "40250") echo ' selected="selected"'; ?> >H4 JOR</option>
							<option value="40310" <?php if($this->session->userdata('climatestationid') == "40310") echo ' selected="selected"'; ?> >Ma&rsquo;An JOR</option>
							<option value="40400" <?php if($this->session->userdata('climatestationid') == "40400") echo ' selected="selected"'; ?> >China Xinganghan</option>
						</select>
						<br/>
						<a href="#" id="showInternationalClimateStations" style="margin-left:95px;"><i class="icon-map-marker" style="opacity:0.7"></i>Show Map</a> 
				    </div>
				    <!-- International end -->
				</div>
			</div>
			
		    <!--  DEFINE SOIL   -->
			<h2 class="toggle_off" id="soil_texture_panel">
				<span class="orange">3. </span>Soil Texture Class
                <a class="help_lnk" style="margin-right:85px;" rel="popover" data-poload="help/getstring/soil_texture_panel" data-original-title="Soil Texture Class Panel"></a>
			</h2>
			<div class="panel">
                <label><b>Upper soil layer depth <span id="soil_layer_depth">(top 4cm)</span>:</b></label>
                <br/>
                <label class="rhem_label_short" for="soiltexture">Soil Texture:</label>
                <select size="1" id="soiltexture" tabindex="5" >
                    <option value="" >-----</option>
                    <?php
                    foreach ($soil_textures_rs as $row)
                    {
                        echo '<option value="' . $row->class_id . '" ';
                        if($this->session->userdata('soilid') == $row->class_id)
                            echo ' selected="selected"';
                        echo '>';
                        echo $row->class_name;
                        echo '</option>';
                    }
                    ?>
                </select>
                <a class="help_lnk" rel="popover" data-poload="help/getstring/texture" data-original-title="Soil Texture"></a>
                <br/>
                <label class="rhem_label_short" for="salinity_checkbox" style="width: 115px;">Saline? (optional):</label>
                <input class="form-check-input" type="checkbox" id="salinity_checkbox" onclick="$('#sar_input').toggle();$('#sar_value').val('');" style="margin-top:-5px;" <?php if($this->session->userdata('sar')!='')echo "checked";?>>
                <a class="help_lnk" rel="popover" data-poload="help/getstring/salinity" data-original-title="Salinity option"></a>
                <div style="display:<?php if($this->session->userdata('sar')==''){echo 'none';}else{echo 'block';}?>;" id="sar_input">
                    <label for="sar_value">SAR:</label>
				    <input type="text" id="sar_value" style="width: 60px;" class="alphanumericCheck2 tb" <?php if($this->session->userdata('sar')!='')echo "value=" . $this->session->userdata('sar');?>>
                </div>
			</div>
		    <!--   DEFINE SLOPE  -->
			<h2 class="toggle_off"  id="slope_panel">
				<span class="orange">4. </span> Slope
				<a class="help_lnk" rel="popover" style="margin-right: 190px;" data-poload="help/getstring/slope" data-original-title="Slope/Topography"></a>
			</h2>
			<div class="panel">
				<label class="rhem_label_medium" for="slopelength" id="lengthunits"  style="display:none;"> Length (meters):</label>
				<?php
					// Set slope length to 100ft by default (only for Mariano and I for now)
					// TODO: Persist this after testing and adjust it for the selected units
					$uid = intval($this->session->userdata('user_id'));
					$tmp_slp_length = $this->session->userdata('slopelength');

					/////////////////////////////////////
					// For new runs, set the slope length to 50 by default, for old runs with slope lenths other than 50m, the slope
					// length with be set using JavaScript in the 
					$disabled_box = "disabled";
					if($tmp_slp_length == "") {
						$tmp_slp_length = 50;
					}
				?>
				<input type="text" id="slopelength" class="numericCheck1 tb" tabindex="7" value="<?php echo $tmp_slp_length; ?>" <?php echo $disabled_box; ?> style="display:none;"/>
                <a class="help_lnk" rel="popover" data-poload="help/getstring/slope_length" data-original-title="Slope Length"  style="display:none;"></a>
				<br/>
				<!-- Slope Shape -->
				<label class="rhem_label_medium" for="slopeshape">Slope Shape:</label>
				<select size="1" style="width:100px" id="slopeshape" tabindex="9">
					<option value="">-----</option>
					<?php
					foreach ($slope_shape_rs as $row)
					{
					   echo '<option value="' . $row->shape_id . '" ';
					   if($this->session->userdata('slopeshape') == $row->shape_id)
					   		echo ' selected="selected"';
					   echo '>';
					   echo $row->shape_name;
					   echo '</option>';
					}
					?>
				</select>
                <a class="help_lnk" rel="popover" data-poload="help/getstring/slope_shape" data-original-title="Slope Shape"></a>
				<br/>
				<!-- Steepness -->
				<label class="rhem_label_medium" for="slopesteepness">% Steepness:</label>
                <input type="text" id="slopesteepness" class="numericCheck1 tb" tabindex="10" value="<?php echo $this->session->userdata('slopesteepness');?>"/>
                <a class="help_lnk" rel="popover" data-poload="help/getstring/slope_steepness" data-original-title="Slope Steepness"></a>
                
				<p class="centered_p">
					<a href="#" id="showSlopeChart">Show Chart</a>
				</p>
			</div>
		    <!-- DEFINE COVER CHARACTERISTICS -->
			<h2 class="toggle_off"  id="cover_characteristics_panel">
				<span class="orange">5. </span> Cover Characteristics
                <a class="help_lnk" rel="popover" style="margin-right: 65px;" data-poload="help/getstring/cover_characteristics" data-original-title="Cover Characteristics"></a>
			</h2>
			<div class="panel">
				<div class="quickbtn">
					<span class="label label-important" style="background-color:#EBEBEB;color:#464548;text-shadow: none;font-size: 14px;margin-left: -6px;">Foliar Cover %</span>
					<br/>
	                <label class="rhem_label_long" for="bunchgrasscanopycover">Bunch Grass :</label>
	                <input type="text" id="bunchgrasscanopycover" class="numericCheck1 tb" tabindex="12" value="<?php echo $this->session->userdata('bunchgrasscanopycover');?>"/>
	                <a class="help_lnk" rel="popover" data-poload="help/getstring/bunch_canopy_cover" data-original-title="Bunch Grass Foliar Cover"></a>
	                <br/>
	                <label class="rhem_label_long" for="forbscanopycover">Forbs/Annuals:</label>
	                <input type="text" id="forbscanopycover" class="numericCheck1 tb" tabindex="12" value="<?php echo $this->session->userdata('forbscanopycover');?>"/>
	                <a class="help_lnk" rel="popover" data-poload="help/getstring/forbs_canopy_cover" data-original-title="Forbs/Annual Grasses Foliar Cover"></a>
	                <br/>
	                <label class="rhem_label_long" for="shrubscanopycover">Shrubs:</label>
	                <input type="text" id="shrubscanopycover" class="numericCheck1 tb" tabindex="12" value="<?php echo $this->session->userdata('shrubscanopycover');?>"/>
	                <a class="help_lnk" rel="popover" data-poload="help/getstring/shrubs_canopy_cover" data-original-title="Shrubs Foliar Cover"></a>
	                <br/>
	                <label class="rhem_label_long" for="sodgrasscanopycover">Sod Grass:</label>
	                <input type="text" id="sodgrasscanopycover" class="numericCheck1 tb" tabindex="12" value="<?php echo $this->session->userdata('sodgrasscanopycover');?>"/>
	                <a class="help_lnk" rel="popover" data-poload="help/getstring/sod_canopy_cover" data-original-title="Sod Grass Foliar Cover"></a>
	                <br/>
            	</div>
            	<br/>
                 
				<label class="rhem_label_long" for="groundcover">Basal Plant Cover %:</label>
				<input type="text" id="basalcover" class="numericCheck1 tb" tabindex="13" value="<?php echo $this->session->userdata('basalcover');?>"/>
                <a class="help_lnk" rel="popover" data-poload="help/getstring/basal_cover" data-original-title="Basal Plant Cover"></a>
                <br/>
				<label class="rhem_label_long" for="rockcover">Rock Cover %:</label>
				<input type="text" id="rockcover" class="numericCheck1 tb" tabindex="14" value="<?php echo $this->session->userdata('rockcover');?>"/>
                <a class="help_lnk" rel="popover" data-poload="help/getstring/rock_cover" data-original-title="Rock Cover"></a>
                <br/>
				<!-- Mark's update. Deleted rock cover -->
				<label class="rhem_label_long" for="littercover">Litter Cover %:</label>
				<input type="text" id="littercover" class="numericCheck1 tb" tabindex="15" value="<?php echo $this->session->userdata('littercover');?>"/>
                <a class="help_lnk" rel="popover" data-poload="help/getstring/litter" data-original-title="Litter Cover"></a>
                <br/>
				<label class="rhem_label_long" for="littercover">Biological Crusts Cover %:</label>
				<input type="text" id="cryptogamscover" class="numericCheck1 tb" tabindex="16" value="<?php echo $this->session->userdata('cryptogamscover');?>"/>
                <a class="help_lnk" rel="popover" data-poload="help/getstring/cryptogams_cover" data-original-title="Biological Crusts Cover"></a>
			</div>
		    <!--  RUN SCENARIO   -->
			<h3 style="padding-left:10px;">
				<span class="orange">6. </span> 
                <button type="button" class="btn btn-warning btn-large" style="width:200px;-webkit-box-shadow: 0 1px 8px #888;" id="runScenario" tabindex="17"><i class="icon-play icon-white"></i>Run Scenario</button>
                <a class="help_lnk" rel="popover" data-poload="help/getstring/run_scenario" data-original-title="Run Scenario"></a>
			</h3>
		    <!-- C0MPARE SCENARIOS -->
			<h3 style="padding-left:10px;">
				<span class="orange">7.</span> 
                <button type="button" id="printCompareTable" class="btn" style="width:200px;">Select Scenarios to Compare</button>
                <a class="help_lnk" rel="popover" data-poload="help/getstring/compare_scenarios" data-original-title="Compare Scenarios"></a>
			</h3>
		    <!-- CREATE A RISK ASSESSMENT SCENARIO -->
			<h3 style="padding-left:10px;">
				<span class="orange">8.</span> 
                <button type="button" id="printRiskAssessmentTableBtn" class="btn" style="width:200px;">Select Scenarios for Risk Assessment</button>
                <a class="help_lnk" rel="popover" data-poload="help/getstring/risk_assessment" data-original-title="Create Risk Assessment"></a>
			</h3>
		</div>
		<!--   RESULTS PANEL   -->
		<div id="results_panel">
			<div id="progressBar"></div> 
			<div id="welcomeMessage" style="background-color:#fff">
				<p style="padding:30px 30px 5px 30px;"><span style="font-weight:bold;font-size:22px;color:#AB5806;padding:0px 0px 20px 5px;">Welcome</span></p>
				<p style="padding:5px 30px 20px 30px;">To begin using this tool please start building a scenario using steps 1 to 6.
					<br/>You can also compare scenarios by clicking on the "7. Select Scenarios to Compare" button. 
					<br/>To run the risk assessment tool, click on the "8. Select Scenarios for Risk Assessment" button.
				</p>
			</div>
			<div id="mapContainer" style="width:773px;height:565px;display:none;">
				<span class="toggleButton" id="mapHeader" style="display:none;">CLIMATE STATIONS MAP</span>
				<div id="googleMap" style="width: 100%; height:100% ;border:1px solid #F4F4F4;display:none;"></div>
			</div>
			<div id="userScenariosTableContainer" style="display:none;"></div>
			<div id="slopeChartContainer" style="display:none;"></div>
			<div id="scenarioRunContainer" style="display:none"></div>
			<div id="scenarioTableContainer" style="display:none"></div>
			<div id="resultsContainer"></div>
		</div>
		</form>
		</div>
        <?php  include 'footer.php'; ?>

		 <div id="raModal" class="modal_large hide fade in" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
            <div class="modal-header">
            	<button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color:#333333;">Close</button>
            	<h3 id="myModalLabel">Probability of Occurrence</h3> 
            </div>
            <div class="modal-body" style="max-height:600px;">
		        <p>This graph represents the probability of occurrence of soil loss for any year to fall into the Low, Medium, High, or Very High categories.  Low, Medium, High, and Very High thresholds are based on the 50, 80, and 95 percentiles for probability of occurrence of yearly soil loss for the baseline condition. 
		        </p>
		        <p>
		        	For example, in every baseline case it is considered that 5% (in red) of the years for the baseline scenario are categorized as “Very High”.  The red parts of the bars in the other scenarios represent the fraction of years for those scenarios that also fall in that same range of yearly soil losses as defined by the greatest 5% of the baseline condition.  
		       	</p>

				<p>Note that we are reporting here soil losses and not sediment yields, which will be different, particularly when using S-shape or concave slope shapes. 
				</p>
				<figure style="text-align:center; margin: -2px 1px 0px 1px;">
		  			<img src="assets/images/Risk-Assessment-Probability-of-Occurrence-Graph.png" alt="Figure 1" style="width:670px;">
		  			<figcaption style="text-align: center;font-size:11px;"><b>Figure 1.</b> Probability of Occurrence Chart.</figcaption>
				</figure>
				<p><b>Example:</b>
				<p>We illustrate the use of the risk assessment approach at the Kendall Grassland site located in the Walnut Gulch Experimental Watershed in Tombstone, AZ. The mapping unit consists of a complex of Loamy Upland and Limy Slopes. The State-Transition-Model (STM) for the Limy Slopes 12-16” p.z. Ecological Site included 3 states: Historic Climax Plant Community (HCPC), Eroded, Lehmann Love Grass (hereafter referred to as Grass). Total foliar cover and total ground cover for each ecological state on the STM were: (HCPC=61%, Eroded=35%, Grass=38%); (HCPC=70%, Eroded=25%, Grass=54%), respectively.  
				</p>
				<p>RHEM was run for the HCPC, Grass, and Eroded states using the RHEM web interface. The 50th, 80th, and 95th percentiles for yearly soil loss were determined [β1= 0.367 (Mg/ha), β2=0.655 (Mg/ha) and β3=1.049 (Mg/ha)] from the HCPC empirical cumulative distribution of yearly soil loss values. The mean annual soil losses for the HCPC, Grass, and Eroded states are 0.360 (Mg/ha), 0.725 (Mg/ha), and 3.949 (Mg/ha), respectively. The probabilities of occurrence for each state are presented in <b>Table 1.</b>
				</p>
				<figure style="float: right; margin: -2px 1px 0px 1px;">
		  			<img src="assets/images/Risk-Assessment-Probability-of-Occurrence-Table.png" alt="Table 1"  style="width:630px;">
		  			<figcaption style="text-align: center;font-size:11px;"><b>Table 1.</b> Probability of Occurrence Matrix</figcaption>
				</figure>
			 	<p>The results (<b>Figure 1</b> and <b>Table 1</b>) indicate that the 5% chance of occurring in any year (annual exceedance probability) for the baseline condition (>1.049 Mg/ha) increases to an 90% chance of occurring in the Eroded state for any given year. RHEM results suggest that this state has many more years that fall within the Very High soil erosion severity class relative to the HCPC condition for the ecological site. In contrast, the 5% chance of occurring in any year for the baseline condition (>1.049 Mg/ha) has a 27% chance of occurring in the Grass state for any given year. In addition, the 50% annual probability soil loss year total that represents "Low" soil loss years (<=0.367 Mg/ha) has a 25% chance of occurring in the Grass state and only a 1% chance of occurring for the Eroded state for any given year. The mean annual soil loss of the Grass state (0.725 Mg/ha) falls in the Medium soil loss severity class and that of the Eroded state (3.949 Mg/ha) falls in the Very High soil loss severity class relative to the HCPC state.
			 	</p>
				<div class="modal-footer">
	                <button class="btn btn-warning" data-dismiss="modal" aria-hidden="true">Close</button>
	            </div>
			</div>
		</div>

		<div id="downloadImageModal" class="modal_large hide fade in" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
            <div class="modal-header">
            	<button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color:#333333;">Close</button>
            	<h3 id="myModalLabel">Download Image</h3> 
            </div>
            <div class="modal-body" style="max-height:600px;">
		    </div>
			<div class="modal-footer">
                <button class="btn btn-warning" data-dismiss="modal" aria-hidden="true">Download Image</button>
            </div>
		</div>

        <script type="text/javascript" src="assets/scripts/jquery-plugins.js?updated=<?php echo time();?>"></script>
		<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCbJ2kgRWtqwTBYeMV9tdRHE-9tSRqQDw0" type="text/javascript"></script>
        <script type="text/javascript" src="assets/scripts/charts.js?updated=<?php echo time();?>"></script>
        <script type="text/javascript" src="assets/scripts/map_func.js?updated=<?php echo time();?>"></script>
        <script type="text/javascript" src="assets/scripts/html2canvas.js"></script>
        <script type="text/javascript" src="assets/scripts/html2canvas.svg.js"></script>
        <script type="text/javascript" src="assets/scripts/jquery.plugin.html2canvas.js"></script>
        <script type="text/javascript" src="assets/scripts/drhem.js?updated=<?php echo time();?>"></script>
        <script type="text/javascript" src="assets/scripts/jquery.jeditable-1.7.1.mini.js"></script>
</body>
</html>