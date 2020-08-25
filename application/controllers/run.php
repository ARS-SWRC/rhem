<?php
/**
 * This controller is used the build all the input files and to run the RHEM model.
 *
 *
 * @access	public
 * @return	void
 */

class Run extends CI_Controller {

	private $ssh;
	private $scp;
	
	/* This defines a constructor for the current controller */
	function __construct()
    {
		parent::__construct();
		// load plugins
		$this->load->library('session');

		// load the phpseclib and create global $ssh and $scp objects
		chdir(APPROOT_FOLDER);
		include_once(APPPATH.'third_party/phpseclib/Net/SSH2.php');
		include_once(APPPATH.'third_party/phpseclib/Net/SCP.php');
		set_include_path(APPPATH . 'third_party/phpseclib');

		// create the SSH object
		$ssh = new Net_SSH2('10.1.2.234');
		// allows to run model in time
		$ssh->setTimeout(600);
		if (!$ssh->login('garmendariz', '(arsDSSdev)')) {
		    exit('Login Failed');
		}
		$this->ssh = $ssh;

		// create the SCP object
		$scp = new Net_SCP($this->ssh);

		$this->scp = $scp;
    }

	/**
	 * Find out if a particular sceneario already exists for the current user.
	 *
	 * @param scenarioname - The name of the current scenario
	 * @return boolean
	 */
	function doesScenarioExist($scenarioname)
	{
		if($this->session->userdata('is_logged_in') == false)
			echo 'session_expired';
		else{
			$scenarioExists = $this->Rhemmodel->does_scenario_exist($scenarioname, $this->session->userdata('user_id'));
		
			echo $scenarioExists;
		}
	}
	
	/**
	 * Runs a scenario based on the selected input values
	 *
	 * @param scenarioname - The name of the current scenario
	 * @param stateid - 
	 * @access public
	 * @return	a success string
	 * PURP: contorl
	 */
	function runScenario($saveScenarioAction, $scenarioname,$units, $stateid,$climatestation, $soiltexture,$modsoilflag,
						 $slopelength,$slopeshape,$slopesteepness,$bunchgrasscanopycover,$forbscanopycover,$shrubscanopycover,$sodgrasscanopycover,$basalcover,$rockcover, $littercover, $cryptogamscover, $sar_value)
	{
		$scenariodata['user_id'] = $this->session->userdata('user_id');
		$scenariodata['scenario_name'] = $scenarioname;
		$scenariodata['scenario_description'] = $_REQUEST['scenarioDescription'];

		// TODO: Think about removing this ability to choose which model to run later.  This was implemented to satisfy Jason William's request
		$user_account_settings = $this->Rhemmodel->get_user_account_settings($this->session->userdata('user_id'));
		if($user_account_settings->model_version == 2.1){	
			$scenariodata['version'] = 2.1;
		}
		else{
			$scenariodata['version'] = RHEM_VERSION;
		}
		$scenariodata['units'] = $units;
		// default to metric units for the current scenario
		$this->session->set_userdata('units',$units);
		$scenariodata['state_id'] = $stateid;
		$scenariodata['station_id'] = $climatestation;
		$scenariodata['soil_id'] = $soiltexture;
		$scenariodata['slope_length'] = $slopelength;
		$scenariodata['slope_shape'] = $slopeshape;
		$scenariodata['soil_moisture'] = 25;  // default parameter to be added to the scenario
		                                      // this value is defalted to 0.25 in the PAR file
		$scenariodata['slope_steepness'] = $slopesteepness;
		$scenariodata['bunchgrass_canopy_cover'] = $bunchgrasscanopycover;
		$scenariodata['forbs_canopy_cover'] = $forbscanopycover;
		$scenariodata['shrubs_canopy_cover'] = $shrubscanopycover;
		$scenariodata['sodgrass_canopy_cover'] = $sodgrasscanopycover;
		$scenariodata['basal_cover'] = $basalcover;
		$scenariodata['cryptogams_cover'] = $cryptogamscover;
		$scenariodata['rock_cover'] = $rockcover;
		$scenariodata['litter_cover'] = $littercover;
        $scenariodata['scenario_date'] = date("Y-m-d H:i:s");
        $scenariodata['sar'] = $sar_value;
	
		$currentScenarioID = 0;
		// first save the current scenario in order for user to retrieve it later
		if($saveScenarioAction == 'save'){
			$lastInsertID = $this->saveScenario($scenariodata);
			$currentScenarioID = $lastInsertID;
			// save the scenario_id to the current session	
			$this->session->set_userdata('scenarioid', $lastInsertID);
		}
		else if($saveScenarioAction == 'update'){
			$lastUpdatedID = $this->updateScenario($scenariodata);
			$currentScenarioID = $lastUpdatedID;
			// save the scenario_id to the current session	
			$this->session->set_userdata('scenarioid', $lastUpdatedID);
		}
		
		// build the soil file from the parameters saved by the user 
		if($modsoilflag == "false"){
			// build the required input files based on user inputs. 
			// TODO: Think about removing this ability to choose which model to run later.  This was implemented to satisfy Jason William's reques
			if($scenariodata['version'] == 2.1){
				$Ke = $this->buildParametersFile_v21($scenarioname,$units,$soiltexture,$bunchgrasscanopycover,$forbscanopycover,$shrubscanopycover,$sodgrasscanopycover,
											 $rockcover, $basalcover, $littercover,$cryptogamscover,$slopelength,$slopeshape,$slopesteepness, $scenariodata['version'] );
			}
			else{
				$Ke = $this->buildParametersFile($scenarioname,$units,$soiltexture,$bunchgrasscanopycover,$forbscanopycover,$shrubscanopycover,$sodgrasscanopycover,
											 $rockcover, $basalcover, $littercover,$cryptogamscover,$slopelength,$slopeshape,$slopesteepness, $sar_value, $scenariodata['version'] );
			}
		}
		else{
			$Ke = $this->openModifiedPARFile();
		}

		// Ke from the Soil file calculations will be used to exclude precipitation dates
		$this->buildStormFile($scenarioname,$stateid,$climatestation, $Ke, $scenariodata['version']);
		
		$this->buildRunFile($scenarioname, $currentScenarioID);
		
		if(EXE_SERVER == "STORM"){
			$pidAndUserID = $this->runRHEM_storm($scenarioname);
		}
		else{
			$pidAndUserID = $this->runRHEM_mudflow($scenarioname);
		}

		//error_log("SCENARIO ID: " . $currentScenarioID , 0);
		
		// return the scenarioid for the new or modified scenario
		echo $currentScenarioID . "-" . $pidAndUserID;
	}
	
	/**
	 * Saves the current scenario to the database.
	 *
	 * @access	public
	 * @return	lastInsertID - The ID of the last inserted row
	 */
	function saveScenario($scenariodata)
	{
		$lastInsertID = $this->Rhemmodel->save_scenario($scenariodata);
		
		return $lastInsertID;
	}
	
