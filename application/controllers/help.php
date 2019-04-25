<?php
/**
 * This controller is used to send AJAX messages for the interface tooltips (documentation).
 *
 * @access	public
 * @return	void
 */
class Help extends CI_Controller {
	
	function __construct()
    {
		parent::__construct();
    }
	
	function getstring($topic)
	{
		switch($topic){
			case 'define_scenario_panel':
				echo '<p>A scenario is defined as a unique set of input parameters needed to run RHEM.  It can be saved to view results, compared with other scenarios, or modified to create a new scenario.</p>';
				break;
			case 'clear_scenario': 
				echo 'Clears all of the input parameters for the current scenario.  Use this option to start a new scenario.';
				break;
			case 'session_name': 
				echo 'A unique user-assigned name given to the current scenario.  No two scenarios can have the same name. <b>Note:</b> characters allowed are . - _ ,';
				break;
			case 'session_description': 
				echo 'A short description about the current scenario (optional).';
				break;
			case 'units_selection': 
				echo 'Select the units to be used for the current scenario\'s input and output values. Note that if scenarios are to be compared, they must have the same units<br/><br/>
					<b>Note:</b> For soil loss and sediment yield, RHEM uses Mg/ha (megagrams per hectare) for metric units and ton/ac (U.S. tons per acre) for English units.  The conversion factor to move from Mg/ha to ton/ac is 0.446.  1 Mg/ha = 0.446 ton/ac. <br/>For precipitation and runoff, RHEM uses mm for metric units and inches for English units.</b>';
				break;
			case 'show_scenarios': 
				echo 'Shows a list of scenarios saved by the user.  In this screen, the user can: <ul>
						<li>view input parameters and output results for scenarios that have been ran</li>
						<li>open an existing scenario</li>
						<li>rename an existing scenario</li>
						<li>delete an existing scenario</li>';
				break;
			case 'manual_inputs_modification':
				echo 'Allows the user to manually modify the inputs parameter file.  <br/><br/><b>NOTE:</b> the soil texture, slope, and vegetation input
					  panels will be disabled when modifying inputs manually.';
				break;
			case 'climate_station_panel': 
				echo '<p>The climate station can be selected either by selecting the State and Station name or by locating the scenario area on a Google Map.  
					  If the second option is used, the station closest to the selected area will be used.</p>
					  <p>RHEM uses the CLIGEN model to generate daily rainfall statistics for a 300 year weather sequence that is representative of a time-stationary climate and used by the rainfall disaggregation component of RHEM.  
					  The disaggregation component uses rainfall amount, duration, ratio of time of peak intensity to duration, and the ratio of peak intensity to 
					  average intensity to compute a time-intensity distribution of a rainfall event.</p>';
				break;
			case 'state': 
				echo 'The state to use for the selection of the climate station.';
				break;
			case 'station': 
				echo 'Select the climate station  that is most like the location being analyzed (a station with similar elevation to the study area).';
				break;
			case 'show_selected_station': 
				echo 'Select the scenario area on the map.  The climate station closest to the area selected will be used in the simulation. ';
				break;
			case 'select_station_from_map': 
				echo 'Select the climate station  that is most like the location being analyzed (a station with similar elevation to the study area).';
				break;
			case 'soil_texture_panel': 
				echo '<p>The soil texture class is the soil texture of the upper 4 cm (1.57 in) of the soil profile.  It is input as a class name from the USDA soil textural triangle:</p>
				      <p style="text-align:center;"><img src="assets/images/soil_triangle.jpg" style="border:0px;"/></p>';
				break;
			case 'texture_select': 
				echo 'If the upper soil layer is greater than 20cm/8in, the user can select the soil texture from the "Soil Texture:" list box. <br/> 
				      Otherwise, the soil texture can be calculated by adding a series of layer thickness values and their corresponding layer textures.';
				break;
			case 'texture_input_method': 
				echo 'Enter the layer thickness in cm/inches and the texture of the layer.  <br/>Example: <br/>
				      <table>
						<tr><th>Layer</th><th>Depth (cm/in)</th><th>Thickness (cm/in)</th><th>Texture</th></tr>
					    <tr><td>1</td><td>0-8</td><td><b>8</b></td><td><b>Loam</b></td></tr>
						<tr><td>2</td><td>8-23</td><td><b>12</b></td><td><b>Clay Loam</b></td></tr>
					  </table>';
				break;
			case 'texture': 
				echo 'Select a soil texture from the 12 classes of the USDA Soil Classification.';
				break;
			case 'moisture_content': 
				echo 'Defines the wetness of the soil based on a Dry (0.25), Medium (0.50), or Wet (0.75) scale. The moisture content is the percent saturation defined by the percent of the soil porosity filled by water.';
				break;
			case 'slope':
				echo '<p>In order to assess the <b>Soil Loss</b> rates on a hillslope, the length of the path that water flows down a slope as sheet and rill flow until it reaches an area where flow begins to concentrate in a channel, or to the point where the slope flattens out causing deposition of the sediment load.</p>
					  <p> In order to assess <b>Sediment Delivery</b> from a hillslope to a channel one may continue the hillslope across any toe-slope deposition area up to a channel, but the slope shape in that case must be designated either as concave or S-shaped.  These are the slope shapes that will experience toe-slope deposition.</p> <br/>
					  <p><b>Soil Loss</b> is the rate of erosion occurring on the portion of the slope that experiences net loss.<br/>
						 <b>Sediment Delivery</b> relates to the net amount of sediment that leaves the hillslope profile, which includes the soil loss less any toe-slope deposition.</p><br/><p><b>Note:</b> Slope legth defaults to 50m (164ft) for all scenario runs.</p>';
				break;
			case 'slope_length': 
				echo '<p>Slopes up to 394ft(120m) long are supported.</p>
					  <p>
					  	Examples:<br/>
					  	<b style="color:#de1c1c;">A </b>Point of origin of runoff to road that concentrates runoff.<br/>
					  	<b style="color:#1c3cde;">B </b>On nose of hill, from point to origin of runoff to flood plain where deposition would occur.<br/>
					  	<b style="color:#b31cde;">C </b>Point of origin of runoff to slight depression where runoff would concentrate.<br/>
					  </p>
					  <p style="text-align:center;"><img src="assets/images/slope_length_image.gif" style="border:0px;"/></p>';
				break;
			case 'slope_width': 
				echo 'Enter the length of the slope in feet.';
				break;
			case 'slope_shape': 
				echo 'Select a slope shape which most closely matches the typical slope shape of the scenario area. <br/><img src="assets/images/slope_graphs.gif" style="border:0px;"/>';
				break;
			case 'slope_steepness': 
				//echo 'The steepness is calculated as the change in elevation divided by the field measured slope length, commonly referred to as the "rise over the run".  Note that the units of the change in elevation and slope length have to be the same.';
				echo 'The steepness percent is calculated as the change in elevation divided by the field measured slope length, which is the sin of the slope angle, multiplied by 100.';
				break;
			case 'slope_width': 
				echo 'Enter the length of the slope in feet.';
				break;
			case 'cover_characteristics': 
				echo '<p>The foliar and ground cover inputs are used in the parameter estimation equations for the Green-Ampt infiltration equation, the friction factor, and the erosion equations.  
					  <br/><b><a href="http://wiki.landscapetoolbox.org/doku.php/field_methods:line_point_intercept" target="_blank">See here</a></b> for guidelines on measuring foliar and ground cover.  In general, cover is measured as the first hit above ground being foliar cover and first hit on 
					  material in contact with the ground as ground cover.  For example, using the line transect method of measurement, a given point on the line can only have one type of foliar 
					  cover associated with it, that being the foliar cover type of first (highest elevation) contact.  The sum of percentages of ground cover types should equal 100% - bare soil%.</p>';
				break;
			case 'veg_community': 
				echo 'The dominant vegetation community as determined by that plant growth form that constitutes the greatest percentage of total foliar cover.';
				break;
			case 'basal_cover': 
				echo 'Refers to the area occupied at the intersection of the plant and soil surface.';
				break;
			case 'cryptogams_cover': 
				echo '<p>Biological soil crusts are cyanobacteria, green algae, lichens, mosses, microfungi, and other bacteria that grow directly on the soil surface.  
				(other names, crytogamic, cryptobiotic, microphytic)</p>';
				break;
			case 'bunch_canopy_cover': 
				echo 'Refers to the area covered by the vertical projection of bunch grass plants present, alive or dead, onto the soil surface.';
				break;
			case 'forbs_canopy_cover': 
				echo 'Refers to the area covered by the vertical projection of forbs/annual plants present, alive or dead, onto the soil surface.';
				break;
			case 'shrubs_canopy_cover': 
				echo 'Refers to the area covered by the vertical projection of shurb plants present, alive or dead, onto the soil surface.';
				break;
			case 'sod_canopy_cover': 
				echo 'Refers to the area covered by the vertical projection of sod grass plants present, alive or dead, onto the soil surface.';
				break;
			case 'rock_cover': 
				echo 'The rock cover is the percentage of cover of the soil surface by rocks (> 0.25 inch or 5 mm).';
				break;
			case 'litter': 
				echo 'The proportion of the soil surface covered by a vertical projection of litter.Litter is the uppermost layer or organic debris on the soil surface.  Litter can be freshly fallen or slightly decomposed plant material.  Usually has a haylike appearance on the soil surface.  The litter component is not rooted in the soil.  Can be persistent (from woody plants, or yearly carryover) or nonpersistent (produced annually by herbaceous plants).  In the tall grass prairie, the herbaceous litter component can be up 3 years old.';
				break;
			case 'run_scenario': 
				echo '<p>Run scenario is used to generate output from:
						<ul>
							<li>a new scenario</li>
							<li>an edited scenario</li>
							<li>a renamed scenario</li>
						</ul></p>';
				break;
			case 'annual_averages':
				echo 'The user may download these graphs in either .svg (raster) or .jpg by hovering mouse over the top left corners of the plots.  The .svg files can later be transformed to other formats such as EPS or PDF using tools such as Adobe Illustrator or Inkscape to provide high resolution graphs. ';
				break;
			case 'return_frequency':
				echo 'A return frequency storm is the size of the largest runoff or erosion event that is expected to occur on average once during the designated time period.  Typically we look at these types of storms to assess the potential effects of the large and infrequent events.<br/><br/>The user may download these graphs in either .svg (raster) or .jpg by hovering mouse over the top left corners of the plots.  The .svg files can later be transformed to other formats such as EPS or PDF using tools such as Adobe Illustrator or Inkscape to provide high resolution graphs.';
				break;
			case 'return_frequency_yearly_maximum_daily':
				echo 'These values are for daily occurrences.  They are computed by determining the maximum daily value for each year of simulation for each of the correspondent variables (rain, runoff, soil loss, sediment yield), then ranking them for all 300 years of simulated weather.';
				break;
			case 'return_frequency_yearly_totals':
				echo 'These values are for yearly totals.  They are computed by determining the yearly total value for each year of simulation for each of the correspondent variables (rain, runoff, soil loss, sediment yield), then ranking them for all 300 years of simulated weather. (You will need to rerun scenarios run in previous interface versions in order for you to see the values for this table.)';
				break;
			case 'csv_report':
				echo 'Download report as an image (PNG) or as a CSV(Comma-Separated Values). CSV files can be opened with most spreadsheet applications, such as Excel.';
				break;
			case 'compare_scenarios': 
				echo '<p>Compare the results of up to 5 existing scenarios</p>';
				break;
			case 'compare_scenarios_with_order': 
				echo '<p style="text-transform:none!important;">The user can define the specific order of appearance of scenarios compared in the output report based on the selection order (click order) of the scenarios. The integer label next to the checkbox defines the order in which the scenarios will appear (from left to right).</p>';
				break;
			case 'risk_assessment': 
				echo '<p style="line-height:110%;">Create a risk assessment scenario for up to 5 existing scenarios</p>';
				break;
			case 'frequency_analysis_rp_chart':
				echo '<p>This graph shows the yearly total return frequencies for the user defined baseline and chosen scenarios, which is the total soil loss for a year that will occur once in every X Return Period years.  This graph is interactive with the correspondent table below.  Click on a line in the table to see its position on the graph. Hover over the different scenario lines to identify scenario names.</p>';
				break;
			case 'frequency_analysis_rp_table_1':
				echo '<p>This table shows the total yearly soil loss (column 1) for a year that will occur once every 2, 5, 10, etc… years (column 2) for the baseline condition.  The return frequency associated with the baseline yearly soil loss is shown in subsequent columns for each alternative scenario. Click on a line in the table to see its position on the graph.</p>';
				break;
			case 'frequency_analysis_rp_table_2':
				echo '<p>This table shows the amount of total soil loss for a single year (ton/ acre or Mg/ha) that can be expected to occur every 2, 5, 10, etc. years for each scenario. </p>';
				break;
		}
	}
}
?>