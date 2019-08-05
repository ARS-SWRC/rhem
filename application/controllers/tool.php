<?php
/**
 * This controller is used to list, define, delete, and check if a scenario exists. 
 *
 * @access	public
 * @return	void
 */
class Tool extends CI_Controller {
	
	/* This defines a constructor for the current controller */
	function __construct()
    {
		parent::__construct();
		$this->load->library('session');
		$this->load->library('Errors');
		$this->load->library('table');
    }

	/**
	 * This is the index page for the first step of the DRHEM process.  Provides messages based
	 * on user authentication state and wb 
	 *
	 * @access	public
	 * @return	void
	 */
	function index()
	{
		// check if user is logged in before retreiving the user's scenarios
		if($this->session->userdata('is_logged_in') == true)
		{
			// keep track of weather the scenario was opened or not, if it was set the flas go false, if not distroy the scenario session data
			if($this->session->userdata('opened_scenario') == true) {
				$this->session->set_userdata('opened_scenario', false);
			}
			else{
				$this->session->unset_userdata('scenarioid');
			    $this->session->unset_userdata('scenarioname');
			    $this->session->unset_userdata('scenariodescription');
			    $this->session->unset_userdata('units'); 
			    $this->session->unset_userdata('version'); 
			    $this->session->unset_userdata('stateid');
			    $this->session->unset_userdata('climatestationid');
			    $this->session->unset_userdata('soilid');
                $this->session->unset_userdata('soiltextureinput');
                $this->session->unset_userdata('sar');
			    $this->session->unset_userdata('soilmoisture');
			    $this->session->unset_userdata('slopelength');
			    $this->session->unset_userdata('slopeshape');
			    $this->session->unset_userdata('slopesteepness');
			    $this->session->unset_userdata('bunchgrasscanopycover');
			    $this->session->unset_userdata('forbscanopycover');
			    $this->session->unset_userdata('shrubscanopycover');
			    $this->session->unset_userdata('sodgrasscanopycover');
			    $this->session->unset_userdata('basalcover');
			    $this->session->unset_userdata('rockcover');
			    $this->session->unset_userdata('littercover');
			    $this->session->unset_userdata('cryptogamscover');
			}

			// load the model data required by the current view
	  		$this->data['states_rs'] = $this->Rhemmodel->get_all_states();
			$this->data['soil_textures_rs'] = $this->Rhemmodel->get_soil_textures();
			$this->data['soil_moisture_rs'] = $this->Rhemmodel->get_soil_moisture_values();
			$this->data['slope_shape_rs'] = $this->Rhemmodel->get_slope_shape();
			$this->data['vegetation_communities_rs'] = $this->Rhemmodel->get_vegetation_communities();
			// user account settings
			$user_account_settings = $this->Rhemmodel->get_user_account_settings($this->session->userdata('user_id'));
			if($user_account_settings->detailed_output_flag)
				$this->session->set_userdata('detailed_output_flag', true);
			else
				$this->session->set_userdata('detailed_output_flag', false);
			$this->data['user_account_settings_rs'] = $user_account_settings;
			
			// load the DRHEM view
			$this->load->view('tool', $this->data);
		}
		
		// show a warning message if user is not logged in and disable input tags
		else
		{
			redirect('login');
		}
	}


	/**
	 * Checks if the user is currently logged in. 
	 *
	 * @access	public
	 * @return	void
	 */
	function checkIfLoggedIn()
	{
		if($this->session->userdata('is_logged_in') == 'true'){
		}
		else{
			return 'Your session has expired.';	
		}
	}
	