	/**
	 * Updates the current scenario to the database.
	 *
	 * @access	public
	 * @return	lastInsertID - The ID of the last inserted row
	 */
	function updateScenario($scenariodata)
	{
		$lastUpdatedID = $this->Rhemmodel->update_scenario($scenariodata);
		
		return $lastUpdatedID;
	}
	
	/**
	 * Run's the RHEM 
	 *
	 * @access	public
	 * @return	void
	 * 
	 */
	function runRHEM_mudflow($scenarioname)
	{		
		// the run file for this scenario
		$runFileName = OUTPUT_FOLDER . "run_scenario_" . $this->session->userdata('user_id') .  "_" . $this->session->userdata('scenarioid') . ".run";

		// load the RHEM model
		// TODO: Think about removing this ability to choose which model to run later.  This was implemented to satisfy Jason William's request
		$user_account_settings = $this->Rhemmodel->get_user_account_settings($this->session->userdata('user_id'));
		if($user_account_settings->model_version == 2.1){	
			exec('drhem_disag_dblex_13Dec2013.exe' . " -b " . $runFileName);	
		}
		else{
			chdir(OUTPUT_FOLDER);
			exec(RHEM_MODEL_EXEC . " -b " . $runFileName);	
		}	
		
		chdir(getcwd());	
		
		return 1;
	}


	/**
	 * Run RHEM in STORM
	 *
	 * @access	public
	 * @return	void
	 * 
	 */
	function runRHEM_storm($scenarioname)
	{		
		// the run file for this scenario
		$runFileName = OUTPUT_FOLDER . "run_scenario_" . $this->session->userdata('user_id') .  "_" . $this->session->userdata('scenarioid') . ".run";

		// load the RHEM model
		// TODO: Think about removing this ability to choose which model to run later.  This was implemented to satisfy Jason William's request
		$user_account_settings = $this->Rhemmodel->get_user_account_settings($this->session->userdata('user_id'));
		if($user_account_settings->model_version == 2.1){	
			exec('drhem_disag_dblex_13Dec2013.exe' . " -b " . $runFileName);
		}
		else{
			$runFile = "run_scenario_" . $this->session->userdata('user_id') .  "_" . $this->session->userdata('scenarioid') . ".run";
			$stormFile = "storm_input_" . $this->session->userdata('user_id') . "_" . $this->session->userdata('scenarioid') . ".pre";
			$parFile = "scenario_input_" . $this->session->userdata('user_id') . "_" . $this->session->userdata('scenarioid') . ".par";
			$summaryOutputFile =  "scenario_output_summary_" . $this->session->userdata('user_id') . "_" . $this->session->userdata('scenarioid') . ".sum";
			$detailedOutputFile =  "scenario_output_summary_" . $this->session->userdata('user_id') . "_" . $this->session->userdata('scenarioid') . ".out"; 
			 
			chdir(OUTPUT_FOLDER);
			$this->scp->put('/home/garmendariz/Projects/RHEM/' . $runFile, $runFile , NET_SCP_LOCAL_FILE);
			$this->scp->put('/home/garmendariz/Projects/RHEM/' . $stormFile, $stormFile , NET_SCP_LOCAL_FILE);
			$this->scp->put('/home/garmendariz/Projects/RHEM/' . $parFile, $parFile, NET_SCP_LOCAL_FILE);

			// run RHEM on STORM as a background process
			// GFortran: k2cligen     IFortran: ./rhem_intel  IFortran v2: k2cligen_intel
			//$pid = $this->ssh->exec('cd /home/garmendariz/Projects/RHEM; k2cligen_intel  -b ' . $runFile . ' > run.out & echo $!');

			/////////
			/////
			// new verion of RHEM which incorporates new section in summary report
			//
			// NOTE: in order to incorporate the new report section I will need to:
			//       1)  Replace the RHEM executable when running from STORM
			//       2)  Read the Yearly Totals in addition to the Yearly Maximum Dailies from the .sum file
			//       3)  Modify the scenario_output DB table and incorporate columns for the 2,5,10,25,50, and 100 year Yearly Totals
			//       4)  Modify the Tool view and controller so that the new "Yearly Totals" table can be added when the user opens a single scenario
			//       5)  Think about cleaning up this controller (Run) in order to disallow the tool to run the version of the model from MUDFLOW
			//       6)  Give the updated executable to Olaf David, so that they can update their CSIP RHEM service
			// 
			$pid = $this->ssh->exec('cd /home/garmendariz/Projects/RHEM; rhem_intel_05May2017 -b ' . $runFile . ' > run.out & echo $!');

			$this->session->set_userdata('scenario_pid', $pid);
		}	
		
		chdir(getcwd());	
		
		// return the pid and the userid
		return $pid . "-" . $this->session->userdata('user_id');
	}

	/**
	 * Checks if the current process is done running on STORM
	 *
	 * @access	public
	 * @return	void
	 * 
	 */
	function is_process_running($pid, $scenarioid, $userid)
	{	
		//error_log("PID FOR STORM PROCESS: " . $pid , 0);
		$message = $this->ssh->exec("ps ". $pid);
		$lines_arr = preg_split('/\n|\r/',$message);
		$num_newlines = count($lines_arr); 
		
		// if the STORM process has finished (num_newlines < 3), copy the results to MUDFLOW and disconnect
		if($num_newlines != 3){
			//error_log("PID FOR STORM PROCESS:  " . $num_newlines . "   is " . gettype($num_newlines), 0);
			$this->copyResults($scenarioid, $userid);
		}

		// return the number of lines form the exec (this lists the status of the PID)
		echo $num_newlines;
	}

	/**
	 * Copy results from STORM to MUDFLOW and delte these files from STORM.
	 *
	 * @access	public
	 * @return	void
	 * 
	 */
	function copyResults($scenarioid, $userid)
	{
		$summaryOutputFile =  "scenario_output_summary_" . $userid . "_" . $scenarioid . ".sum";
		$detailedOutputFile =  "scenario_output_summary_" . $userid . "_" . $scenarioid . ".out"; 

		//error_log("COPY RESULTS SCENARIO ID: " . $scenarioid , 0);

		chdir(OUTPUT_FOLDER);
		$this->scp->get('/home/garmendariz/Projects/RHEM/' . $summaryOutputFile, $summaryOutputFile);
		$this->scp->get('/home/garmendariz/Projects/RHEM/' . $detailedOutputFile, $detailedOutputFile);
	}


	/**
	 * Deletes the inputs and ouputs for the current run from STORM
	 *
	 * @access	public
	 * @return	void
	 * 
	 */
	function deleteFilesFromSTORM($scenarioid,$userid)
	{
		$runFile = "run_scenario_" . $userid .  "_" . $scenarioid . ".run";
		$stormFile = "storm_input_" . $userid . "_" . $scenarioid . ".pre";
		$parFile = "scenario_input_" . $userid . "_" . $scenarioid . ".par";
		$summaryOutputFile =  "scenario_output_summary_" . $userid . "_" . $scenarioid . ".sum";
		$detailedOutputFile =  "scenario_output_summary_" . $userid . "_" . $scenarioid . ".out"; 

		// remove scenario files from the server
		$this->ssh->exec('cd /home/garmendariz/Projects/RHEM; rm run.out ' . $runFile . ' ' . $stormFile . ' ' . $parFile . ' ' . $summaryOutputFile . ' ' . $detailedOutputFile . ';');

		$this->ssh->disconnect();
		 
	}

