<?php
/**
 * This controller is used to test various parts of the Run controller.
 *
 *
 * @access	public
 * @return	void
 */
class Run_test extends CI_Controller {
	
	/* This defines a constructor for the current controller */
	function __construct()
    {
		parent::__construct();
    }

/**
	 * Test function to verify correct results from Ke and Kss equations
	 */
	function testKeAndKss()
	{
		$moisturecontent = 0.25;
		$littercover = 48.04;
		$basalcover = 28.43;
		$cryptogamscover = 1.96;
		$rockcover = 0;
		$slopesteepness = 12;

		$soiltexture = 4;

		$bunchgrasscanopycover = 24.51;
		$forbscanopycover = 0;
		$shrubscanopycover = 42.16; 
		$sodgrasscanopycover = 10.78;





		$bunchgrasscanopycover = $bunchgrasscanopycover/100;
		$forbscanopycover = $forbscanopycover/100;
		$shrubscanopycover = $shrubscanopycover/100;
		$sodgrasscanopycover = $sodgrasscanopycover/100;

		$moisturecontent = $moisturecontent/100;
		$groundcover = ($basalcover + $littercover + $cryptogamscover + $rockcover)/100;
		$rockcover = $rockcover/100;
		$basalcover = $basalcover/100;
		$littercover = $littercover/100;
		$cryptogamscover = $cryptogamscover/100;

		$slopesteepness = $slopesteepness/100;


		// get the soil information from the database
		$meanclay = 0.1915;

		// Set the matric potential and the pore size distribution dynamically
		$meanmatricpotential = 110;
		$poresizedistribution = 0.25;

		$meanporosity = 0.4531;	

		// compute ft (replaces fe and fr)
		$ft =  ( -1 * 0.109) + (1.425 * $littercover) + (0.442 * $rockcover) + (1.764 * ($basalcover + $cryptogamscover)) + (2.068 * $slopesteepness);
		$ft = pow(10,$ft);

		////
		// Add all vegetation canopy cover percentages
		$totalcanopycover = $bunchgrasscanopycover + $forbscanopycover + $shrubscanopycover + $sodgrasscanopycover;

		////
		// Calculate weighted KE

		// this array will be use to store the canopy cover, Ke, and Kss values for the cover types that are not 0
		$vegetationCanopyCoverArray = array();

		// Caculate the new equation to calculate Ke.  For now, only Mariano will have access to
		// this functionality
		/////// Calculate baseline Ke based soil type
		switch ($soiltexture) {
			case 1: // "Sand"
				$Keb = 24 * exp(0.3483 * ($basalcover + $littercover) );
				break;
			case 2: // "Loamy Sand"
				$Keb = 10 * exp(0.8755 * ($basalcover + $littercover) );
				break;
			case 3: // "Sandy Loam"
				$Keb = 5 * exp(1.1632 * ($basalcover + $littercover) );
				break;
			case 4: // "Loam"
				$Keb = 2.5 * exp(1.5686 * ($basalcover + $littercover) );
				break;
			case 5: // "Silt Loam"	
				$Keb = 1.2 * exp(2.0149 * ($basalcover + $littercover) );
				break;
			case 6: // "Silt" (there is no equation devoped, yet, for silt)
				$Keb = 1.2 * exp(2.0149 * ($basalcover + $littercover) );
				break;
			case 7: // "Sandy Clay Loam"
				$Keb = 0.80 * exp(2.1691 * ($basalcover + $littercover) );
				break;
			case 8: // "Clay Loam"
				$Keb = 0.50 * exp(2.3026 * ($basalcover + $littercover) );
				break;
			case 9: // "Silty Clay Loam"
				$Keb = 0.40 * exp(2.1691 * ($basalcover + $littercover) );
				break;
			case 10: // "Sandy Clay"
				$Keb = 0.30 * exp(2.1203 * ($basalcover + $littercover) );
				break;
			case 11: // "Silty Clay"
				$Keb = 0.25 * exp(1.7918 * ($basalcover + $littercover) );
				break;
			case 12: // "Clay"
				$Keb = 0.2 * exp(1.3218 * ($basalcover + $littercover) );
				break;
		}
		//// 
		// Calculate KE and KSS based on vegetation type
		// Ke and Kss for Shrubs
		if($shrubscanopycover != 0){
			$Ke = $Keb * 1.2;

			$Kss = 4.00836 - (1.17804 * $rockcover) - (0.98196 * ($littercover + $totalcanopycover));
			$Kss = pow(10,$Kss); // antilog

			//$Kss = $Kss * 2.6;

		        echo "----Kss for Shrubs\n";
		        echo $Kss . "\n";

			$shrubsCoverArray = array("CanopyCover" => $shrubscanopycover,"Ke" => $Ke, "Kss" => $Kss);
			array_push($vegetationCanopyCoverArray,$shrubsCoverArray);
		}
		// Ke and Kss for Sod Grass
		if($sodgrasscanopycover != 0){
			$Ke = $Keb * 0.8;

			$Kss = 3.13334  - (0.20055 * $totalcanopycover) - (0.50550 * $littercover);
			$Kss = pow(10,$Kss);
			$Kss = $Kss/1.5;  // to account for less erosion. Nearing 2011
			//$Kss = $Kss * 2.6;

		        echo "----Kss for Sod\n";
		        echo $Kss . "\n";

			$sodgrassCoverArray = array("CanopyCover" => $sodgrasscanopycover,"Ke" => $Ke, "Kss" => $Kss);
			array_push($vegetationCanopyCoverArray,$sodgrassCoverArray);
		}
		// Ke and Kss Bunch Grass
		if($bunchgrasscanopycover != 0){
			$Ke = $Keb * 1.0;

			$Kss = 3.13334  - (0.20055 * $totalcanopycover) - (0.50550 * $littercover);
			$Kss = pow(10,$Kss); // antilog

			//$Kss = $Kss * 2.6;

		        echo "----Kss for Bunch\n";
		        echo $Kss . "\n";

			$bunchgrassCoverArray = array("CanopyCover" => $bunchgrasscanopycover,"Ke" => $Ke, "Kss" => $Kss);
			array_push($vegetationCanopyCoverArray,$bunchgrassCoverArray);
		}
		// Ke and Kss for Forbs
		if($forbscanopycover != 0){
			$Ke = $Keb * 1.0;

			$Kss = 3.13334  - (0.20055 * $totalcanopycover) - (0.50550 * $littercover);
			$Kss = pow(10,$Kss); // antilog
			//$Kss = $Kss * 2.6;
		        
		        echo "----Kss for Forbs\n";
		        echo $Kss . "\n";

			$forbsCoverArray = array("CanopyCover" => $forbscanopycover,"Ke" => $Ke, "Kss" => $Kss);
			array_push($vegetationCanopyCoverArray,$forbsCoverArray);
		}

		// Calculate the weighted Ke and Kss values based on the selected vegetation types by the user
		$weightedKe = 0;
		$weightedKss = 0;

		// calculate weighted Ke and Kss values for the vegetation types that have non-zero values
		foreach($vegetationCanopyCoverArray as $selCanopyCover){
			$weightedKe = $weightedKe + ( ($selCanopyCover['CanopyCover']/$totalcanopycover) * $selCanopyCover['Ke'] );
			$weightedKss = $weightedKss + ( ($selCanopyCover['CanopyCover']/$totalcanopycover) * $selCanopyCover['Kss'] );
		        
		        echo ( ($selCanopyCover['CanopyCover']/$totalcanopycover) * $selCanopyCover['Kss'] ) . "\n";
		}

		echo "----Kss after weighting\n";
		echo $weightedKss . "\n";


		echo "----Kss after mutiplying by 1.3\n";
		$weightedKss = $weightedKss * 1.3 ;
		echo $weightedKss . "\n";



		// To test this, a slope of 0.09 (or 9%) should give a slope steepness factor of 1
		// calculate atan in radians and pass it to sin in radians, convert to degrees
		$ssf_b = atan($slopesteepness);
		$ssf = (2.96 * ( pow(sin($ssf_b), 0.79) ) + 0.56 );

		echo "----Slope steepness factor \n";
		echo $ssf . "\n";

		// multiply the Kss by the steepness factor
		$weightedKss = $weightedKss * $ssf;

		echo "----Kss after slope steepness factor\n";
		echo $weightedKss . "\n";

		echo "----Kss after 2 \n";
		echo $weightedKss * 1.9895;
	}
}
?>