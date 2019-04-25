<!-- NOTE: This controller has been renamed to editPARfile -->

<?php
/**
 * This controller is used to define a new scenario or open an existing one. 
 *
 * @access	public
 * @return	void
 */
class Editsoil extends CI_Controller {
	
	/* This defines a constructor for the current controller */
	function __construct()
    {
		parent::__construct();
		
		$this->load->library('session');
		
		// load custom errors class
		$this->load->library('Errors');
		// load plugins
		$this->load->helper('html2text');
		// global variables
		$this->data = array();
    }
	
	/**
	 * This is the index page for the first step of the RHEM process.  Provides	 messages based
	 * on user authentication state. 
	 *
	 * @access	public 
	 * @return	void
	 */
	function open($modifiedPARfileflag)
	{
		// check if user is logged in before retreiving the user's scenarios
		if($this->session->userdata('is_logged_in') == true)
		{
			// open the soil input file for the scenario used as a template only if the user has not yet modified this file, else open the modified file
			if($modifiedPARfileflag == "false")
				$fileName = "scenario_input_" . $this->session->userdata('user_id') . "_" . $this->session->userdata('scenarioid') . ".par";
			else if($modifiedPARfileflag == "true")
				$fileName = "scenario_input_" . $this->session->userdata('user_id') . "_(m).par";
				
			$file=fopen(OUTPUT_FOLDER . $fileName, "r") or exit("Unable to open file!");;
			$fileString = '';
			
			$i = 0; 
			while(!feof($file))
			{
				$line = fgets($file);
				
				if($line != '')
				{	 
					// split the parameter for all of the parameters except the parent nodes
					if($i != 0 or $i != 1 or $i != 2 or $i != 3 or $i != 10 or $i != 11 or $i != 39){
						$params = explode('=',$line);
					}

					// process parent tags first
					if($i == 0){
						$currentScenarioName = $this->session->userdata('scenarioname');
						$fileString = $fileString  . "! Parameter file for scenario: " . $currentScenarioName . ' (manually modified)<br/>';
					}
					else if($i == 1){
						// soil log file
						$timestr = date("F j, Y, g:i a");
						$fileString = $fileString  . "! Date built: " . $timestr .  " (Version ". RHEM_VERSION . ') <br/>';
					}
					else if($i == 2 or $i == 3 or $i == 10 or $i == 11 or $i == 39){
						$fileString = $fileString . nl2br($line);
					}
					else if($i == 4 or $i == 15 or $i == 16 or $i == 19 or $i == 20 or $i == 22 or $i == 23 or $i == 24 or $i == 27 or $i == 33 or $i == 34 or $i == 35){
						// these conditions were added in order to rename scenarios that were saved for old version of the KSS, KOMEGA, and KE variable labels
						if($i == 22 and trim($params[0]) == "KI")
							$params[0] = "KSS";
						else if($i == 23 and trim($params[0]) == "KCU")
							$params[0] = "KOMEGA";
						else if($i == 27 and trim($params[0]) == "KS")
							$params[0] = "KE";

						$fileString = $fileString . '<span style="margin-left:20px;"></span>' . $params[0] . ' = <span class="editable133">' . $params[1] . '</span><br/>';
					}
					// slope shape
					else if($i == 17 or $i == 18){
						$subparams = explode(',', $params[1]); 
						$fileString = $fileString . '<span style="margin-left:20px;"></span>' . $params[0] . ' = <span class="editable133">' . $subparams[0] . '</span><span class="editable133"> ,' . $subparams[1] . '</span>';
						if(count($subparams) == 3){
							$fileString = $fileString . '<span class="editable133"> ,' . $subparams[2] . '</span>';
						}
						$fileString = $fileString . '<br/>';
					}
					// include parent tags
					else{
						$fileString = $fileString . '<span style="margin-left:20px;"></span>' . $params[0] . ' = ' . $params[1] . '<br/>';
					}
				}
				$i++; 
			}
			fclose($file);
			$this->data['soilfile'] = $fileString;
			$this->load->view('editsoil', $this->data);
		}
		// show a warning message if user is not logged in and disable input tags
		else
		{
			redirect('login');
		}
	}

	/**
	 * This is the index page for the first step of the RHEM process.  Provides messages based
	 * on user authentication state. 
	 *
	 * @access	public
	 * @return	void
	 */
	function save()
	{
		$soilinputhtml = $_REQUEST['soilinputhtml'];
		$h2t = new html2text($soilinputhtml); 
		$soilinputtext = $h2t->get_text(); 
		
		$soilFileName = OUTPUT_FOLDER . "scenario_input_" . $this->session->userdata('user_id') . "_(m).par";
		$file = fopen($soilFileName, "w") or exit("Unable to open file!");
		fwrite($file, $soilinputtext);
		
		fclose($file);
	}
}