	/**
	 * This test is used to calibrate the runtime of the DRHEM model and to verify
	 * how long a request can stay open before it is shut down by the server.
	 *
	 * @access	public
	 * @return	void
	 */
	function runTestRHEM()
	{		
		chdir(OUTPUT_FOLDER);
		// the run file for this scenario
		$runFileName = OUTPUT_FOLDER . "run_scenario_1.run";
		// load the RHEM model
		//exec(RHEM_MODEL_EXEC . " -b " . $runFileName);
		sleep(301);
		chdir(getcwd());	
		
		echo "Finished running application in 5min 1sec!!";
		return 1;
	}
	
	/**
	 * Builds the run fille based on soil,slop,and storm files.
	 *
	 * @access	public
	 * @return	vpopmail_del_domain(domain)
	 */
	function buildRunFile($scenarioname)
	{
		// Sets paths
		$runFileName = OUTPUT_FOLDER . "run_scenario_" . $this->session->userdata('user_id') . "_" . $this->session->userdata('scenarioid') . ".run";
		$timestr = date("F j, Y, g:i a");
		
		// Write to run file
		$handle = fopen($runFileName, "w");
		
		// define the flag for creating a detailed output report
		$user_account_settings = $this->Rhemmodel->get_user_account_settings($this->session->userdata('user_id'));

		$create_detailed_repot_flag = ($user_account_settings->detailed_output_flag == 1 ? "y":"n" );

		// Build the single-line run file for DRHEM
		fwrite($handle, "scenario_input_" . $this->session->userdata('user_id') . "_" . $this->session->userdata('scenarioid') . ".par," .
						"storm_input_" . $this->session->userdata('user_id') . "_" . $this->session->userdata('scenarioid') . ".pre," .
		                "scenario_output_summary_" . $this->session->userdata('user_id') . "_" . $this->session->userdata('scenarioid') . ".sum," .
						'"' . $scenarioname . '"' . ",0,2,y,y,n,n," . $create_detailed_repot_flag);
		
		// Closes the run file
		fclose($handle);
		
		// Change the current directory
		chdir(getcwd());
	}
	
