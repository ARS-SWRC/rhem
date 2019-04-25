<?php
/**
 * This controller is used define the climate conditions for an area. 
 *
 * @access	public
 * @return	void
 */
class Defineclimate extends CI_Controller {
	function __construct()
    {
		parent::__construct();
		
		$this->load->library('session');
    }
	
	function index()
	{	
	}
	
	/**
	 * Gets all available climate statins based on the selected state
	 *
	 * @access	public
	 * @return	void
	 */
	function getclimatestations()
	{
		if($this->session->userdata('is_logged_in') == false)
			echo 'session_expired';
		else{
			$state = $_POST['stateQueryString']; 
			$stations = $this->Rhemmodel->get_climate_stations($state);
			
			echo '<option value="">-----</option>';
			foreach($stations as $item):
				echo '<option value="'. $item->station_id . '" ';
				if($this->session->userdata('climatestationid') == $item->station_id)
					echo 'selected="selected"';
				echo '>'; 
				echo ucwords(strtolower($item->station)) . '</option>';
			 endforeach;
		}
	}	
	
	/**
	 * Gets all available soils based on the selected state
	 *
	 * @access	public
	 * @return	void
	 */
	function getsoils()
	{
		$state = $_POST['stateQueryString']; 
	    $soils = $this->Rhemmodel->get_state_soils($state);
		
		foreach($soils as $item):
		 	echo '<option value="' . ucwords(strtolower($item->name)) . '">' . ucwords(strtolower($item->name)) . '</option>';
		 endforeach;
	}
	
	/**
	 * Get all available climate stations 
	 *
	 * @access	public
	 * @return	void
	 */
	function showstateclimatestations($state)
	{
		if($this->session->userdata('is_logged_in') == false)
			echo 'session_expired';
		else{
			$climatestations = $this->Rhemmodel->get_all_climatestation_coordinates($state);
			
			 echo json_encode($climatestations);
		}
	}
	
	/**
	 * Retrieves the zoom level adequate for a state.
	 *
	 * @access	public
	 * @return	void
	 * @TODO    make this function return the results as JSON
	 */
	function zoomtostate($state)
	{
		$zoomlevelRow = $this->Rhemmodel->get_state_zoom_level($state);
		echo $zoomlevelRow->latitude . ":" . $zoomlevelRow->longitude . ":" . $zoomlevelRow->zoom;
	}
	
	/**
	 * Get climate station coordinates based on the selected state and station.
	 *
	 * @access	public
	 * @return	void
	 * @TODO    make this function return the results as JSON
	 */
	function getclimatestationcoordinates($stationid)
	{
		if($this->session->userdata('is_logged_in') == false)
			echo 'session_expired';
		else{
			$climatestation = $this->Rhemmodel->get_climatestation_coordinates($stationid);
		 	echo ucwords(strtolower($climatestation->station)) . ":" . $climatestation->lat . ":" . $climatestation->longitude;
		}
    }
}
?>