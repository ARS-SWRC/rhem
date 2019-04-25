<?php
/**
 * This is just a test controller.
 *
 * @access	public
 * @return	void
 */
class Test extends CI_Controller {	
	/* This defines a constructor for the current controller */
	function __construct()
    {
		//parent::__construct();
		// load plugins
		//$this->load->library('session');
    }

	/**
	 * This test is used to calibrate the runtime of the DRHEM model and to verify
	 * how long a request can stay open before it is shut down by the server.
	 *
	 * @access	public
	 * @return	void
	 */
	function testTimeout()
	{		
		sleep(301);
		
		echo "Finished running application in 5min 1sec!!!!!";
	}
}
?>