	/**
	 * Builds the parameters file. This is the main parameter file to run the DRHEM model.
	 *
	 * @access public
	 * @param scenarioname - Name of scenario
	 * @param soiltexture - The soil texture represented as an integer
	 * @param vegcommunity - The vegetation cummunity represented as an integer
	 * @param groundcover - The percent ground cover
	 * @param canopycover - The percent canopy cover
	 * @param litter - The percent of litter
	 * @return	Ke - the calculated Ke value
	 */
	function buildParametersFile($scenarioname,$units,$soiltexture,$bunchgrasscanopycover,$forbscanopycover,$shrubscanopycover,$sodgrasscanopycover,
								 $rockcover, $basalcover, $littercover,$cryptogamscover,$slopelength,$slopeshape,$slopesteepness, $sar_value, $modelversion)
	{
		// convert to percent values
		$bunchgrasscanopycover = $bunchgrasscanopycover/100;
		$forbscanopycover = $forbscanopycover/100;
		$shrubscanopycover = $shrubscanopycover/100;
		$sodgrasscanopycover = $sodgrasscanopycover/100;

		// canopy cover for grass (this is for the new Kss equations from Sam)
		$grasscanopycover = $bunchgrasscanopycover + $forbscanopycover + $sodgrasscanopycover;

		////
		// TOTAL CANOPY COVER
		$totalcanopycover = $bunchgrasscanopycover + $forbscanopycover + $shrubscanopycover + $sodgrasscanopycover;

		$rockcover = $rockcover/100;
		$basalcover = $basalcover/100;
		$littercover = $littercover/100;
		$cryptogamscover = $cryptogamscover/100;

		////
		// TOTAL GROUND COVER
		$totalgroundcover = $basalcover + $littercover + $cryptogamscover + $rockcover;

		$slopesteepness = $slopesteepness/100;

		// get the soil information from the database
		$texturerow = $this->Rhemmodel->get_soil_texture($soiltexture);
		$meanclay = $texturerow->mean_clay;

		$meanmatricpotential = $texturerow->mean_matric_potential;
		$poresizedistribution = $texturerow->pore_size_distribution;

		$meanporosity = $texturerow->mean_porosity;	
		
		// compute ft (replaces fe and fr)
		$ft =  ( -1 * 0.109) + (1.425 * $littercover) + (0.442 * $rockcover) + (1.764 * ($basalcover + $cryptogamscover)) + (2.068 * $slopesteepness);
		$ft = pow(10,$ft);


		// Caculate the new equation to calculate Ke. 
		switch ($soiltexture) {
			case 1: // "Sand"
				$Keb = 24 * exp(0.3483 * ($basalcover + $littercover) );
				break;
			case 2: // "Loamy Sand"
				$Keb = 10 * exp(0.8755 * ($basalcover + $littercover) );
				break;
			case 3: // "Sandy Loam"
				$Keb = 5 * exp(1.1632 * ($basalcover + $littercover) );
				break;
			case 4: // "Loam"
				$Keb = 2.5 * exp(1.5686 * ($basalcover + $littercover) );
				break;
			case 5: // "Silt Loam"	
				$Keb = 1.2 * exp(2.0149 * ($basalcover + $littercover) );
				break;
			case 6: // "Silt" (there is no equation devoped, yet, for silt)
				$Keb = 1.2 * exp(2.0149 * ($basalcover + $littercover) );
				break;
			case 7: // "Sandy Clay Loam"
				$Keb = 0.80 * exp(2.1691 * ($basalcover + $littercover) );
				break;
			case 8: // "Clay Loam"
				$Keb = 0.50 * exp(2.3026 * ($basalcover + $littercover) );
				break;
			case 9: // "Silty Clay Loam"
				$Keb = 0.40 * exp(2.1691 * ($basalcover + $littercover) );
				break;
			case 10: // "Sandy Clay"
				$Keb = 0.30 * exp(2.1203 * ($basalcover + $littercover) );
				break;
			case 11: // "Silty Clay"
				$Keb = 0.25 * exp(1.7918 * ($basalcover + $littercover) );
				break;
			case 12: // "Clay"
				$Keb = 0.2 * exp(1.3218 * ($basalcover + $littercover) );
				break;
		}


		/////////////////////////////////////////////
		////// 
		/////
		////
		// Calculate weighted KE
		// this array will be used to store the canopy cover, Ke, and Kss values for the cover types that are not 0
		$vegetationCanopyCoverArray = array();
		//// 
		// Calculate KE and KSS based on vegetation type

		// Ke and Kss for Shrubs
		$Ke = $Keb * 1.2;
		$shrubsCoverArray = array("CanopyCover" => $shrubscanopycover,"Ke" => $Ke);
		array_push($vegetationCanopyCoverArray,$shrubsCoverArray);

		// Ke and Kss for Sod Grass
		$Ke = $Keb * 0.8;
		$sodgrassCoverArray = array("CanopyCover" => $sodgrasscanopycover,"Ke" => $Ke);
		array_push($vegetationCanopyCoverArray,$sodgrassCoverArray);

		// Ke and Kss Bunch Grass
		$Ke = $Keb * 1.0;
		$bunchgrassCoverArray = array("CanopyCover" => $bunchgrasscanopycover,"Ke" => $Ke);
		array_push($vegetationCanopyCoverArray,$bunchgrassCoverArray);

		// Ke and Kss for Forbs
		$Ke = $Keb * 1.0;
		$forbsCoverArray = array("CanopyCover" => $forbscanopycover,"Ke" => $Ke);
		array_push($vegetationCanopyCoverArray,$forbsCoverArray);

		// Calculate the weighted Ke and Kss values based on the selected vegetation types by the user
		$weightedKe = 0;

		// calculate weighted Ke and Kss values for the vegetation types that have non-zero values
		if($totalcanopycover != 0){
			foreach($vegetationCanopyCoverArray as $selCanopyCover){
				$weightedKe = $weightedKe + ( ($selCanopyCover['CanopyCover']/$totalcanopycover) * $selCanopyCover['Ke'] );
			}
		}
		else{
			$weightedKe = $Keb;
		}

		/////////////////////////////////////////////
		////// 
		/////
		// IMPLEMENT THE NEW EQUATIONS FROM SAM FROM 01222015
		// Kss variables
		$Kss_Seg_Bunch = 0;
		$Kss_Seg_Sod = 0;
		$Kss_Seg_Shrub = 0;
		$Kss_Seg_Shrub_0 = 0;
		$Kss_Seg_Forbs = 0;

		$Kss_Average = 0;

		$Kss_Final = 0;

		// 1) 
		//   a) CALCULATE KSS FOR EACH VEGETATION COMMUNITY USING TOTAL FOLIAR COVER
		//		A)   BUNCH GRASS
		if ($totalgroundcover < 0.475){
			$Kss_Seg_Bunch = 4.154 + 2.5535 * $slopesteepness - 2.547 * $totalgroundcover - 0.7822 * $totalcanopycover;
			$Kss_Seg_Bunch = pow(10,$Kss_Seg_Bunch);
		}
		else{
			$Kss_Seg_Bunch = 3.1726975 + 2.5535 * $slopesteepness - 0.4811 * $totalgroundcover - 0.7822 * $totalcanopycover;
			$Kss_Seg_Bunch = pow(10,$Kss_Seg_Bunch);
		}

		//		B)   SOD GRASS
		if ($totalgroundcover < 0.475){
			$Kss_Seg_Sod = 4.2169 + 2.5535 * $slopesteepness - 2.547 * $totalgroundcover - 0.7822 * $totalcanopycover;
			$Kss_Seg_Sod = pow(10,$Kss_Seg_Sod);
		}
		else{
			$Kss_Seg_Sod = 3.2355975 + 2.5535 * $slopesteepness - 0.4811 * $totalgroundcover - 0.7822 * $totalcanopycover;
			$Kss_Seg_Sod = pow(10,$Kss_Seg_Sod);
		}

		//		C)   SHRUBS
		if ($totalgroundcover < 0.475){
			$Kss_Seg_Shrub = 4.2587 + 2.5535 * $slopesteepness - 2.547 * $totalgroundcover - 0.7822 * $totalcanopycover;
			$Kss_Seg_Shrub = pow(10,$Kss_Seg_Shrub);
		}
		else{
			$Kss_Seg_Shrub = 3.2773975 + 2.5535 * $slopesteepness - 0.4811 * $totalgroundcover - 0.7822 * $totalcanopycover;
			$Kss_Seg_Shrub = pow(10,$Kss_Seg_Shrub);
		}

		//		D)   FORBS
		if ($totalgroundcover < 0.475){
			$Kss_Seg_Forbs = 4.1106 + 2.5535 * $slopesteepness - 2.547 * $totalgroundcover - 0.7822 * $totalcanopycover;
			$Kss_Seg_Forbs = pow(10,$Kss_Seg_Forbs);
		}
		else{
			$Kss_Seg_Forbs = 3.1292975 + 2.5535 * $slopesteepness  - 0.4811 * $totalgroundcover - 0.7822 * $totalcanopycover;
			$Kss_Seg_Forbs = pow(10,$Kss_Seg_Forbs);
		}

		//   b) CALCULATE KSS AT TOTAL FOLIAR = 0 FROM SHRUB EQUATION
		if ($totalgroundcover < 0.475){
			$Kss_Seg_Shrub_0 = 4.2587 + 2.5535 * $slopesteepness - 2.547 * $totalgroundcover;
			$Kss_Seg_Shrub_0 = pow(10,$Kss_Seg_Shrub_0);
		}
		else{
			$Kss_Seg_Shrub_0 = 3.2773975 + 2.5535 * $slopesteepness - 0.4811 * $totalgroundcover;
			$Kss_Seg_Shrub_0 = pow(10,$Kss_Seg_Shrub_0);
		}

		// 2) CALCULATE AVERAGE KSS WHEN TOTAL FOLIAR COVER IS CLOSE TO 0
		if($totalcanopycover > 0 && $totalcanopycover < 0.02){
			$Kss_Average = $totalcanopycover/0.02 * ( ($shrubscanopycover/$totalcanopycover) * $Kss_Seg_Shrub + 
													 ($sodgrasscanopycover/$totalcanopycover) * $Kss_Seg_Sod + 
													 ($bunchgrasscanopycover/$totalcanopycover) * $Kss_Seg_Bunch + 
													 ($forbscanopycover/$totalcanopycover) * $Kss_Seg_Forbs ) + 
													 (0.02 - $totalcanopycover)/0.02 * $Kss_Seg_Shrub_0; 
		}
		else{
			$Kss_Average = ($shrubscanopycover/$totalcanopycover) * $Kss_Seg_Shrub + 
						   ($sodgrasscanopycover/$totalcanopycover) * $Kss_Seg_Sod +
						   ($bunchgrasscanopycover/$totalcanopycover) * $Kss_Seg_Bunch +
						   ($forbscanopycover/$totalcanopycover) * $Kss_Seg_Forbs;
		}

		// 3) CALCULATE KSS USED FOR RHEM (with canopy cover == 0 and canopy cover > 0)
		if($totalcanopycover == 0){
			if($totalgroundcover < 0.475){
				$Kss_Final = 4.2587 + 2.5535 * $slopesteepness - 2.547 * $totalgroundcover;
				$Kss_Final = pow(10,$Kss_Final);
			}
			else{
				$Kss_Final = 3.2773975 + 2.5535 * $slopesteepness - 0.4811 * $totalgroundcover;
				$Kss_Final = pow(10,$Kss_Final);
			}
		}
		else{
			if($totalgroundcover < 0.475){
				$Kss_Final = $totalgroundcover/0.475 * $Kss_Average + (0.475 - $totalgroundcover)/0.475 * $Kss_Seg_Shrub;
			}
			else{
				$Kss_Final = $Kss_Average;
			}
		}

		$Kss_Final = ($Kss_Final * 1.3) * 2.0;

        if($sar_value != ""){
            // first version of the equation from Kossi
            //$Kss_Final = $Kss_Final + (642 * $sar_value);
            // Kss = KssRHEM + ( f*SAR ), the value of f is 642
            // Update version of the equation by Kossi
            $Kss_Final = $Kss_Final + (711 * $sar_value);
        }
        
		# changes units back to metric when english is selected
		if($units == 'english'){
			$slopelength = $slopelength * 0.3048;
		}

		// Set working directory and file name
		$soilFileName = OUTPUT_FOLDER . "scenario_input_" . $this->session->userdata('user_id') . "_" . $this->session->userdata('scenarioid') . ".par";

		// Write to soil log file
		$handle = fopen($soilFileName, "w");		

		// soil log file
		$timestr = date("F j, Y, g:i a");

		// writes the parameter file required by DRHEM 
		fwrite($handle, "! Parameter file for scenario: " . $scenarioname . "\n");
		fwrite($handle, "! Date built: " . $timestr .  " (Version ". $modelversion . ") \n");
		fwrite($handle, "! Parameter units: DIAMS(mm), DENSITY(g/cc),TEMP(deg C) \n");
		fwrite($handle,"BEGIN GLOBAL \n");
		fwrite($handle, "		CLEN	=	" . ($slopelength*2.5) . " \n"); // The characteristic length of the hillslope in meters or feet
		fwrite($handle, "		UNITS	=	metric \n");                     // The units for the length parameter
		fwrite($handle, "		DIAMS	=	" . $texturerow->clay_diameter . "\t" . $texturerow->silt_diameter . "\t" . $texturerow->small_aggregates_diameter . "\t" . $texturerow->large_aggregates_diameter . "\t" . $texturerow->sand_diameter . "\n");      // List of representative soil particle diameters (mm or in) for up to 5 particle classes
		fwrite($handle, "		DENSITY	=	" . $texturerow->clay_specific_gravity . "\t" . $texturerow->silt_specific_gravity . "\t" . $texturerow->small_aggregates_specific_gravity . "\t" . $texturerow->large_aggregates_specific_gravity . "\t" . $texturerow->sand_specific_gravity . "\n");  // List of densities (g/cc) corresponding to the above particle classes
		fwrite($handle, "		TEMP	=	40 \n");                      // temperature in degrees C 
		fwrite($handle, "		NELE	=	1 \n");                       // number of hillslope elements (planes)
		fwrite($handle, "END GLOBAL \n");                  
		fwrite($handle, "BEGIN PLANE \n");                               
		fwrite($handle, "		ID	=	1 \n");                           // identifier for the current plane
		
		fwrite($handle, "		LEN	=	" . $slopelength . " \n");        // The plane slope length in meters or feet
		fwrite($handle, "		WIDTH	=	1.000 \n");                   // The plane bottom width in meters or feet

		$chezy = pow( ( (8 * 9.8)/$ft ), 0.5 );
		$rchezy = pow( ( (8 * 9.8)/$ft ), 0.5 );

		fwrite($handle, "		CHEZY	=	" . $chezy . " \n");         // Overland flow Chezy Coeff. (m^(1/2)/s) (square root meter per second)
		fwrite($handle, "		RCHEZY	=	" . $rchezy . " \n");        // Concentrated flow Chezy Coeff. (m^(1/2)/s) (square root meter per second)
		
		$slopeParameters = $this->createSlopeParameters($units,$slopeshape,$slopesteepness);
		fwrite($handle, $slopeParameters);                               // SL: Slope expressed as fractional rise/run
																         // SX: Normalized distance
			  
		fwrite($handle, "		CV	=	1.0000 \n");                     // This is the coefficient of variation for Ke
		fwrite($handle, "		SAT	=	0.25 \n");   // Initial degree of soil saturation, expressed as a fraction of of the pore space filled
		fwrite($handle, "		PR	=	1 \n");                          // Print flag
		fwrite($handle, "		KSS	=	" . $Kss_Final . " \n");         // Splash and sheet erodibility coefficient
		fwrite($handle, "		KOMEGA	=	0.000007747 \n");            // Undisturbed concentrated erodibility coeff. (s2/m2) value suggested by Nearing 02Jul2014  
		fwrite($handle, "		KCM	=	0.000299364300 \n");             // Maximum concentrated erodibility coefficient (s2/m2) 
		fwrite($handle, "		CA	=	1.000 \n");                      // Cover fraction of surface covered by intercepting cover — rainfall intensity is reduced by this fraction until the specified interception depth has accumulated
		fwrite($handle, "		IN	=	0.0000 \n");                     // Interception depth in mm or inches
		fwrite($handle, "		KE	=	" . $weightedKe . " \n");        // Effective hydraulic conductivity (mm/h)
		fwrite($handle, "		G	=	" . $meanmatricpotential . " \n"); // Mean capillary drive, mm or inches — a zero value sets the infiltration at a constant value of Ke
		fwrite($handle, "		DIST	=	" . $poresizedistribution . " \n"); // Pore size distribution index. This parameter is used for redistribution of soil moisture during unponded intervals
		fwrite($handle, "		POR	=	" . $meanporosity . " \n");      //  Porosity
		fwrite($handle, "		ROCK	=	0.0000 \n");                 // Volumetric rock fraction, if any. If KE is estimated based on textural class it should be multiplied by (1 - Rock) to reflect this rock volume
		fwrite($handle, "		SMAX	=	1.0000 \n");                 // Upper limit to SAT
		fwrite($handle, "		ADF	=	0.00 \n");                       // Beta decay factor in the detachement equation in Al-Hamdan et al 2012 (Non-FIRE)
		fwrite($handle, "		ALF	=	0.8000 \n");                     // allow variable alfa in the infiltration Smith-Parlange Equation, alf <= 0.05, Green and Ampt  
		fwrite($handle, "		BARE	= 0 ! INACTIVE \n");                   // Fraction of bare soil to total area. 1 - ground cover ( this will be used if ADF is not 0)
		fwrite($handle, "		RSP	=	1.000 \n");                      // Rill spacing in meters or feet
		fwrite($handle, "		SPACING	=	1.000 \n");		             // Average micro topographic spacing in meters or feet
		fwrite($handle, "		FRACT	=	" .  $texturerow->clay_fraction  . "\t" . $texturerow->silt_fraction . "\t" . $texturerow->small_aggregates_fraction . "\t" . $texturerow->large_aggregates_fraction . "\t" . $texturerow->sand_fraction . "\n"); // List of particle class fractions — must sum to one
		fwrite($handle, "END PLANE \n");
		
		// Closes the soil file
		fclose($handle);

		return $weightedKe;
	}


/**
	 * Builds the parameters file. This is the main parameter file to run the DRHEM model.
	 *
	 * @access public
	 * @param scenarioname - Name of scenario
	 * @param soiltexture - The soil texture represented as an integer
	 * @param vegcommunity - The vegetation cummunity represented as an integer
	 * @param groundcover - The percent ground cover
	 * @param canopycover - The percent canopy cover
	 * @param litter - The percent of litter
	 * @return	Ke - the calculated Ke value
	 */    
	function buildParametersFile_v21($scenarioname,$units,$soiltexture,$bunchgrasscanopycover,$forbscanopycover,$shrubscanopycover,$sodgrasscanopycover,
								 $rockcover, $basalcover, $littercover,$cryptogamscover,$slopelength,$slopeshape,$slopesteepness, $modelversion)
	{
			// convert to percent values
		$bunchgrasscanopycover = $bunchgrasscanopycover/100;
		$forbscanopycover = $forbscanopycover/100;
		$shrubscanopycover = $shrubscanopycover/100;
		$sodgrasscanopycover = $sodgrasscanopycover/100;

		$groundcover = ($basalcover + $littercover + $cryptogamscover + $rockcover)/100;
		$rockcover = $rockcover/100;
		$basalcover = $basalcover/100;
		$littercover = $littercover/100;
		$cryptogamscover = $cryptogamscover/100;
	
		// Set working directory and file name
		$soilFileName = OUTPUT_FOLDER. "scenario_input_" . $this->session->userdata('user_id') . "_" . $this->session->userdata('scenarioid') . ".par";
		
		// soil log file
		$timestr = date("F j, Y, g:i a");
		
		$slopesteepness = $slopesteepness/100;

		// get the soil information from the database
		$texturerow = $this->Rhemmodel->get_soil_texture($soiltexture);
		$meanclay = $texturerow->mean_clay;
		//$meansand = $texturerow->mean_sand;
		$meanmatricpotential = 100; //$texturerow->mean_matric_potential;
		$meanporosity = $texturerow->mean_porosity;	
		
		// compute ft (replaces fe and fr)
		$ft =  ( -1 * 0.109) + (1.425 * $littercover) + (0.442 * $rockcover) + (1.764 * ($basalcover + $cryptogamscover)) + (2.068 * $slopesteepness);
		$ft = pow(10,$ft);
		
		////
		// Calculate weighted KSS
		////
		// Add all vegetation canopy cover percentages
		$totalcanopycover = $bunchgrasscanopycover + $forbscanopycover + $shrubscanopycover + $sodgrasscanopycover;

		// this array will be use to store the canopy cover, Ke, and Kss values for the cover types that are not 0
		$vegetationCanopyCoverArray = array();

		// Ks and Kss equation number 1 (shrubs)
		if($shrubscanopycover != 0){
			$Keb = 0.174 - (1.450 * $meanclay) + (2.975 * $groundcover) + (0.923 * $totalcanopycover);
			$Keb = exp($Keb) * 0.3;	
			$Ke = $Keb * 1.2;
			
			$Kss = 4.00836 - (1.17804 * $rockcover) - (0.98196 * ($littercover + $totalcanopycover));
			$Kss = pow(10,$Kss); // antilog

			$shrubsCoverArray = array("CanopyCover" => $shrubscanopycover,"Ke" => $Ke, "Kss" => $Kss);
			array_push($vegetationCanopyCoverArray,$shrubsCoverArray);
		}
		// Ks and Kss equation number 2 (sod grass)
		if($sodgrasscanopycover != 0){
			$Keb = 0.174 - (1.450 * $meanclay) + (2.975 * $groundcover) + (0.923 * $totalcanopycover);
			$Keb = exp($Keb) * 0.3;	
			$Ke = $Keb * 0.8;

			$Kss = 3.13334  - (0.20055 * $totalcanopycover) - (0.50550 * $littercover);
			$Kss = pow(10,$Kss);
			$Kss = ($Kss/1.5);  

			$sodgrassCoverArray = array("CanopyCover" => $sodgrasscanopycover,"Ke" => $Ke, "Kss" => $Kss);
			array_push($vegetationCanopyCoverArray,$sodgrassCoverArray);
		}
		// Ks and Kss equation number 3 (bunch grass and forbs)
		if($bunchgrasscanopycover != 0){
			$Keb = 0.174 - (1.450 * $meanclay) + (2.975 * $groundcover) + (0.923 * $totalcanopycover);
			$Keb = exp($Keb) * 0.3;	
			$Ke = $Keb * 1.0;

			$Kss = 3.13334  - (0.20055 * $totalcanopycover) - (0.50550 * $littercover);
			$Kss = pow(10,$Kss); // antilog

			$bunchgrassCoverArray = array("CanopyCover" => $bunchgrasscanopycover,"Ke" => $Ke, "Kss" => $Kss);
			array_push($vegetationCanopyCoverArray,$bunchgrassCoverArray);
		}
		if($forbscanopycover != 0){
			$Keb = 0.174 - (1.450 * $meanclay) + (2.975 * $groundcover) + (0.923 * $totalcanopycover);
			$Keb = exp($Keb) * 0.3;	
			$Ke = $Keb * 1.0;

			$Kss = 3.13334  - (0.20055 * $totalcanopycover) - (0.50550 * $littercover);
			$Kss = pow(10,$Kss); // antilog

			$forbsCoverArray = array("CanopyCover" => $forbscanopycover,"Ke" => $Ke, "Kss" => $Kss);
			array_push($vegetationCanopyCoverArray,$forbsCoverArray);
		}

		// if all vegetation types are 0, get Ke and Kss for all types and weigh them
		if($shrubscanopycover == 0  and $sodgrasscanopycover == 0 and $bunchgrasscanopycover == 0 and $forbscanopycover == 0)
		{
			// Calculate Ke and Kss values based on selected vegetation community
			$Keb = 0.174 - (1.450 * $meanclay) + (2.975 * $groundcover) + (0.923 * $shrubscanopycover);
			$Keb = exp($Keb) * 0.3;
			$Ke = $Keb * 1.2;
			
			$Kss = 4.00836 - (1.17804 * $rockcover) - (0.98196 * ($littercover + $shrubscanopycover));
			$Kss = pow(10,$Kss); // antilog

			$shrubsCoverArray = array("CanopyCover" => $shrubscanopycover,"Ke" => $Ke, "Kss" => $Kss);
			array_push($vegetationCanopyCoverArray,$shrubsCoverArray);

			$Keb = 0.174 - (1.450 * $meanclay) + (2.975 * $groundcover) + (0.923 * $sodgrasscanopycover);
			$Keb = exp($Keb) * 0.3;
			$Ke = $Keb * 0.8;

			$Kss = 3.13334  - (0.20055 * $sodgrasscanopycover) - (0.50550 * $littercover);
			$Kss = pow(10,$Kss);
			$Kss = ($Kss/1.5);  

			$sodgrassCoverArray = array("CanopyCover" => $sodgrasscanopycover,"Ke" => $Ke, "Kss" => $Kss);
			array_push($vegetationCanopyCoverArray,$sodgrassCoverArray);

			$Keb = 0.174 - (1.450 * $meanclay) + (2.975 * $groundcover) + (0.923 * $bunchgrasscanopycover);
			$Keb = exp($Keb) * 0.3;
			$Ke = $Keb * 1.0;

			$Kss = 3.13334  - (0.20055 * $bunchgrasscanopycover) - (0.50550 * $littercover);
			$Kss = pow(10,$Kss); // antilog

			$bunchgrassCoverArray = array("CanopyCover" => $bunchgrasscanopycover,"Ke" => $Ke, "Kss" => $Kss);
			array_push($vegetationCanopyCoverArray,$bunchgrassCoverArray);

			$Keb = 0.174 - (1.450 * $meanclay) + (2.975 * $groundcover) + (0.923 * $forbscanopycover);
			$Keb = exp($Keb) * 0.3;
			$Ke = $Keb * 1.0;

			$Kss = 3.13334  - (0.20055 * $forbscanopycover) - (0.50550 * $littercover);
			$Kss = pow(10,$Kss); // antilog

			$forbsCoverArray = array("CanopyCover" => $forbscanopycover,"Ke" => $Ke, "Kss" => $Kss);
			array_push($vegetationCanopyCoverArray,$forbsCoverArray);
		}

		// Calculate the weighted Ke and Kss values based on the selected vegetation types by the user
		$weightedKe = 0;
		$weightedKss = 0;

		// weigh Ke and Kss values and divide by 4 if the total canopy cover is 0
		if($shrubscanopycover == 0  and $sodgrasscanopycover == 0 and $bunchgrasscanopycover == 0 and $forbscanopycover == 0){
			$weightedKe = ($vegetationCanopyCoverArray[0]['Ke'] + $vegetationCanopyCoverArray[1]['Ke'] + $vegetationCanopyCoverArray[2]['Ke'] + $vegetationCanopyCoverArray[3]['Ke'])/4;
			$weightedKss = ($vegetationCanopyCoverArray[0]['Kss'] + $vegetationCanopyCoverArray[1]['Kss'] + $vegetationCanopyCoverArray[2]['Kss'] + $vegetationCanopyCoverArray[3]['Kss'])/4;
		}
		// calculate weighted Ke and Kss values for the vegetation types that have non-zero values
		else{
			foreach($vegetationCanopyCoverArray as $selCanopyCover){
				//echo 'Ke: ' . $selCanopyCover['Ke'] . '     Kss: ' . $selCanopyCover['Kss'] . '<br/>';
				$weightedKe = $weightedKe + ( ($selCanopyCover['CanopyCover']/$totalcanopycover) * $selCanopyCover['Ke'] );
				$weightedKss = $weightedKss + ( ($selCanopyCover['CanopyCover']/$totalcanopycover) * $selCanopyCover['Kss'] );
			}
		}

		// Multiply Kss by 1.3 in order to account for the bias in the log transformation (relative to Duan 1989)
		$weightedKss = $weightedKss * 1.3;
		
		// Write to soil log file 
		$handle = fopen($soilFileName, "w");

		# changes units back to metric when english is selected 
		if($units == 'english'){
			$slopelength = $slopelength * 0.3048;
		}
		
		// writes the parameter file required by DRHEM 
		fwrite($handle, "! Parameter file for scenario: " . $scenarioname . "\n");
		fwrite($handle, "! Date built: " . $timestr .  " (Version ". $modelversion . ") \n");
		fwrite($handle, "! Parameter units: DIAMS(mm), DENSITY(g/cc),TEMP(deg C) \n");
		fwrite($handle,"BEGIN GLOBAL \n");
		fwrite($handle, "		CLEN	=	" . ($slopelength*2.5) . " \n");
		fwrite($handle, "		UNITS	=	metric \n");
		fwrite($handle, "		DIAMS	=	" . $texturerow->clay_diameter . "\t" . $texturerow->silt_diameter . "\t" . $texturerow->small_aggregates_diameter . "\t" . $texturerow->large_aggregates_diameter . "\t" . $texturerow->sand_diameter . "\n");      
		fwrite($handle, "		DENSITY	=	" . $texturerow->clay_specific_gravity . "\t" . $texturerow->silt_specific_gravity . "\t" . $texturerow->small_aggregates_specific_gravity . "\t" . $texturerow->large_aggregates_specific_gravity . "\t" . $texturerow->sand_specific_gravity . "\n");
		fwrite($handle, "		TEMP	=	40 \n");
		fwrite($handle, "		NELE	=	1 \n");
		fwrite($handle, "END GLOBAL \n");                  
		fwrite($handle, "BEGIN PLANE \n");                               
		fwrite($handle, "		ID	=	1 \n");
		
		fwrite($handle, "		LEN	=	" . $slopelength . " \n");
		fwrite($handle, "		WIDTH	=	1.000 \n");

		$chezy = pow( ( (8 * 9.8)/$ft ), 0.5 );
		$rchezy = pow( ( (8 * 9.8)/$ft ), 0.5 );

		fwrite($handle, "		CHEZY	=	" . $chezy . " \n");
		fwrite($handle, "		RCHEZY	=	" . $rchezy . " \n");
		
		$slopeParameters = $this->createSlopeParameters($units,$slopeshape,$slopesteepness);
		fwrite($handle, $slopeParameters);
			  
		fwrite($handle, "		CV	=	0.0000 \n"); 
		fwrite($handle, "		SAT	=	0.25 \n"); 
		fwrite($handle, "		PR	=	1 \n");
		fwrite($handle, "		KSS	=	" . $weightedKss . " \n");
		fwrite($handle, "		KOMEGA	=	0.000003134007 \n");
		fwrite($handle, "		KCM	=	0.000299364300 \n");
		fwrite($handle, "		CA	=	1.000 \n");
		fwrite($handle, "		IN	=	0.0000 \n");
		fwrite($handle, "		KE	=	" . $weightedKe . " \n");
		fwrite($handle, "		G	=	100.0000 \n");
		fwrite($handle, "		DIST	=	0.2500 \n");
		fwrite($handle, "		POR	=	" . $meanporosity . " \n");
		fwrite($handle, "		ROCK	=	0.0000 \n");
		fwrite($handle, "		SMAX	=	1.0000 \n");
		fwrite($handle, "		ADF	=	0.00 \n");
		fwrite($handle, "		ALF	=	0.03000 \n");
		fwrite($handle, "		BARE	=	0.23 \n"); // 1 - ground cover ( this will be used if ADF is not 0)
		fwrite($handle, "		RSP	=	1.000 \n");
		fwrite($handle, "		SPACING	=	1.000 \n");		  
		fwrite($handle, "		FRACT	=	" .  $texturerow->clay_fraction  . "\t" . $texturerow->silt_fraction . "\t" . $texturerow->small_aggregates_fraction . "\t" . $texturerow->large_aggregates_fraction . "\t" . $texturerow->sand_fraction . "\n");
		fwrite($handle, "END PLANE \n");
		
		// Closes the soil file
		fclose($handle);

		return $weightedKe;
	}


