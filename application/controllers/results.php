<?php
/*
 * This controller is used the parse the RHEM ouput and send it back to the view 
 * to be displayed to the user. 
 *
 * @access	public
 * @return	void
 */
class Results extends CI_Controller {
	
	private $SCENARIOS_COLOR_ARRAY = array('#46A23C','#BFA11B','#404040','#73AEC9','#A13F3D');

	/* This defines a constructor for the current controller */
	function __construct()
    {
		parent::__construct();
		//load libraries
		$this->load->library('session');
		$this->load->library('table');
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
	 * Get the return periods for a list of scenarios by id. 
	 *
	 * @access	public
	 * @param   $userScenariosArray - an array of the scenarios selected by the user to compare
	 * @param   $unitsArray - an array of units (based on wheather the user selected metric or english units
	 * @return	An array of scenario ids with associated return period data.
	 */
	function getReturnPeriods($userScenariosArray, $unitsArray)
	{
		// get the return period variable data for each scenario
		foreach ( $userScenariosArray as $scenario_id=>$scenario_name ){
			$scenarioReturnPeriods[$scenario_id] = $this->Rhemmodel->get_variables_return_periods($scenario_id,$unitsArray);
		}
		return $scenarioReturnPeriods;
	}


	/**
	 * Get the risk assessment values for a list of scenarios.
	 *
	 * @access	public
	 * @param   $userScenariosArray - an array of the scenarios selected by the user to compare
	 * @param   $unitsArray - an array of units (based on wheather the user selected metric or english units
	 * @return	An array of scenario ids with associated variable yearly average values.
	 */
	function getRiskAssessmentValues($userScenariosArray, $unitsArray)
	{
		foreach ($userScenariosArray as $scenario_id => $scenario_name)
		{
			$riskAssessmentArray[$scenario_id] = $this->Rhemmodel->get_risk_assessment_values($scenario_id);
		}
		return $riskAssessmentArray;
	}
	

	/**
	 * Builds the scenario table with the scenario inputs and all the output results for a single scenario based on the current sessionid
	 *
	 * @access	public
	 * @param   $scenarioid - The scenarioid of the current scenario
	 * @return	void
	 */
	function printSingleScenarioResultsTable()
	{
		$scenarioid = $this->session->userdata('scenarioid');

		$this->printSingleScenarioResultsTableById($scenarioid);
	}

	/**
	 * Builds the scenario table with the scenario inputs and all the output results for a single scenario.
	 *
	 * @access	public
	 * @param   $scenarioname - The Name of the current scenario
	 * @return	void
	 */
	function printSingleScenarioResultsTableById($scenarioid)
	{
		// first, get the units for the current scenario. these will be used in the
		// output table, charts, and output saved to the DB.
		$unitsArray = $this->getUnitsArray($scenarioid);

		// tooltip about the CSV report
		echo '<a class="help_lnk left" rel="popover" data-poload="help/getstring/csv_report" data-original-title="CSV Report"></a>';		
		// link to CSV report
		echo '<span style="text-align:right;float:right;margin-right:10px;"><b><a href="' . base_url() . 'temp/' . "csv_report_" . $this->session->userdata('user_id') . ".csv" . '" target="_blank"><i class="icon-download-alt" style="opacity:0.7"></i>Download results as CSV</a></b></span>';	
	
		// get all scenario outputs (scenarioname, averages, risk assessment results,and return periods)
		$allScenarioOutputs = $this->Rhemmodel->get_all_scenario_outputs($scenarioid,$unitsArray);

		// creates an array with a key as the scenarioid and the value as the scenario name
		$scenarioname = $allScenarioOutputs['scenario_name'];
		$userScenariosArray[$scenarioid] = $scenarioname;

		echo $this->createScenarioComparisonTable($userScenariosArray,$unitsArray,true);
		
		// creates the averages table
		echo $this->createScenarioAveragesTableSingle($allScenarioOutputs,$scenarioid, $scenarioname, $unitsArray);
		
		// creates the return period table
		echo $this->createScenarioReturnPeriodTableSingle($allScenarioOutputs, $unitsArray);

		// creates the return period table (with yearly totals)
		echo $this->createScenarioReturnPeriodYearlyTotalsTableSingle($allScenarioOutputs, $unitsArray);
		
		// print this script to register events for new help icons
		echo '<script> attachPopupEvents();activateResultsContainerTabs();</script>';
	}

	/**
	 * Builds a table with the average yearly values for a single scenario
	 *
	 * @access	public
	 * @param   $userScenariosArray - an array of the scenarios selected by the user to compare
	 * @param   $unitsArray - an array of units (based on wheather the user selected metric or english units
	 * @return	An HTML string with the a header and table section.
	 */
	function createScenarioAveragesTableSingle($allScenarioOutputs,$scenarioid, $scenarioname, $unitsArray)
	{
		$headersArray = array('','<span style="color:' . $this->SCENARIOS_COLOR_ARRAY[0] . ';">' . $scenarioname . '</span>');
		$resultsForCSV = array();
		$precipValuesArray = array('Avg. Precipitation (' . $unitsArray[1] . '/year)', number_format($allScenarioOutputs['rainYrlyAvg'],3));
		$runoffValuesArray = array('Avg. Runoff (' . $unitsArray[1] . '/year)', number_format($allScenarioOutputs['runoffYrlyAvg'],3));
		$sedYieldValuesArray = array('Avg. Sediment Yield (' . $unitsArray[2] . '/year)', number_format($allScenarioOutputs['sedYieldYrlyAvg'],3));
		$soilLossValuesArray = array('Avg. Soil Loss (' . $unitsArray[2] . '/year)', number_format($allScenarioOutputs['soilLossYrlyAvg'],3));

        // get the scenario inputs in order to pring Salt Load if it's a saline scenario
        $inputs_row = $this->Rhemmodel->get_user_scenario_inputs($scenarioid);
        if(isset($inputs_row->sar)){
            // TDS (g) = 2.36 * [Soil Loss (Kg)] + 0.99 - Equation from Kossi
            if($unitsArray[1] == 'inches'){
                // convert soil loss back to metric > to kg > after calculating initial TDS divide by 1000 to get from g to kg
                $TDS = (2.36 * ( ( $allScenarioOutputs['soilLossYrlyAvg'] / 0.446 ) * 1000 ) + 0.99)/1000;
                // convert to lbs (* 2.20462) and to acres (/2.471)
                $TDS = ($TDS * 2.20462)/2.471;
            }
            else{
                // report in kg
                $TDS = (2.36 * ( $allScenarioOutputs['soilLossYrlyAvg'] * 1000 ) + 0.99)/1000;
            }
            $saltloadValuesArray = array('Salt Load (' . $unitsArray[5] . ')', number_format($TDS,3));
        }

		// add this a header for the current return period to the CSV results array
		array_push($resultsForCSV,array('AVERAGE ANNUAL RESULTS'));

		$tableHeaderTitle =  '<span class="toggleButton"><span class="toggleIconOn"/>ANNUAL AVERAGES</span>';
		
		$this->table->set_heading($headersArray);
		$this->table->add_row($precipValuesArray);
		$this->table->add_row($runoffValuesArray);
		$this->table->add_row($sedYieldValuesArray);
        $this->table->add_row($soilLossValuesArray);
        if(isset($inputs_row->sar)){
            $this->table->add_row($saltloadValuesArray);
        }
		
		// add to table that will be added to the csv file
		array_push($resultsForCSV,$precipValuesArray);
		array_push($resultsForCSV,$runoffValuesArray);
		array_push($resultsForCSV,$sedYieldValuesArray);
        array_push($resultsForCSV,$soilLossValuesArray);
        if(isset($inputs_row->sar)){
            array_push($resultsForCSV,$saltloadValuesArray);
        }
        
        
		// add results to the csv report
		$this->addToCSVReport($resultsForCSV);
		
		$htmlTable = $this->table->generate();
		$this->table->clear();
		
		return $tableHeaderTitle . '<div class="divBox">' . $htmlTable . '</div>';
	}

	/**
	 * Builds a table of return periods for a single scenario. 
	 *
	 * @access	public
	 * @param   $allScenarioOutputs - an array of the outputs for the currently selected scenario
	 * @param   $unitsArray - an array of units (based on wheather the user selected metric or english units
	 * @return	void
	 */
	function createScenarioReturnPeriodTableSingle($allScenarioOutputs, $unitsArray)
	{
		$headersArray = array('Variable','2 yr','5 yr', '10 yr','25 yr','50 yr','100 yr');
		$resultsForCSV = array();
		
		// add this a header for the current return period to the CSV results array
		array_push($resultsForCSV,array('RETURN FREQUENCY RESULTS FOR YEARLY MAXIMUM DAILY'));
		array_push($resultsForCSV,$headersArray);
		
		// the tooltip
		$tooltip = '<a class="help_lnk left" style="margin: 15px 10px 0px 0px;" rel="popover" data-poload="help/getstring/return_frequency_yearly_maximum_daily" data-original-title="Return Frequency for Yearly Maximum Daily"></a>';
		
		$tableHeaderTitle =  '<span class="toggleButton"><span class="toggleIconOn"/>RETURN FREQUENCY RESULTS FOR YEARLY MAXIMUM DAILY</span>';
		$this->table->set_heading($headersArray);
		// array rows for return frequency table
		$rain = array('Rain (' . $unitsArray[1] . ')',number_format($allScenarioOutputs['2yr_rp_rain'],3),number_format($allScenarioOutputs['5yr_rp_rain'],3),number_format($allScenarioOutputs['10yr_rp_rain'],3),number_format($allScenarioOutputs['25yr_rp_rain'],3),number_format($allScenarioOutputs['50yr_rp_rain'],3),number_format($allScenarioOutputs['100yr_rp_rain'],3));
		$runoff = array('Runoff (' . $unitsArray[1] . ')',number_format($allScenarioOutputs['2yr_rp_runoff'],3),number_format($allScenarioOutputs['5yr_rp_runoff'],3),number_format($allScenarioOutputs['10yr_rp_runoff'],3),number_format($allScenarioOutputs['25yr_rp_runoff'],3),number_format($allScenarioOutputs['50yr_rp_runoff'],3),number_format($allScenarioOutputs['100yr_rp_runoff'],3));
		$soil_loss = array('Soil Loss (' . $unitsArray[2] . ')',number_format($allScenarioOutputs['2yr_rp_soilloss'],3),number_format($allScenarioOutputs['5yr_rp_soilloss'],3),number_format($allScenarioOutputs['10yr_rp_soilloss'],3),number_format($allScenarioOutputs['25yr_rp_soilloss'],3),number_format($allScenarioOutputs['50yr_rp_soilloss'],3),number_format($allScenarioOutputs['100yr_rp_soilloss'],3));
		$sediment = array('Sediment Yield (' . $unitsArray[2] . ')',number_format($allScenarioOutputs['2yr_rp_sedyield'],3),number_format($allScenarioOutputs['5yr_rp_sedyield'],3),number_format($allScenarioOutputs['10yr_rp_sedyield'],3),number_format($allScenarioOutputs['25yr_rp_sedyield'],3),number_format($allScenarioOutputs['50yr_rp_sedyield'],3),number_format($allScenarioOutputs['100yr_rp_sedyield'],3));
		
		// add array rows to table
		$this->table->add_row($rain);
		$this->table->add_row($runoff);
		$this->table->add_row($soil_loss);
		$this->table->add_row($sediment);
		
		// add to array rows to the CSV table
		array_push($resultsForCSV,$rain);
		array_push($resultsForCSV,$runoff);
		array_push($resultsForCSV,$soil_loss);
		array_push($resultsForCSV,$sediment);
		
		$htmlTable = $this->table->generate();
		$this->table->clear();
		
		// add results to the csv report
		$this->addToCSVReport($resultsForCSV);
		
		return $tooltip . $tableHeaderTitle . '<div class="divBox">' . $htmlTable . '</div>';
	}


	/**
	 * Builds a table of return periods (yearly results) for a single scenario. 
	 *
	 * @access	public
	 * @param   $allScenarioOutputs - an array of the outputs for the currently selected scenario
	 * @param   $unitsArray - an array of units (based on wheather the user selected metric or english units
	 * @return	void
	 */
	function createScenarioReturnPeriodYearlyTotalsTableSingle($allScenarioOutputs, $unitsArray)
	{
		$headersArray = array('Variable','2 yr','5 yr', '10 yr','25 yr','50 yr','100 yr');
		$resultsForCSV = array();
		
		// add this a header for the current return period to the CSV results array
		array_push($resultsForCSV,array('RETURN FREQUENCY RESULTS FOR YEARLY TOTALS'));
		array_push($resultsForCSV,$headersArray);
		
		// the tooltip
		$tooltip = '<a class="help_lnk left" style="margin: 15px 10px 0px 0px;" rel="popover" data-poload="help/getstring/return_frequency_yearly_totals" data-original-title="Return Frequency for Yearly Totals"></a>';
		
		$tableHeaderTitle =  '<span class="toggleButton"><span class="toggleIconOn"/>RETURN FREQUENCY RESULTS FOR YEARLY TOTALS</span>';
		$this->table->set_heading($headersArray);
		// array rows for return frequency table
		$rain = array('Rain (' . $unitsArray[1] . ')',number_format($allScenarioOutputs['2yr_rp_rain_yt'],3),number_format($allScenarioOutputs['5yr_rp_rain_yt'],3),number_format($allScenarioOutputs['10yr_rp_rain_yt'],3),number_format($allScenarioOutputs['25yr_rp_rain_yt'],3),number_format($allScenarioOutputs['50yr_rp_rain_yt'],3),number_format($allScenarioOutputs['100yr_rp_rain_yt'],3));
		$runoff = array('Runoff (' . $unitsArray[1] . ')',number_format($allScenarioOutputs['2yr_rp_runoff_yt'],3),number_format($allScenarioOutputs['5yr_rp_runoff_yt'],3),number_format($allScenarioOutputs['10yr_rp_runoff_yt'],3),number_format($allScenarioOutputs['25yr_rp_runoff_yt'],3),number_format($allScenarioOutputs['50yr_rp_runoff_yt'],3),number_format($allScenarioOutputs['100yr_rp_runoff_yt'],3));
		$soil_loss = array('Soil Loss (' . $unitsArray[2] . ')',number_format($allScenarioOutputs['2yr_rp_soilloss_yt'],3),number_format($allScenarioOutputs['5yr_rp_soilloss_yt'],3),number_format($allScenarioOutputs['10yr_rp_soilloss_yt'],3),number_format($allScenarioOutputs['25yr_rp_soilloss_yt'],3),number_format($allScenarioOutputs['50yr_rp_soilloss_yt'],3),number_format($allScenarioOutputs['100yr_rp_soilloss_yt'],3));
		$sediment = array('Sediment Yield (' . $unitsArray[2] . ')',number_format($allScenarioOutputs['2yr_rp_sedyield_yt'],3),number_format($allScenarioOutputs['5yr_rp_sedyield_yt'],3),number_format($allScenarioOutputs['10yr_rp_sedyield_yt'],3),number_format($allScenarioOutputs['25yr_rp_sedyield_yt'],3),number_format($allScenarioOutputs['50yr_rp_sedyield_yt'],3),number_format($allScenarioOutputs['100yr_rp_sedyield_yt'],3));
		
		// add array rows to table
		$this->table->add_row($rain);
		$this->table->add_row($runoff);
		$this->table->add_row($soil_loss);
		$this->table->add_row($sediment);
		
		// add to array rows to the CSV table
		array_push($resultsForCSV,$rain);
		array_push($resultsForCSV,$runoff);
		array_push($resultsForCSV,$soil_loss);
		array_push($resultsForCSV,$sediment);
		
		$htmlTable = $this->table->generate();
		$this->table->clear();
		
		// add results to the csv report
		$this->addToCSVReport($resultsForCSV);
		
		return $tooltip . $tableHeaderTitle . '<div class="divBox">' . $htmlTable . '</div>';
	}

	/**
	 * 
	 * Builds a table of that will display the risk assessment values calculated for this scenario.
	 *
	 * @access	public
	 * @param   $allScenarioOutputs - an array of the outputs for the selected scenario
	 * @param   $scenarioname - the name given by the user to the current scenario
	 * @param   $unitsArray - an array of units (based on wheather the user selected metric or english units
	 * @return	An HTML string with the a header and table section.
	 *
	 * @TODO   Remove this function since it is not being used.
	 */
	function createScenarioRiskAssessmentTableSingle($allScenarioOutputs, $scenarioname, $unitsArray)
	{
		$headersArray = array();
		$resultsForCSV = array();
		if($unitsArray[1] == 'inches'){
			// show results in ton/ac if english units are selected
			$ra_negligible_array = array('Negligible','Up to 0.112');
			$ra_acceptable_array = array('Acceptable','0.112 - 0.223');
			$ra_undesirable_aray = array('Undesirable','0.223 - 0.669');
			$ra_unacceptable_array = array('Unacceptable','Over  0.669');
		}
		else{
			$ra_negligible_array = array('Negligible','Up to 0.25');
			$ra_acceptable_array = array('Acceptable','0.25 - 0.50');
			$ra_undesirable_aray = array('Undesirable','0.50 - 1.50');
			$ra_unacceptable_array = array('Unacceptable','Over  1.50');	
		}
		
		// add this a header for the current return period to the CSV results array
		array_push($resultsForCSV,array('RISK ASSESSMENT RESULTS'));

		$tableHeaderTitle =  '<span class="toggleButton"><span class="toggleIconOn"/>RISK ASSESSMENT RESULTS</span>';

		array_push($headersArray, 'SEVERITY DESCRIPTION');
		array_push($headersArray, 'SOIL LOSS TOLERANCE RANGE (' . $unitsArray[2] . ')');
		
		array_push($headersArray, '<span style="color:' . $this->SCENARIOS_COLOR_ARRAY[0] . ';">' . $scenarioname . '</span>');
		array_push($ra_negligible_array, number_format($allScenarioOutputs['slt_prob_negligible'],3));
		array_push($ra_acceptable_array, number_format($allScenarioOutputs['slt_prob_acceptable'],3));
		array_push($ra_undesirable_aray, number_format($allScenarioOutputs['slt_prob_undesirable'],3));
		array_push($ra_unacceptable_array, number_format($allScenarioOutputs['slt_prob_unacceptable'],3));

		
		$this->table->set_heading($headersArray);
		$this->table->add_row($ra_negligible_array);
		$this->table->add_row($ra_acceptable_array);
		$this->table->add_row($ra_undesirable_aray);
		$this->table->add_row($ra_unacceptable_array);
		
		// add to table that will be added to the csv file
		array_push($resultsForCSV,$ra_negligible_array);
		array_push($resultsForCSV,$ra_acceptable_array);
		array_push($resultsForCSV,$ra_undesirable_aray);
		array_push($resultsForCSV,$ra_unacceptable_array);
		
		// add results to the csv report
		$this->addToCSVReport($resultsForCSV);
		
		$htmlTable = $this->table->generate();
		$this->table->clear();
		
		return $tableHeaderTitle . '<div class="divBox">' . $htmlTable . '</div>';
	}

	/**
	 * Builds a table with the average yearly values.
	 *
	 * @access	public
	 * @param   $userScenariosArray - an array of the scenarios selected by the user to compare
	 * @param   $unitsArray - an array of units (based on wheather the user selected metric or english units
	 * @return	An HTML string with the a header and table section.
	 */
	function createScenarioAveragesTable($userScenariosArray, $yearlyAvgArray, $unitsArray)
	{
		$headersArray = array('');
		$resultsForCSV = array();
		$precipValuesArray = array('Avg. Precipitation (' . $unitsArray[1] . '/year)');
		$runoffValuesArray = array('Avg. Runoff (' . $unitsArray[1] . '/year)');
		$sedYieldValuesArray = array('Avg. Sediment Yield (' . $unitsArray[2] . '/year)');
		$soilLossValuesArray = array('Avg. Soil Loss (' . $unitsArray[2] . '/year)');
        
        // prepare the salt load array in case any of the scnarios are saline
        $saltloadValuesArray = array('Salt Load (' . $unitsArray[5] . ')');
        $salineScenariosExist = FALSE;
                
		// add this a header for the current return period to the CSV results array
		array_push($resultsForCSV,array('AVERAGE ANNUAL RESULTS'));

		$tableHeaderTitle =  '<span class="toggleButton"><span class="toggleIconOn"/>ANNUAL AVERAGES</span>';
		
		$i = 0;
		foreach ($userScenariosArray as $scenario_id => $scenario_name){
			array_push($headersArray, '<span style="color:' . $this->SCENARIOS_COLOR_ARRAY[$i] . ';">' . $scenario_name . '</span>');
			array_push($precipValuesArray, number_format($yearlyAvgArray[$scenario_id]['rainYrlyAvg'],3));
			array_push($runoffValuesArray, number_format($yearlyAvgArray[$scenario_id]['runoffYrlyAvg'],3));
			array_push($sedYieldValuesArray, number_format($yearlyAvgArray[$scenario_id]['sedYieldYrlyAvg'],3));
			array_push($soilLossValuesArray, number_format($yearlyAvgArray[$scenario_id]['soilLossYrlyAvg'],3));
          
            // get the scenario inputs in order to pring Salt Load if it's a saline scenario
            $inputs_row = $this->Rhemmodel->get_user_scenario_inputs($scenario_id);
            if(isset($inputs_row->sar)){
                $salineScenariosExist = TRUE;
                // TDS (g) = 2.36 * [Soil Loss (Kg)] + 0.99 - Equation from Kossi
                if($unitsArray[1] == 'inches'){
                    // convert soil loss back to metric > to kg > after calculating initial TDS divide by 1000 to get from g to kg
                    $TDS = (2.36 * ( ($yearlyAvgArray[$scenario_id]['soilLossYrlyAvg'] / 0.446) * 1000 ) + 0.99)/1000;
                    // convert to lbs (* 2.20462) and to acres (/2.471) => lbs/ac/year
                    $TDS = ($TDS * 2.20462)/2.471;
                }
                else{
                    // report in kg/ha/year
                    $TDS = (2.36 * ( $yearlyAvgArray[$scenario_id]['soilLossYrlyAvg'] * 1000 ) + 0.99)/1000;
                }
                array_push($saltloadValuesArray, number_format($TDS,3));
            }
            else{
                array_push($saltloadValuesArray, "");
            }
            
            $i++;
		}
		
		$this->table->set_heading($headersArray);
		$this->table->add_row($precipValuesArray);
		$this->table->add_row($runoffValuesArray);
		$this->table->add_row($sedYieldValuesArray);
        $this->table->add_row($soilLossValuesArray);
        if($salineScenariosExist == TRUE){
            $this->table->add_row($saltloadValuesArray);
        }
		
		// add to table that will be added to the csv file
		array_push($resultsForCSV,$precipValuesArray);
		array_push($resultsForCSV,$runoffValuesArray);
		array_push($resultsForCSV,$sedYieldValuesArray);
        array_push($resultsForCSV,$soilLossValuesArray);
        if($salineScenariosExist == TRUE){
            array_push($resultsForCSV,$saltloadValuesArray);
        }
		
		// add results to the csv report
		$this->addToCSVReport($resultsForCSV);
		
		$htmlTable = $this->table->generate();
		$this->table->clear();
		
		return $tableHeaderTitle . '<div class="divBox">' . $htmlTable . '</div>';
	}
	
	/**
	 * Prints tables and charts for the scenarios selected by the user to compare.
	 *
	 * @access	public
	 * @return	void
	 */
	function printScenariosComparisonTablesandCharts()
	{
		// get the selected scenarios and decode the JSON string
		$scenarios = json_decode(str_replace('\\', '', $_REQUEST['scenarios']), true);
		$variables = array('rain','runoff','sedyield','soilloss'); //json_decode(str_replace('\\', '', $_REQUEST['variables']), true);

		// get user scenarios based on the selected scenario ids and their selection order
		$userScenariosRS = $this->Rhemmodel->get_user_scenarios_by_scenario_ids($this->session->userdata('user_id'), $scenarios);
		
		// only add the sccenarios select by the user
		foreach ($userScenariosRS as $row){
			if( is_numeric(array_search($row->scenario_id, $scenarios)))
				$userScenariosArray[$row->scenario_id] = $row->scenario_name;
		}
		
		// get the common units for all scenarios based on the first one
		$unitsArray = $this->getUnitsArray($scenarios[0]);
		
		// tooltip for the CSV and PNG reports
		echo '<a class="help_lnk left" rel="popover" data-poload="help/getstring/csv_report" data-original-title="Image/CSV Report"></a>';
		// link to CSV report
		echo '<span style="text-align:right;float:right;margin-right:10px;"><b><a href="' . base_url() . 'temp/' . "csv_report_" . $this->session->userdata('user_id') . ".csv" . '" target="_blank"><i class="icon-download-alt" style="opacity:0.7"></i> CSV</a></b></span>';
		// link to PDF report
		echo '<span style="text-align:right;float:right;margin-right:10px;"><b>Download results as <a href="#" onclick="saveComparisonReportAsImage()"><i class="icon-download-alt" style="opacity:0.7"></i>PNG</a></b></span>';	

		// print the scenarios input parameter table
		echo $this->createScenarioComparisonTable($userScenariosArray, $unitsArray, true);

		// grab the yearly average array
		$yearlyAvgArray = $this->getYearlyAverages($userScenariosArray, $unitsArray);
		
		// grab the return period array
		$returnPeriodArray = $this->getReturnPeriods($userScenariosArray, $unitsArray);

		// grab the risk assessment array
		$riskAssessmentArray = $this->getRiskAssessmentValues($userScenariosArray, $unitsArray);
		
		// print the scenarios return period graphics
		echo $this->printAnnualAvgScenariosChart($yearlyAvgArray, $unitsArray);;
		
		// print the yearly average table
		echo $this->createScenarioAveragesTable($userScenariosArray, $yearlyAvgArray, $unitsArray);
		
		// the return frequency chart
		echo $this->printReturnPeriodScenariosChart($returnPeriodArray, $unitsArray);

		echo $this->createScenariosReturnPeriodTables($userScenariosArray, $returnPeriodArray, $variables, $unitsArray);
		
		echo '<script>attachPopupEvents();activateResultsContainerTabs();enableDowloadableImages();</script>';
	}
	
	
	/**
	 * Prints a bar graph (using Google Charts) comparing return periods for each scenario. 
	 *
	 * @access	public
	 * @param   $yearlyAvgArray - an array of average output results
	 * @param   $unitsArray - an array of the units for the current scenario
	 * @return	void
	 */
	function printAnnualAvgScenariosChart($yearlyAvgArray, $unitsArray)
	{
		// print the help icon
		$chart_container = '<a class="help_lnk left" style="margin: 15px 10px 0px 0px;" rel="popover" data-poload="help/getstring/annual_averages" data-original-title="Annual Averages"></a>';

		// the header tab
		$chart_container = $chart_container . '<span class="toggleButton"><span class="toggleIconOn"/>ANNUAL AVERAGE GRAPHS</span>';

		// map the variables selected to the complete list of variables
		$variablesArray = array(0=>"rainYrlyAvg",1=>"runoffYrlyAvg",2=>"soilLossYrlyAvg",3=>"sedYieldYrlyAvg");

		// build an array a data array that will be used to pass in the scneario averages to the Google Charts JavaScript component
		$gdata_table_str[0] = array("");
		foreach($variablesArray as $varIndex => $varName)
		{
			$var_tabl_array = array(array("Scenario"));
			$number_of_scenarios = count($yearlyAvgArray);
			$scenario_index = 0;
			foreach($yearlyAvgArray as $scenario_id => $avg_array){
				$scenarioStrID = "S" . ($scenario_index + 1);
				array_push($var_tabl_array[0], $scenarioStrID);
				array_push($var_tabl_array, array($scenarioStrID));
				for ($i = 0; $i <  $number_of_scenarios; $i++)
				{
					if($i == $scenario_index)
						array_push($var_tabl_array[$scenario_index + 1],floatval($avg_array[$varName]));
					else
						array_push($var_tabl_array[$scenario_index + 1],0.0);
				}
				$scenario_index = $scenario_index + 1;
			}
			$gdata_table_str[$varIndex] = $var_tabl_array;
		}
		
		$chart_container = $chart_container . '<div class="chart_grid">
							<div class="cell" id="rain_avg_chart_div"></div>
							<div class="cell" id="ro_avg_chart_div"></div>
							<div class="cell" id="sy_avg_chart_div"></div>
							<div class="cell" id="sl_avg_chart_div"></div>
							</div>';
							
		$gchart_call = '<script> var avgDataArray = ' . json_encode($gdata_table_str) . ';' . 'renderAvgCharts(avgDataArray, "' . $unitsArray[0] . '");</script>';
		
		return $chart_container . $gchart_call;
	}
	
	/**
	 * Prints a bar graph comparing return periods for each scenario. 
	 *
	 * @access	public
	 * @param   $scenarioid - The Name of the current scenario
	 * @return	void
	 */
	function printReturnPeriodScenariosChart($scenarioReturnPeriodsArray, $unitsArray)
	{
		// print the help icon
		$chart_container = '<a class="help_lnk left" style="margin: 15px 10px 0px 0px;" rel="popover" data-poload="help/getstring/return_frequency" data-original-title="Return Frequency"></a>';
		// the header tab
		$chart_container = $chart_container . '<span class="toggleButton"><span class="toggleIconOn"/>RETURN FREQUENCY GRAPHS</span>';

		// used as the first column description for the output return period table
		$variableDescArray = array(0=>'rain',1=>'runoff',2=>'soilloss',3=>'sedyield');

		$gdata_table_str[0] = array("");
		foreach($variableDescArray as $varIndex => $varName)
		{
			$var_tabl_array = array(array("Scenario"));
			$rp2array = array("2");
			$rp5array = array("5");
			$rp10array = array("10");
			$rp25array = array("25");
			$rp50array = array("50");
			$rp100array = array("100");
			$number_of_scenarios = count($scenarioReturnPeriodsArray);
			$scenario_index = 0;
			foreach($scenarioReturnPeriodsArray as $scenario_id => $rp_array){
				$scenarioStrID = "S" . ($scenario_index + 1);
				array_push($var_tabl_array[0], $scenarioStrID);
				
				array_push($rp2array, floatval($rp_array['2yr_rp_' . $varName]));
				array_push($rp5array, floatval($rp_array['5yr_rp_' . $varName]));
				array_push($rp10array, floatval($rp_array['10yr_rp_' . $varName]));
				array_push($rp25array, floatval($rp_array['25yr_rp_' . $varName]));
				array_push($rp50array, floatval($rp_array['50yr_rp_' . $varName]));
				array_push($rp100array, floatval($rp_array['100yr_rp_' . $varName]));
				
				$scenario_index = $scenario_index + 1;
			}
			array_push($var_tabl_array,$rp2array,$rp5array,$rp10array,$rp25array,$rp50array,$rp100array);
			
			$gdata_table_str[$varIndex] = $var_tabl_array;
		}
		$chart_container = $chart_container . '<div class="chart_grid">
							<div class="cell" id="rain_rp_chart_div"></div>
							<div class="cell" id="ro_rp_chart_div"></div>
							<div class="cell" id="sy_rp_chart_div"></div>
							<div class="cell" id="sl_rp_chart_div"></div>
						  </div>';
		$gchart_call = '<script> var rpDataArray = ' . json_encode($gdata_table_str) . ' ; renderRPCharts(rpDataArray,"' . $unitsArray[0] . '");</script>';
		  
		  
	   return $chart_container . $gchart_call;// . '<b>' . json_encode($gdata_table_str) . '</b>';
	}
	
	/**
	 * Builds a table with up to 5 scenarios selected by the user to compare. 
	 *
	 * @access	public
	 * @param   $scenarioid - The name of the current scenario
	 * @param   $units      - an array of units (based on wheather the user selected metric or english units
	 * @return	void
	 */
	function createScenarioComparisonTable($userScenariosArray, $unitsArray, $buildCSVFlag)
	{
		// array to hold input parameters from the interface for each scenario selected
		// removed version on 4/1/2014. To add it back add ,array('Version') to the $inputParamsArray and push the $versionNumber to the table
		$inputParamsArray = array(array(''), array('Version'), array('State ID'),array('Climate Station'),array('Soil Texture'),array('SAR'), array('Soil Water Saturation %'),array('Slope Length ' . '(' . $unitsArray[3] . ')'),
					  array('Slope Shape'),array('Slope Steepness %'),array('Bunch Grass Foliar Cover %'),array('Forbs and/or Annual Grasses Foliar Cover %'),array('Shrubs Foliar Cover %'),array('Sod Grass Foliar Cover %'),array('Total Foliar Cover %'),array('Basal Cover %'),array('Rock Cover %'),array('Litter Cover %'), array('Biological Crusts Cover %'), array('Total Ground Cover %'));
		$headersArray = array('');
    
        $salineScenariosExist = FALSE;
    
        $i = 0;
		foreach ($userScenariosArray as $scenario_id => $scenario_name)
		{
		    // get the user scenario inputs for the current scenario
			$inputs_row = $this->Rhemmodel->get_user_scenario_inputs($scenario_id);
			// input parameters
			array_push($inputParamsArray[0], '<span style="color:' . $this->SCENARIOS_COLOR_ARRAY[$i] . ';">' . $scenario_name . '</span>');
			
			// print the version number.
			//$versionNumber = RHEM_VERSION; 
			//array_push($inputParamsArray[1], $versionNumber);
			
			array_push($inputParamsArray[1], $inputs_row->version);
			array_push($inputParamsArray[2], strtoupper($inputs_row->state_id));
			array_push($inputParamsArray[3], ucwords(strtolower($inputs_row->station)));
			array_push($inputParamsArray[4], $inputs_row->class_name);
			if(isset($inputs_row->sar)){
                array_push($inputParamsArray[5], $inputs_row->sar);
                $salineScenariosExist = TRUE;
            }
            else{
                array_push($inputParamsArray[5], "");
            }
			array_push($inputParamsArray[6], $inputs_row->soil_moisture);
			array_push($inputParamsArray[7], $inputs_row->slope_length);
			array_push($inputParamsArray[8], $inputs_row->shape_name);
			array_push($inputParamsArray[9], $inputs_row->slope_steepness);
			array_push($inputParamsArray[10], $inputs_row->bunchgrass_canopy_cover);
			array_push($inputParamsArray[11], $inputs_row->forbs_canopy_cover);
			array_push($inputParamsArray[12], $inputs_row->shrubs_canopy_cover);
			array_push($inputParamsArray[13], $inputs_row->sodgrass_canopy_cover);

			$canopycover = $inputs_row->bunchgrass_canopy_cover + $inputs_row->forbs_canopy_cover + $inputs_row->shrubs_canopy_cover + $inputs_row->sodgrass_canopy_cover;
			array_push($inputParamsArray[14], $canopycover);

			array_push($inputParamsArray[15], $inputs_row->basal_cover);
			array_push($inputParamsArray[16], $inputs_row->rock_cover);
			array_push($inputParamsArray[17], $inputs_row->litter_cover);
			array_push($inputParamsArray[18], $inputs_row->cryptogams_cover);
			$groundcover = $inputs_row->basal_cover + $inputs_row->litter_cover + $inputs_row->cryptogams_cover + $inputs_row->rock_cover;
			array_push($inputParamsArray[19], $groundcover);
			$i++;
		}
		
		// create a template for the table
		$tmpl = array ('table_open' => '<table border="0" cellpadding="4" cellspacing="0" class="s_comp_table">', 'heading_cell_start' => '<th width="16%">' ,  'row_alt_start' => '<tr class="odd">');
		$this->table->set_template($tmpl); 
		
		// add all the rows to the table
		for($i =0; $i<count($inputParamsArray); $i++){
			if($i == 0)
                $this->table->set_heading($inputParamsArray[$i]);
            elseif($i == 5){
                if($salineScenariosExist == TRUE)
                    $this->table->add_row($inputParamsArray[$i]);
            }
			else
				$this->table->add_row($inputParamsArray[$i]);
		}
		
		// create the table
		$tableHeaderTitle = '<span class="toggleButton"><span class="toggleIconOn"/>SCENARIO INPUTS</span>';
		$htmlTable = '<div class="divBox">' . $this->table->generate() . '</div>';
		$this->table->clear();
		
		if($buildCSVFlag){
            // remove the SAR input, if no saline scenario present
            if($salineScenariosExist == FALSE)
                unset($inputParamsArray[5]);
			// create a new CVS file and add the input parameters for the selected scenarios
			$this->createCSVReport($inputParamsArray);
		}
		return $tableHeaderTitle . $htmlTable . '<script>styleInputParameterTable();</script>';
	}
	
	/**
	 * Builds a table of return periods all scenarios selected.
	 *
	 * @access	public
	 * @param   $userScenariosArray - an array of the scenarios selected by the user to compare
	 * @param   $units - an array of units (based on wheather the user selected metric or english units
	 * @return	void
	 */
	function createScenariosReturnPeriodTables($userScenariosArray, $retunPeriodArray, $variables, $unitsArray)
	{
		$htmlTables = '';
		$headersArray = array('');
		
		$resultsForCSV = array();
		
		// used as the first column description for the output return period table
		$variableDescArray = array('Rain (' . $unitsArray[1] . ')','Runoff (' . $unitsArray[1] . ')',
								'Sediment Yield (' . $unitsArray[2] . ')','Soil Loss (' . $unitsArray[2] . ')');
		// used to map the variables selected to the complete list of variables
		$variablesArray = array("rain","runoff","sedyield","soilloss");
		
		// iterate trough all of the selected user scenarios and get the return periods for all variables
		$i = 0;
		foreach($userScenariosArray as $scenario_id => $scenario_name)
		{
			array_push($headersArray, '<span style="color:' . $this->SCENARIOS_COLOR_ARRAY[$i] . ';">' . $scenario_name . '</span>');
			$i++;
		}
		
		$returnPeriodNumber = array(2,5,10,25,50,100);
		// loop through all the time periods
		for($rp = 0; $rp < 6; $rp++)
		{
			$this->table->clear();
			$tableHeaderTitle =  '<span class="toggleButton"><span class="toggleIconOn"/> ' . $returnPeriodNumber[$rp] . ' YEAR RETURN FREQUENCY RESULTS FOR YEARLY VALUES</span>';
			$this->table->set_heading($headersArray);
			
			// add this a header for the current return period to the CSV results array
			array_push($resultsForCSV,array($returnPeriodNumber[$rp] . ' YEAR RETURN FREQUENCY RESULTS FOR YEARLY VALUES'));
			
			// loop through all the variables
			for($var = 0; $var < 4; $var++)
			{
				// add only the selected variables to the table as rows
				if(in_array($variablesArray[$var],$variables))
				{
					$j = 0;
					$scenario_var_values = array();
					// loop through all of the scenarios
					foreach($retunPeriodArray as $scenario_id => $scenario_rp_variable_array)
					{
						// add the name of the variable to the first cell in the row
						if($j == 0)
							array_push($scenario_var_values, $variableDescArray[$var]);
							
						// add all of the scenario values for this return period to the row
						$colName = $returnPeriodNumber[$rp] . 'yr_rp_' . $variablesArray[$var];
						array_push($scenario_var_values,number_format($retunPeriodArray[$scenario_id][$colName],3));	
						$j++;
					}
					$this->table->add_row($scenario_var_values);
					// add to table that will be added to the csv file
					array_push($resultsForCSV,$scenario_var_values);
				}
			}
			// print the help icon
			$htmlTables = $htmlTables . '<a class="help_lnk left" style="margin: 15px 10px 0px 0px;" rel="popover" data-poload="help/getstring/return_frequency" data-original-title="Return Frequency"></a>';
			$htmlTables = $htmlTables . $tableHeaderTitle . '<div class="divBox">' . $this->table->generate() . '</div>';
		}
		
		$htmlTables = $htmlTables . '<br/><span style="float:right;font-weight:bold;"><a href="#"><i class="icon-arrow-up" style="opacity:0.7"></i>Go to Top</a></span>';
		
		// add results to the csv report
		$this->addToCSVReport($resultsForCSV);
		
		return $htmlTables;
	}

	/**
	 * Builds a table of that will display the risk assessment values calculated for this scenario.
	 *
	 * @access	public
	 * @param   $userScenariosArray - an array of the scenarios selected by the user to compare
	 * @param   $unitsArray - an array of units (based on wheather the user selected metric or english units
	 * @return	An HTML string with the a header and table section.
	 */
	function createScenarioRiskAssessmentTable($userScenariosArray, $riskAssessmentArray, $unitsArray)
	{
		$headersArray = array();
		$resultsForCSV = array();
		if($unitsArray[1] == 'inches'){
			// show results in ton/ac if english units are selected
			$ra_negligible_array = array('Negligible','Up to 0.223');
			$ra_acceptable_array = array('Acceptable','0.223 - 0.446');
			$ra_undesirable_aray = array('Undesirable','0.446 - 1.338');
			$ra_unacceptable_array = array('Unacceptable','Over  1.338');
		}
		else{
			$ra_negligible_array = array('Negligible','Up to 0.25');
			$ra_acceptable_array = array('Acceptable','0.25 - 0.50');
			$ra_undesirable_aray = array('Undesirable','0.50 - 1.50');
			$ra_unacceptable_array = array('Unacceptable','Over  1.50');	
		}
		
		// add this a header for the current return period to the CSV results array
		array_push($resultsForCSV,array('RISK ASSESSMENT RESULTS'));

		$tableHeaderTitle =  '<span class="toggleButton"><span class="toggleIconOn"/>RISK ASSESSMENT RESULTS</span>';

		array_push($headersArray, 'SEVERITY DESCRIPTION');
		array_push($headersArray, 'SOIL LOSS TOLERANCE RANGE (' . $unitsArray[2] . ')');
		
		$i = 0;
		foreach ($userScenariosArray as $scenario_id => $scenario_name)
		{
			array_push($headersArray, '<span style="color:' . $this->SCENARIOS_COLOR_ARRAY[$i] . ';">' . $scenario_name . '</span>');
			array_push($ra_negligible_array, number_format($riskAssessmentArray[$scenario_id]['slt_prob_negligible'],3));
			array_push($ra_acceptable_array, number_format($riskAssessmentArray[$scenario_id]['slt_prob_acceptable'],3));
			array_push($ra_undesirable_aray, number_format($riskAssessmentArray[$scenario_id]['slt_prob_undesirable'],3));
			array_push($ra_unacceptable_array, number_format($riskAssessmentArray[$scenario_id]['slt_prob_unacceptable'],3));
			$i++;
		}
		
		$this->table->set_heading($headersArray);
		$this->table->add_row($ra_negligible_array);
		$this->table->add_row($ra_acceptable_array);
		$this->table->add_row($ra_undesirable_aray);
		$this->table->add_row($ra_unacceptable_array);
		
		// add to table that will be added to the csv file
		array_push($resultsForCSV,$ra_negligible_array);
		array_push($resultsForCSV,$ra_acceptable_array);
		array_push($resultsForCSV,$ra_undesirable_aray);
		array_push($resultsForCSV,$ra_unacceptable_array);
		
		// add results to the csv report
		$this->addToCSVReport($resultsForCSV);
		
		$htmlTable = $this->table->generate();
		$this->table->clear();
		
		return $tableHeaderTitle . '<div class="divBox">' . $htmlTable . '</div>';
	}

	/**
	 * Parses the summary output file and returns a JSON object representation of the same. 
	 *
	 * @access	public
	 * @return	void
	 */
	function saveSummaryOutput($scenarioid)
	{
		// save the output to the database based on the current scenario id and the saved units
		//$scenarioid = $this->session->userdata('scenarioid');
		$units = $this->session->userdata('units');
		
		// change the directory to output cligen files
		chdir(OUTPUT_FOLDER);
		$summary_file = fopen("scenario_output_summary_" . $this->session->userdata('user_id') . "_" . $scenarioid . ".sum", "r") or exit("Unable to open summary output file " . "scenario_output_summary_" . $this->session->userdata('user_id') . "_" . $scenarioid . ".sum" . "!");
		$linenumber = 1;
		
		// 
		// TODO: exclude this since we are now extracting the average precipitation and return period information for the scenario (in the database) from the scenario output
		//
		////// THIS WAS DONE IN ORDER TO GET THE PPT VALUES BEFORE THE KSS FILTER
		// get average precipitation for the climate station used in this scenario
		$station_ppt_avg = $this->Rhemmodel->get_station_ppt_average($scenarioid);
		// get return periods for the climate station used in this scenario
		//$station_ppt_rp = $this->Rhemmodel->get_ppt_station_ppt_return_periods($scenarioid);


		/// the new string that will hold the updated summary file
		$summary_string = "";

		// iterate through the entire summary file, extract the result data, in preparation for DB table insert
		while(!feof($summary_file))
		{
			// read a line from the ouput summary file
			$line = fgets($summary_file);
			
			// extract all of the average values
			if ($linenumber > 2 and $linenumber < 7)
			{
				$lineArray = explode("=", $line);
				$value = trim($lineArray[1]);
				
				switch($linenumber){
					case 3:
						// 
						// TODO: exclude this since we are now extracting the average precipitation information for the scenario (in the database) from the scenario output
						//
						$avgValuesArray[0] = $station_ppt_avg['rainYrlyAvg'];
						$summary_string = $summary_string . "  Avg-Precipitation(mm/year) =     " .  $station_ppt_avg['rainYrlyAvg'] . "\n";
						#$avgValuesArray[0] = $value;
						#$summary_string = $summary_string . $line;
						break;
					case 4:
						$avgValuesArray[1] = $value;
						$summary_string = $summary_string . $line;
						break;
					case 5:
						$avgValuesArray[2] = $value;
						$summary_string = $summary_string . $line;
						break;
					case 6:
						$avgValuesArray[3] = $value;
						$summary_string = $summary_string . $line;
						break;
				}
			}
			// extract all of the return period results - yeary totals
			else if($linenumber > 12 and $linenumber < 17){
				$lineArray = explode(")", trim($line));
				$lineArray = preg_split("/\s+/", trim($lineArray[1]));
				switch($linenumber){
					case 13: // rain
						// 
						// TODO: exclude this since we are now extracting the return period precipitation information for the scenario (in the database) from the scenario output
						//
						/*$rp2yrYTValuesArray[0] = $station_ppt_rp['2yr_rp_rain'];
						$rp5yrYTValuesArray[0] = $station_ppt_rp['5yr_rp_rain'];
						$rp10yrYTValuesArray[0] = $station_ppt_rp['10yr_rp_rain'];
						$rp25yrYTValuesArray[0] = $station_ppt_rp['25yr_rp_rain'];
						$rp50yrYTValuesArray[0] = $station_ppt_rp['50yr_rp_rain'];
						$rp100yrYTValuesArray[0] = $station_ppt_rp['100yr_rp_rain'];
						$summary_string = $summary_string . "  Precipitation(mm)      " . $station_ppt_rp['2yr_rp_rain'] . "      " . $station_ppt_rp['5yr_rp_rain'] . "      " . $station_ppt_rp['10yr_rp_rain'] . "      " . $station_ppt_rp['25yr_rp_rain'] . "      " . $station_ppt_rp['50yr_rp_rain'] . "      " . $station_ppt_rp['100yr_rp_rain'] . "\n";
						*/
						$rp2yrYTValuesArray[0] = $lineArray[0];
						$rp5yrYTValuesArray[0] = $lineArray[1];
						$rp10yrYTValuesArray[0] = $lineArray[2];
						$rp25yrYTValuesArray[0] = $lineArray[3];
						$rp50yrYTValuesArray[0] = $lineArray[4];
						$rp100yrYTValuesArray[0] = $lineArray[5];
						$summary_string = $summary_string . $line;
						break;
					case 14: // runoff
						$rp2yrYTValuesArray[1] = $lineArray[0];
						$rp5yrYTValuesArray[1] = $lineArray[1];
						$rp10yrYTValuesArray[1] = $lineArray[2];
						$rp25yrYTValuesArray[1] = $lineArray[3];
						$rp50yrYTValuesArray[1] = $lineArray[4];
						$rp100yrYTValuesArray[1] = $lineArray[5];
						$summary_string = $summary_string . $line;
						break;
					case 15: // soil loss
						$rp2yrYTValuesArray[2] = $lineArray[0];
						$rp5yrYTValuesArray[2] = $lineArray[1];
						$rp10yrYTValuesArray[2] = $lineArray[2];
						$rp25yrYTValuesArray[2] = $lineArray[3];
						$rp50yrYTValuesArray[2] = $lineArray[4];
						$rp100yrYTValuesArray[2] = $lineArray[5];
						$summary_string = $summary_string . $line;
						break;
					case 16: // sediment
						$rp2yrYTValuesArray[3] = $lineArray[0];
						$rp5yrYTValuesArray[3] = $lineArray[1];
						$rp10yrYTValuesArray[3] = $lineArray[2];
						$rp25yrYTValuesArray[3] = $lineArray[3];
						$rp50yrYTValuesArray[3] = $lineArray[4];
						$rp100yrYTValuesArray[3] = $lineArray[5];
						$summary_string = $summary_string . $line;
						break;
				}
			}

			// extract all of the return period results - yearly maximum daily values
			else if($linenumber > 24 and $linenumber < 29){
				$lineArray = explode(")", trim($line));
				$lineArray = preg_split("/\s+/", trim($lineArray[1]));
				switch($linenumber){
					case 25: // rain
						// 
						// TODO: exclude this since we are now extracting the return period precipitation information for the scenario (in the database) from the scenario output
						//
						/*$rp2yrValuesArray[0] = $station_ppt_rp['2yr_rp_rain'];
						$rp5yrValuesArray[0] = $station_ppt_rp['5yr_rp_rain'];
						$rp10yrValuesArray[0] = $station_ppt_rp['10yr_rp_rain'];
						$rp25yrValuesArray[0] = $station_ppt_rp['25yr_rp_rain'];
						$rp50yrValuesArray[0] = $station_ppt_rp['50yr_rp_rain'];
						$rp100yrValuesArray[0] = $station_ppt_rp['100yr_rp_rain'];
						$summary_string = $summary_string . "  Precipitation(mm)      " . $station_ppt_rp['2yr_rp_rain'] . "      " . $station_ppt_rp['5yr_rp_rain'] . "      " . $station_ppt_rp['10yr_rp_rain'] . "      " . $station_ppt_rp['25yr_rp_rain'] . "      " . $station_ppt_rp['50yr_rp_rain'] . "      " . $station_ppt_rp['100yr_rp_rain'] . "\n";
						*/
						$rp2yrValuesArray[0] = $lineArray[0];
						$rp5yrValuesArray[0] = $lineArray[1];
						$rp10yrValuesArray[0] = $lineArray[2];
						$rp25yrValuesArray[0] = $lineArray[3];
						$rp50yrValuesArray[0] = $lineArray[4];
						$rp100yrValuesArray[0] = $lineArray[5];
						$summary_string = $summary_string . $line;
						break;
					case 26: // runoff
						$rp2yrValuesArray[1] = $lineArray[0];
						$rp5yrValuesArray[1] = $lineArray[1];
						$rp10yrValuesArray[1] = $lineArray[2];
						$rp25yrValuesArray[1] = $lineArray[3];
						$rp50yrValuesArray[1] = $lineArray[4];
						$rp100yrValuesArray[1] = $lineArray[5];
						$summary_string = $summary_string . $line;
						break;
					case 27: // soil loss
						$rp2yrValuesArray[2] = $lineArray[0];
						$rp5yrValuesArray[2] = $lineArray[1];
						$rp10yrValuesArray[2] = $lineArray[2];
						$rp25yrValuesArray[2] = $lineArray[3];
						$rp50yrValuesArray[2] = $lineArray[4];
						$rp100yrValuesArray[2] = $lineArray[5];
						$summary_string = $summary_string . $line;
						break;
					case 28: // sediment
						$rp2yrValuesArray[3] = $lineArray[0];
						$rp5yrValuesArray[3] = $lineArray[1];
						$rp10yrValuesArray[3] = $lineArray[2];
						$rp25yrValuesArray[3] = $lineArray[3];
						$rp50yrValuesArray[3] = $lineArray[4];
						$rp100yrValuesArray[3] = $lineArray[5];
						$summary_string = $summary_string . $line;
						break;
				}
			}

			// exclude the header for the risk assessment section...for now
			/*else if($linenumber > 15 and $linenumber < 21){
			}
			// extract the values for soil loss tolerance probabilities	
			else if($linenumber > 20 and $linenumber < 25){
				$slt_probability_value = substr($line,-10);
				switch($linenumber){
					case 21: // SLT Probability Negligible
						$sltValuesArray[0] = $slt_probability_value;
						#$summary_string = $summary_string . $line;
						break;
					case 22: // SLT Probability Acceptable
						$sltValuesArray[1] = $slt_probability_value;
						#$summary_string = $summary_string . $line;
						break;
					case 23: // SLT Probability Undesirable
						$sltValuesArray[2] = $slt_probability_value;
						#$summary_string = $summary_string . $line;
						break;
					case 24: // SLT Probability Unacceptable
						$sltValuesArray[3] = $slt_probability_value;
						#$summary_string = $summary_string . $line;
						break;
				}
			}*/
			else{
				$summary_string = $summary_string . $line;
			}
			$linenumber = $linenumber + 1;
		}

		// TODO: For now, add 0 values for the risk assessment values for this scenario (this should be remove in the future. 
		// Both from the datbase and from this code)
		$sltValuesArray[0] = 0;
		$sltValuesArray[1] = 0;
		$sltValuesArray[2] = 0;
		$sltValuesArray[3] = 0;

		$valuesArray = array_merge($avgValuesArray,$rp2yrValuesArray,$rp5yrValuesArray,$rp10yrValuesArray,$rp25yrValuesArray,$rp50yrValuesArray,$rp100yrValuesArray,$sltValuesArray,
								   $rp2yrYTValuesArray,$rp5yrYTValuesArray,$rp10yrYTValuesArray,$rp25yrYTValuesArray,$rp50yrYTValuesArray,$rp100yrYTValuesArray);
		// insert the average values obtained from the summary file to the database
		$this->Rhemmodel->insert_scenario_output($scenarioid, $valuesArray);
		
		fclose($summary_file);

		// write the chages to the summary output file
		$summary_file_new = fopen("scenario_output_summary_" . $this->session->userdata('user_id') . "_" . $scenarioid . ".sum", "w") or exit("Unable to save summary output file!");
		fwrite($summary_file_new, $summary_string);
		fclose($summary_file_new);

		chdir(getcwd());
		
		// in order to keep the execution syncrhonous, I decied to call the print results table right after the output summary is saved
		$this->printSingleScenarioResultsTableById($scenarioid);
		//return "Scenario Results Saved!";
	}

	/**
	 * Builds a comma delimited file based on the given array.
	 *
	 * @access	public
	 * @param   $array - the array to be converted to a CSV
	 * @return	void	
	 */
	function createCSVReport($inputParamsArray)
	{
		// change the directory to output cligen files
		chdir(OUTPUT_FOLDER);
		$file = fopen("csv_report_" . $this->session->userdata('user_id') . ".csv", "w") or exit("Unable to save CSV report file!");
		
		$i = 0;
		foreach ($inputParamsArray as $row) {
			// strip the <span> tags from the header
			if($i == 0){
				for($j = 0; $j < count($row); $j++){
					$row[$j] = strip_tags($row[$j]);
				}
			}				
			fputcsv($file, $row);
			$i++;
		}
				
		fclose($file);
		chdir(getcwd());
	}

	/**
	 * Builds a PDF report from the scenario comparison
	 *
	 * @access	public
	 * @param   $array - the array to be converted to a CSV
	 * @return	void	
	 */
	function createPDFReport()
	{
		// change the directory to output cligen files
		chdir(OUTPUT_FOLDER);
		
		$data = $_POST['img_val'];

		// TODO: figure out why I have to replace these characters after the
		// image data has been transferend from the client
		//Get the base-64 string from data
		$data = str_replace('iVBORw ','iVBORw0',$data);

		// testing the encoded image data coming from the client
		//$file = fopen("image_data.txt", "w") or exit("Unable to save CSV report file!");
		//fwrite($file, $_POST['img_val']);

		//Decode the string
		$unencodedData=base64_decode($data);
 
		//Save the image
		file_put_contents("png_report_" . $this->session->userdata('user_id') . ".png", $unencodedData);
				
		chdir(getcwd());
		//$fclose($file);

		echo "png_report_" . $this->session->userdata('user_id') . ".png";
	}
	
	/**
	 * Adds the return period tables to the CVS report. 
	 *
	 * @access	public
	 * @param   $returnPeriodsArray - the array to be converted to a CSV
	 * @return	void	
	 */
	function addToCSVReport($returnPeriodsArray)
	{
		// change the directory to output cligen files
		chdir(OUTPUT_FOLDER);
		$file = fopen("csv_report_" . $this->session->userdata('user_id') . ".csv", "a") or exit("Unable to add to CSV report file!");
		
		foreach ($returnPeriodsArray as $row) {
			fputcsv($file, $row);
		}
				
		fclose($file);
		chdir(getcwd());
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
		
		$unitsArray = array($scenarioUnits,'mm','Mg/ha','meters','mm/hr', 'kg/ha/year');
		
		if($scenarioUnits == 'english'){
			$unitsArray = array($scenarioUnits,'inches','ton/ac','feet','inches/hr','lbs/ac/year');
		}
		return $unitsArray;
	}
}
?>