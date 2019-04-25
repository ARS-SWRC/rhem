<?php
/**
 * This controller will: 1) parse the list of scenarios sent from the client
 *                       2) build the input file required to run the RHEM risk assessment module
 *                       3) run the risk assessment module for a list of scenarios
 *                       4) create a volatile report based on the results from the risk assessment run (table and charts)
 *
 *
 * @access	public
 * @return	void
 */
class RunRiskAssessment extends CI_Controller {
	
	private $SCENARIOS_COLOR_ARRAY = array('#46A23C','#BFA11B','#404040','#73AEC9','#A13F3D');

	private $ssh;
	private $scp;

	/* This defines a constructor for the current controller */
	function __construct()
    {
		parent::__construct();
		// load plugins
		$this->load->library('session');
		$this->load->library('table');

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
	 * Runs the risk assessment scenario based using the selected scenarios and baseline scenario defined by the user.
	 *
	 * @access	public
	 * @return	void
	 */
    function runRiskAssessmentSenario()
    {
		// create an array of the scnarios that will be used for this risk assessment scenario
		$scenarios = json_decode(str_replace('\\', '', $_REQUEST['scenarios']), true);
		
		// get the baseline scneario id	from the list of scnearios
		$scenarios_json_str_array = explode(",", $_REQUEST['scenarios']);
		$baseline_scenario_id = str_replace('{"baseline":','',$scenarios_json_str_array[0]);

		$unitsArray = $this->getUnitsArray($baseline_scenario_id);

		$this->createInputFileForRiskAssessment($scenarios);

		$this->doRunRiskAssessmentModule($scenarios);

		$ra_results_array = $this->readRiskAssessmentResults($unitsArray);

		$this->loadRiskAssessmentResults($scenarios, $ra_results_array, $unitsArray, $baseline_scenario_id);

		// TESTING
		//echo json_encode($ra_results_array[0], JSON_NUMERIC_CHECK);
		//$myLastElement = end($_REQUEST['scenarios']);
		//echo "Last element is..." . $myLastElement;
		//echo "<br/>";
		//echo $scenarios;		
		//echo implode(",",$scenarios);
		//echo "Ran risk assessment!";
    }


	/**
	 * Run the RHEM risk assessment module using the input file created for this scenario.
	 *
	 * @access	public
	 * @return	void
	 */
    function doRunRiskAssessmentModule($scenarios)
    {
    	chdir(OUTPUT_FOLDER);

    	$raRunFileName = "ra_run_scenario_" . $this->session->userdata('user_id') . ".run";

    	exec(RHEM_RISK_ASSESSMENT_EXEC . " -b " . $raRunFileName);


    	// STORM Running Risk Assessment Module
    	/*$this->scp->put('/home/garmendariz/Projects/RHEM/risk_assessment/' . $raRunFileName , $raRunFileName , NET_SCP_LOCAL_FILE);
    	foreach($scenarios as $scenario_index => $scenario_id){
    		$current_scenario = "scenario_output_summary_" . $this->session->userdata('user_id') .  "_" . $scenario_id . ".out";
    		$this->scp->put('/home/garmendariz/Projects/RHEM/risk_assessment/' . $current_scenario, $current_scenario , NET_SCP_LOCAL_FILE);
    	}

    	$this->ssh->exec('cd /home/garmendariz/Projects/RHEM/risk_assessment; rhem_ra  -b ' . $runFile . ' > run.out & echo $!');
    	sleep(2);*/


    	chdir(getcwd());
    }

	/**
	 * Creates an input file to be used to run the Risk Assessment module
	 *
	 * @access	public
	 * @return	void
	 */
    function createInputFileForRiskAssessment($scenarios)
    {
    	chdir(OUTPUT_FOLDER);

    	// Sets the name for the risk assessment input file
		$raRunFileName = "ra_run_scenario_" . $this->session->userdata('user_id') . ".run";
		$raOutFileName = "ra_run_scenario_" . $this->session->userdata('user_id') . ".OUT";

		if (file_exists($raRunFileName)) {
			unlink($raRunFileName);
			unlink($raOutFileName);
		}

		// Write to run file to be used to run the risk assessment
		$handle = fopen($raRunFileName, "w");

    	foreach($scenarios as $scenario_index => $scenario_id){
    		fwrite($handle, "scenario_output_summary_" . $this->session->userdata('user_id') .  "_" . $scenario_id . ".out" . "\n");
    	}

    	// Closes the run file
		fclose($handle);
		
		// Change the current directory
		chdir(getcwd());
    }

	/**
	 * Reads the risk assemssment result and retuns an array with the results that will be used to:
	 *    1) Create the frequency distribution chart
	 *    2) Create the Risk Assessment Scenario chart
	 *    3) Create the Soil Loss Probabilities Occurrance chart
	 *
	 * @access	public
	 * @return	void
	 */
    function readRiskAssessmentResults($unitsArray)
    {
    	chdir(OUTPUT_FOLDER);

    	// define the conversion value if user is looking at a risk assessment scenario with English units
    	$conversionValue = 1;
		if($unitsArray[0] == "english")
			$conversionValue = 0.446;

    	// THE ARRAY TO STORE RESULTS
    	$ra_results_array = array();

    	  // Sets the name for the risk assessment input file
		$raOutputFileName = "ra_run_scenario_" . $this->session->userdata('user_id') . ".OUT";

		// Write to run file to be used to run the risk assessment
		$handle = fopen($raOutputFileName, "r");

		// array to hold the risk assessment analysis data
		$asl_array  = array();
		$ra_array   = array();
		$fd_array   = array();
		$rar_array  = array(); // risk assessment ranges array
		$farp_array = array(); // frequency analysis return period array

		// array to store the range values from the risk function
		$risk_function_ranges_array = array();

		$line_num = 0;
		$aslSectionExtractIndex = 0;
		$aslStandardDeviation = 0;
		$aslScenarioIndex = 0;
		$raSectionExtractIndex = 0;
		$fdSectionExtractIndex = 0;
		$farpSectionExtractIndex = 0;
		while(!feof($handle))
		{
			$line = fgets($handle);

			// EXTRACTING THE RISK ASSESSMENT ANALSYSIS REPORT
			if(trim($line) == "-ANNUAL SOIL LOSS AVERAGES-")
				$aslSectionExtractIndex = $line_num;
			if(trim($line) == "-ANNUAL SOIL LOSS STANDARD DEVIATION-")
				$aslStandardDeviation = $line_num;
			if(trim($line) == "-RISK ASSESSMENT ANALYSIS-")
				$raSectionExtractIndex = $line_num;
			//if(trim($line) == "- FREQUENCY ANALYSIS -")
			//	$fdSectionExtractIndex = $line_num;
			if(trim($line) == "- FREQUENCY ANALYSIS RETURN PERIOD -")
				$farpSectionExtractIndex = $line_num;

			// EXTRACT THE ANNUAL SOIL LOSS AVERFAGES
			if($line_num > 2 and ($aslStandardDeviation == 0 or $line_num == $aslStandardDeviation) ) {
				$lineArr = preg_split('/\\) \\(|\\(|\\)/', substr($line,24), -1, PREG_SPLIT_NO_EMPTY);
				$asl_for_line = trim($lineArr[0]);

				if($asl_for_line != "" and $asl_for_line != "-ANNUAL SOIL LOSS STANDARD DEVIATION-"){
					array_push($asl_array, array(floatval($asl_for_line) * $conversionValue, null, null, $aslScenarioIndex + 1));
				}
				$aslScenarioIndex = $aslScenarioIndex + 1;				
			}

			// EXTRACTING THE RISK ASSESSMENT ANALSYSIS REPORT
			if($line_num >= ($raSectionExtractIndex + 7) and $raSectionExtractIndex != 0 and $line_num < ($raSectionExtractIndex + 11)){
				$ra_array_line = preg_split('/\s+/', trim(substr($line,27)));
				// push to the risk fuction ranges array

				array_push($risk_function_ranges_array, $ra_array_line[0]);

				// add the prorability of occurrance values for each scenario for the current risk function range
				$ra_prob_array_scenarios = array();
				for($i = 1; $i < count($ra_array_line); $i++){
					array_push($ra_prob_array_scenarios, $ra_array_line[$i]);
				}

				// push to the risk assessment array.  Will be used for the JavaScript array table
				array_push($ra_array,$ra_prob_array_scenarios);
			}

			// EXTRACTING THE FREQUENCY DISTRIBUTION DATA
			//if($line_num >= ($fdSectionExtractIndex + 3) and $fdSectionExtractIndex != 0 and $line_num < ($fdSectionExtractIndex + 12)){
			//	$frequency_distribution_array_line = preg_split('/\s+/', trim($line));
			//	array_push($fd_array, array(floatval($frequency_distribution_array_line[2]) * $conversionValue, null, null, $frequency_distribution_array_line[0], "color:#b87333,stroke-width:2;stroke-color:#FFFFFF"));
			//}

			// EXTRACTING THE FREQUENCY ANALYSIS RETURN PERIOD
			if($line_num >= ($farpSectionExtractIndex + 4) and $farpSectionExtractIndex != 0 and $line_num < ($farpSectionExtractIndex + 16)){
				$farp_array_line = preg_split('/\s+/', trim($line));

				/*if($unitsArray[0] == "english"){
					for ($j = 1;$j<count($farp_array_line);$j++){
						$farp_array_line[$j] = floatval($farp_array_line[$j]) * $conversionValue;
					}
				}*/

				// round all of the values to the nearest on hundreath place
				for ($j = 1;$j<count($farp_array_line);$j++){
					// change the units of the soil loss values based on the selected units for the scenarios 
					if($unitsArray[0] == "english"){
						$farp_array_line[$j] = floatval($farp_array_line[$j]) * $conversionValue;
					}
					else{
						$farp_array_line[$j] = floatval($farp_array_line[$j]);
					}
				}

				// push to the risk fuction ranges array
				array_push($farp_array, $farp_array_line);
			}

			$line_num = $line_num + 1;
		}
		fclose($handle);

		// ADD TO THE RISK ASSESSMENT ANALSYSIS ARRAY
		// reorder the array that will be used to create the array that will
		//$ra_js_array = array(array("Scenario", "Low","{type:'string', role:'annotation'}", "Medium","{type:'string', role:'annotation'}","Intermediate","{type:'string', role:'annotation'}","High", "{type:'string', role:'annotation'}"));
		$ra_js_array = array();

		for($i=0; $i < count($ra_array[0]); $i++){
			$scenario_column_label = 'Scenario '  . $i;
			if($i==0){
				$scenario_column_label = 'Baseline';
			}
			//array_push($ra_js_array, array( $scenario_column_label,  $ra_array[0][$i],$ra_array[0][$i], $ra_array[1][$i],$ra_array[1][$i], $ra_array[2][$i],$ra_array[2][$i], $ra_array[3][$i],$ra_array[3][$i] ));
			// round the probality values to the nearest 100th of a place before sending to the client
			array_push($ra_js_array, array( $scenario_column_label,  round($ra_array[0][$i],2),round($ra_array[0][$i],2) , round($ra_array[1][$i],2), round($ra_array[1][$i],2), round($ra_array[2][$i],2), round($ra_array[2][$i],2), round($ra_array[3][$i],2), round($ra_array[3][$i],2) ));
		}

		// add the risk function ranges to the soil loss probability of occurrance data table
		array_push($asl_array, array( floatval($risk_function_ranges_array[0]) * $conversionValue , '[.500]', '[.500]', null));
		array_push($asl_array, array( floatval($risk_function_ranges_array[1]) * $conversionValue , '[.800]', '[.800]', null));
		array_push($asl_array, array( floatval($risk_function_ranges_array[2]) * $conversionValue , '[0.950]', '[0.950]', null));

		// add the risk function ranges to the frequency distribution data table
		array_push($fd_array, array( floatval($risk_function_ranges_array[0]) * $conversionValue , '[.500]', '[.500]', null, null));
		array_push($fd_array, array( floatval($risk_function_ranges_array[2]) * $conversionValue , '[0.950]', '[0.950]', null, null));
		array_push($fd_array, array( floatval($risk_function_ranges_array[1]) * $conversionValue , '[.800]', '[.800]', null, null));

		//add to the risk assessment ranges array
		$MAX_SL_FROM_FREQUENCY_DIST = floatval($fd_array[8][0]);

		array_push($rar_array, array("","",floatval($risk_function_ranges_array[0]),"Low",floatval($risk_function_ranges_array[1]),"Medium",floatval($risk_function_ranges_array[2]),"High", floatval($MAX_SL_FROM_FREQUENCY_DIST),"Very High"));
		// calculating percentages in order to use in stacked bar chart (Google Chart)

		$rar_array_temp = array();
		array_push($rar_array_temp, array("","",floatval($risk_function_ranges_array[0]),"Low",floatval($risk_function_ranges_array[1]),"Medium",floatval($risk_function_ranges_array[2]),"High", floatval($MAX_SL_FROM_FREQUENCY_DIST),"Very High"));

		// CALCULATE PERCENTAGES BASED ON THE THRESHOLD VALUES IN THE RISK ASSESSMENT ANALYSIS PORTION OF THE OUTPUT FILE
		// 
		$low_r = floatval($risk_function_ranges_array[0]) * $conversionValue;
		$med_r = floatval($risk_function_ranges_array[1]) * $conversionValue;
		$high_r = floatval($risk_function_ranges_array[2]) * $conversionValue;

		// convert to the risk fuction ranges to proper units
		for ($j = 0;$j<count($risk_function_ranges_array);$j++){
			$risk_function_ranges_array[$j] = round(floatval($risk_function_ranges_array[$j]) * $conversionValue, 3);
		}

		$vhigh_r = $MAX_SL_FROM_FREQUENCY_DIST;
		$sum_r = $MAX_SL_FROM_FREQUENCY_DIST;

	    $low_r_percent = ($low_r/$sum_r)*100;
	    $med_r_percent = (($med_r - $low_r)/$sum_r)*100;
	    $high_r_percent = (($high_r - $med_r)/$sum_r)*100;
	    $vhigh_r_percent = (($vhigh_r - $high_r)/$sum_r)*100;

	    $rar_array_final = array();
		array_push($rar_array_final, array("","",$low_r_percent,"Low",$med_r_percent,"Medium",$high_r_percent,"High", $vhigh_r_percent,"Very High"));
		
		// PUSH ALL ARRAYS TO THE ARRAY THAT HOLD ALL RISK ASSESSMENT RESULTS
		array_push($ra_results_array, $asl_array);
		array_push($ra_results_array, $ra_js_array);
		array_push($ra_results_array, $fd_array);
		array_push($ra_results_array, $rar_array_final);
		array_push($ra_results_array, $farp_array);
		array_push($ra_results_array, $risk_function_ranges_array);
		//array_push($ra_results_array, $rar_array_temp);

		return $ra_results_array;
    }


	/**
	 * Prints the risk assessment report:
	 *                                    1) Scenarios input table
	 *                                    2) Risk assessment scenario chart
	 *                                    3) Frequency distribution chart
	 *                                    4) A simple bar chart showing the risk function ranges that will be used as a legend
	 *                                    5) Soil loss probabilities of occurrance chart
	 *
	 * @access	public
	 * @param   $scenarios - an array with a list of scenarios which will be used to create the scenarios table
	 * @param   $ra_results_array - an array with the data extracted from the risk assessment report
	 * @param   $baseline_scenario_id - the identifier for the baseline scenario (used to mark the baseline scenario in the scenario inputs table)
	 * @return	void
	 */
    function loadRiskAssessmentResults($scenarios, $ra_results_array, $unitsArray, $baseline_scenario_id)
    {
    	// get the fields in the order in which they will display in the report
    	$fields_in_order =  $baseline_scenario_id . "," . implode(",",$scenarios);

		$MAX_SL_FROM_FREQUENCY_DIST = floatval($ra_results_array[2][8][0]);

		// get all user scenarios
		$userScenariosRS = $this->Rhemmodel->get_user_scenarios_order_by_field($this->session->userdata('user_id'), "order_by_date", $fields_in_order);
		
		// only add the sccenarios select by the user
		foreach ($userScenariosRS as $row){
			if(in_array($row->scenario_id, $scenarios)){
				$userScenariosArray[$row->scenario_id] = $row->scenario_name;
			}
		}

		echo '<b><a target="_blank" href="'. base_url() . 'temp/ra_run_scenario_' . $this->session->userdata('user_id') . '.OUT" style="float:right;margin-top: 10px;"><i class="icon-download-alt" style="opacity:0.7"></i>Download Output File</a></b>';
	
		// tooltip about the CSV report
		//echo '<a class="help_lnk left" rel="popover" data-poload="help/getstring/csv_report" data-original-title="Image/CSV Report"></a>';
		// link to CSV report
		//echo '<span style="text-align:right;float:right;margin-right:10px;"><b><a href="' . base_url() . 'temp/' . "csv_report_" . $this->session->userdata('user_id') . ".csv" . '" target="_blank"><i class="icon-download-alt" style="opacity:0.7"></i> CSV</a></b></span>';
		// link to PDF report
		//echo '<span style="text-align:right;float:right;margin-right:10px;"><b>Download results as <a href="#" onclick="saveComparisonReportAsImage()"><i class="icon-download-alt" style="opacity:0.7"></i>PNG</a></b></span>';	

		// grab the yearly average array
		$yearlyAvgArray = $this->getYearlyAverages($userScenariosArray, $unitsArray);

		// Create the scenario comparison table
		echo $this->createScenarioComparisonTable($userScenariosArray,$unitsArray,true, $baseline_scenario_id);

		// include basic averages for baseline and alternatie scenarios
				// print the yearly average table
		echo $this->createScenarioAveragesTable($userScenariosArray, $yearlyAvgArray, $unitsArray, $baseline_scenario_id);

		///////////
		// organize the scenario names
		$scenarioNamesArray = array();
		foreach ($userScenariosArray as $scenario_id => $scenario_name){
			// is this the baseline scenario?
			if($scenario_id == $baseline_scenario_id){
				array_push($scenarioNamesArray, $scenario_name);
			}
			else{
				array_push($scenarioNamesArray, $scenario_name);
			}
		}
		///////////

		// PROBABILITY OF OCCURRENCE (TOTAL ANNUAL SOIL LOSS)
		echo $this->createProbabilityOfOccurrenceChart(json_encode($scenarioNamesArray), json_encode($ra_results_array[1], JSON_NUMERIC_CHECK));

		// PROBABILITY OF OCCURRENCE TABLE
		echo $this->createProbabilityOfOccurrenceTable($scenarioNamesArray,  $ra_results_array[1], $ra_results_array[5], $unitsArray[2]);
		//echo implode(",",$ra_results_array[5]);

		// FREQUENCY ANALYSIS RETURN PERIOD TABLE
		echo $this->createFrequencyAnalysisReturnPeriodTableBySoilLoss($scenarioNamesArray, $ra_results_array[4],$unitsArray);

		// FREQUENCY ANALYSIS RETURN PERIOD CHART 
		echo $this->createFrequencyAnalysisReturnPeriodsChart(json_encode($ra_results_array[4], JSON_NUMERIC_CHECK), $unitsArray[2]);

		// FREQUENCY ANALYSIS RETURN PERIOD DYNAMIC COMPONENT
		//echo $this->createFrequencyAnalysisReturnPeriodsTableByYear(json_encode($scenarioNamesArray), json_encode($ra_results_array[4], JSON_NUMERIC_CHECK), $unitsArray[2]);
		//echo json_encode($ra_results_array[4], JSON_NUMERIC_CHECK);

		// FREQUENCY AND SOIL LOSS ANALYSIS CHART
		//echo '<span class="toggleButton"><span class="toggleIconOn"/>FREQUENCY AND SOIL LOSS ANALYSIS</span>';
		//echo $this->createFrequencyDistributionChart(json_encode($ra_results_array[2], JSON_NUMERIC_CHECK), $MAX_SL_FROM_FREQUENCY_DIST, $unitsArray[2]);

		// SOIL LOSS PROBABILITY OF OCCURENCE CHART
		//echo '<span class="toggleButton"><span class="toggleIconOn"/>SOIL LOSS PROBABILITY OF OCCURENCE</span>';
		//echo $this->createSoilLossProbabilitiesOccurrenceScenariosChart(json_encode($ra_results_array[0], JSON_NUMERIC_CHECK), $MAX_SL_FROM_FREQUENCY_DIST, $unitsArray[2]);

		echo '<script>attachPopupEvents();activateResultsContainerTabs();</script>';
    }

	/**
	 * Prints a bar graph (using Google Charts) comparing return periods for each scenario. 
	 *
	 * @access	public
	 * @param   $yearlyAvgArray - an array of average output results
	 * @param   $unitsArray - an array of the units for the current scenario
	 * @return	a Google Chart object (script and html)
	 */ 
	function createProbabilityOfOccurrenceChart($scenarioNamesArray, $ra_js_array)
	{
		// colors v1 colors:["#A5B576","#D7C76F","#C4963E","#DA5228"],
		//$chart_container = '<img src="..assets/images/ra_categories.gif" style="position: absolute;top: 929px;left: 520px;height: 20px;z-index: 40;"/><div id="ra_chart_div" style="width: 650px; height: 300px;"></div>';
		$chart_container = '<span class="toggleButton"><span class="toggleIconOn"/>PROBABILITY OF OCCURRENCE (yearly soil losses)</span>';
		$chart_container = $chart_container . '<div style="margin-top:10px;margin-bottom:30px;"><img src="/rhem/assets/images/ra_categories.gif" style="height: 20px;margin-left: 290px;"/><div id="ra_chart_div" style="width: 650px; height: 350px;"></div></div>';
		//$chart_container = '<div id="ra_chart_div" style="width: 650px; height: 350px;"></div></div>';
							
		$ra_gchart = '<script>// draws the risk 
						var ra_js_array = ' . $ra_js_array . ';
						var scenarioNamesArray = ' . $scenarioNamesArray .';
						drawRiskAssessmentChart(ra_js_array, scenarioNamesArray);
					   </script>';

		
		//$helpLink = '<a class="help_lnk left wide" style="margin: 13px 5px 0px 0px;" rel="popover" data-poload="help/getstring/probability_of_occurrence" data-original-title="Probability of Occurrence"></a>';
		$helpLink = '<a class="help_lnk" style="width:90px;padding-left:20px;margin-top:20px;" href="#raModal" data-toggle="modal">Click for help</a>';
		return $helpLink . $chart_container . $ra_gchart;
	}


	/**
	 * Prints a bar graph (using Google Charts) comparing return periods for each scenario. 
	 *
	 * @access	public
	 * @param   $yearlyAvgArray - an array of average output results
	 * @param   $unitsArray - an array of the units for the current scenario
	 * @return	a Google Chart object (script and html)
	 */
	function createFrequencyDistributionChart($fd_js_array, $MAX_SL_FROM_FREQUENCY_DIST,$units)
	{	
		$chart_container = '<div id="fd_chart_div" style="width: 650px; height: 300px;"></div>';

		// FREQUENCY DISTRIBUTION CHART
		$fd_gchart = '<script>
			function drawFrequencyDistributionChart() {
			  var data = new google.visualization.DataTable();
	          data.addColumn("number","Interval");
	          data.addColumn({type:"string", role:"annotation"});
	          data.addColumn({type:"string", role: "annotationText"});
	          data.addColumn("number","Frequency");
	          data.addColumn({type:"string", role:"style"});
	          data.addRows('. $fd_js_array . ');

			  var options = {
				title: "Frequency Analysis",
			    hAxis: {
			    	title: "Avg. Annual Soil Loss (' .  $units . '/year)", 
			    	titleTextStyle: {color: "black"},
			    	minValue: 0, 
			    	maxValue: ' . $MAX_SL_FROM_FREQUENCY_DIST . ',
			    	gridlines:{color:"transparent"},
			   		viewWindowMode:"explicit",
				    viewWindow: {
				        max:' . $MAX_SL_FROM_FREQUENCY_DIST . ',
				        min:0
				    }
			    },
			    vAxis: {title: "Frequency Distribution", titleTextStyle: {color: "black"}},
			    bar: {groupWidth: "100%"},
			    colors:["#C4C4C4"],
				annotations: { style:"line" },
				fontSize: 12,
			    width:650,
			    height:300,
			    chartArea:{left:80,top:40,width:"650",height:"230",backgroundColor:{"stroke":"#B1B1B1","strokeWidth": 1}},
			    bar: {groupWidth: "100%"}
			  };

			  var fd_chart = new google.visualization.ColumnChart(document.getElementById("fd_chart_div"));
			  fd_chart.draw(data, options);
			}
			drawFrequencyDistributionChart();
			</script>';

		return $chart_container . $fd_gchart ;
	}


	/**
	 * Prints a bar graph (using Google Charts) comparing return periods for each scenario. 
	 *
	 * @access	public
	 * @param   $yearlyAvgArray - an array of average output results
	 * @param   $unitsArray - an array of the units for the current scenario
	 * @return	a Google Chart object (script and html)
	 */
	function createSoilLossProbabilitiesOccurrenceScenariosChart($sl_js_array, $MAX_SL_FROM_FREQUENCY_DIST, $units)
	{	

		$chart_container = '<div id="slpo_chart_div" style="width: 650px; height: 300px;"></div>';
							
		$slpo_gchart = '<script>// draws the risk 
						function drawSoilLossProbabilitiesOccurrenceChart() {
						   var data = new google.visualization.DataTable();
						   data.addColumn("number","SL_Value");
						   data.addColumn({type:"string", role:"annotation"});
						   data.addColumn({type: "string", role: "annotationText"});
						   data.addColumn("number","Scenario");
						   data.addRows(' . $sl_js_array . ');
						   // create an array for the vAxis ticks
						   sl_js_array = ' . $sl_js_array . ';
						   vAxisMaxValue = eval(sl_js_array.length - 3 + 1);
						   ticksArray = "[";
						   for(s = 0; s < sl_js_array.length - 3; s++){
						   		if(s == 0){
						   			ticksArray = ticksArray + "{v:" + eval(s + 1) + ",f:\"Baseline\"}";
						   		}
						   		else{
						   			ticksArray = ticksArray + "{v:" + eval(s + 1) + ",f:\"Scenario " + s + "\"}";
						   		}
						   		if(s < sl_js_array.length - 4){
						   			ticksArray = ticksArray + ","; // "{v:" + eval(s + 1) + ", f:\"Scenario " + s + "\"}";
						   		}
						   }
						   ticksArray = ticksArray + "]";

						   //console.log(ticksArray);
						   //var data = google.visualization.arrayToDataTable(sl_js_array);

						    var options = {
						      title: "Soil Loss Analysis",
						      hAxis: { 
						      		title: "Average Annual Soil Loss (' . $units . '/year)",
						            minValue: 0, 
						            maxValue: ' . $MAX_SL_FROM_FREQUENCY_DIST . ',
						            gridlines:{color:"transparent"},
						            viewWindowMode:"explicit",
								    viewWindow: {
								        max:' . $MAX_SL_FROM_FREQUENCY_DIST . ',
								        min:0
								    }
						      },
						      vAxis: { 
						               title: "", 
						               minValue: 0, 
						               maxValue: vAxisMaxValue,
						               ticks: eval(ticksArray)
						             },
						      legend: "none",
						      interpolateNulls: true,
						      width:650,
						      height:300,
						      colors:["#C4C4C4"],
						      fontSize: 12,
						      lineWidth: 0,
      						  pointSize: 10,
      						  annotations: {
						      	style: "line"
						      },
						      chartArea:{left:80,top:40,width:"650",height:"230",backgroundColor:{"stroke":"#B1B1B1","strokeWidth": 1}}
						    };

						    var chart = new google.visualization.LineChart(document.getElementById("slpo_chart_div"));
						    chart.draw(data, options);
						}
						drawSoilLossProbabilitiesOccurrenceChart();
						</script>';

		return $chart_container . $slpo_gchart ;
	}

	/**
	 * Prints a bar graph (using Google Charts) defining the risk function ranges/categories
	 *
	 * @access	public
	 * @param   $yearlyAvgArray - an array of average output results
	 * @param   $unitsArray - an array of the units for the current scenario
	 * @return	a Google Chart object (script and html)
	 */
	function createRiskFunctionRangesChart($rar_js_array, $MAX_SL_FROM_FREQUENCY_DIST)
	{
		$chart_container = '<div id="ranges_chart_div" style="width: 600px; height: 100px;"></div>';

		$rf_ranges_gchart = '<script>
							// RISK ASSESSMENT CHART
							function drawRangesChart() {
							    var data = new google.visualization.DataTable();
							                   data.addColumn("string","Range");
							                   data.addColumn({type:"string", role:"annotation"});
							                   data.addColumn("number","Low");
							                   data.addColumn({type:"string",role:"annotation"});
							                   data.addColumn("number","Medium");
							                   data.addColumn({type:"string",role:"annotation"});
							                   data.addColumn("number","High");
							                   data.addColumn({type:"string",role:"annotation"});
							                   data.addColumn("number","Very High");
							                   data.addColumn({type:"string",role:"annotation"});
							  data.addRows(' . $rar_js_array . ');

							  var options = {
							    vAxis: {titleTextStyle: {color: "black"}},
							    isStacked:true,
							    bar: {groupWidth: "100%"},
							    width:650,
							    height:100,
							    chartArea:{left:80,top:5,width:"650",height:"220"},
							    colors:["#99CC00","#FFFC66","#FFCC00","#DC1F00"],
							    legend:{position:"bottom", alignment:"center"},
							    annotations: { textStyle: { fontSize:16, bold: true,color:"#1A1A1A"}}
							  };

							  var ra_chart = new google.visualization.BarChart(document.getElementById("ranges_chart_div"));
							  ra_chart.draw(data, options);
							}
							drawRangesChart();
							</script>';

		return $chart_container . $rf_ranges_gchart;
	}


/**
	 * Creates a table with return periods calculated based on the baseline scenario.  The Everpolate JavaScrip library is used in order to
	 * incorporate the numerical interpolation for each of the return preiods for all selected alternative scenarios. 
	 *
	 * @access	public
	 * @return	a dynamic component to recalculate the return periods
	 */
	function createFrequencyAnalysisReturnPeriodsTableByYear($scenarioNamesArray, $ra_js_array, $units)
	{	

		$calculator_container = '<span class="toggleButton"><span class="toggleIconOn"/>FREQUENCY ANALYSIS RETURN PERIOD TABLE (' . $units . ', years)</span>';
		$calculator_container = $calculator_container . '<div id="rp_interpolated_table_div"></div><span style="color:#8B8B8B;font-size:12px;margin-left: 275px;">Note: A value of 1 in the above table represents a recurrence interval of 1 year or less.</span>';
							
		$calculator_code = '<script>
						// results array definition
						var outputRAArray = ' . $ra_js_array . ';
						var scenarioNames = ' . $scenarioNamesArray . ';
						
						outputRAArray = transposeArray(outputRAArray); 

						// when the document is ready, execute the funciton to calculate all return periods					
						$(function(){calculateAllReturnPeriods(outputRAArray, scenarioNames);}); 
						</script>';

		$helpLink = '<a class="help_lnk left" style="margin: 13px 5px 0px 0px;" rel="popover" data-poload="help/getstring/frequency_analysis_rp_table_1" data-original-title="Frequency Analysis Return Period Table"></a>';
		return $helpLink . $calculator_container . $calculator_code;
	}


	/**
	 * Prints a line chart (using Google Charts) showing the various frequency analysis return periods for soil loss
	 *
	 * @access	public
	 * @param   $sl_js_array - an array of the scenarios
	 * @param   $units - a string with the current units
	 * @return	a Google Chart object (script and html)
	 */
	function createFrequencyAnalysisReturnPeriodsChart($sl_js_array, $units)
	{	
		$chart_container = '<span class="toggleButton"><span class="toggleIconOn"/>FREQUENCY ANALYSIS RETURN PERIOD CHART (yearly soil losses)</span>';

		$chart_container = $chart_container . '<div id="farp_chart_div" style="width: 750px; height: 370px;"></div>';
						
		$slpo_gchart = '<script>// draws the risk 
						var scenarios_array = ' . $sl_js_array . ';
						var units = "' . $units .'";
						
						drawFrequencyAnalysisReturnPeriodsChart(scenarios_array, units);
						</script>';

		$helpLink = '<a class="help_lnk left" style="margin: 13px 5px 0px 0px;" rel="popover" data-poload="help/getstring/frequency_analysis_rp_chart" data-original-title="Frequency Analysis Return Period Chart"></a>';
		return $helpLink . $chart_container . $slpo_gchart ;
	}


	/**
	 * Builds a table with up to 5 scenarios selected by the user to compare.
	 *
	 * @access	public
	 * @param   $scenarioid - The name of the current scenario
	 * @param   $units      - an array of units (based on wheather the user selected metric or english units
	 * @return	void
	 */
	function createScenarioComparisonTable($userScenariosArray, $unitsArray, $buildCSVFlag, $BASELINE_SCENARIO)
	{
		// array to hold input parameters from the interface for each scenario selected
		// removed version on 4/1/2014. To add it back add ,array('Version') to the $inputParamsArray and push the $versionNumber to the table
		$inputParamsArray = array(array(''), array('Version'), array('State ID'),array('Climate Station'),array('Soil Texture'),array('Soil Water Saturation %'),array('Slope Length ' . '(' . $unitsArray[3] . ')'),
					  array('Slope Shape'),array('Slope Steepness %'),array('Bunch Grass Foliar Cover %'),array('Forbs and/or Annual Grasses Foliar Cover %'),array('Shrubs Foliar Cover %'),array('Sod Grass Foliar Cover %'),array('Total Foliar Cover %'),array('Basal Cover %'),array('Rock Cover %'),array('Litter Cover %'), array('Biological Crusts Cover %'), array('Total Ground Cover %'));
		$headersArray = array('');
	
		$i = 0;
		$alternativeScenarioIndex = 1;
		foreach ($userScenariosArray as $scenario_id => $scenario_name)
		{
		    // get the user scenario inputs for the current scenario
			$inputs_row = $this->Rhemmodel->get_user_scenario_inputs($scenario_id);


			$scenarioTextColor = 'color:#8F8F8F;';
			// is this the baseline scenario?
			if($scenario_id == $BASELINE_SCENARIO){
				$scenario_name = "<br/><br/><b>BASELINE SCENARIO</b> <br/><br/>" . $scenario_name;
				$scenarioTextColor = 'color:#1A1A1A';
			}
			else{
				$scenario_name = "<br/><br/><b>Scenario " . $alternativeScenarioIndex . "</b><br/><br/>" . $scenario_name;
				$alternativeScenarioIndex = $alternativeScenarioIndex + 1;
			}

			// input parameters 
			// set all scenario name colors to gray
			array_push($inputParamsArray[0], '<span style="' . $scenarioTextColor . '">' . $scenario_name . '</span>');
			
			// print the version number.
			$versionNumber = RHEM_VERSION; 
			//array_push($inputParamsArray[1], $versionNumber);
			
			array_push($inputParamsArray[1], $inputs_row->version);
			array_push($inputParamsArray[2], strtoupper($inputs_row->state_id));
			array_push($inputParamsArray[3], ucwords(strtolower($inputs_row->station)));
			array_push($inputParamsArray[4], $inputs_row->class_name);
			array_push($inputParamsArray[5], $inputs_row->soil_moisture);
			array_push($inputParamsArray[6], $inputs_row->slope_length);
			array_push($inputParamsArray[7], $inputs_row->shape_name);
			array_push($inputParamsArray[8], $inputs_row->slope_steepness);
			array_push($inputParamsArray[9], $inputs_row->bunchgrass_canopy_cover);
			array_push($inputParamsArray[10], $inputs_row->forbs_canopy_cover);
			array_push($inputParamsArray[11], $inputs_row->shrubs_canopy_cover);
			array_push($inputParamsArray[12], $inputs_row->sodgrass_canopy_cover);

			$canopycover = $inputs_row->bunchgrass_canopy_cover + $inputs_row->forbs_canopy_cover + $inputs_row->shrubs_canopy_cover + $inputs_row->sodgrass_canopy_cover;
			array_push($inputParamsArray[13], $canopycover);

			array_push($inputParamsArray[14], $inputs_row->basal_cover);
			array_push($inputParamsArray[15], $inputs_row->rock_cover);
			array_push($inputParamsArray[16], $inputs_row->litter_cover);
			array_push($inputParamsArray[17], $inputs_row->cryptogams_cover);
			$groundcover = $inputs_row->basal_cover + $inputs_row->litter_cover + $inputs_row->cryptogams_cover + $inputs_row->rock_cover;
			array_push($inputParamsArray[18], $groundcover);
			$i++;
		}

		// create a template for the table
		$tmpl = array ('table_open' => '<table border="0" cellpadding="4" cellspacing="0" class="s_comp_table">', 'heading_cell_start' => '<th width="16%">' ,  'row_alt_start' => '<tr class="odd">');
		$this->table->set_template($tmpl); 
		
		// add all the rows to the table
		for($i =0; $i<count($inputParamsArray); $i++){
			if($i == 0)
				$this->table->set_heading($inputParamsArray[$i]);
			else
				$this->table->add_row($inputParamsArray[$i]);
		}
		
		// create the table
		$tableHeaderTitle = '<span class="toggleButton"><span class="toggleIconOn"/>SCENARIO INPUTS</span>';
		$htmlTable = '<div class="divBox">' . $this->table->generate() . '</div>';
		$this->table->clear();
		
		//if($buildCSVFlag){
			// create a new CVS file and add the input parameters for the selected scenarios
		//	$this->createCSVReport($inputParamsArray);
		//}
		return $tableHeaderTitle . $htmlTable . '<script>styleInputParameterTable()</script>';
	}


	/**
	 * Builds a table with the average yearly values.
	 *
	 * @access	public
	 * @param   $userScenariosArray - an array of the scenarios selected by the user to compare
	 * @param   $unitsArray - an array of units (based on wheather the user selected metric or english units
	 * @return	An HTML string with the a header and table section.
	 */
	function createProbabilityOfOccurrenceTable($scenarioNamesArray, $po_table, $ranges_array, $units)
	{
		$headersArray = array('Soil Loss Severity Class <br/> <br/> Range of Annual Soil Loss (' . $units . ')');
		$lowValuesArray = array('<div style="width:30px;height:30px;background-color:#99CC00;float:left;margin-right: 10px;"></div> <b>Low</b> <br/> <span> X < ' . $ranges_array[0] . '</span>');
		$mediumValuesArray = array('<div style="width:30px;height:30px;background-color:#FFFC66;float:left;margin-right: 10px;"></div> <b>Medium</b> <br/> <span> ' . $ranges_array[0] . ' <= X < ' . $ranges_array[1] . '</span>');
		$highValuesArray = array('<div style="width:30px;height:30px;background-color:#FFCC00;float:left;margin-right: 10px;"></div> <b>High</b> <br/> <span> ' . $ranges_array[1] . ' <= X < ' . $ranges_array[2] . '</span>');
		$veryValuesArray = array('<div style="width:30px;height:30px;background-color:#DC1F00;float:left;margin-right: 10px;"></div> <b>Very High</b> <br/> <span> X >= ' . $ranges_array[2] . '</span>');

		$tableHeaderTitle =  '<span class="toggleButton"><span class="toggleIconOn"/>PROBABILITY OF OCCURRENCE TABLE (yearly soil losses)</span>';
		
		$i = 0;
		$alternativeScenarioIndex = 1;


		foreach ($po_table as $scenario_array){
			$scenario_name = $scenario_array[0];

			$scenarioTextColor = 'color:#8F8F8F;';
			// is this the baseline scenario?
			if($scenario_name == "Baseline"){
				$scenario_name = "<br/><br/><b>BASELINE SCENARIO</b><br/><br/><b>" . $scenarioNamesArray[0] . "</b>"; //" <br/><br/>" . $scenario_name;
				$scenarioTextColor = 'color:#1A1A1A';
			}
			else{
				$scenario_name = "<br/><br/><b>Scenario " . $alternativeScenarioIndex . "</b><br/><br/><b>" . $scenarioNamesArray[$alternativeScenarioIndex] . "</b>"; 
				$alternativeScenarioIndex = $alternativeScenarioIndex + 1;
			}

			array_push($headersArray, '<span style="' . $scenarioTextColor . ';">' . $scenario_name . '</span>');
			array_push($lowValuesArray, $scenario_array[1]);
			array_push($mediumValuesArray, $scenario_array[3]);
			array_push($highValuesArray, $scenario_array[5]);
			array_push($veryValuesArray, $scenario_array[7]);
			$i++;
		}
		
		$this->table->set_heading($headersArray);
		$this->table->add_row($lowValuesArray);
		$this->table->add_row($mediumValuesArray);
		$this->table->add_row($highValuesArray);
		$this->table->add_row($veryValuesArray);
	
		
		$htmlTable = $this->table->generate();
		$this->table->clear();
		
		//$ra_image = '<div style="position: absolute;margin-left:-120px;margin-top:50px;"><img src="../rhem_test/assets/images/risk_assessment_probability_brackets.gif"/></div>';
		return $tableHeaderTitle . '<div class="divBox">' . $htmlTable .  '</div>';
	}


	/**
	 * Builds a table with the average yearly values.
	 *
	 * @access	public
	 * @param   $userScenariosArray - an array of the scenarios selected by the user to compare
	 * @param   $unitsArray - an array of units (based on wheather the user selected metric or english units
	 * @return	An HTML string with the a header and table section.
	 */
	function createFrequencyAnalysisReturnPeriodTableBySoilLoss($scenarioNamesArray, $po_table, $units)
	{
		$headersArray = array('<br/><b>RETURN PERIOD</b>');
		$tableHeaderTitle =  '<span class="toggleButton"><span class="toggleIconOn"/>FREQUENCY ANALYSIS RETURN PERIOD TABLE (years, ' . $units[2] . ')</span>';
		
		$i = 0;
		$alternativeScenarioIndex = 0;
		foreach ($po_table as $scenario_array){

			// set the table headers
			if($i == 0){
				for($i =0; $i<count($scenario_array) - 1; $i++){
					$scenarioTextColor = 'color:#8F8F8F;';
					// is this the baseline scenario?
					if($alternativeScenarioIndex == 0){
						$scenario_name = "<br/><br/><b>BASELINE SCENARIO</b><br/><br/><b>". $scenarioNamesArray[0] . "</b>";
						$scenarioTextColor = 'color:#1A1A1A';
					}
					else{
						$scenario_name = "<br/><br/><b>Scenario " . $alternativeScenarioIndex . "<br/><br/><b>". $scenarioNamesArray[$alternativeScenarioIndex] . "</b>";
					}
					$alternativeScenarioIndex = $alternativeScenarioIndex + 1;

					array_push($headersArray, '<span style="' . $scenarioTextColor . ';">' . $scenario_name . '</span>');
				}
			}

			// modify the way the rreturn period column is shown in the table
			$array_replace_rp = array(0 => ( "<span style='text-align:center;font-weight:bold;'>" . intval($scenario_array[0]) . "</span>"));

			//echo $scenario_array[0] . '     ' . $scenario_array[1]. '     ' . $scenario_array[2]. '     ' . $scenario_array[3] . '<br/>';

			// change to soil loss to 3 decimal places for english units
			if($units[0] == "english"){
				for($i = 1; $i < count($scenario_array); $i++){
					$scenario_array[$i] = round($scenario_array[$i], 3);
				}
			}

			$this->table->add_row(array_replace($scenario_array, $array_replace_rp));
			$i++;
		}

		// add each row to the table
		$this->table->set_heading($headersArray);
		
		$htmlTable = $this->table->generate();
		$this->table->clear();
		
		$helpLink = '<a class="help_lnk left" style="margin: 13px 5px 0px 0px;" rel="popover" data-poload="help/getstring/frequency_analysis_rp_table_2" data-original-title="Frequency Analysis Return Period Table"></a>';
		return $helpLink . $tableHeaderTitle . '<div class="divBox">' . $htmlTable .  '</div>';
	}


	/**
	 * Get the yearly avearage values of a particular variable. This will enable the application to reuse the query.
	 *
	 * @access	public
	 * @param   $userScenariosArray - an array of the scenarios selected by the user to compare
	 * @param   $unitsArray - an array of units (based on wheather the user selected metric or english units
	 * @return	An array of scenario ids with associated variable yearly average values.
	 */
	function getYearlyAverages($userScenariosArray, $unitsArray)
	{
		foreach ($userScenariosArray as $scenario_id => $scenario_name)
		{
			$yearlyAvgArray[$scenario_id] = $this->Rhemmodel->get_variable_annual_avg($scenario_id,$unitsArray);
		}
		return $yearlyAvgArray;
	}

	/**
	 * Builds a table with the average yearly values.
	 *
	 * @access	public
	 * @param   $userScenariosArray - an array of the scenarios selected by the user to compare
	 * @param   $unitsArray - an array of units (based on wheather the user selected metric or english units
	 * @return	An HTML string with the a header and table section.
	 */
	function createScenarioAveragesTable($userScenariosArray, $yearlyAvgArray, $unitsArray, $BASELINE_SCENARIO)
	{
		$headersArray = array('');
		$resultsForCSV = array();
		$precipValuesArray = array('Avg. Precipitation (' . $unitsArray[1] . '/year)');
		$runoffValuesArray = array('Avg. Runoff (' . $unitsArray[1] . '/year)');
		$sedYieldValuesArray = array('Avg. Sediment Yield (' . $unitsArray[2] . '/year)');
		$soilLossValuesArray = array('Avg. Soil Loss (' . $unitsArray[2] . '/year)');
		
		// add this a header for the current return period to the CSV results array
		array_push($resultsForCSV,array('AVERAGE ANNUAL RESULTS'));

		$tableHeaderTitle =  '<span class="toggleButton"><span class="toggleIconOn"/>ANNUAL AVERAGES</span>';
		
		$i = 0;
		$alternativeScenarioIndex = 1;

		foreach ($userScenariosArray as $scenario_id => $scenario_name){

			$scenarioTextColor = 'color:#8F8F8F;';
			// is this the baseline scenario?
			if($scenario_id == $BASELINE_SCENARIO){
				$scenario_name = "<br/><br/><b>BASELINE SCENARIO</b> <br/><br/>" . $scenario_name;
				$scenarioTextColor = 'color:#1A1A1A';
			}
			else{
				$scenario_name = "<br/><br/><b>Scenario " . $alternativeScenarioIndex . "</b><br/><br/>" . $scenario_name;
				$alternativeScenarioIndex = $alternativeScenarioIndex + 1;
			}

			array_push($headersArray, '<span style="' . $scenarioTextColor . ';">' . $scenario_name . '</span>');
			array_push($precipValuesArray, number_format($yearlyAvgArray[$scenario_id]['rainYrlyAvg'],3));
			array_push($runoffValuesArray, number_format($yearlyAvgArray[$scenario_id]['runoffYrlyAvg'],3));
			array_push($sedYieldValuesArray, number_format($yearlyAvgArray[$scenario_id]['sedYieldYrlyAvg'],3));
			array_push($soilLossValuesArray, number_format($yearlyAvgArray[$scenario_id]['soilLossYrlyAvg'],3));
			$i++;
		}
		
		$this->table->set_heading($headersArray);
		$this->table->add_row($precipValuesArray);
		$this->table->add_row($runoffValuesArray);
		$this->table->add_row($sedYieldValuesArray);
		$this->table->add_row($soilLossValuesArray);
		
		// add to table that will be added to the csv file
		array_push($resultsForCSV,$precipValuesArray);
		array_push($resultsForCSV,$runoffValuesArray);
		array_push($resultsForCSV,$sedYieldValuesArray);
		array_push($resultsForCSV,$soilLossValuesArray);
		
		// add results to the csv report
		//$this->addToCSVReport($resultsForCSV);
		
		$htmlTable = $this->table->generate();
		$this->table->clear();
		
		return $tableHeaderTitle . '<div class="divBox">' . $htmlTable . '</div>';
	}

	/**
	 * Builds and returns an array of unit labels based on the units saved for a particular scenario.
	 * The first item in the array will contain the scenario units: metric or engish.
	 *
	 * @access	public
	 * @param   $scenarioid - The Name of the current scenario
	 * @return	an array of unit labels
	 */
	function getUnitsArray($scenarioid) 
	{
		$unitsRS = $this->Rhemmodel->getScenarioUnits($scenarioid);
		$scenarioUnits = $unitsRS->units;
		
		$unitsArray = array($scenarioUnits,'mm','Mg/ha','meters','mm/hr');
		
		if($scenarioUnits == 'english'){
			$unitsArray = array($scenarioUnits,'inches','ton/ac','feet','inches/hr');
		}
		return $unitsArray;
	}
	
}
?>