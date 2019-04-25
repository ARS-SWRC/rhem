<?php
/**
 * This controller is used to access soils data. It also has some functionality for
 * determining texture class based on a sand and clay input. 
 *
 * Note: this class is currently not being used in the RHEM Web Tool
 *
 * @access	public
 * @return	void
 */
class Definesoil extends CI_Controller {

	function __construct()
    {
		parent::__construct();
		//$this->load->library('session');
    }
	
	function index()
	{			
		echo $this->gettexture(8.5,4.5);
	}
	
	
	/**
	 * Function to test the gettexture function
	 *
	 * Will test the function based on the list of inputs obtained from Mariano H.
	 * 
	 * @access	public
	 * @return	void
	 */
	function testgettexture()
	{
		$texturestest = array(array(10,5,"silt"),
							array(20,15,"silt loam"),
							array(40,20,"loam"),
							array(50,30,"sandy clay loam"),
							array(70,10,"sandy loam"),
							array(85,5,"loamy sand"),
							array(90,3,"sand"),
							array(10,45,"silty clay"),
							array(20,60,"clay"),
							array(50,45,"sandy clay"),
							array(10,35,"silty clay loam"),
							array(70,25,"sandy clay loam"),
							array(50,20,"loam"),
							array(48,38,"sandy clay"),
							array(20,40,"clay loam or silty clay"));
							
		foreach($texturestest as $test)
		{
			$foundtexture = $this->gettexture($test[0],$test[1]);
			echo "Texture: " . $test[2] .  "    is: " . $foundtexture . " ---";
			echo '<br/>';
		}
	}
	
	/**
	 * Function to classify a soil in the triangle based on sand and clay percent. 
	 *
	 * Translated from Fortran code created by aris gerakis, apr. 98
	 * 
	 * @param clay - clay value
	 * @param sand - sand value
	 * @access	public
	 * @return	integer - an integer representing texture class found
	 */
	function gettexture($sand,$clay)
	{
		$texture = '';
		
		// soil texture polygon array representations
		$sandy = array(array(85, 90, 100, 0, 0, 0, 0),array(0, 10, 0, 0, 0, 0, 0));
		$loamy_sand = array(array(70, 85, 90, 85, 0, 0, 0),array(0, 15, 10, 0, 0, 0, 0));
		$sandy_loam = array(array(50, 43, 52, 52, 80, 85, 70),array(0, 7, 7, 20, 20, 15, 0));
		$loam = array(array(43, 23, 45, 52, 52, 0, 0),array(7, 27, 27, 20, 7, 0, 0));
		$silty_loam = array(array(0, 0, 23, 50, 20, 8, 0),array(12, 27, 27, 0, 0, 12, 0));
		$silt = array(array(0, 0, 8, 20, 0, 0, 0),array(0, 12, 12, 0, 0, 0, 0));
		$sandy_clay_loam = array(array(52, 45, 45, 65, 80, 0, 0),array(20, 27, 35, 35, 20, 0, 0));
		$clay_loam = array(array(20, 20, 45, 45, 0, 0, 0),array(27, 40, 40, 27, 0, 0, 0));
		$silty_clay_loam = array(array(0, 0, 20, 20, 0, 0, 0),array(27, 40, 40, 27, 0, 0, 0));
		$sandy_clay = array(array(45, 45, 65, 0, 0, 0, 0),array(35, 55, 35, 0, 0, 0, 0));
		$silty_clay = array(array(0, 0, 20, 0, 0, 0, 0),array(40, 60, 40, 0, 0, 0, 0));
		$clayey = array(array(20, 0, 0, 45, 45, 0, 0),array(40, 60, 100, 55, 40, 0, 0));
		
		// create an array of texture classes. each element containing the texture polygon array, 
		// the numbero of points, the index, and the texture name
		$textures = array(array($sandy,3,1,'Sandy'),
						array($silt,4,6,'Silt'),
						array($clayey,5,12,'Clayey'),
						array($loam,5,4,'Loam'),
						array($loamy_sand,4,2,'Loamy Sand'),
						array($sandy_loam,7,3,'Sandy Loam'),
						array($silty_loam,6,5,'Silt Loam'),
						array($clay_loam,4,8,'Clay Loam'),  
						array($sandy_clay,3,10,'Sandy Clay'),	 
						array($silty_clay,3,11,'Silty Clay'),						
						array($sandy_clay_loam,5,7,'Sandy Clay Loam'),
						array($silty_clay_loam,4,9,'Silty Clay Loam'));
						
		
		// first, check that sand and clay values are positive
		if($sand >= 0.0 && $clay >= 0.0){
			// iterate through texture and find out texture based on clay and sand values
			foreach($textures as $texture)
			{
				if ($this->inpoly($texture[0], $texture[1], $sand, $clay)){
				  echo $texture[2];
				  return;
				}
			}
		}
		else{
			echo 'Invalid values for sand and/or clay entered!!';
		}
	}
	
