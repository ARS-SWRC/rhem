<?php
/**
 * This controller is used to list, define, delete, and check if a scenario exists. 
 *
 * @access	public
 * @return	void
 */
class Analytics extends CI_Controller {
	
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
		/*
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
		}*/

		echo "PRUEBA";
	}


	/**
	 * Checks if the user is currently logged in. 
	 *
	 * @access	public
	 * @return	void
	 */
	function getAllScenarioLocations()
	{
		/*
		SELECT us.state_id, us.station_id, cs.lat, cs.longitude
		FROM user_scenarios us
		JOIN climate_stations AS cs ON us.station_id = cs.station_id
		WHERE us.state_id <> "INL" AND us.state_id <> "ITL";
		*/
	}
	
	/**
	 * Prints all of the available of th user scenarios as a table provided an array of scenarios.
	 *
	 * @access	public
	 * @return	void
	 */
	function getUniqueScenarioStates()
	{
		/*
	     SELECT DISTINCT us.state_id
		FROM user_scenarios us;
		*/
	}


}