<?php
/**
 * RHEM v2 Model Class
 *
 * This class will be used to access all of the data stored
 * in the RHEM v2 database. 
 *
 * The methods in this class will be ordered as follows:
 * 1) Scenario methods
 * 2) Session methods
 * 3) Climate Station methods
 * 4) Soil methods
 * 5) Slope methods
 * 6) Vegetation Cover methods
 * 7) Miscellaneous methods
 *  
 * @package		RHEM v2 Web Tool
 * @author		Gerardo Armendariz
 *
 * @TODO Prevent SQL injections by following the following example for each of the 
 *       queries: $query = sprintf("SELECT * FROM Users where UserName='%s' and Password='%s'", 
 *                 mysql_real_escape_string($Username), mysql_real_escape_string($Password));
 */
class Rhemmodel extends CI_Model 
{
	/**
	 * RHEM v2 Model
	 *
	 * This is the constructor for the current model.
	 */
    function __construct()
    {
        parent::__construct();

        $this->load->helper('security');
    }
	
	/************************************************************************/
	/*************************   SCENARIO RELATED METHODS *******************/
	/************************************************************************/
	
	/**
	 * Find out if a user scenario based on its name already exists.
	 *
	 *
	 * @access	public
	 * @param	$scenarioname The name of the scenario
	 * @param   $userid  The user id
	 * @return	void
	 */
	function does_scenario_exist($scenarioname, $userid)
	{
		//$query = $this->db->query("SELECT scenario_id FROM user_scenarios WHERE user_id = " . $userid . " AND scenario_name = '" . $scenarioname . "';");
		
		//return $query->num_rows(); 

		$this->db->select('scenario_id');
		$this->db->from('user_scenarios');
		$this->db->where("user_id = " . $userid . " AND scenario_name = '" . $scenarioname . "';");
		$num_results = $this->db->count_all_results();

		return $num_results;
	}

	function test_scenario_exists()
	{
		echo "Making Query";
		$this->db->select('scenario_id');
		$this->db->from('user_scenarios');
		$this->db->where("user_id = 1 AND scenario_name = 'Mountainair Test 2 v2';");
		$num_results = $this->db->count_all_results();

		return $num_results;
	}
	
	/**
	 * Saves the current scenario to the database.
	 * 
	 * Add all the data collected and save is as as a user scenario.  This will enable the user to  retreive saved scenarios.
	 *
	 * @access	public
	 * @param	N/A
	 * @return	void 
	 */
	function save_scenario($scenariodata)
	{
		// insert the user scenario data
		$this->db->insert('user_scenarios', $scenariodata);
		
		$last_item_inserted = mysql_insert_id($this->db->conn_id);

		return $last_item_inserted;
	}
	
	/**
	 * Saves the current scenario to the database.
	 * 
	 * Add all the data collected and save is as as a user scenario.  This will enable the user to  retreive saved scenarios.
	 *
	 * @access	public
	 * @param	N/A
	 * @return	The id of the scenario updated. 
	 */
	function update_scenario($scenariodata)
	{
		$scenarioid = $this->find_scenario_id($scenariodata['scenario_name'], $scenariodata['user_id']);
		$this->db->update('user_scenarios', $scenariodata, 'scenario_id = ' . $scenarioid . '');

		// delete the current scenario results/output since some of the inputs have changed
		$this->delete_scenario_results($scenarioid);
		
		return $scenarioid; 
	}

	/**
	 * Renames a scenario based on the new name assigned by the user.  If this scenario name exists for the user, a 0 will be returned.
	 * 
	 * @access	public
	 * @param   new_scenario_name - The new scenario name
	 * @param   scenario_id - The scenario id
	 * @return	The id of the scenario update or a 0 if the scneario name already exists for the user
	 */
	function rename_scenario($new_scenario_name, $scenario_id)
	{
		$this->db->query("UPDATE user_scenarios SET scenario_name = '" . $new_scenario_name . "' WHERE scenario_id = " . $scenario_id . ";");

		return $this->db->affected_rows(); 
	}
	
	/**
	 * Finds the scneario ID based on the scenario name and the user name.
	 * 
	 * Add all the data collected and save is as as a user scenario.  This will enable the user to  retreive saved scenarios.
	 *
	 * @access	public
	 * @param	N/A
	 * @return	void 
	 */
	function find_scenario_id($scenarioname, $userid)
	{
		$query = $this->db->query("SELECT scenario_id FROM user_scenarios WHERE scenario_name='" . $scenarioname . "' AND user_id = " . $userid . ";");
		
		// make sure that a row has been returned, if not output a -999
		$scenarioid = -999;
		if($query->num_rows() > 0)
			$scenarioid = $query->row()->scenario_id;
		
		return $scenarioid;
	}
	
