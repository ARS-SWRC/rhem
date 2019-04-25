	<?php include 'head.php'?>
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/jquery.jscrollpane.css" type="text/css" media="screen" charset="utf-8"/>

	<title>RHEM Web Tool: Welcome</title>
</head>
<body>
	<div class="content">
		<?php  include 'intro.php'; ?>
		<div class="subheader">
		</div>
		<div class="subcontent">
			<div class="subcontent_item">
				<h2>The RHEM<span class="blue"> Web Tool</span></h2>
				<p>The RHEM Web Tool is a web-based interface for the Rangeland Hydrology and Erosion Model (RHEM). 
				The interface allows users to input commonly known rangeland characteristics and use parameter estimation equations to 
				construct model input files and run the RHEM model.</p>
				<p>
					This web application was built with the following goals in mind:
					<ul>
						<li>Simplify the use of RHEM</li>
						<li>Manage user sessions</li>
						<li>Centralize scenario results (model runs)</li>
						<li>Compare scenario results</li>
						<li>Provide tabular and graphical reports</li>
					</uL>
					</p>
			</div>
			<div class="subcontent_item">
				<h2>The RHEM <span class="blue"> Model</span></h2>
				<p>RHEM is designed to provide sound, science-based technology to model 
				<img src="assets/images/range_land.jpg" style="float:left;padding:5px 20px 0px 20px;"/>
				and predict runoff and erosion rates on rangelands and to assist in assessing rangeland conservation practice effects.  
				RHEM is a newly conceptualized, process-based erosion prediction tool specific for rangeland application, based on 
				fundamentals of infiltration, hydrology, plant science, hydraulics and erosion mechanics.  </p>
			</div>
		</div>	
		<?php include 'right.php'; ?>
        <?php include 'version.php'; ?>
		<?php  include 'footer.php'; ?>
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/scripts/jquery.jscrollpane.min.js"></script>
	<script>
		$('.scroll-pane').jScrollPane();
	</script>	
</body>
</html>