	/**
	 * Prints all of the available of th user scenarios as a table provided an array of scenarios.
	 *
	 * @access	public
	 * @return	void
	 */
	function printUserScenariosTable()
	{
		if($this->session->userdata('is_logged_in') == false)
			echo 'session_expired';
		else{
			$userscenarios = $this->Rhemmodel->get_user_scenarios($this->session->userdata('user_id'),"order_by_date");
			$scenariosTableHTML = '';

			$scenariosTableHTML .= '<table id="userScenariosTable" style="table-layout:fixed;width:100%;">';
			$scenariosTableHTML .= '<thead>';
			$scenariosTableHTML .= '<tr>';
			$scenariosTableHTML .= '<th>Open?</th>';
			$scenariosTableHTML .= '<th class="sort" style="width:100px;">Scenario Name</th>';
			$scenariosTableHTML .= '<th class="sort">Date Ran</th>';
			$scenariosTableHTML .= '<th style="width:50px;">Version</th>';
			$scenariosTableHTML .= '<th style="width:40px;">State</th>';
			$scenariosTableHTML .= '<th class="sort">Climate Station</th>';
			$scenariosTableHTML .= '<th style="width:55px;">Units</th>';
			$scenariosTableHTML .= '<th>Scenario Files</th>';
			$scenariosTableHTML .= '<th style="width: 55px;">Delete?</th>';
			$scenariosTableHTML .= '</tr>';
			$scenariosTableHTML .= '</thead>';
			$scenariosTableHTML .= '<tbody>';
				
			if(count($userscenarios) == 0){
				$scenariosTableHTML .= '<tr><td colspan="9">' . $this->errors->print_message('You have not run any scenarios yet.', 'red_box') . '</td></tr>';
			}
			else{
				foreach ($userscenarios as $row)
				{
					$scenariosTableHTML .= '<tr>';
						//$protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https:' : 'http:';
						//error_log("TEST: " . $protocol , 0);
						$scenariosTableHTML .= '<td><button class="btn" style="height: 40px;" type="button" onclick="location.href = \'' . base_url() . 'tool/setscenario/' . $row->scenario_id  . '\'">Open</button></td>';
						$scenariosTableHTML .= '<td class="table-column-text-overflow" style="font-weight:bold;"><span class="scenario_name_overflow editable">' . $row->scenario_name. '</span></td>';
						$scenariosTableHTML .= '<td>' .  date("m/d/y H:i:s",strtotime($row->scenario_date)) . '</td>';
						
						$scenariosTableHTML .= '<td>' .  $row->version  . '</td>'; 
						
						$scenariosTableHTML .= '<td>' . $row->state_id . '</td>';
						$scenariosTableHTML .= '<td>' . ucwords(strtolower($row->station)) . '</td>';
						$scenariosTableHTML .= '<td>' . $row->units . '</td>';
						$scenariosTableHTML .= '<td>
												<ul style="list-style-type:none;padding-left:0px;">
													<li><a href="' . base_url() . 'temp/scenario_input_' . $this->session->userdata('user_id') . '_' . $row->scenario_id . '.par?updated=' . time() .  '" target="_blank">Inputs</a></li>
													<li><a href="' . base_url() . 'temp/storm_input_' . $this->session->userdata('user_id') . '_' . $row->scenario_id . '.pre" target="_blank">Storm</a></li>
													<li><a href="' . base_url() . 'temp/scenario_output_summary_' . $this->session->userdata('user_id') . '_' . $row->scenario_id . '.sum" target="_blank">Summary</a></li>';
						if($this->session->userdata('detailed_output_flag') == true){
							$scenariosTableHTML .= $this->printDetailedOutput($this->session->userdata('user_id'), $row->scenario_id);
						}
						$scenariosTableHTML .= '</ul>
												</td>';
						$scenariosTableHTML .= '<td><input type="checkbox" value="' . $row->scenario_id  . '" /></td>';
					$scenariosTableHTML .= '</tr>';
				}
			}
			$scenariosTableHTML .= '</tbody>';
			$scenariosTableHTML .= '</table>';
			$scenariosTableHTML .= '<button id="scenariosDeleteButton" type="button" class="btn btn-danger btn-large" style="width:200px;height:45px;margin:10px 0px 0px 270px;" onclick="deleteScenarios()"><i class="icon-trash"></i>Delete Scenarios</button>';
			$scenariosTableHTML .= '<script>enableButtonSticky("scenariosDeleteButton");loadScenariosTableInteraction();</script>';

			echo $scenariosTableHTML;
		}
	}

	/**
	 * Prints a link to the model's detailed output if the user has enabled this feature thrhgouth the account panel.
	 *
	 * @access	public
	 * @return	void
	 */
	function printDetailedOutput($user_id, $scenario_id)
	{
		$detailOutputrow = '';
		$detailedOutputFile = OUTPUT_FOLDER .  'scenario_output_summary_' . $user_id . '_' . $scenario_id . '.out';
		if(file_exists($detailedOutputFile)){
			$detailOutputrow  = '<li><a href="' . base_url() . 'temp/scenario_output_summary_' . $user_id . '_' . $scenario_id . '.out" target="_blank">Detailed </a></li>';
		}

		return $detailOutputrow;
	}
	