	/**
	 * This function opens the modified soil file and returns the Ke saved in the soil file.
	 *
	 * @return	float
	 */
	function openModifiedPARFile()
	{
		// first rename the soil file based on the current scenarioid
		$soilFileName = OUTPUT_FOLDER . "scenario_input_" . $this->session->userdata('user_id') . "_(m).par";
		$soilFileNameRename = OUTPUT_FOLDER . "scenario_input_" . $this->session->userdata('user_id') . "_" . $this->session->userdata('scenarioid') . ".par";
		rename($soilFileName, $soilFileNameRename);
		
		// open the file and extract the Ke value
		$file = fopen($soilFileNameRename, "r");
		$Ke = 0.0;
		$linenumber = 1;
		while(!feof($file))
		{
			$line = fgets($file);
			if($linenumber == 28){
				$linearray = explode("=", $line);
				$Ke = $linearray[1];
			}
			$linenumber = $linenumber + 1;
		}
		// Closes the soil file
		//fclose($handle);
		
		return $Ke;
	}
	
	/**
	 * Builds the storm file based on the selected or modified Cligen PAR file.
	 *
	 * @access	public
	 * @param scenarioname - The name of the current scenario
	 * @param stateid - the param
	 * @state climatestation - the selected climate station
	 * @param Ke - The Ke value used to filter the rain events
	 * @return	void
	 */
	function  buildStormFile($scenarioname,$stateid,$climatestation,$Ke, $modelversion)
	{
		// Set working directory and file name
		$stormFileName = OUTPUT_FOLDER . "storm_input_" . $this->session->userdata('user_id') . "_" . $this->session->userdata('scenarioid') . ".pre";
		$timestr = date("F j, Y, g:i a");

		// Write the precip file to the file.  This is done first in order to get the total number of rain events. 
		$numevents = $this->extractPrecipFromCligenOutput($scenarioname,$stateid,$climatestation,$Ke,$stormFileName);

		// Re-open the storm file and append the scenario information at the beginning of the file
		$storm_content = file_get_contents($stormFileName);
		$handle = fopen($stormFileName, "w");
		fwrite($handle, "# Storm file for scenario: " . $scenarioname . "\n");
		fwrite($handle, "# Date built: " . $timestr .  " (Version ". $modelversion . ") \n");
		fwrite($handle, "# State: " . strtoupper($stateid) . "\n");  
		fwrite($handle, "# Climate Station: " . $climatestation . "\n");
		fwrite($handle, $numevents . " # The number of rain events\n");
		fwrite($handle, "0 # Breakpoint data? (0 for no, 1 for yes)\n");
		fwrite($handle, "#  id     day  month  year  Rain   Dur    Tp     Ip\n");
		fwrite($handle, "#                           (mm)   (h)\n");
	
		fwrite($handle, $storm_content);
		fclose($handle);
	}
	
