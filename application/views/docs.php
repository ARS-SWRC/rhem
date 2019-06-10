	<?php 
		include 'head.php'
		// NOTE:  Current version of the interface is v2.1 current version of RHEM model is 12132013
	?>
	<title>RHEM Web Tool: Links</title>
</head>
<body>
	<div class="content">
		<?php  include 'intro.php'; ?>
		<div class="subheader">
		</div>
		<div class="subcontent">
			<div class="subcontent_item">
            	<h2>Documentation</h2>
            	<p><i>RHEM version used in this interface: <b>rhem_v2.3.exe</b></i></p>

				<div class="tabbable"> <!-- Only required for left/right tabs -->
				<ul class="nav nav-tabs" style="margin-left: 0px;">
					<li class="active"><a href="#tab1" data-toggle="tab">Technical Docs</a></li>
					<li><a href="#tab2" data-toggle="tab">Tutorials and Training</a></li>
					<li><a href="#tab3" data-toggle="tab">Publications</a></li>
					<li><a href="#tab4" data-toggle="tab">International Stations</a></li>
					<li><a href="#tab5" data-toggle="tab">Batch Script</a></li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="tab1">
						<h4>RHEM Model Technical Documentation</h4>
						<p>
							<ul>
								<li><a href="assets/docs/RHEM_Manuscript_April_2011.pdf">A Rangeland Hydrology and Erosion Model</a> </li>
							</ul>
						</p>
						<h4>RHEM Parameter Estimation Equations</h4>
						<p>
							<ul>
								<li><a target="_blank" href="assets/docs/RHEM_Equations_01222015.pdf">RHEM Equations(Ft, Ke, and Kss) v2.3</a> </li>
								<li><a target="_blank" href="assets/docs/RHEM_Equations_07022014.pdf">RHEM Equations(Ft, Ke, and Kss) v2.2</a> </li>
								<li><a target="_blank" href="assets/docs/RHEM_Equations_03312014.pdf">RHEM Equations(Ft, Ke, and Kss) v2.1</a> </li>
							</ul>
						</p>
						<h4>RHEM Parameter File Descriptions</h4>
						<p>
							<ul>
								<li><a href="assets/docs/RHEM_Input_File_Parameters_Descriptions.pdf">RHEM Input File Parameter Descriptions</a> </li>
							</ul>
						</p>
						<h4>RHEM Executable</h4>
						<p> 
							Download the latest version of the RHEM executable:
							<ul>
								<li><a href="assets/model/rhem_v2.3.zip">RHEM Executable (version 2.3)</a> </li>
							</ul>
							In order to run the RHEM executable, the following files are needed:
								<ul>
								<li>.RUN - description of the files needed to perform the run </li>
								<li>.PAR - the input parameter file </li>
								<li>.STM - the storm file create for the selected climate station </li>
							</ul>
							<!--TODO: include a link to the .RUN file structure-->
						</p>	
					</div>
					<div class="tab-pane" id="tab2">
						<!-- This section will be used to store the tutorials that explain how to use RHEM -->
						<h4>RHEM Tutorials and Learning</h4>
						<p>
							<ul>
								<li><a href="assets/docs/tutorials_and_fact_sheet/RHEM_Tutorial.pdf">RHEM Tutorial</a> </li>
								<li><a href="assets/docs/tutorials_and_fact_sheet/RHEM_Factsheet_Post-Oak_Savana_Texas.pdf">RHEM Fact Sheet (for Post Oak Savana in central Texas)</a> </li>
								<li><a href="assets/docs/tutorials_and_fact_sheet/RHEM_Factsheet-Deep_Hardland_Shortgrass_site_Texas.pdf">RHEM Fact Sheet (for Short Grass Prairie Ecological Site, west Texas)</a> </li>
							</ul>
						</p>
						<h4>Past RHEM Trainings</h4>
						<p>
							<ul>
								<li><a href="http://2016canada.rangelandcongress.org/workshops.html#sthash.IXhsfFi2.dpbs">RHEM Web Tool Workshop at the 10th International Rangeland Congress (7-17-2016)</a></li>
							</ul>
						</p>
					</div>
					<div class="tab-pane" id="tab3">
					<h4>Related Publications</h4>
	                <p>
	                	<ul class="list-group">
	                		<li class="pub_year_label"><span>2017</span></li>
	                		<li><a href="https://www.tucson.ars.ag.gov/unit/publications/PDFfiles/2354.pdf">The Rangeland Hydrology and Erosion Model: A Dynamic
