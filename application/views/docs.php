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
            	<p><i>RHEM engine version used in this interface: <b>rhem_v2.3.exe</b></i></p>

				<div class="tabbable"> <!-- Only required for left/right tabs -->
				<ul class="nav nav-tabs" style="margin-left: 0px;">
					<li class="active"><a id="technical_tab" href="#technical" data-toggle="tab"><b>Technical Docs</b></a></li>
					<li><a id="tutorial_tab" href="#tutorials" data-toggle="tab"><b>Tutorials/Training</b></a></li>
					<li><a id="publications_tab" href="#publications" data-toggle="tab"><b>Publications</b></a></li>
					<li><a id="international_tab" href="#international" data-toggle="tab"><b>Int’l Stations</b></a></li>
					<li><a id="batchscript_tab" href="#batchscript" data-toggle="tab"><b>Batch Script</b></a></li>
                    <li><a id="versions_tab" href="#versions" data-toggle="tab"><b>Versions</b></a></li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="technical">
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
								<li>.PRE - the storm file create for the selected climate station </li>
							</ul>
							<!--TODO: include a link to the .RUN file structure-->
						</p>	
					</div>
					<div class="tab-pane" id="tutorials">
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
					<div class="tab-pane" id="publications">
					<h4>Related Publications</h4>
	                <p>
	                	<ul class="list-group">
                            <li class="pub_year_label"><span>2020</span></li>
	                		<li><a href="assets/docs/publications/Salinity/Arsland_Weltz_Nouwakpo_Salinity_Rangelands.pdf">Salt Balance of Moderately Saline-Alkaline Rangeland Soil and Runoff Water Quality from Rainfall Simulation Studies near Moab, Utah U.S.A.</a></li>
                            <li><a href="assets/docs/publications/Salinity/BLM_ARS_Salinity_Final_Report.pdf">Report: Quantifying Relative Contributions of Salt Mobilization and Transport from Rangeland Ecological Sites in the Intermountain West</a></li>
                            <li class="pub_year_label"><span>2019</span></li>
	                		<li><a href="assets/docs/publications/Salinity/Mapping_erosion_risks_for_saline_rangelands_of_Mancos_Shale_using_RHEM_McGwire_Weltz.pdf">Mapping erosion risk for saline rangelands of the Mancos Shale using the rangeland hydrology erosion model</a></li>
	                		<li class="pub_year_label"><span>2018</span></li>
	                		<li><a href="assets/docs/publications/Salinity/Nouwakpo_Saline_and_Sodic_Soils.pdf">Process-Based Modeling of Infiltration, Soil Loss, and Dissolved Solids on Saline and Sodic Soils</a></li>
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
					<div class="tab-pane" id="international">
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
					<div class="tab-pane" id="batchscript">
						<h4>RHEM Batch Script</h4>
						<p>This script is able to run RHEM in batch mode based on the RHEM Model-as-a-Service (MaaS) using the Cloud Services Innovation Platform (CSIP). More inormation about MaaS and CSIP can be read in <a href="https://digitalcommons.tacoma.uw.edu/cgi/viewcontent.cgi?article=1012&context=tech_pub">this publication</a>. This functionality enables you to run RHEM with multiple scenarios in batch format using a Python script. In order for you to run RHEM in batch mode, you are required to have:
							<ol>
								<li>An internet-enabled computer</li>
								<li>Python 3</li>
								<li>pip or conda (Python package installers)</li>
								<li>Spredsheet editing software (Excel, OpenOffice, Google Sheets, etc.)</li>
							</ol>
						</p>
						<p>
							The workflow to run the script:
							<ol>
								<li>Download the script, the template spreadsheet, and the requirements.txt file (this is used to install required Python packages) from this location: <a href="https://github.com/ARS-SWRC/rhem_batch_csip">https://github.com/ARS-SWRC/rhem_batch_csip</a></li>
								<li>Install the Python dependencies (from the requirements.txt file)</li>
								<li>Populate the Excel spreadsheet with the scenarios you would like to run</li>
								<li>Run the Python script</li>
								<li>After the Python script finishes running, the results will be saved to the same spreadsheet and the parameter files and summary outputs from RHEM will be saved in an output folder</li>
							</ol>
						</p>
					</div>
                    <div class="tab-pane" id="versions">
						<h4>RHEM Web Tool Versions Documentation</h4>
						<p>
                            <b>Version: <i>2.3 Update 5</i> </b>
                            <ul style="color:#666">
                                <li>Added a new checkbox and SAR input box to the Soil Texture panel for saline scanarios.</li>
                                <li>When a scenario is ran with this SAR value, a new row will appear in the Annual Averages table showing the "Salt Load".</li>
                            </ul>
                            <p>
                                <p>RHEM v2.3 Update 5 now includes a new capacity for saline soils.</p>
                                <p>The RHEM Web Tool has been updated with the capacity to account for the effect of saline soils on erosion and to provide estimates of salt loads associated with runoff and erosion events.</p>
                                <p>The web interface now has an option under the Soil Texture Class tab. When checked, the user will be prompted to input the SAR (sodium adsorption ratio) value of the soil. If the box is not checked the model works as previously.</p>
                                <p>Saline soils have greater erodibility and produce more erosion.  The equations used here to adjust the soil erodibility are based on the work of Nouwakpo et al., 2018. In addition, if the salinity option is active, the model will predict salt loads from the hillslope, also based on equations of Nouwakpo et al., 2018 on work done in the upper Colorado basin in Utah and Colorado. The salt load prediction is reported in the Annual Averages output table.</p>
                                <p>Nouwakpo, S. K., Weltz, M. A., Arslan, A., Green, C. H., & Al-Hamdan, O. Z. (2018). Process-Based Modeling of Infiltration, Soil Loss, and Dissolved Solids on Saline and Sodic Soils. Transactions of the American Society of Agricultural and Biological Engineers ISSN 2151-0032 <a href="https://doi.org/10.13031/trans.12705">https://doi.org/10.13031/trans.12705</a></p>
                            </p>
                        </p>
                        <p>
                            <b>Version: <i>2.3 Update 4</i> </b>
                            <br /> 
                            In this new Update 4, we are incorporating the following functionality:
                            <ul style="color:#666">
                                <li>Ability to export report graphics as SVG -  this feature will enable users to export each of the report graphics in vector format (SVG).  These files can later be transformed to other formats such as EPS or PDF using tools such as Adobe Illustrator or Inkscape.  The report graphics will also be available for download as PNG images.</li>
                                <li>Added a "Yearly Totals" table to the return period table when running single scenarios -  In addition to viewing the yearly maximum daily values for return frequency results, the user will now see yearly totals.  Note that scenarios will have to be rerun in order for users to see this new section in their scenario results.</li>
                                <li>Added ability to define the order of the scenarios when running a scenario comparison. - This functionality is useful when the user needs to define a specific order of appearance for the scenario comparison report.  This functionality is based on the selection order (click order) of the scenarios.  An orange label (number) will appear next to each scenario selected. </li>
                                <li>Metric units in the output reports have been changed from tonne/ha to Mg/ha (4-3-2019)</li>
                            </ul>
                        </p>
                        <p>
                            <b>Version: <i>2.3 Update 3</i> </b>
                            <br /> 
                            <ul style="color:#666">
                                <li> International climate stations are now supported.  Users can <a href="https://apps.tucson.ars.ag.gov/rhem/docs#international_stations">submit CLIGEN .PAR files</a> (with station statistics) to create a new climate station.</li>
                                <li>Slope length is defaulted to 50m (164ft).  The slope length input box in the "Slope" panel has been removed.</li>
                            </ul>
                        </p>
                        <p>
                            <b>Version: <i>2.3 Update 2</i> </b>
                            <br /> 
                            <ul style="color:#666">
                                <li>Slope length is now default to 50m (164ft).</li>
                                <li>The input parameter modification tool now allows the user to modify the LEN parameter (slope length).</li>
                                <li>Application has been scaled up allowing scenarios to run about 40% faster.</li>
                            </ul>
                        </p>
                        <p>
                            <b>Version: <i>2.3 Update 1</i> </b>
                            <br /> 
                            <ul style="color:#666">
                                <li>This update provides a risk assessment (probability-based) approach to natural rangeland conditions erosion modeling, and provides the frequency of occurrence for each alternative scenario based on annual soil loss return levels.</li>
                            </ul>
                        </p>
                        <p>
                            <b>Version: <i>2.3</i> </b>
                            <br /> 
                            <ul style="color:#666">
                                <li>New set of parameter estimation equations were developed to estimate the splash and sheet erodibility coefficient on natural rangeland conditions</li>
                                <li>Improvement to the rainfall disaggregation algorithm</li>
                                <li>In general, you may find that RHEM version 2.3 produces greater soil loss predictions than the previous version</li>
                            </ul>
                        </p>
                        <p>
                            <b>Version: <i>2.2</i> </b>
                            <br /> 
                            <ul style="color:#666">
                                <li>New set of parameter estimation equations were developed for the Smith-Parlange infiltration equation</li>
                                <li>Minor adjustment done to the splash and sheet erodibility parameter estimation equation (11) and (12) described in Nearing et al. 2011 to account for an improved calibration factor and for better representing slope steepness</li>
                                <li>Erodibility coefficient Kω has been slightly increased to better represent concentrated flow erosion in undisturbed rangelands</li>
                                <li>Model version used to run scenarios can be seen in the scenarios listing</li>

                            </ul>
                        </p>
                        <p>
                            <b>Version: <i>2.1</i> </b>
                            <br /> 
                            <ul style="color:#666">
                                <li>Multiply Kss for all cases by 1.3 in order to account for the bias in the log transformation (relative to Duan 1989)</li>
                                <li>Ability to rename scenarios through the scenarios list by clicking on scenario name</li>
                                <li>A graphical (PNG) report can be created from a scenario comparison</li>
                                <li>User account section added to control detailed output reports and manual input file modifications</li>
                            </ul>
                        </p>
                        <p>
                            <b>Version: <i>2.0</i> </b>
                            <br /> 
                            <span style="color:#666">A prototype next-generation version (version 2) of the Rangeland Decision Support System (RHEM) is under development.</span>
                        </p>
                        <p>
                            <b>Version: <i>1.2</i> </b>
                            <br /> 
                            <span style="color:#666">Updated Kc and Tc.</span>
                        </p>
                        <p>
                            <b>Version: <i>1.1</i></b>
                            <br /> 
                            <span style="color:#666">Updated model equations.</span>
                        </p>
                        <p>
                            <b>Version: <i>1.0</i></b><br>
                        </p>
					</div>
				</div>
				</div>

            	<br/>
			</div>
		</div>	
		<?php include 'right.php'; ?> 
		<?php include 'footer.php'; ?>
    <script>
    $(document).ready(function(e) {
        $("#technical_tab").click(function(){
            location.replace("https://apps.tucson.ars.ag.gov/rhem/docs#technical");
            setTimeout(function() { window.scrollTo(0, 0);}, 1); 
        });
        $("#tutorial_tab").click(function(){
            location.replace("https://apps.tucson.ars.ag.gov/rhem/docs#tutorials");
            setTimeout(function() { window.scrollTo(0, 0);}, 1); 
        });
        $("#publications_tab").click(function(){
            location.replace("https://apps.tucson.ars.ag.gov/rhem/docs#publications");
            setTimeout(function() { window.scrollTo(0, 0);}, 1); 
        });
        $("#international_tab").click(function(){
            location.replace("https://apps.tucson.ars.ag.gov/rhem/docs#international");
            setTimeout(function() { window.scrollTo(0, 0);}, 1); 
        });
        $("#batchscript_tab").click(function(){
            location.replace("https://apps.tucson.ars.ag.gov/rhem/docs#batchscript");
            setTimeout(function() { window.scrollTo(0, 0);}, 1); 
        });
        $("#versions_tab").click(function(){
            location.replace("https://apps.tucson.ars.ag.gov/rhem/docs#versions");
            setTimeout(function() { window.scrollTo(0, 0);}, 1); 
        });
    });
    </script>	
</body>
</html>