	/**
	 * Builds the slope and normalized distance parameters for the input parameters file
	 *
	 * @access	public
	 * @param units - The units that will be used for the scenario
	 * @param slopelength - The length of slope in feet
	 * @param slopeshape - The shape of the slope represented as an integer
	 * @param sloopesteepness - The steepness of the slope represented as a percent (alreay divided by 100)
	 * @return	void
	 */
	function createSlopeParameters($units,$slopeshape,$slopesteepness)
	{
		$SL = "		SL	=	";
		$SX = "		SX	=	";
		switch ($slopeshape) {
			case 1: // "Uniform"
				$SL = $SL . $slopesteepness . "	,	" . $slopesteepness . "\n";
				$SX = $SX . "0.00	,	1.00 \n";
				break;
			case 2: // "Convex"
				$SL = $SL . "0.001	,	" . $slopesteepness * 2 . "\n";
				$SX = $SX . "0.00	,	1.00 \n";
				break;
			case 3: // "Concave"
				$SL = $SL . $slopesteepness * 2  . "	,	0.001\n";
				$SX = $SX . "0.00	,	1.00 \n";
				break;
			case 4: // "S-shaped"
				$SL = $SL . "0.001	,	" .  $slopesteepness * 2  . "	,	0.001\n";
				$SX = $SX . "0.00	,	0.50	,	1.00 \n";
				break;
		}
		
		return $SL . $SX;
	}
	