Approach for Predicting Soil Loss on Rangelands</a></li>
	                		<li class="pub_year_label"><span>2016</span></li>
	                		<li><a href="assets/docs/publications/Nouwakpo RHEM USe on Construction SItes.pdf">Performance of the Rangeland Hydrology and Erosion Model for runoff and erosion assessment on a semiarid reclaimed construction site</a></li>
	                		<li class="pub_year_label"><span>2015</span></li>
	                		<li><a href="assets/docs/publications/Nouwakpo Estimating soil erosion in 3-D.pdf">Assessing the performance of structure from-motion photogrammetry and terrestrial LiDAR for reconstructing soil surface microtopography of naturally vegetated plots</a></li>
	                		<li><a href="assets/docs/publications/Williams Ecohydrology paper.pdf">Incorporating Hydrologic Data and Ecohydrologic Relationships into Ecological Site Descriptions</a></li>
	                		<li class="pub_year_label"><span>2014</span></li>
	                		<li><a href="assets/docs/publications/Weltz RHEM Great Basin Example.pdf">Cheatgrass invasion and woody species encroachment in the great basin: Benefits of conservation</a></li>
	                		<li><a href="assets/docs/publications/Felegari RHEM Evaluation in Iran.pdf">Efficiency Assessment of Rangeland Hydrology and Erosion Model (RHEM) for water erosion quantification (Case Study: Sangane Watershed-Iran)</a></li>
	                		<li><a href="assets/docs/publications/Weltz National NRI Soil Erosion Estimates.pdf">Estimating conservation needs for rangelands using USDA National Resources Inventory Assessments</a></li>
	                		<li><a href="assets/docs/publications/RHEM_enhancements_for_applications_on_disturbed_rangelands.pdf">Rangeland hydrology and erosion model (RHEM) enhancements for applications on disturbed rangelands</a></li>
	                		<li class="pub_year_label"><span>2013</span></li>
	                		<li><a href="assets/docs/publications/Hernandez RHEM - Arizona NRI.pdf">Application of a rangeland soil erosion model using National Resources Inventory data in southeastern Arizona</a></li>
	                		<li><a href="assets/docs/publications/Ross RHEM Assessing Range Practices in Arizona Thesis UofA.pdf">(Master's Thesis) Using the Rangeland Hydrology and Erosion Model to assess rangeland management practices on the Kaler Ranch</a></li>
	                		<li class="pub_year_label"><span>2012</span></li>
	                		<li><a href="assets/docs/publications/Al-Hamdan Concentrated flow erodibility.pdf">Concentrated flow erodibility for physically based erosion models: Temporal variability in disturbed and undisturbed rangelands</a></li>
	                		<li><a href="assets/docs/publications/Weltz RHEM Texas Hill Country example.pdf">Estimating Effects of Targeted Conservation on Nonfederal Rangelands</a></li>
	                		<li><a href="assets/docs/publications/Zhang-Rhem & Climate change.pdf">Modeling climate chnage effects on runoff and soil erosion in southeastern Arizona rangelands and implications for mitigation with conservation practices</a></li>
	                		<li><a href="assets/docs/publications/Belnap RHEM Evaluation in Utah - Cryptogams.pdf">Successional stage of biological soil crusts: an accurate indicator of ecohydrological condition</a></li>
	                		<li class="pub_year_label"><span>2011</span></li>
	                		<li><a href="assets/docs/publications/Al-Hamdan Concentrated flow hydraulics.pdf">Characteristics of concentrated flow hydraulics for rangeland ecosystems: implications for hydrologic modeling</a></li>
	                		<li><a href="assets/docs/publications/USDA National Report -RCA Chapter 3 - RHEM.pdf">RCA Apprailsal - The State of the Land</a></li>
	                		<li class="pub_year_label"><span>2009</span></li>
	                		<li><a href="assets/docs/publications/Wei RHEM splash and Sheet erosion equation.pdf">A New Splash and Sheet Erosion Equation for Rangelands</a></li>
	                		<li class="pub_year_label"><span>2008</span></li>
	                		<li><a href="assets/docs/publications/Wei RHEM Model Uncertainity.pdf">A dual Monte Carlo approach to estimate model uncertainty and its application to the Rangeland Hydrology and Erosion Model</a></li>
	                		<li><a href="assets/docs/publications/Weltz Future of Erosion Modeling on Rangelands.pdf">Overview of current and future technologies in rangeland management</a></li>
	                		<li class="pub_year_label"><span>2007</span></li>
	                		<li><a href="assets/docs/publications/Wei RHEM Sensitivity.pdf">A comprehensive sensitivity analysis framework for model evaluation and improvement using a case study of the Rangeland Hydrology and Erosion Model</a></li>
	                	</ul>
	                </p>
					</div>
					<div class="tab-pane" id="tab4">
						<h4 id="international_stations">International Climate Stations</h4>
						<p>
							We have been working on expanding the coverage of the RHEM Web Tool (interface).  You now have the ability to use international climate stations.  In the “Climate Station” panel, you will see a tab for “International” stations. At the moment, we are providing 4 climate stations from Jordan.   Our intent is to include more stations from around the world.  If you are interested in contributing additional stations to the RHEM interface, please let us know. 
							<br/><br/>
							The workflow to add new stations would be as follows:
							<ol>
							<li>You send us a PAR file (description on the format of the CLIGEN .PAR file can be found here: <a href="https://www.ars.usda.gov/midwest-area/west-lafayette-in/national-soil-erosion-research/docs/wepp/cligen">https://www.ars.usda.gov/midwest-area/west-lafayette-in/national-soil-erosion-research/docs/wepp/cligen</a></li>
							<li>We run CLIGEN with your PAR file</li>
							<li>The new CLIGEN output is added to our RHEM interface as a new climate station</li>
							</ol>
							If you are unable to create a PAR file but you have rainfall records, or if you need assistance, don’t hesitate to contacts us for assistance
						</p>
						<h4>Related Projects</h4>
						<p>
							<ul>
								<li><a href="http://www.ars.usda.gov/Research/docs.htm?docid=18094">Cligen Weather Generator</a> (documentation and source code for Cligen can be found here) </li>
								<li><a href="http://typhoon.tucson.ars.ag.gov/weppcat/index.php">Water Erosion Prediction Project Climate Assessment Tool</a> </li>
							</ul>
						</p>
					</div>
					<div class="tab-pane" id="tab5">
						<h4>RHEM Batch Script</h4>
						<p>After many requests from our users, we now have the ability to run RHEM in batch mode based on the RHEM Model-as-a-Service (MaaS) using the Cloud Services Innovation Platform (CSIP). More inormation about MaaS and CSIP can be read in <a href="https://digitalcommons.tacoma.uw.edu/cgi/viewcontent.cgi?article=1012&context=tech_pub">this publication</a>. This functionality enables you to run RHEM with multiple scenarios in batch format using a Python script. In order for you to run RHEM in batch mode, you are required to:
							<ol>
								<li>Have an internet enabled computer</li>
								<li>Install Python</li>
								<li>Install pip or conda (Python package installers)</li>
								<li>Install Excel</li>
							</ol>
						</p>
						<p>
							The workflow to run the script will be as follows:
							<ol>
								<li>Download the script, the template spreadsheet, and the requirements.txt file (this is used to install required Python packages) from this location: <a href="https://github.com/ARS-SWRC/rhem_batch_csip">https://github.com/ARS-SWRC/rhem_batch_csip</a></li>
								<li>Install the Python dependencies (from the requirements.txt file)</li>
								<li>Populate the Excel spreadsheet with the scenarios you would like to run</li>
								<li>Run the Python script</li>
								<li>After the Python script finishes running, the results will be saved to the same spreadsheet and the parameter files and summary outputs from RHEM will be saved in an output folder</li>
							</ol>
						</p>
					</div>
				</div>
				</div>

            	<br/>
			</div>
		</div>	
		<?php include 'right.php'; ?> 
		<?php include 'footer.php'; ?>	
</body>
</html>