	/**
	 * Creates a table with all user scenarios. This table will be used by user to
	 * select the scenarios to compare.
	 *
	 * @access	public
	 * @return	void
	 */
	function printUserScenariosTableToCompare()
	{
		if($this->session->userdata('is_logged_in') == false)
			echo 'session_expired';
		else{
			$userscenarios = $this->Rhemmodel->get_user_scenarios($this->session->userdata('user_id'), "order_by_date");
			
			$scenariosTableHTML = '<div id="scenarioTable">';
			$scenariosTableHTML .= '<table id="scenariosTable" style="table-layout:fixed;width:100%;">';
			$scenariosTableHTML .= '<thead>';
			$scenariosTableHTML .= '<tr>';
			$scenariosTableHTML .= '<th class="sort" style="width: 130px;">Scenario Name</th>';
			$scenariosTableHTML .= '<th class="sort"  style="width:45px;">Date Ran</th>';
			$scenariosTableHTML .= '<th class="sort" style="width:35px;">Version</th>';
			$scenariosTableHTML .= '<th class="sort" style="width:35px;">State</th>';
			$scenariosTableHTML .= '<th class="sort"  style="width:80px;">Climate Station</th>';
			$scenariosTableHTML .= '<th style="width:50px;">Units</th>';
			$scenariosTableHTML .= '<th style="width:60px;">Compare <a class="help_lnk left" rel="popover" data-poload="help/getstring/compare_scenarios_with_order" data-original-title="Scenario Comparison Order" style="margin-top:0px;"></a></th>';
			$scenariosTableHTML .= '</tr>';
			$scenariosTableHTML .= '</thead>';
			$scenariosTableHTML .= '<tbody>';
			
			if(count($userscenarios) == 0){
				$scenariosTableHTML .= '<tr><td colspan="9">' . $this->errors->print_message('You have not run any scenarios yet.', 'red_box') . '</td></tr>';
			}
			else{
				foreach ($userscenarios as $row)
				{
					$scenariosTableHTML .= '<tr>';
					$scenariosTableHTML .= '<td class="table-column-text-overflow" style="font-weight:bold"><span class="compare_scenario_name_overflow">' . $row->scenario_name . '</span></td>';
					$scenariosTableHTML .= '<td>' . date("m/d/y H:i:s",strtotime($row->scenario_date)) . '</td>';
					
					$scenariosTableHTML .= '<td>' .  $row->version  . '</td>';  
					
					$scenariosTableHTML .= '<td>' . $row->state_id . '</td>';
					$scenariosTableHTML .= '<td>' . ucwords(strtolower($row->station)) . '</td>';
					$scenariosTableHTML .= '<td>' . $row->units . '</td>';
					$scenariosTableHTML .= '<td><input type="checkbox" id="' . $row->scenario_id . '"/></td>';
					$scenariosTableHTML .= '</tr>';
				}
			}
			$scenariosTableHTML .= '</tbody>';
			$scenariosTableHTML .= '</table>';
			$scenariosTableHTML .= '</div>';
			$scenariosTableHTML .= '<button type="button" class="btn btn-warning btn-large" style="width:200px;height:45px;margin:10px 0px 0px 270px;" id="runCompareScenarios">Compare Scenarios</button>';
			$scenariosTableHTML .= '<script>enableButtonSticky("runCompareScenarios");attachPopupEvents();</script>';
			echo $scenariosTableHTML; 
		}
	}
	
