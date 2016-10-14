	</div><!-- postbox-->
<div id="right-sidebar">	
<div id="sticky-anchor"></div>
<div class="postbox" id="sticky"> 
	<h3 style="cursor: default;" class="nowrap"><?php echo sprintf(__('Support %s', $this->WPB_PREFIX), $this->WPB_PLUGIN_NAME);?> </h3>
		<div class="inside">
			
				<?php
				$credits = $this->_credits;
				?>

				<div class="intro"><p><strong><?php _e('If you enjoyed, please support this plugin:', $this->WPB_PREFIX);?></strong></p></div>
				
				<p>
						<a href="http://wordpress.org/extend/plugins/<?php echo $this->WPB_SLUG;?>/"><?php _e('Rate the plugin 5★ on WordPress.org', $this->WPB_PREFIX);?></a>
				</p>
				<p>
						<a href="<?php echo $this->WPB_PLUGIN_URL.'/'.$this->WPB_PREFIX.'.po';?>"><?php _e('Translate the plugin to your language', $this->WPB_PREFIX);?></a>
				</p>
				
				<p>
						<a href="http://www.timersys.com/en/contact-us/"><?php _e('Hire me!', $this->WPB_PREFIX);?></a>
				</p>
				
				<p><?php _e('Invite me a coffee :', $this->WPB_PREFIX);?></p>
						<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
						<input type="hidden" name="cmd" value="_s-xclick">
						<input type="hidden" name="hosted_button_id" value="K4T6L69EV9G2Q">
						<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
						<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
						</form>
				<p> Other cool plugins:</p>
				<ul>
					<li><a href="http://wordpress.org/plugins/wpfavs/">Wp Favs</a> - Bulk Plugin installs</li>
					<li><a href="http://wordpress.org/plugins/wp-social-invitations">Wordpress Social Invitations</a></li>
					<li><a href="http://wordpress.org/plugins/popups/">Popups</a> - Multi popups plugins</li>

		
				
			
		
				<!--	<input type="submit" value="<?php _e('Save Settings','wsi');?>" class="button-large button-primary" name="save">-->
		</div>
</div>
 

</div><!--wsl_admin_tab_content-->

</div><!--wlsdiv-->