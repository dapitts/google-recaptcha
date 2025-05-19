<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
	<div class="col-md-12">		  
		<div class="login-container">		    
			<div class="login-wrapper">			    
				<div class="login-content">
					
					<div class="login-logo">
						<img src="/_/img/quadrant-q-flat.png" />
						<h4>Information Security</h4>
					</div>
					
					<?php echo form_open($this->uri->uri_string()); ?>
						<div class="form-group display">
							<div class="user-display">
								<div>Password Reset</div>				
							</div>								
						</div>	
						
						<div class="form-group">
							<div class="input-group input-group-lg">
								<span class="input-group-addon"><i class="fas fa-user"></i></span>
								<input type="text" class="form-control field-focus" name="login" id="login" value="<?php echo $this->session->tempdata('tmp_username'); ?>" maxlength="80" placeholder="Enter Your Username" tabindex="1" />
							</div>
						</div>							

						<?php if ($use_recaptcha) { ?>
							<div class="form-group">
								<div class="g-recaptcha" data-sitekey="<?php echo $site_key; ?>"></div>
							</div>
						<?php } ?>

						<div class="form-group">
							<button type="submit" class="btn btn-quad-gold btn-lg btn-block" data-loading-text="Please Wait...">Continue</button>
						</div>
						
						<div class="form-group display">
							<div class="action-link">
								<a href="/auth/clear">Return To Login</a>
							</div>
						</div>
						
					<?php echo form_close(); ?>

				</div>		    	
			</div>		    
		</div>    
	</div>
</div>