	/**
	 * Creates a table with all user scenarios. This table will be used by user to
	 * select the scenarios to pass to the risk assessment module.  Not only will the user will have to define the list
	 * of scenarios to use for the assessment but the user will also need to specify the baseline scneario that will be used
	 * as a baseline for the risk assessment simulation.
	 *
	 * @access	public
	 * @return	void
	 */
	function printUserScenariosTableForRiskAssessment()
	{
		if($this->session->userdata('is_logged_in') == false)
			echo 'session_expired';
		else{
			$userscenarios = $this->Rhemmodel->get_user_scenarios_with_results($this->session->userdata('user_id'), "order_by_date");

			$scenariosTableHTML = '<div class=" alert alert-success alert-block">
										<button type="button" class="close" data-dismiss="alert">×</button>
									  	<h4>Note</h4>
									 	 Only scenarios with detailed output and ran with verion 2.3 of RHEM will be displayed here. To run scenarios with detailed output, please enable this in your <b>Account</b> section (top right corner).
									</div>';
			$scenariosTableHTML .= '<div id="scenarioTable">';
			$scenariosTableHTML .= '<table id="scenariosTable" style="table-layout:fixed;width:100%;">';
			$scenariosTableHTML .= '<thead>';
			$scenariosTableHTML .= '<tr>';
			$scenariosTableHTML .= '<th class="sort" style="width: 100px;">Scenario Name</th>';
			$scenariosTableHTML .= '<th class="sort" style="width:40px;">Date Ran</th>';
			$scenariosTableHTML .= '<th class="sort" style="width:30px;">Version</th>';
			$scenariosTableHTML .= '<th class="sort" style="width:20px;">State</th>';
			$scenariosTableHTML .= '<th class="sort"  style="width:50px;">Climate Station</th>';
			$scenariosTableHTML .= '<th style="width:40px;">Units</th>';
			$scenariosTableHTML .= '<th style="width:40px;">Soil Loss (ton/ac or ha/year)</th>';
			$scenariosTableHTML .= '<th style="width:50px;">Alternative</th>';
			$scenariosTableHTML .= '<th style="width:40px;">Baseline</th>';
			$scenariosTableHTML .= '</tr>';
			$scenariosTableHTML .= '</thead>';
			$scenariosTableHTML .= '<tbody>';

			if(count($userscenarios) == 0){
				$scenariosTableHTML .= '<tr><td colspan="9">' . $this->errors->print_message('You have not run any scenarios yet.', 'red_box') . '</td></tr>';
			}
			else{

				foreach ($userscenarios as $row)
				{
					// only print the scenarios that the user has ran with the detailed output and scenarios that have been run with version 2.3 of RHEM
					$detailedOutputFile = OUTPUT_FOLDER .  'scenario_output_summary_' . $this->session->userdata('user_id') . '_' . $row->scenario_id . '.out';
					if(file_exists($detailedOutputFile) and $row->version == '2.3'){
						$scenariosTableHTML .= '<tr>';
						$scenariosTableHTML .= '<td class="table-column-text-overflow" style="font-weight:bold"><span class="compare_scenario_name_overflow">' . $row->scenario_name . '</span></td>';
						$scenariosTableHTML .= '<td>' . date("m/d/y H:i:s",strtotime($row->scenario_date)) . '</td>';
						
						$scenariosTableHTML .= '<td>' .  $row->version  . '</td>';  
						
						$scenariosTableHTML .= '<td>' . $row->state_id . '</td>';
						$scenariosTableHTML .= '<td>' . ucwords(strtolower($row->station)) . '</td>';
						$scenariosTableHTML .= '<td>' . $row->units . '</td>';
						$scenariosTableHTML .= '<td>' . $row->avg_soilloss . '</td>';
						$scenariosTableHTML .= '<td style="text-align:center;"><input type="checkbox" id="' . $row->scenario_id . '"/></td>';
						$scenariosTableHTML .= '<td></td>';
						$scenariosTableHTML .= '</tr>';
					}
				}
			}
			$scenariosTableHTML .= '</tbody>';
			$scenariosTableHTML .= '</table>';
			$scenariosTableHTML .= '</div>';
			$scenariosTableHTML .= '<button type="button" class="btn btn-warning btn-large" style="width:220px;height:45px;margin:10px 0px 0px 270px;" id="runRiskAssessment">Run Risk Assessment</button>';
			$scenariosTableHTML .= '<script>enableButtonSticky("runRiskAssessment");</script>';
			echo $scenariosTableHTML;
		}
	}

