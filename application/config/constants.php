<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with property_exists(class, property)
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');


/*
|-------------------------------------------------------------------------
| RHEM constants
|-------------------------------------------------------------------------
|
*/
define('RHEM_VERSION', '2.3');
define('APPROOT_FOLDER', 'D:/appsroot/rhem_v2/');
define('OUTPUT_FOLDER', 'D:/appsroot/rhem_v2_data/temp/');
define('CLIGEN_FOLDER', 'D:/appsroot/rhem_v2_data/cligen (CONUS)/');

//define('RHEM_MODEL_EXEC', 'drhem_disag_dblex_11Feb2015.exe');
define('RHEM_MODEL_EXEC', 'drhem_disag_dblex_08Jun2015.exe');

//define('RHEM_RISK_ASSESSMENT_EXEC','pmrm_20JUL2015.exe');
//define('RHEM_RISK_ASSESSMENT_EXEC','pmrm_22Oct2015.exe');
//define('RHEM_RISK_ASSESSMENT_EXEC','pmrm_4Mar2016.exe');
define('RHEM_RISK_ASSESSMENT_EXEC','pmrm_2_04Mar2016.exe');

define('EXE_SERVER', 'STORM');

/*
/* End of file constants.php */
/* Location: ./application/config/constants.php */