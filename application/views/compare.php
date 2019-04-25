	<?php include 'head.php'?>
	<title><?php echo $title;?></title>
	<script language="javascript" src="<?php echo base_url(); ?>assets/scripts/jquery-1.2.6.js" ></script>
	<script type="text/javascript">
	/*<![CDATA[*/
		function toggleResults()
		{	
			if($("#results").css("display") == 'none'){
				$("#resultsButton").val("Hide Results");
				$("#results").slideDown(600);
			}
			else{
				$("#resultsButton").val("Show Results");
				$("#results").slideUp(600);
			}
		}
	/*]]>*/
	</script>
</head>
<body>
	<div class="content">
		<?php  include 'intro.php'; ?>
		<div class="subheader">
			<div class="nav_bar">
				<ul>
					<?php  include 'tool_nav_bar.php'; ?>
				</ul>
			</div>
		</div>
		<div class="subcontent_expanded">
			<h2><?php echo $heading;?></h2>
			<form action="<?php echo base_url(); ?>compare" method="post" class="styled_min">
			<fieldset>
				<?php echo isset($message)?$message:'';?>
				<p class="left_p">
					<label for="scenarioname">Please select the scenarios to compare:</label>
				    <?php 
						if(isset($userscenarios))
						{
							echo $userscenarios;
						}
					?>
				</p>
				<p class="centered_p">
					<input type="submit" value="Compare" id="Compare" />
				</p>
			</fieldset>
			</form>	
		</div>	
		<?php  include 'footer.php'; ?>	
</body>
</html>