	/**
	 * Set a selected scenario as the current scenario.  This function will set all of the session variables to the
	 * ones found in the current scenario.
	 *
	 * @access	public
	 * @param   scenarioid - The scenario id
	 * @return	void
	 */
	function deleteScenarios()
	{	
		if($_REQUEST['scenarios'] != ''){
			$scenariosToDelete = json_decode(str_replace('\\', '', $_REQUEST['scenarios']), true); 
			
			foreach ( $scenariosToDelete as $index=>$scenario_id ){
				// delete scenarios from database
				$this->Rhemmodel->delete_scenario($scenario_id);
				$this->Rhemmodel->delete_scenario_results($scenario_id);
				
				// delete input and output files from file system
				$scenarioInput = OUTPUT_FOLDER . 'scenario_input_' . $this->session->userdata('user_id') . '_' . $scenario_id . '.par';
				$stormInput = OUTPUT_FOLDER . 'storm_input_' . $this->session->userdata('user_id') . '_' . $scenario_id . '.pre';
				$scenarioSummaryOutput = OUTPUT_FOLDER . 'scenario_output_summary_' . $this->session->userdata('user_id') . '_' . $scenario_id . '.sum';
				$scenarioDetailOutput = OUTPUT_FOLDER . 'scenario_output_summary_' . $this->session->userdata('user_id') . '_' . $scenario_id . '.out';
				if (file_exists($scenarioInput)) {
					unlink($scenarioInput);
				}
				if (file_exists($stormInput)) {
					unlink($stormInput);
				}
				if (file_exists($scenarioSummaryOutput)) {
					unlink($scenarioSummaryOutput);
				}
				if (file_exists($scenarioDetailOutput)) {
					unlink($scenarioDetailOutput);
				}
			}
			
			echo $this->printUserScenariosTable();
		}
	}
	
	/**
	 * Set a selected scenario as the current scenario.  This function will set all of the session variables to the
	 * ones found in the current scenario.
	 *
	 * @access	public
	 * @param   scenarioid - The scenario id
	 * @return	void
	 */
	function setScenario($scenarioid)
	{	
		$currentscenario = $this->Rhemmodel->get_single_scenario($scenarioid);

		// create a new scenario array to hold all of the data for the scenario to be loaded
		$scenario = array(
			   'scenarioid' => $currentscenario->scenario_id,
			   'scenarioname' => $currentscenario->scenario_name,
			   'scenariodescription' => $currentscenario->scenario_description,
			   'units' => $currentscenario->units,
			   'version' => $currentscenario->version,
			   'stateid'      => $currentscenario->state_id,
			   'climatestationid' => $currentscenario->station_id,
			   'soilid'       =>  $currentscenario->soil_id,
			   'soilmoisture' => $currentscenario->soil_moisture,
			   'slopelength'  => $currentscenario->slope_length,
			   'slopeshape'   => $currentscenario->slope_shape,
			   'slopesteepness' => $currentscenario->slope_steepness,
			   'bunchgrasscanopycover' => $currentscenario->bunchgrass_canopy_cover,
			   'forbscanopycover' => $currentscenario->forbs_canopy_cover,
			   'shrubscanopycover' => $currentscenario->shrubs_canopy_cover,
			   'sodgrasscanopycover' => $currentscenario->sodgrass_canopy_cover,
			   'basalcover'  => $currentscenario->basal_cover,
			   'rockcover'    => $currentscenario->rock_cover,
			   'littercover'  => $currentscenario->litter_cover,
               'cryptogamscover'  => $currentscenario->cryptogams_cover,
               'sar'  => $currentscenario->sar,
			   'opened_scenario'  => true 
		   );

		$this->session->set_userdata($scenario);

		// redirect to the tool controler
		redirect('tool');
	}

	/**
	 * Renames a scenario. If the scnario name exits, let the user know, if not rename it. 
	 *
	 * @access	public
	 * @param   new_scenario_name - The new scenario name
	 * @param   scenario_id - The scenario id
	 * @return	String - a message if scenario exists
	 */
	function renameScenario($new_scenario_name,$scenario_id)
	{	
		if($this->Rhemmodel->find_scenario_id($new_scenario_name, $this->session->userdata('user_id')) == -999){
			$this->Rhemmodel->rename_scenario($new_scenario_name,$scenario_id);				
		}
		else{
			echo "Scenario exists";
		}
	}

	/************************************************************************/
	/********************* MISCELLANEOUS  METHODS ***************************/
	/************************************************************************/
	/**
	 * This is a miscellaneous function implemented to find out which users exist in legacy RHEM that are not in
	 * v2 of RHEM.  The ending list will be used to add the users to the v2 RHEM users table.
	 * 
	 */
	function findUniqueUsers()
	{
		$usersTable = $this->Rhemmodel->find_unique_users();
		echo count($usersTable) + "<br/>";
		//echo print_r($usersTable);
		//echo $this->table->generate($usersTable);
		foreach($usersTable as $value){
			echo $value . ";";
		}
	}

	function test_scenario_exists()
	{
		echo "Waiting for DB to respond...<br/>";
		echo $this->Rhemmodel->test_scenario_exists() . '<br/>';
		echo "DB Responded";
	}

}