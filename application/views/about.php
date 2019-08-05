	<?php include 'head.php'?>
	<title>RHEM Web Tool: Reserach</title>
</head>
<body>
	<div class="content">
		<?php  include 'intro.php'; ?>
		<div class="subheader">
		</div>
		<div class="subcontent">
			<div class="subcontent_item">
            	<h2>About RHEM</h2>
				<h4>Core <span class="blue">Engine</span></h4>
				<p>The RHEM core engine development was initiated in 2003. The current version of RHEM is a single storm, hillslope scale model that requires user input information (Figure 1) on:
					<ol>
						<li>Storm</li>
						<li>Soil and Cover</li> 
						<li>Slope</li> 
						<li>Infiltration/runoff/erosion parameters</li> 
					</ol>
					<p class="img_desc"><strong>Figure 1.</strong> A flow chart of the RHEM erosion prediction procedure</p>
					<img src="assets/images/rhem/rhem_flowchart.gif" style="padding-left:175px;"/>
				</p>
			</div>
			<div class="subcontent_item">
				<h4>Current <span class="blue">Capabilities</span></h4>
				<p>RHEM is currently a single storm runoff and erosion computer model.  
				Parameter estimation equations have been developed based on the measured rangeland erosion plot data for the two primary 
				erodibility and infiltration parameters (Kss and Ke).  Parameter estimation for other input parameters, including concentrated 
				flow detachment, are currently under development.  The parameter estimation procedures for Kss and Ke are grouped 
				according to dominant plant forms of sod-grass, bunch-grass, shrubs, and forbs with a different set of estimation equations for each 
				grouping.  Hence currently, the model may be parameterized and executed for undisturbed rangeland conditions.</p>
			</div>
			<div class="subcontent_item">
				<h4>Additional <span class="blue">Capabilities</span></h4>
				<p>
					<ul>
						<li>Parameter estimation procedures for the remaining infiltration, runoff, and erosion parameters, which include concentrated 
						flow erodibilities and hydraulic friction factors capable of representing infiltration, runoff, and erosion on undisturbed 
                        rangeland hillslopes.</li>   
						<li>Procedures have been developed to specifically execute model predictions for NRI data, such that a user may estimate runoff 
						and erosion rates for single storms at NRI sites.</li>  
						<li>A risk assessment methodology has been developed that calculates the risk of various sized erosion events occurring at a site 
						of interest in its current or assumed condition.</li>  
						<li>The risk assessment methodology using return frequency storm values will be further refined to calculate the risk of various 
						sized erosion events occurring at a site of interest in its current or assumed condition.</li>  
					</ul>
				</p>
            </div>
            <div class="subcontent_item">
                <h4>Capabilities <span class="blue">In Progress</span></h4>
				<p>
					<ul>
						<li>Parameter estimation procedures for the infiltration, runoff, and erosion parameters, which include concentrated 
						flow erodibilities and hydraulic friction factors are underway for disturbed rangeland hillslopes.</li>   
						<li>Implemention the CLIGrid featue which will enable RHEM to use storm inputs from pre-ran CLIGEN scenario runs for CONUS at a 4km scale.</li>  
					</ul>
				</p>
			</div>
		</div>	
		<?php include 'right.php'; ?>
		<?php include 'footer.php'; ?>	
</body>
</html>