	/**
	 * Filter precipitation events based on Ke 
	 *   NOTE: This version of the function will read the new Cligen output format 
	 *         (based on modificaitons by Mariano)
	 *
	 * @access	public
	 * @param scenarioname - The name of the current scenario
	 * @param stateid - The state abbreviation
	 * @param climatestation - The numeric representation of the climatestation
	 * @return	void
	 */
	function extractPrecipFromCligenOutput($scenarioname,$stateid,$climatestation,$Ke,$stormFileName)
	{
		$handle = fopen($stormFileName, "w");
		// change the directory to output cligen files
		chdir(CLIGEN_FOLDER . $stateid . '/300yr');
		$file = fopen(strtoupper($stateid) . "_" . $climatestation . "_300yr.out", "r") or exit("Unable to open Cligen output file!");

		//Output a line of the file until the end is reached
		$lineNumberAfterFilter = 1;

		$lineNumber = 0;
		while(!feof($file))
		{
			$line = fgets($file);
			
			// print data from non-blank lines
			if($line != '' and $lineNumber > 17)
			{	
			 	$linearray = preg_split('/\s+/', $line);

				$KeComparisonValue = $linearray[8] * ($linearray[5]/$linearray[6]);

				// include rain events only if Ke >=   ip    *   (  P  /  D  )
				if($Ke < $KeComparisonValue)
				{
					$values = str_pad($lineNumberAfterFilter,10," ",STR_PAD_BOTH) . str_pad($linearray[2], 6) . 
					str_pad($linearray[3],6) . str_pad($linearray[4],6) . str_pad($linearray[5],7) . 
					str_pad($linearray[6],7) . str_pad($linearray[7],7) . str_pad($linearray[8],4);		

					fwrite($handle,  $values  . PHP_EOL);
					
					$lineNumberAfterFilter = $lineNumberAfterFilter + 1;
				}
			 }
			$lineNumber = $lineNumber + 1;
		}
		fclose($file);
		fclose($handle);
		chdir(getcwd());
		
		// return the number of events after the Ke filter has been applied
		return $lineNumberAfterFilter - 1;
	}
}
?>