	/**
	 * Retrieve all the scenarios based on the given user id. 
	 * 
	 * Returns a query result sets of all the columns needed to run scenarios.
	 *
	 * @access	public
	 * @param	N/A
	 * @return	void
	 */
	function get_user_scenarios($userid, $orderby)
	{
		$orderbyClause = "u.scenario_date DESC";
		if($orderby == "order_by_name")
			$orderbyClause = "u.scenario_name";

		$query = $this->db->query("SELECT scenario_id, upper(u.state_id) AS state_id, c.station, u.scenario_name, u.version, u.scenario_date, u.units 
		                           FROM user_scenarios u, climate_stations c 
								   WHERE u.station_id = c.station_id AND u.user_id = " . $userid . " ORDER BY " . $orderbyClause . ";");
		
		return $query->result();
	}


	/**
	 * 
	 * Retrieve selected scenarios based on the given user id.  Use the ORDER BY FIELD functionality to follow a specific order. 
	 * 
	 * Returns a query result sets of all the columns needed to run scenarios.
	 *
	 * @access	public
	 * @param	User ID, Scenarios array
	 * @return	void
	 */
	function get_user_scenarios_by_scenario_ids($userid, $scenarios)
	{
		// convert the $scenarios to a string before passing to the ORDER BY FIELD
		$scenarios = implode(",", $scenarios);
		$query = $this->db->query("SELECT scenario_id, upper(u.state_id) AS state_id, c.station, u.scenario_name, u.version, u.scenario_date, u.units 
		                           FROM user_scenarios u, climate_stations c 
								   WHERE u.station_id = c.station_id AND u.user_id = " . $userid . " AND u.scenario_id IN (" . $scenarios  .  ") ORDER BY FIELD (scenario_id, " . $scenarios . ");");
		
		return $query->result();
	}
	
	/**
	 * Retrieve all the scenarios based on the given user id and two order by fields.  One is by name or date and the other by specific field order.
	 * 
	 * Returns a query result sets of all the columns needed to run scenarios.
	 *
	 * @access	public
	 * @param	N/A
	 * @return	void
	 */
	function get_user_scenarios_order_by_field($userid, $orderby, $fields)
	{
		$orderbyClause = "u.scenario_date DESC";
		if($orderby == "order_by_name")
			$orderbyClause = "u.scenario_name";

		$query = $this->db->query("SELECT scenario_id, upper(u.state_id) AS state_id, c.station, u.scenario_name, u.version, u.scenario_date, u.units 
		                           FROM user_scenarios u, climate_stations c 
								   WHERE u.station_id = c.station_id AND u.user_id = " . $userid . " ORDER BY FIELD(u.scenario_id, " . $fields . "), " . $orderbyClause . ";");
		
		return $query->result();
	}


	/**
	 * Retrieve all the scenarios based on the given user id.  Also retrieve the soil loss for the selected scenarios. 
	 * 
	 * Returns a query result sets of all the columns needed to run scenarios.
	 *
	 * @access	public
	 * @param	N/A
	 * @return	void
	 */
	function get_user_scenarios_with_results($userid, $orderby)
	{
		$orderbyClause = "u.scenario_date DESC";
		if($orderby == "order_by_name")
			$orderbyClause = "u.scenario_name";

		$query = $this->db->query("SELECT u.scenario_id, upper(u.state_id) AS state_id, c.station, u.scenario_name, u.version, u.scenario_date, u.units, so.avg_soilloss
									FROM user_scenarios u
									JOIN climate_stations AS c ON c.station_id = u.station_id
									JOIN scenario_output AS so ON u.scenario_id = so.scenario_id
								   WHERE u.user_id = " . $userid . " ORDER BY " . $orderbyClause . ";");
		
		return $query->result();
	}

	/**
	 * Get a user scenario by ID and return a result set of the input values used to run this scenario.
	 *
	 * @access	public
	 * @param	N/A
	 * @return	void
	 */
	function get_user_scenario_inputs($scenarioid)
	{
		$query = $this->db->query("SELECT us.state_id, us.version, cs.station, st.class_name, us.sar, us.slope_length, ss.shape_name, us.slope_steepness, 
								   us.soil_moisture, us.bunchgrass_canopy_cover, us.forbs_canopy_cover,us.shrubs_canopy_cover,us.sodgrass_canopy_cover, us.basal_cover, us.rock_cover, us.litter_cover, us.cryptogams_cover, us.scenario_date
								   FROM user_scenarios us
								   JOIN climate_stations AS cs ON us.station_id = cs.station_id
								   JOIN soil_texture AS st ON us.soil_id = st.class_id
								   JOIN slope_shape AS ss ON us.slope_shape = ss.shape_id
								   WHERE us.scenario_id = "  .  $scenarioid  . ";");
								  
		return $query->row();
	}
	
	/**
	 * Retrieve all the inputs for a single scenario based on the given user id. 
	 * 
	 * @access	public
	 * @param	$scenarioid
	 * @return	a single row
	 */
	function get_single_scenario($scenarioid)
	{
		$query = $this->db->query("SELECT * FROM user_scenarios WHERE scenario_id = " . $scenarioid . ";");
		
		return $query->row();
	}

	/**
	 * Retrieve all the inputs for a single scenario based on the given user id. 
	 * 
	 * @access	public
	 * @param	$scenarioid
	 * @return	a single row
	 */
	function get_single_scenario_new($scenarioid)
	{
		$query = $this->db->query("SELECT * FROM user_scenarios WHERE scenario_id = " . $scenarioid . ";");
		
		return $query->row();
	}
	
	/**
	 * Deletes a scenario given a scenario ID.
	 * 
	 * Deletea a scenario based on a scenario ID.
	 *
	 * @access	public
	 * @param	N/A
	 * @return	void
	 */
	function delete_scenario($scenarioid)
	{
		$query = $this->db->query("DELETE FROM user_scenarios WHERE scenario_id = " . $scenarioid . ";");
		
		return 'Scenario Deleted.';
	}
	
	/**
	 * Deletes a scenario results (output) given a scenario ID
	 * 
	 * Deletea a scenario results (output) based on a scenario ID.
	 *
	 * @access	public
	 * @param	N/A
	 * @return	void
	 */
	function delete_scenario_results($scenarioid)
	{
		$query = $this->db->query("DELETE FROM scenario_output WHERE scenario_id = " . $scenarioid . ";");
		
		return 'Scenario Results Deleted.';
	}
	
	/**
	 * Inserts a new row to the scenario output table based on the scenario id.
	 *
	 * @access	public
	 * @param	N/A
	 * @return	void
	 */
	function insert_scenario_output($scenario_id, $eventArray)
	{
		$sql = "INSERT INTO scenario_output (scenario_id, avg_ppt, avg_runoff, avg_soilloss, avg_sedyield,
											 2yr_rp_rain, 2yr_rp_runoff, 2yr_rp_soilloss, 2yr_rp_sedyield,
											 5yr_rp_rain, 5yr_rp_runoff, 5yr_rp_soilloss, 5yr_rp_sedyield, 
											 10yr_rp_rain, 10yr_rp_runoff, 10yr_rp_soilloss, 10yr_rp_sedyield,
											 25yr_rp_rain, 25yr_rp_runoff, 25yr_rp_soilloss, 25yr_rp_sedyield,
											 50yr_rp_rain, 50yr_rp_runoff, 50yr_rp_soilloss, 50yr_rp_sedyield,
											 100yr_rp_rain, 100yr_rp_runoff, 100yr_rp_soilloss, 100yr_rp_sedyield,
											 slt_prob_negligible,slt_prob_acceptable,slt_prob_undesirable,slt_prob_unacceptable,
											 2yr_rp_rain_yt, 2yr_rp_runoff_yt, 2yr_rp_soilloss_yt, 2yr_rp_sedyield_yt,
											 5yr_rp_rain_yt, 5yr_rp_runoff_yt, 5yr_rp_soilloss_yt, 5yr_rp_sedyield_yt, 
											 10yr_rp_rain_yt, 10yr_rp_runoff_yt, 10yr_rp_soilloss_yt, 10yr_rp_sedyield_yt,
											 25yr_rp_rain_yt, 25yr_rp_runoff_yt, 25yr_rp_soilloss_yt, 25yr_rp_sedyield_yt,
											 50yr_rp_rain_yt, 50yr_rp_runoff_yt, 50yr_rp_soilloss_yt, 50yr_rp_sedyield_yt,
											 100yr_rp_rain_yt, 100yr_rp_runoff_yt, 100yr_rp_soilloss_yt, 100yr_rp_sedyield_yt) 
                VALUES (" .  $scenario_id . "," . $eventArray[0] .",". $eventArray[1] .",". $eventArray[2] .",". $eventArray[3] . 
				",". $eventArray[4] .",". $eventArray[5] . "," . $eventArray[6] .",". $eventArray[7] . 
				",". $eventArray[8] .",". $eventArray[9] . "," . $eventArray[10] . "," . $eventArray[11] .
				",". $eventArray[12] .",". $eventArray[13] . "," . $eventArray[14] . "," . $eventArray[15] .
				",". $eventArray[16] .",". $eventArray[17] . "," . $eventArray[18] . "," . $eventArray[19] .
				",". $eventArray[20] .",". $eventArray[21] . "," . $eventArray[22] . "," . $eventArray[23] .
				",". $eventArray[24] .",". $eventArray[25] . "," . $eventArray[26] . "," . $eventArray[27] . 
				",". $eventArray[28] .",". $eventArray[29] . "," . $eventArray[30] . "," . $eventArray[31] .
				",". $eventArray[32] .",". $eventArray[33] . "," . $eventArray[34] . "," . $eventArray[35] .
				",". $eventArray[36] .",". $eventArray[37] . "," . $eventArray[38] . "," . $eventArray[39] .
				",". $eventArray[40] .",". $eventArray[41] . "," . $eventArray[42] . "," . $eventArray[43] . 
				",". $eventArray[44] .",". $eventArray[45] . "," . $eventArray[46] . "," . $eventArray[47] .
				",". $eventArray[48] .",". $eventArray[49] . "," . $eventArray[50] . "," . $eventArray[51] .
				",". $eventArray[52] .",". $eventArray[53] . "," . $eventArray[54] . "," . $eventArray[55] . ")";
				
		$this->db->query($sql);
		//return $this->db->affected_rows(); 
	}
	
	/**
	 * Selects the average annual output Cligen values for each scenario for the entire period (300 years)
	 *
	 * @access	public
	 * @param	N/A
	 * @return	A single row with all of the variables averaged over the entire period
	 */
	function get_scenario_annual_avg($scenario_id)
	{
		$sql = "SELECT us.scenario_name,AVG(p_rain) AS p_rain, AVG(p_duration) as p_duration, AVG(q_runoff) AS q_runoff, AVG(q_duration) 
				AS q_duration, AVG(sed_yield) AS sed_yield, AVG(soil_loss) AS soil_loss 
				FROM scenario_output AS so JOIN user_scenarios AS us ON so.scenario_id = us.scenario_id
				WHERE so.scenario_id = " . $scenario_id . " GROUP BY us.scenario_name;";
		$query = $this->db->query($sql);
		return $query->row();
	}
	
	/**
	 * Selects the maximum yearly output Cligen values for a particular scenario.
	 *
	 * @access	public
	 * @param	N/A
	 * @return	A query resultset with the same number of rows as the number of years simulated (300 years)
	 */
	function get_scenario_annual_max($scenario_id)
	{
		$sql = "SELECT us.scenario_name, event_year, MAX(p_rain) AS p_rain, MAX(p_duration) as p_duration, MAX(q_runoff) AS q_runoff, MAX(q_duration)
				AS q_duration, MAX(sed_yield) AS sed_yield, MAX(soil_loss) AS soil_loss FROM scenario_output AS so
				JOIN user_scenarios AS us ON so.scenario_id = us.scenario_id
				WHERE so.scenario_id = "  . $scenario_id . "GROUP BY us.scenario_name, event_year;";
		$query = $this->db->query($sql);
		return $query;
	}
	
	/**
	 * Returns an array with return periods for 2,10,25,50, and 100 years.  This is done by selecting the maximum
	 * value for each year for each variable first and calculating the return period based on a 300 year simulation.
	 * The equation used is as follows:
	 *                                    Tr = n+1/r   where  n = 300 and r is the ranking value
	 *
	 * @access	public
	 * @param	N/A
	 * @return	An array containg the return periods for a particular variable.
	 * TODO: Remove if not being used.
	 */
	function get_variable_return_periods_by_scenario($variable, $scenario_id)
	{
		$sql = "SELECT MAX(" . $variable . ") AS " . $variable . " FROM scenario_output
				WHERE scenario_id = " . $scenario_id . " GROUP BY event_year ORDER BY " . $variable . " DESC;";
				
		$query = $this->db->query($sql);
		
		$i = 1;
		foreach ($query->result() as $row){
			switch($i){
			case 3: // 100 year
 				$returnPeriodArray[0] =  $row->$variable;
				break;
			case 6: // 50 year
				$returnPeriodArray[1] =  $row->$variable;
				break;
			case 12: // 25 year
				$returnPeriodArray[2] =  $row->$variable;
				break;
			case 30: // 10 year
				$returnPeriodArray[3] =  $row->$variable;
				break;
			case 150: // 2 year
				$returnPeriodArray[4] =  $row->$variable;
				break 2;
			}
			$i++;
		}
		// return the return period array in reverse in order to render it in ascending order in the graph
		return array_reverse($returnPeriodArray,false);
	}
	
	/**
	 * Finds the the annual average for the entire period for a particual variable.
	 *
	 * @access	public
	 * @param	$scenario_id - the scenario identifier
	 *          $unitsArray  - the array of units used for the array of values
	 * @return	An array containg the return periods for a particular variable.
	 */
	function get_variable_annual_avg($scenario_id,$unitsArray)
	{	
		if($unitsArray[1] == 'inches'){
			// convert from mm to inches and Mg/ha to lb/ac
			$inches_cnv = 0.0393700787;
			//$lbs_per_acre_cnv = 892.179122;
			$ton_per_acre_cnv = 0.446;
			$sql = $sql = "SELECT (scenario_output.avg_ppt * $inches_cnv) AS rainYrlyAvg, (scenario_output.avg_runoff * $inches_cnv) AS runoffYrlyAvg, (scenario_output.avg_soilloss * $ton_per_acre_cnv) AS soilLossYrlyAvg, (scenario_output.avg_sedyield * $ton_per_acre_cnv) AS sedYieldYrlyAvg 
					FROM scenario_output
					WHERE scenario_id = " . $scenario_id . ";";
		}
		else{
			// convert from kg/ha to Mg/ha (short ton)
			$sql = "SELECT scenario_output.avg_ppt AS rainYrlyAvg, scenario_output.avg_runoff AS runoffYrlyAvg, (scenario_output.avg_soilloss) AS soilLossYrlyAvg, (scenario_output.avg_sedyield) AS sedYieldYrlyAvg 
					FROM scenario_output
					WHERE scenario_id = " . $scenario_id . ";";
		}
					
		$annual_avg_query = $this->db->query($sql);
		$annual_avg_array = $annual_avg_query->row_array();
		return $annual_avg_array;
	}
	
	/**
	 * Returns an array with return periods for 2,10,25,50, and 100 years.  This is done by selecting the maximum
	 * value for p_rain, q_runoff, sed_yield, and soil_loss
	 * The equation used is as follows:
	 *                                    Tr = n+1/r   where  n = 300 and r is the ranking value
	 *
	 * @access	public
	 * @param	N/A
	 * @return	An array containg the return periods for a particular variable.
	 */
	function get_variables_return_periods($scenario_id,$unitsArray)
	{
		// create queries for return period calculation. First query is based on p_prain ordering.  Second query is based on q_runoff ordering
		// The last two queries are independently ordered.
		
		// select the Cligen based return periods and convert to selected units
		if($unitsArray[1] == 'inches'){
			// convert from mm to inches and Mg/ha to lb/ac
			$inches_cnv = 0.0393700787;
			//$lbs_per_acre_cnv = 892.179122;
			$ton_per_acre_cnv = 0.446;
			$rp_SQL = "SELECT (rp_ppt_tbl.2yr_rp_rain * $inches_cnv) AS 2yr_rp_rain,(rp_ppt_tbl.5yr_rp_rain * $inches_cnv) AS 5yr_rp_rain,(rp_ppt_tbl.10yr_rp_rain * $inches_cnv) AS 10yr_rp_rain,(rp_ppt_tbl.25yr_rp_rain * $inches_cnv) AS 25yr_rp_rain,(rp_ppt_tbl.50yr_rp_rain * $inches_cnv) AS 50yr_rp_rain,(rp_ppt_tbl.100yr_rp_rain * $inches_cnv) AS 100yr_rp_rain,
							(2yr_rp_runoff * $inches_cnv) AS 2yr_rp_runoff,(5yr_rp_runoff * $inches_cnv) AS 5yr_rp_runoff,(10yr_rp_runoff * $inches_cnv) AS 10yr_rp_runoff,(25yr_rp_runoff * $inches_cnv) AS 25yr_rp_runoff,(50yr_rp_runoff * $inches_cnv) AS 50yr_rp_runoff,(100yr_rp_runoff * $inches_cnv) AS 100yr_rp_runoff,
							(2yr_rp_soilloss * $ton_per_acre_cnv) AS 2yr_rp_soilloss,(5yr_rp_soilloss * $ton_per_acre_cnv) AS 5yr_rp_soilloss,(10yr_rp_soilloss * $ton_per_acre_cnv) AS 10yr_rp_soilloss,(25yr_rp_soilloss * $ton_per_acre_cnv) AS 25yr_rp_soilloss,(50yr_rp_soilloss * $ton_per_acre_cnv) AS 50yr_rp_soilloss,(100yr_rp_soilloss * $ton_per_acre_cnv) AS 100yr_rp_soilloss,
							(2yr_rp_sedyield * $ton_per_acre_cnv) AS 2yr_rp_sedyield,(5yr_rp_sedyield * $ton_per_acre_cnv) AS 5yr_rp_sedyield,(10yr_rp_sedyield * $ton_per_acre_cnv) AS 10yr_rp_sedyield,(25yr_rp_sedyield * $ton_per_acre_cnv) AS 25yr_rp_sedyield,(50yr_rp_sedyield * $ton_per_acre_cnv) AS 50yr_rp_sedyield,(100yr_rp_sedyield * $ton_per_acre_cnv) AS 100yr_rp_sedyield
						FROM scenario_output,
							 (SELECT 2yr_rp_rain,5yr_rp_rain,10yr_rp_rain,25yr_rp_rain,50yr_rp_rain,100yr_rp_rain FROM rp_300_yr_rain JOIN user_scenarios AS us_tbl ON rp_300_yr_rain.station_id = us_tbl.station_id
							  WHERE us_tbl.scenario_id = " .  $scenario_id . ") AS rp_ppt_tbl
						WHERE scenario_id = " .  $scenario_id . ";";
		}
		else{
			// convert from kg/ha to Mg/ha (short ton)
			$rp_SQL = "SELECT rp_ppt_tbl.2yr_rp_rain,rp_ppt_tbl.5yr_rp_rain,rp_ppt_tbl.10yr_rp_rain,rp_ppt_tbl.25yr_rp_rain,rp_ppt_tbl.50yr_rp_rain,rp_ppt_tbl.100yr_rp_rain,
							2yr_rp_runoff,5yr_rp_runoff,10yr_rp_runoff,25yr_rp_runoff,50yr_rp_runoff,100yr_rp_runoff,
							2yr_rp_soilloss,5yr_rp_soilloss,10yr_rp_soilloss,25yr_rp_soilloss,50yr_rp_soilloss,100yr_rp_soilloss,
							2yr_rp_sedyield,5yr_rp_sedyield,10yr_rp_sedyield,25yr_rp_sedyield,50yr_rp_sedyield,100yr_rp_sedyield
						FROM scenario_output,
							 (SELECT 2yr_rp_rain,5yr_rp_rain,10yr_rp_rain,25yr_rp_rain,50yr_rp_rain,100yr_rp_rain FROM rp_300_yr_rain JOIN user_scenarios AS us_tbl ON rp_300_yr_rain.station_id = us_tbl.station_id
							  WHERE us_tbl.scenario_id = " .  $scenario_id . ") AS rp_ppt_tbl
						WHERE scenario_id = " .  $scenario_id . ";";
		}
				
		$rp_Query = $this->db->query($rp_SQL);
		$rp_Query_Result_Array = $rp_Query->row_array();
		
		return $rp_Query_Result_Array;	
	}


	/**
	 * Return the calculated risk aasessment values for a scenario
	 *
	 * @access	public
	 * @param	N/A
	 * @return	An array containg the return periods for a particular variable.
	 */
	function get_risk_assessment_values($scenario_id)
	{
		// query for the risk assessment probabilities for a scenario
		$ra_SQL = "SELECT slt_prob_negligible, slt_prob_acceptable,slt_prob_undesirable,slt_prob_unacceptable 
				   FROM scenario_output 
				   WHERE scenario_id = " . $scenario_id . ";";
				
		$ra_Query = $this->db->query($ra_SQL);
		$ra_Query_Result_Array = $ra_Query->row_array();
		
		return $ra_Query_Result_Array;
	}


	function get_all_scenario_outputs($scenario_id, $unitsArray){
		if($unitsArray[1] == 'inches'){
			// convert from mm to inches and Mg/ha to lb/ac
			$inches_cnv = 0.0393700787;
			//$lbs_per_acre_cnv = 892.179122;
			$ton_per_acre_cnv = 0.446;
			$sql = $sql = "SELECT user_scenarios.scenario_name AS scenario_name, (scenario_output.avg_ppt * $inches_cnv) AS rainYrlyAvg, (scenario_output.avg_runoff * $inches_cnv) AS runoffYrlyAvg, (scenario_output.avg_soilloss * $ton_per_acre_cnv) AS soilLossYrlyAvg, (scenario_output.avg_sedyield * $ton_per_acre_cnv) AS sedYieldYrlyAvg,
							(2yr_rp_rain * $inches_cnv) AS 2yr_rp_rain,(5yr_rp_rain * $inches_cnv) AS 5yr_rp_rain,(10yr_rp_rain * $inches_cnv) AS 10yr_rp_rain,(25yr_rp_rain * $inches_cnv) AS 25yr_rp_rain,(50yr_rp_rain * $inches_cnv) AS 50yr_rp_rain,(100yr_rp_rain * $inches_cnv) AS 100yr_rp_rain,
							(2yr_rp_runoff * $inches_cnv) AS 2yr_rp_runoff,(5yr_rp_runoff * $inches_cnv) AS 5yr_rp_runoff,(10yr_rp_runoff * $inches_cnv) AS 10yr_rp_runoff,(25yr_rp_runoff * $inches_cnv) AS 25yr_rp_runoff,(50yr_rp_runoff * $inches_cnv) AS 50yr_rp_runoff,(100yr_rp_runoff * $inches_cnv) AS 100yr_rp_runoff,
							(2yr_rp_soilloss * $ton_per_acre_cnv) AS 2yr_rp_soilloss,(5yr_rp_soilloss * $ton_per_acre_cnv) AS 5yr_rp_soilloss,(10yr_rp_soilloss * $ton_per_acre_cnv) AS 10yr_rp_soilloss,(25yr_rp_soilloss * $ton_per_acre_cnv) AS 25yr_rp_soilloss,(50yr_rp_soilloss * $ton_per_acre_cnv) AS 50yr_rp_soilloss,(100yr_rp_soilloss * $ton_per_acre_cnv) AS 100yr_rp_soilloss,
							(2yr_rp_sedyield * $ton_per_acre_cnv) AS 2yr_rp_sedyield,(5yr_rp_sedyield * $ton_per_acre_cnv) AS 5yr_rp_sedyield,(10yr_rp_sedyield * $ton_per_acre_cnv) AS 10yr_rp_sedyield,(25yr_rp_sedyield * $ton_per_acre_cnv) AS 25yr_rp_sedyield,(50yr_rp_sedyield * $ton_per_acre_cnv) AS 50yr_rp_sedyield,(100yr_rp_sedyield * $ton_per_acre_cnv) AS 100yr_rp_sedyield,
							slt_prob_negligible, slt_prob_acceptable,slt_prob_undesirable, slt_prob_unacceptable,
							(2yr_rp_rain_yt * $inches_cnv) AS 2yr_rp_rain_yt,(5yr_rp_rain_yt * $inches_cnv) AS 5yr_rp_rain_yt,(10yr_rp_rain_yt * $inches_cnv) AS 10yr_rp_rain_yt,(25yr_rp_rain_yt * $inches_cnv) AS 25yr_rp_rain_yt,(50yr_rp_rain_yt * $inches_cnv) AS 50yr_rp_rain_yt,(100yr_rp_rain_yt * $inches_cnv) AS 100yr_rp_rain_yt,
							(2yr_rp_runoff_yt * $inches_cnv) AS 2yr_rp_runoff_yt,(5yr_rp_runoff_yt * $inches_cnv) AS 5yr_rp_runoff_yt,(10yr_rp_runoff_yt * $inches_cnv) AS 10yr_rp_runoff_yt,(25yr_rp_runoff_yt * $inches_cnv) AS 25yr_rp_runoff_yt,(50yr_rp_runoff_yt * $inches_cnv) AS 50yr_rp_runoff_yt,(100yr_rp_runoff_yt * $inches_cnv) AS 100yr_rp_runoff_yt,
							(2yr_rp_soilloss_yt * $ton_per_acre_cnv) AS 2yr_rp_soilloss_yt,(5yr_rp_soilloss_yt * $ton_per_acre_cnv) AS 5yr_rp_soilloss_yt,(10yr_rp_soilloss_yt * $ton_per_acre_cnv) AS 10yr_rp_soilloss_yt,(25yr_rp_soilloss_yt * $ton_per_acre_cnv) AS 25yr_rp_soilloss_yt,(50yr_rp_soilloss_yt * $ton_per_acre_cnv) AS 50yr_rp_soilloss_yt,(100yr_rp_soilloss_yt * $ton_per_acre_cnv) AS 100yr_rp_soilloss_yt,
							(2yr_rp_sedyield_yt * $ton_per_acre_cnv) AS 2yr_rp_sedyield_yt,(5yr_rp_sedyield_yt * $ton_per_acre_cnv) AS 5yr_rp_sedyield_yt,(10yr_rp_sedyield_yt * $ton_per_acre_cnv) AS 10yr_rp_sedyield_yt,(25yr_rp_sedyield_yt * $ton_per_acre_cnv) AS 25yr_rp_sedyield_yt,(50yr_rp_sedyield_yt * $ton_per_acre_cnv) AS 50yr_rp_sedyield_yt,(100yr_rp_sedyield_yt * $ton_per_acre_cnv) AS 100yr_rp_sedyield_yt
					FROM scenario_output JOIN user_scenarios ON user_scenarios.scenario_id = scenario_output.scenario_id
					WHERE user_scenarios.scenario_id = " . $scenario_id . ";";
		}
		else{
			// convert from kg/ha to Mg/ha (short ton)
			$sql = "SELECT user_scenarios.scenario_name AS scenario_name, avg_ppt AS rainYrlyAvg, avg_runoff AS runoffYrlyAvg, (avg_soilloss) AS soilLossYrlyAvg, (avg_sedyield) AS sedYieldYrlyAvg,
					2yr_rp_rain,5yr_rp_rain,10yr_rp_rain,25yr_rp_rain,50yr_rp_rain,100yr_rp_rain,
					2yr_rp_runoff,5yr_rp_runoff,10yr_rp_runoff,25yr_rp_runoff,50yr_rp_runoff,100yr_rp_runoff,
					2yr_rp_soilloss,5yr_rp_soilloss,10yr_rp_soilloss,25yr_rp_soilloss,50yr_rp_soilloss,100yr_rp_soilloss,
					2yr_rp_sedyield,5yr_rp_sedyield,10yr_rp_sedyield,25yr_rp_sedyield,50yr_rp_sedyield,100yr_rp_sedyield,
					slt_prob_negligible, slt_prob_acceptable,slt_prob_undesirable,slt_prob_unacceptable,
					2yr_rp_rain_yt,5yr_rp_rain_yt,10yr_rp_rain_yt,25yr_rp_rain_yt,50yr_rp_rain_yt,100yr_rp_rain_yt,
					2yr_rp_runoff_yt,5yr_rp_runoff_yt,10yr_rp_runoff_yt,25yr_rp_runoff_yt,50yr_rp_runoff_yt,100yr_rp_runoff_yt,
					2yr_rp_soilloss_yt,5yr_rp_soilloss_yt,10yr_rp_soilloss_yt,25yr_rp_soilloss_yt,50yr_rp_soilloss_yt,100yr_rp_soilloss_yt,
					2yr_rp_sedyield_yt,5yr_rp_sedyield_yt,10yr_rp_sedyield_yt,25yr_rp_sedyield_yt,50yr_rp_sedyield_yt,100yr_rp_sedyield_yt
					FROM scenario_output JOIN user_scenarios ON user_scenarios.scenario_id = scenario_output.scenario_id
					WHERE user_scenarios.scenario_id = " . $scenario_id . ";";
		}
					
		$all_output_query = $this->db->query($sql);
		$all_output_query_results = $all_output_query->row_array();
		return $all_output_query_results;
	}


	/**
	 * Gets precipitation return periods for a station based on a scenario_id
	 * Note: this funciton is used to add the average ppt value to the Summary output file
	 *
	 * @access	public
	 * @param	$scnearioid
	 */
	function get_ppt_station_ppt_return_periods($scenario_id)
	{
		$rp_SQL = "SELECT 2yr_rp_rain,5yr_rp_rain,10yr_rp_rain,25yr_rp_rain,50yr_rp_rain,100yr_rp_rain FROM rp_300_yr_rain JOIN user_scenarios AS us_tbl ON rp_300_yr_rain.station_id = us_tbl.station_id
							  WHERE us_tbl.scenario_id = " .  $scenario_id . ";";

		$rp_Query = $this->db->query($rp_SQL);
		$rp_Query_Result_Array = $rp_Query->row_array();
		
		return $rp_Query_Result_Array;
	}

	/**
	 * Gets average precipitation for a station based on a scenario_id
	 * Note: this funciton is used to add the ppt return period values to the Summary output file
	 *
	 * @access	public
	 * @param	$scnearioid
	 */
	function get_station_ppt_average($scenario_id)
	{
		$avg_sql = "SELECT avg_tbl.avg_rain AS rainYrlyAvg FROM avg_300_yr_rain AS avg_tbl JOIN user_scenarios AS us_tbl ON avg_tbl.station_id = us_tbl.station_id
					              		WHERE us_tbl.scenario_id = " . $scenario_id . ";";

		$annual_avg_query = $this->db->query($avg_sql);
		$annual_avg_array = $annual_avg_query->row_array();
		return $annual_avg_array;
	}

	
	/**
	 * Gets the units used for a scenario. 
	 *
	 * @access	public
	 * @param	$scnearioid
	 * @return	A string representing the units used: english or metric
	 */
	function getScenarioUnits($scenarioid)
	{
		$sql = "SELECT units FROM user_scenarios WHERE scenario_id = " . $scenarioid . ";";
		
		$query = $this->db->query($sql);
		return $query->row();
	}
	
	/************************************************************************/
	/*************************   SESSION RELATED METHODS ********************/
	/************************************************************************/	
	/**
	 * Add a new user. 
	 *
	 * Inserts a new user to the users table.
	 * 
	 * @access	public
	 * @param	N/A
	 * @return	Result Set
	 */
	function insert_user($user)
	{
		$sql = "INSERT INTO users (name, last_name, username, password, email, usage_description, date_added) 
                VALUES ('". $user['name'] ."','". $user['last_name'] ."','". $user['username'] ."','". do_hash($user['password'], 'md5')
				."','". $user['email'] ."','". $user['usage_description'] . "','" . date( 'Y-m-d H:i:s') . "')";

		$this->db->query($sql);
		
		return $this->db->affected_rows(); 		
	}
	
	/**
	 * Finds user in DB
	 * 
	 * Find out if user exists in the DB based on a username and password. 
	 *
	 * @access	public
	 * @param	N/A
	 * @return	Result Set
	 */
	function find_user($user)
	{
		$query = $this->db->query("SELECT user_id,name,last_name FROM users WHERE username = '" . $user['username'] . "' AND password = '" . do_hash($user['password'], 'md5') . "';");
        return $query->result();		
	}
	
	/**
	 * Finds user in DB by his email.
	 * 
	 * Find out if user exists in the DB based on an email. 
	 *
	 * @access	public
	 * @param	N/A
	 * @return	Result Set
	 */
	function find_user_by_email($email)
	{
		$query = $this->db->query("SELECT username, name,last_name FROM users WHERE email = '" . $email . "';");
        return $query->result();		
	}
	
	/**
	 * Update user's password.
	 * 
	 * Find the user based on an email and update the password based on a random one.
	 *
	 * @access	public
	 * @param	N/A
	 * @return	void
	 */
	function update_user_password($email, $newpassword)
	{
		$query = $this->db->query("UPDATE users SET password = '" . do_hash($newpassword, 'md5') . "' WHERE email = '" . $email . "';");
	}
	
	/**
	 * Verifies the current password for the user.
	 *
	 * @access	public
	 * @param	$user_id -  the session's user ID
	 * @param   @old_password - the user's current password
	 * @return	int  - the number of rows identified
	 */
	function verfy_password($user_id, $current_password)
	{
		$query = $this->db->query("SELECT * FROM users WHERE user_id = " . $user_id ." and password = '" . do_hash($current_password, 'md5') . "';");	
		return $query->num_rows();
	}
	/**
	 * Save changes to the account profile for the user
	 * User is able to reset passoword using this function.
	 *
	 * @access	public
	 * @param	N/A
	 * @return	Result Set
	 */
	function update_user_account($user_id, $new_password, $modify_inputs_flag, $detailed_output_flag, $model_version)
    {
       // $query = $this->db->query("SELECT * FROM slope_steepness;");
	   $newPassowordUpdate = "";
	   // if the password needs to be updated
	   if($new_password != "")
	   		$newPassowordUpdate = "password = '" . do_hash($new_password, 'md5') . "', ";
		$query = $this->db->query("UPDATE users SET " . $newPassowordUpdate . "modify_parameter_file_flag = " . $modify_inputs_flag . ", detailed_output_flag = " . $detailed_output_flag . ", model_version = " . $model_version . " WHERE user_id = " . $user_id . ";");
    }
	
	/**
	 * Get user account settings for parameter file modification and details model output
	 *
	 * @access	public
	 * @param	N/A
	 * @return	Result Set
	 */
	function get_user_account_settings($user_id)
    {
       // $query = $this->db->query("SELECT * FROM slope_steepness;");
		$query = $this->db->query("SELECT modify_parameter_file_flag, detailed_output_flag, model_version FROM users WHERE user_id = " . $user_id . ";");
		return $query->row();
    }
	
	/*******************************************************************************/
	/*************************   CLIMATE STATION RELATED METHODS *******************/
	/*******************************************************************************/
	/**
	 * Get the list of states. 
	 *
	 * @access	public
	 * @param	N/A
	 * @return	Result Set
	 */
	function get_all_states()
    {
        $query = $this->db->query("SELECT * FROM states ORDER BY state_name ;");
        return $query->result();
    }
	
	/**
	 * Get all the climate stations names and ids for a specific state. 
	 *
	 * @access	public
	 * @param	N/A
	 * @return	Result Set
	 */
	function get_climate_stations($state)
    {
        $query = $this->db->query("SELECT station,station_id FROM climate_stations WHERE state = '" . $state . "' ORDER BY station;");
        return $query->result();
    }

	/**
	 * Get Climate Station Coordinates
	 *
	 * Get all climate station coordinates corresponding to current state.
	 *
	 * @access	public
	 * @param	N/A
	 * @return	Result Set
	 */
	function get_all_climatestation_coordinates($state)
    {
        $query = $this->db->query("SELECT climate_stations.lat, climate_stations.longitude,climate_stations.station, climate_stations.station_id, climate_stations.elevation, avg_300_yr_rain.avg_rain, avg_300_yr_rain.jan,avg_300_yr_rain.feb,avg_300_yr_rain.mar,avg_300_yr_rain.apr,avg_300_yr_rain.may,avg_300_yr_rain.jun,avg_300_yr_rain.jul,avg_300_yr_rain.aug,avg_300_yr_rain.sep,avg_300_yr_rain.oct,avg_300_yr_rain.nov,avg_300_yr_rain.dec
						           FROM climate_stations, avg_300_yr_rain 
						           WHERE state = '" . $state . "' AND climate_stations.station_id = avg_300_yr_rain.station_id;");
        return $query->result();
    }
	
	/**
	 * Get Climate Station Coordinates
	 *
	 * Get the climate station coordinated corresponding to current state and station name.
	 *
	 * @access	public
	 * @param	N/A
	 * @return	Result Set
	 */
	function get_climatestation_coordinates($station)
    {
        $query = $this->db->query("SELECT lat, longitude,station FROM climate_stations WHERE station_id = " . $station . ";");
        return $query->row();
    }
	
	/**
	 * Get Climate Station Zoom Levels
	 *
	 * Get the zoom levels for each state.
	 *
	 * @access	public
	 * @param	N/A
	 * @return	Result Set
	 */
	function get_state_zoom_level($state)
    {
        $query = $this->db->query("SELECT latitude,longitude,zoom FROM states WHERE state_id = '" . $state . "';");
        return $query->row();
    }
	
	/**
	 * Get all climate output from the Cligen output table.
	 *
	 * @access public
	 * @param  $stationid - The 6 digit (unique to each state) climate station id
	 * @param  N/A
	 * @return Result Set
	 * NOTE: This function is not being used as the cligen output is extracted from the file system. 
	 */
	function get_cligen_output($stationid)
    {
        $query = $this->db->query("SELECT * FROM cligen_output WHERE station_id = " . $stationid . " ORDER BY year,month,day;");
        return $query->result();
    }
	
	/************************************************************************/
	/*************************   SOIL RELATED METHODS ***********************/
	/************************************************************************/
	/**
	 * Get Last Ten Soils
	 *
	 * Gets the first 10 soils.
	 *
	 * @access	public
	 * @param	N/A
	 * @return	Result Set
	 */
	function get_last_ten_soils()
    {
        $query = $this->db->get('soils', 10);
        return $query->result();
    }
	
	/**
	 * Get Soils
	 *
	 * Get soil types corresponding to current state.
	 *
	 * @access	public
	 * @param	N/A
	 * @return	Result Set
	 */
	function get_state_soils($state)
    {
        $query = $this->db->query("SELECT name FROM state_soils WHERE state = '" . $state . "';");
        return $query->result();
    }
	
	/**
	 * Get Soil Textures
	 *
	 * Get soil texture class names.
	 *
	 * @access	public
	 * @param	N/A
	 * @return	Result Set
	 */
	function get_soil_textures()
    {
        $query = $this->db->query("SELECT * FROM soil_texture;");
        return $query->result();
    }
	
	/**
	 * Get Soil Texture information
	 *
	 * Get soil texture class names.
	 *
	 * @access	public
	 * @param	class_id - The id of the texture class
	 * @return	Row
	 */
	function get_soil_texture($class_id)
    {
        $query = $this->db->query("SELECT * FROM soil_texture WHERE class_id = " . $class_id . ";");
        return $query->row();
    }
	
	/**
	 * Get Soil Texture information
	 *
	 * Get soil texture class names.
	 *
	 * @access	public
	 * @param	class_id - The id of the texture class
	 * @return	Row
	 */
	function get_soil_texture_sand_and_clay($class_id)
    {
        $query = $this->db->query("SELECT mean_sand,mean_clay FROM soil_texture WHERE class_id = " . $class_id . ";");
        return $query->row();
    }
	
	/**
	 * Get Soil Moisture values
	 *
	 * @access	public
	 * @param	N/A
	 * @return	Result Set
	 */
	function get_soil_moisture_values()
    {
        $query = $this->db->query("SELECT * FROM soil_moisture;");
        return $query->result();
    }
	
	/************************************************************************/
	/*************************   SLOPE RELATED METHODS **********************/
	/************************************************************************/
	
	/**
	 * Get Slope Shapes
	 *
	 * Gets a list of slope shape descriptions.
	 *
	 * @access	public
	 * @param	N/A
	 * @return	Result Set
	 */
	function get_slope_shape()
    {
        $query = $this->db->query("SELECT * FROM slope_shape;");
        return $query->result();
    }
	
	/************************************************************************/
	/**************** VEGETATION COVER RELATED METHODS **********************/
	/************************************************************************/
	/**
	 * Get Vegetation Cover
	 *
	 * Gets a list of available vegetation cover names and ids.
	 *
	 * @access	public
	 * @param	N/A
	 * @return	Result Set
	 */
	function get_vegetation_communities()
    {
        $query = $this->db->query("SELECT * FROM vegetation v WHERE v.veg_community_id != 5 ORDER BY veg_community_name;");
        return $query->result();
    }


	/************************************************************************/
	/********************* MISCELLANEOUS  METHODS ***************************/
	/************************************************************************/
	/**
	 * This is a miscellaneous function implemented to find out which users exist in legacy RHEM that are not in
	 * v2 of RHEM.  The ending list will be used to add the users to the v2 RHEM users table.
	 * 
	 */
    function find_unique_users()
    {
    	$newEmailsToAdd = array();

    	$queryLegacy = $this->db->query("SELECT * FROM legacy_rhem_users;");
    	$query = $this->db->query("SELECT * FROM users;");

    	$legacyEmails = array();
    	foreach ($queryLegacy->result() as $rowLegacy){
    		array_push($legacyEmails, array(0=>$rowLegacy->name, 1=>$rowLegacy->last_name,2=>$rowLegacy->username, 3=>$rowLegacy->password,4=>$rowLegacy->email,5=>$rowLegacy->usage_description));
    	};

    	$newEmails = array();
    	foreach ($query->result() as $row){
    		array_push($newEmails, $row->email);
    	};

    	for($i = 0; $i<count($legacyEmails);$i++)
    	{
    		if(array_search($legacyEmails[$i][4], $newEmails) == FALSE and $legacyEmails[$i][4] != "gerardo.armendariz@ars.usda.gov"){
    				/*$user['name'] = $legacyEmails[$i][0];
					$user['last_name'] = $legacyEmails[$i][1];
					$user['username'] = $legacyEmails[$i][2];
					$user['password'] = $legacyEmails[$i][3];
					$user['email'] = $legacyEmails[$i][4];
					$user['usage_description'] = $legacyEmails[$i][5];
					
					$this->Rhemmodel->insert_user($user);*/

				//array_push($newEmailsToAdd, $legacyEmails[$i][4]);
			}	
    	}

    	return $newEmails;
    }
}
?>