	/**
	 * Function to tell if a point is inside a polygon or not. This function will return 
	 * true if poing is inside polygon or false otherwise
	 *
	 * Translated from Fortran code (1995-1996 Galacticomm, Inc.  Freeware source code.)
	 * 
	 * @param poly(*,2) - polygon points, [0]=x, [1]=y
	 * @param npoints - number of points in polygon
 	 * @param xt - x (horizontal) of target point
 	 * @param yt - y (vertical) of target point
	 * @access	public
	 * @return	void
	 */
	function inpoly($poly, $npoints, $xt, $yt)
	{
		$inpoly = false;
		$inside = false;
		$on_border = false;
	
		// base case. less than three polygon vertices are present
		if($npoints < 3){
			return $inpoly;
		}
		
		// add one to npoints because array is 0 based
		$xold = $poly[0][$npoints - 1];
		$yold = $poly[1][$npoints - 1];
		
		
		for($i = 0; $i < $npoints; $i++)
		{
			$xnew = $poly[0][$i];
			$ynew = $poly[1][$i];
			
			if($xnew > $xold){
				$x1 = $xold;
				$x2 = $xnew;
				$y1 = $yold;
				$y2 = $ynew;
			}
			else{
				$x1 = $xnew;
				$x2 = $xold;
				$y1 = $ynew;
				$y2 = $yold;
			}
			
			// The outer IF is the 'straddle' test and the 'vertical border' test. 
			// The inner IF is the 'non-vertical border' test and the 'north' test.
			  
			// The first statement checks whether a north pointing vector crosses  
			// (stradles) the straight segment.  There are two possibilities, depe-
			// nding on whether xnew < xold or xnew > xold.  The '<' is because edge 
			// must be "open" at left, which is necessary to keep correct count when 
			// vector 'licks' a vertix of a polygon.  
			if (   ($xnew < $xt && $xt <= $xold)  ||    (!($xnew < $xt) && !($xt <= $xold))    ){
				// The test point lies on a non-vertical border:
				if ( ($yt-$y1)*($x2-$x1) == ($y2-$y1)*($xt-$x1)  ){
					$on_border = true;
				}
				// Check if segment is north of test point.  If yes, reverse the 
				// value of INSIDE.  The +0.001 was necessary to avoid errors due   
				// arithmetic (e.g., when clay = 98.87 and sand = 1.13):   
				elseif (  ($yt-$y1)*($x2-$x1) < ($y2-$y1)*($xt-$x1) + 0.001  ){
					$inside = !$inside; //cross a segment
				}
			} // end if
			
			// This is the rare case when test point falls on vertical border or  
			// left edge of non-vertical border. The left x-coordinate must be  
			// common.  The slope requirement must be met, but also point must be
			// between the lower and upper y-coordinate of border segment.  There 
			// are two possibilities,  depending on whether ynew < yold or ynew > 
			// yold:
			elseif (($xnew == $xt || $xold == $xt) && 
			        ($yt-$y1)*($x2-$x1) == ($y2-$y1)*($xt-$x1) && 
					(($ynew <= $yt && $yt <= $yold) || (!($ynew < $yt) && !($yt < $yold)))
				   ){
						$on_border = true; 
			} // end else
			
			$xold = $xnew;
			$yold = $ynew;
		} // end for loop
		
		// If test point is not on a border, the function result is the last state 
		// of INSIDE variable.  Otherwise, INSIDE doesn't matter.  The point is
		// inside the polygon if it falls on any of its borders:
		if (!$on_border){
			$inpoly = $inside;
		}
		else{
			$inpoly = true;
		}
		
		return $inpoly;
	} // end of inpoly
} // end of class
?>