<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This class will be used to give user interface feedback to the user. 
 *
 * @access	public
 * @return	void
 */
class Errors {

	/**
	 * Prints a styled message based on the provided text.
	 *
	 * @param $message -  The message to be printed
	 * @access	public
	 * @return	String
	 */
    function print_message($message, $box)
    {
		return '<div class=" alert ' . $box . ' alert-block">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
				  	<h4>Note</h4>
				 	 ' . $message . '
				</div>';
    }
}

?>