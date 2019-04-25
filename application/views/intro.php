		<div class="header_right">
			<div class="top_info">
						<span>Hello, 
						<?php echo ($this->session->userdata('is_logged_in') == true?$this->session->userdata('user_first_name'):"Guest"); ?>
						 </span>
						<strong>
						<?php 
							if($this->session->userdata('is_logged_in') == true)
							{ 
								echo '<a href="' . base_url() . 'login/out">Log Out</a>' . '  <a href="' . base_url() . 'account" style="font-size:11px;margin:0px -50px 0px 20px;"><i class="icon-user icon-white" style="margin-right: 0px;opacity:0.8;"></i>Account</a>';;
							}
							else
							{
								//echo '<a href="' . base_url() . 'login">Log In</a> or ';
								echo '<a href="#loginModal" data-toggle="modal">Log In</a> or '; // data-target="#myModal" href="login/index"  ** use this to call the modals remotely
								echo '<a href="' . base_url() . 'register">Register</a>';
							}
						?>  
						</strong>		
			</div>
			<div class="menubar">
				<ul class="nav nav-pills pull-right">
					<li <?php echo ($this->uri->segment(1) == ''?'class="active"':'');?>><a href="<?php echo base_url(); ?>."><span></span>Home</a></li>
					<li <?php echo ($this->uri->segment(1) == 'about'?'class="active"':'');?>><a href="<?php echo base_url(); ?>about"><span></span>About</a></li>
					<li <?php echo ($this->uri->segment(1) == 'docs'?'class="active"':'');?>><a href="<?php echo base_url(); ?>docs"><span></span>Documentation</a></li>
					<li <?php echo ($this->uri->segment(1) == 'contact'?'class="active"':'');?>><a href="<?php echo base_url(); ?>contact"><span></span>Contact Us</a></li>
				</ul>
			</div>	
		</div>
			
		<div class="logo">
			<h1 onclick='document.location.href="<?php echo base_url()?>"'></h1>
			<p>Rangeland Hydrology and Erosion Model Web Tool</p>
		</div>
		<div class="date_bar">
			<span class="grey">
			<?php echo 'Now:       '. date('D, M d Y') ."\n"; ?>
			</span>
			<span class="grey" style="margin-left: 40px;">Current Version: RHEM v2.3 Update 4</span>
		</div>
        


