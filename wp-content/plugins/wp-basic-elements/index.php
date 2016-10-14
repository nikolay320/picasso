<?php
/**
* Plugin Name: WP Basic Elements
* Plugin URI: -
* Description: Disable unnecessary features and speed up your site. Make the WP Admin simple and clean. <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=DYLYJ242GX64J&lc=SE&item_name=WP%20Basic%20Elements&item_number=Support%20Open%20Source&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted" target="_blank">Donate</a>
* Version: 3.0.8
* Author: Damir Calusic
* Author URI: https://www.damircalusic.com/
* License: GPLv2
* License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/ 

/*  Copyright (C) 2014  Damir Calusic (email : damir@damircalusic.com)
	
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('WBE_VERSION', '3.0.8');

load_plugin_textdomain('wpbe', false, basename( dirname( __FILE__ ) ) . '/languages');

add_action('admin_menu', 'wpb_elements');

function wpb_elements() {
	add_action('admin_init', 'register_wpb_settings');
	add_submenu_page('options-general.php', 'WPB Elements', 'WPB Elements', 'manage_options', 'wpb_settings_page', 'wpb_settings_page');
}

function register_wpb_settings() {
	register_setting('wpb-settings-group', 'turnoffupdates');
	register_setting('wpb-settings-group', 'gzip');
	register_setting('wpb-settings-group', 'disableemojis');
	register_setting('wpb-settings-group', 'wprss');
	register_setting('wpb-settings-group', 'rsd');
	register_setting('wpb-settings-group', 'wlw');
	register_setting('wpb-settings-group', 'gen');
	register_setting('wpb-settings-group', 'qtxgen');
	register_setting('wpb-settings-group', 'irelink');
	register_setting('wpb-settings-group', 'prevlink');
	register_setting('wpb-settings-group', 'startlink');
	register_setting('wpb-settings-group', 'adjlinks');
	register_setting('wpb-settings-group', 'shortlink');
	register_setting('wpb-settings-group', 'pings');
	register_setting('wpb-settings-group', 'canonical');
	register_setting('wpb-settings-group', 'wpsol');
	register_setting('wpb-settings-group', 'wpchl');
	register_setting('wpb-settings-group', 'welcomewp');
	register_setting('wpb-settings-group', 'wpdbactivity');
	register_setting('wpb-settings-group', 'wpdbqpress');
	register_setting('wpb-settings-group', 'wpdbaag');
	register_setting('wpb-settings-group', 'wpdbrn');
	register_setting('wpb-settings-group', 'yseopo');
	register_setting('wpb-settings-group', 'wpbe_remove_woocoomerce_reviews_dasbhoard');
	register_setting('wpb-settings-group', 'wpdbbprn');
	register_setting('wpb-settings-group', 'wpbemenu');
	register_setting('wpb-settings-group', 'widgetshortcode');
	register_setting('wpb-settings-group', 'excerptshortcode');
	register_setting('wpb-settings-group', 'wplogo');
	register_setting('wpb-settings-group', 'wpnewcontent');
	register_setting('wpb-settings-group', 'sitename');
	register_setting('wpb-settings-group', 'customize');
	register_setting('wpb-settings-group', 'edit');
	register_setting('wpb-settings-group', 'wpupdates');
	register_setting('wpb-settings-group', 'wpsearch');
	register_setting('wpb-settings-group', 'wpcomments');
	register_setting('wpb-settings-group', 'wp3tc');
	register_setting('wpb-settings-group', 'a1s');
	register_setting('wpb-settings-group', 'yseo');
	register_setting('wpb-settings-group', 'wpzoom');
	register_setting('wpb-settings-group', 'vfb');
	register_setting('wpb-settings-group', 'ngg');
	register_setting('wpb-settings-group', 'colorsch');
	register_setting('wpb-settings-group', 'haim');
	register_setting('wpb-settings-group', 'hyim');
	register_setting('wpb-settings-group', 'hjabber');
	register_setting('wpb-settings-group', 'hgplus');
	register_setting('wpb-settings-group', 'footerleft');
	register_setting('wpb-settings-group', 'footerright');
	register_setting('wpb-settings-group', 'mailname');
	register_setting('wpb-settings-group', 'mailadress');
}

function wpb_settings_page() {
?>
    <form method="post" action="options.php" style="width:98%;color:rgba(128,128,128,1) !important;">
        <?php settings_fields('wpb-settings-group'); ?>
        <?php do_settings_sections('baw-settings-group'); ?>
        <div id="welcome-panel" class="welcome-panel">
            <label style="position:absolute;top:5px;right:10px;padding:20px 15px 0 3px;font-size:13px;text-decoration:none;line-height:1;">
            	<?php _e('Version','wpbe'); ?> <?php echo WBE_VERSION; ?>
            </label>
            <div class="welcome-panel-content">
                <h1><?php _e('WP Basic Elements','wpbe'); ?></h1>
                <p class="about-description"><?php _e('With WP Basic Elements you can disable unnecessary features and speed up your site. Make the WP Admin simple and clean. You can activate gzip compression, change admin footers in backend, activate shortcodes in widgets, remove admin toolbar options and you can clean the code markup from unnecessary code snippets like WordPress generator meta tag and a bunch of other non important code snippets in the code. Cleaning the code markup will speed up your sites loadtime and increase the overall performance.','wpbe'); ?></p>
                <div class="welcome-panel-column-container">
                    <div class="welcome-panel-column">
                        <h4><?php _e('Quick Info','wpbe'); ?></h4>
                        <p><?php _e('When you change something do not forget to click on this blue Save Changes button below this text. If you need more information just click on the Read More Information button next to the Save Changes button.','wpbe'); ?></p>
                        <p class="submit">
                        	<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes','wpbe'); ?>">
                            <a href="http://www.wknet.se/wp-basic-elements/" class="button button-secondary" target="_blank"><?php _e('Information','wpbe'); ?></a>
                       	</p>
                    </div>
                    <div class="welcome-panel-column">
                    	<h4><?php _e('Quick Tip','wpbe'); ?></h4>
                    	<p><?php _e('Follow me on Twitter to keep up with the latest updates and if you want you can donate to support open source. Just click on the buttons below to choose what you want to do.','wpbe'); ?></p>
                        <p class="submit">
                        	<a class="button button-secondary" href="https://twitter.com/damircalusic/" target="_blank">
                           		<?php _e('FOLLOW ON TWITTER','wpbe'); ?>
                        	</a>
                        	<a class="button button-secondary" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=DYLYJ242GX64J&lc=SE&item_name=WP%20Basic%20Elements&item_number=Support%20Open%20Source&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted" target="_blank">
                           		<?php _e('DONATE TO SUPPORT','wpbe'); ?>
                        	</a>
                     	</p>
                     </div>
                    <div class="welcome-panel-column welcome-panel-last"></div>
                </div>
            </div>
        </div>
        
        <div id="dashboard-widgets-wrap">
        	<div id="dashboard-widgets" class="metabox-holder">
            	
                <div id="postbox-container-1" class="postbox-container">
                    <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                    
                    	<div id="wpcore" class="postbox">
                        	<div class="handlediv" data-src="wpcore" title="<?php _e('Toggle content','wpbe'); ?>"><br></div>
                            <h3 class="hndle"><span><?php _e('WP Core','wpbe'); ?></span></h3>
							<div class="inside">
								<div class="main">
                                    <ul>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="wpbemenu" value="1" <?php echo checked(1, get_option('wpbemenu'), false); ?> />
                                                <?php _e('Add shortcut for WPB Elements in sidebar menu','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="gzip" value="1" <?php echo checked(1, get_option('gzip'), false); ?> />
                                                <?php _e('Enable GZIP compression (ob_start(\'ob_gzhandler\') used)','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="turnoffupdates" value="1" <?php echo checked(1, get_option('turnoffupdates'), false); ?> />
                                                <?php _e('Disable Plugins, WordPress and Themes update notifications for non-admins.','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="disableemojis" value="1" <?php echo checked(1, get_option('disableemojis'), false); ?> />
                                                <?php _e('Disable Emoji icons if not in use.','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="widgetshortcode" value="1" <?php echo checked(1, get_option('widgetshortcode'), false); ?> />
                                                <?php _e('Enable shortcode in widgets','wpbe'); ?>
                                            </label>
                                        </li>
                                         <li>
                                            <label>
                                                <input type="checkbox" name="excerptshortcode" value="1" <?php echo checked(1, get_option('excerptshortcode'), false); ?> />
                                                <?php _e('Enable shortcode in manual excerpts','wpbe'); ?>
                                            </label>
                                        </li>
                                    </ul>
                                 </div>
                          	</div>
						</div>
                        
                        <div id="wpoptimisation" class="postbox">
                        	<div class="handlediv" data-src="wpoptimisation" title="<?php _e('Toggle content','wpbe'); ?>"><br></div>
                            <h3 class="hndle"><span><?php _e('WP Optimisation','wpbe'); ?></span></h3>
							<div class="inside">
								<div class="main">
                                	<p><?php _e('Disable features from the wp_head() function and make your code cleaner.','wpbe'); ?></p>
                                    <ul>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="wprss" value="1" <?php echo checked(1, get_option('wprss'), false); ?> />
                                                <?php _e('Remove Post, Comment and Category feeds','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="rsd" value="1" <?php echo checked(1, get_option('rsd'), false); ?> />
                                                <?php _e('Remove EditURI link','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="wlw" value="1" <?php echo checked(1, get_option('wlw'), false); ?> />
                                                <?php _e('Remove Windows Live Writer Manifest File','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="gen" value="1" <?php echo checked(1, get_option('gen'), false); ?> />
                                                <?php _e('Remove WordPress &amp; WooCommerce Generator Meta Tag','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="qtxgen" value="1" <?php echo checked(1, get_option('qtxgen'), false); ?> />
                                                <?php _e('Remove qTranslate-X Generator Meta Tag','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="irelink" value="1" <?php echo checked(1, get_option('irelink'), false); ?> />
                                                <?php _e('Remove Index link','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="prevlink" value="1" <?php echo checked(1, get_option('prevlink'), false); ?> />
                                                <?php _e('Remove Prev link','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="startlink" value="1" <?php echo checked(1, get_option('startlink'), false); ?> />
                                                <?php _e('Remove Start link','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="adjlinks" value="1" <?php echo checked(1, get_option('adjlinks'), false); ?> />
                                                <?php _e('Remove Relational links for the Posts','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="shortlink" value="1" <?php echo checked(1, get_option('shortlink'), false); ?> />
                                                <?php _e('Remove WordPress Shortlink','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="pings" value="1" <?php echo checked(1, get_option('pings'), false); ?> />
                                                <?php _e('Remove WordPress Pingbacks','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="canonical" value="1" <?php echo checked(1, get_option('canonical'), false); ?> />
                                                <?php _e('Remove Canonical link','wpbe'); ?>
                                            </label>
                                        </li>
                                    </ul>
                                    
                                 </div>
                          	</div>
						</div>
                        
                        <div id="wpdashboard" class="postbox">
                        	<div class="handlediv" data-src="wpdashboard" title="<?php _e('Toggle content','wpbe'); ?>"><br></div>
                            <h3 class="hndle"><span><?php _e('WP Dashboard','wpbe'); ?></span></h3>
							<div class="inside">
								<div class="main">
                                	<p><?php _e('Disable features from the main WordPress Dashboard and make it a little bit cleaner.','wpbe'); ?></p>
                                    <ul>
                                    	<li>
                                            <label>
                                                <input type="checkbox" name="wpsol" value="1" <?php echo checked(1, get_option('wpsol'), false); ?> />
                                                <?php _e('Remove Screen Options from top right corner','wpbe'); ?>
                                            </label>
                                        </li>
                                    	<li>
                                            <label>
                                                <input type="checkbox" name="wpchl" value="1" <?php echo checked(1, get_option('wpchl'), false); ?> />
                                                <?php _e('Remove Help link from top right corner','wpbe'); ?>
                                            </label>
                                        </li>
                                    	<li>
                                            <label>
                                                <input type="checkbox" name="welcomewp" value="1" <?php echo checked(1, get_option('welcomewp'), false); ?> />
                                                <?php _e('Remove Welcome to WordPress!','wpbe'); ?>
                                            </label>
                                        </li>
                                    	<li>
                                            <label>
                                                <input type="checkbox" name="wpdbaag" value="1" <?php echo checked(1, get_option('wpdbaag'), false); ?> />
                                                <?php _e('Remove At a Glance','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="wpdbactivity" value="1" <?php echo checked(1, get_option('wpdbactivity'), false); ?> />
                                                <?php _e('Remove Activity','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="wpdbqpress" value="1" <?php echo checked(1, get_option('wpdbqpress'), false); ?> />
                                                <?php _e('Remove Quick Draft','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="wpdbrn" value="1" <?php echo checked(1, get_option('wpdbrn'), false); ?> />
                                                <?php _e('Remove WordPress News','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="yseopo" value="1" <?php echo checked(1, get_option('yseopo'), false); ?> />
                                                <?php _e('Remove Yoast SEO Posts Overview','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="wpbe_remove_woocoomerce_reviews_dasbhoard" value="1" <?php echo checked(1, get_option('wpbe_remove_woocoomerce_reviews_dasbhoard'), false); ?> />
                                                <?php _e('Remove WooCommerce Latest Reviews','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="wpdbbprn" value="1" <?php echo checked(1, get_option('wpdbbprn'), false); ?> />
                                                <?php _e('Remove bbPress Right Now','wpbe'); ?>
                                            </label>
                                        </li>
                                    </ul>
                                    
                                 </div>
                          	</div>
						</div>
                
                    </div>
            	</div>
                
                <div id="postbox-container-2" class="postbox-container">
                	<div id="side-sortables" class="meta-box-sortables ui-sortable">
                    	
                        <div id="generellt" class="postbox">
                        	<div class="handlediv" data-src="generellt" title="<?php _e('Toggle content','wpbe'); ?>"><br></div>
                            <h3 class="hndle"><span><?php _e('WP Admin Toolbar','wpbe'); ?></span></h3>
							<div class="inside">
								<div class="main">
                                	<p><?php _e('Check the ones you would like to disable in the admin toolbar. You are not going to delete anything instead you will just make your toolbar cleaner and more friendly.','wpbe'); ?></p>
                                    <p><?php _e('Just test and see what happens in the toolbar. If you want anything back just uncheck and it will appear again.','wpbe'); ?></p>
                                    <ul>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="wplogo" value="1" <?php echo checked(1, get_option('wplogo'), false); ?> />
                                                <?php _e('Remove WP Logo','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="wpnewcontent" value="1" <?php echo checked(1, get_option('wpnewcontent'), false); ?> />
                                                <?php _e('Remove WP New Content','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="sitename" value="1" <?php echo checked(1, get_option('sitename'), false); ?> />
                                                <?php _e('Remove WP Sitename','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="customize" value="1" <?php echo checked(1, get_option('customize'), false); ?> />
                                                <?php _e('Remove WP Customize','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="edit" value="1" <?php echo checked(1, get_option('edit'), false); ?> />
                                                <?php _e('Remove WP Edit','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="wpupdates" value="1" <?php echo checked(1, get_option('wpupdates'), false); ?> />
                                                <?php _e('Remove WP Updates','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="wpcomments" value="1" <?php echo checked(1, get_option('wpcomments'), false); ?> />
                                                <?php _e('Remove WP Comments','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="wpsearch" value="1" <?php echo checked(1, get_option('wpsearch'), false); ?> />
                                                <?php _e('Remove WP Search','wpbe'); ?>
                                            </label>
                                        </li>
                                    </ul>
                                    <p><strong><?php _e('Plugins (if used)','wpbe'); ?></strong></p>
                                    <ul>    
                                        <li>
                                            <label>
                                                <input type="checkbox" name="wp3tc" value="1" <?php echo checked(1, get_option('wp3tc'), false); ?> />
                                                <?php _e('Remove W3 Total Cache','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="a1s" value="1" <?php echo checked(1, get_option('a1s'), false); ?> />
                                                <?php _e('Remove All in One Seo','wpbe'); ?>
                                            </label>
                                        </li> 
                                        <li>
                                            <label>
                                                <input type="checkbox" name="yseo" value="1" <?php echo checked(1, get_option('yseo'), false); ?> />
                                                <?php _e('Remove Yoast SEO','wpbe'); ?>
                                            </label>
                                        </li> 
                                        <li>
                                            <label>
                                                <input type="checkbox" name="wpzoom" value="1" <?php echo checked(1, get_option('wpzoom'), false); ?> />
                                                <?php _e('Remove WP Zoom Framework','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="vfb" value="1" <?php echo checked(1, get_option('vfb'), false); ?> />
                                                <?php _e('Remove Visual Form Builder','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="ngg" value="1" <?php echo checked(1, get_option('ngg'), false); ?> />
                                                <?php _e('Remove NextGen Gallery link','wpbe'); ?>
                                            </label>
                                        </li> 
                                    </ul>
                                 </div>
                          	</div>
						</div>
                        
                        <div id="wpusers" class="postbox">
                        	<div class="handlediv" data-src="wpusers" title="<?php _e('Toggle content','wpbe'); ?>"><br></div>
                            <h3 class="hndle"><span><?php _e('WP Users','wpbe'); ?></span></h3>
							<div class="inside">
								<div class="main">
                                	<p><?php _e('Disable unnecessary fields that you do not want to display to your users in admin backend.','wpbe'); ?></p>
                                	<ul>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="colorsch" value="1" <?php echo checked(1, get_option('colorsch'), false); ?> />
                                                <?php _e('Disable Color Scheme selector for users','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="haim" value="1" <?php echo checked(1, get_option('haim'), false); ?> />
                                                <?php _e('Disable AIM field from users contact field','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="hjabber" value="1" <?php echo checked(1, get_option('hjabber'), false); ?> />
                                                <?php _e('Disable Jabber field from users contact field','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="hyim" value="1" <?php echo checked(1, get_option('hyim'), false); ?> />
                                                <?php _e('Disable Yahoo IM field from users contact field','wpbe'); ?>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="checkbox" name="hgplus" value="1" <?php echo checked(1, get_option('hgplus'), false); ?> />
                                                <?php _e('Disable Google Plus field from users contact field','wpbe'); ?>
                                            </label>
                                        </li>
                                    </ul>    
                                </div>
                          	</div>
						</div>
                        
                    </div>
               	</div>
                
                <div id="postbox-container-3" class="postbox-container">
                	<div id="column3-sortables" class="meta-box-sortables ui-sortable">
                    
                    	<div id="wpadminfooter" class="postbox">
                        	<div class="handlediv" data-src="wpadminfooter" title="<?php _e('Toggle content','wpbe'); ?>"><br></div>
                            <h3 class="hndle"><span><?php _e('WP Admin Footer','wpbe'); ?></span></h3>
							<div class="inside">
								<div class="main">
                                	<p><?php _e('Change the default footer text to what you want.','wpbe'); ?></p>
                                	<ul>
                                        <li>
                                            <label style="display:block;margin-bottom:5px;">
                                                <?php _e('Text Left (HTML allowed)','wpbe'); ?>
                                            </label>
                                            <textarea type="text" name="footerleft" style="width:100%;height:100px;"><?php echo get_option('footerleft'); ?></textarea>
                                        </li>
                                        <li>
                                            <label style="display:block;margin-bottom:5px;">
                                                <?php _e('Text Right (HTML allowed)','wpbe'); ?>
                                            </label>
                                            <textarea type="text" name="footerright" style="width:100%;height:100px;"><?php echo get_option('footerright'); ?></textarea>
                                        </li>
                                    </ul>
                                </div>
                          	</div>
						 </div>
                         
                         <div id="wpmail" class="postbox">
                        	<div class="handlediv" data-src="wpmail" title="<?php _e('Toggle content','wpbe'); ?>"><br></div>
                            <h3 class="hndle"><span><?php _e('WP Mail','wpbe'); ?></span></h3>
							<div class="inside">
								<div class="main">
                                	<ul>
                                        <li>
                                            <label style="display:block;margin-bottom:5px;">
                                                <?php _e('Change mail name <strong>(WordPress)</strong> sent to users to your own','wpbe'); ?>
                                            </label>
                                            <input type="text" name="mailname" style="width:100%;" value="<?php echo get_option('mailname'); ?>" placeholder="<?php _e('Example:','wpbe');?> <?php echo get_bloginfo('name'); ?>" />
                                        </li>
                                        <li>
                                            <label style="display:block;margin-bottom:5px;">
                                                <?php _e('Change mail adress <strong>(wordpress@mysite.com)</strong> sent to users','wpbe'); ?>
                                            </label>
                                            <input type="text" name="mailadress" style="width:100%;" value="<?php echo get_option('mailadress'); ?>" placeholder="<?php _e('Example:','wpbe');?> <?php echo get_bloginfo('admin_email'); ?>" />
                                        </li>
                                    </ul>
                                </div>
                          	</div>
						 </div>
                        
                    
                    </div>
               	</div>
            
            </div>
        </div>
        
    </form>
    <script>
		jQuery(document).ready(function( $ ) {
			$('.handlediv').click(function() {
				var div = $(this).attr("data-src");
				if($("#" + div).hasClass("closed")){
					$("#" + div).removeClass("closed");
				}
				else{
					$("#" + div).addClass("closed");
				}
			});
		});
	</script>
<?php 
}  

function gzip(){ 
    ob_start('ob_gzhandler');
}

function turnoffupdates(){
	if(!current_user_can('manage_options')){
		function remove_core_updates(){
			global $wp_version;
			return (object)array('last_checked'=> time(), 'version_checked'=> $wp_version);
		}
	
		add_filter('pre_site_transient_update_core','remove_core_updates');
		add_filter('pre_site_transient_update_plugins','remove_core_updates');
		add_filter('pre_site_transient_update_themes','remove_core_updates');
	}
}

function wpb_shortcut(){ 
	add_menu_page('WPB Elements', 'WPB Elements', 'manage_options', __FILE__, 'wpb_settings_page', 'dashicons-awards', 3);
}

function remove_wplogo() {   
    global $wp_admin_bar;
	$wp_admin_bar->remove_menu('wp-logo'); 
}

function remove_wpnewcontent() {   
    global $wp_admin_bar;
	$wp_admin_bar->remove_menu('new-content'); 
}

function remove_sitename() {   
    global $wp_admin_bar;
	$wp_admin_bar->remove_menu('site-name');  
}

function remove_customize() {   
    global $wp_admin_bar;
	$wp_admin_bar->remove_menu('customize');  
}

function remove_edit() {   
    global $wp_admin_bar;
	$wp_admin_bar->remove_menu('edit');  
}

function remove_wpupdates() {
    global $wp_admin_bar;
	$wp_admin_bar->remove_menu('updates'); 
}

function remove_wpsearch() {
	global $wp_admin_bar;
    $wp_admin_bar->remove_menu('search'); 
}

function remove_wpcomments() {
	global $wp_admin_bar;
    $wp_admin_bar->remove_menu('comments'); 
}

function remove_wp3tc() {
	global $wp_admin_bar;
    $wp_admin_bar->remove_menu('w3tc'); 
}

function remove_a1s() {
	global $wp_admin_bar;
    $wp_admin_bar->remove_menu('all-in-one-seo-pack'); 
}

function remove_yseo() {
	global $wp_admin_bar;
    $wp_admin_bar->remove_menu('wpseo-menu'); 
}

function remove_wpzoom() {
	global $wp_admin_bar;
    $wp_admin_bar->remove_menu('wpzoom'); 
}

function remove_vfb() {
	global $wp_admin_bar;
    $wp_admin_bar->remove_menu('vfb_admin_toolbar'); 
}

function remove_ngg() {
	global $wp_admin_bar;
    $wp_admin_bar->remove_menu('ngg-menu'); 
}

function remove_pings($headers) {
	unset($headers['X-Pingback']);
	return $headers;
}

function hide_aim($contactmethods) {
	unset($contactmethods['aim']);
	return $contactmethods;
}

function hide_jabber($contactmethods) {
	unset($contactmethods['jabber']);
	return $contactmethods;
}

function hide_yim($contactmethods) {
	unset($contactmethods['yim']);
	return $contactmethods;
}

function hide_gplus($contactmethods) {
	unset($contactmethods['googleplus']);
	return $contactmethods;
}

function remove_screen_options(){
	?>
    <style type="text/css">
		#screen-options-link-wrap #show-settings-link{display:none !important;}
		#contextual-help-link-wrap, #screen-options-link-wrap{border:none !important;}
	</style>
    <?php
}

function remove_help_link(){
	?>
    <style type="text/css">
		#contextual-help-link-wrap #contextual-help-link{display:none !important;}
		#contextual-help-link-wrap, #screen-options-link-wrap{border:none !important;}
	</style>
    <?php
}

function disable_emojis() {
	remove_action('wp_head', 'print_emoji_detection_script', 7);
	remove_action('admin_print_scripts', 'print_emoji_detection_script');
	remove_action('wp_print_styles', 'print_emoji_styles');
	remove_action('admin_print_styles', 'print_emoji_styles');	
	remove_filter('the_content_feed', 'wp_staticize_emoji');
	remove_filter('comment_text_rss', 'wp_staticize_emoji');	
	remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
}

function remove_qtx_generator(){
	remove_action('wp_head', 'qtranxf_wp_head_meta_generator');
}

function remove_aag_dashboard(){
	remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
}

function remove_activity_dashboard(){
	remove_meta_box('dashboard_activity', 'dashboard', 'normal');
}

function remove_quick_press_dashboard(){
	remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
}

function remove_wpnews_dashboard(){
	remove_meta_box('dashboard_primary', 'dashboard', 'side');
}

function remove_yseopo_dashboard(){
	remove_meta_box('wpseo-dashboard-overview', 'dashboard', 'normal');
}

function wpbe_remove_woocoomerce_reviews_dasbhoard(){
	remove_meta_box('woocommerce_dashboard_recent_reviews', 'dashboard', 'normal');
}

function remove_wpbprn_dashboard(){
	remove_meta_box('bbp-dashboard-right-now', 'dashboard', 'side');
}

function admin_footer_left() { 
     echo get_option('footerleft'); 
}

function admin_footer_right() { 
     return get_option('footerright'); 
}

function new_mail_from($old) {
	return get_option('mailadress');
}

function new_mail_from_name($old) {
	return get_option('mailname');
}

// Disable Plugins, WordPress and Themes update notifications for non-admins
if(get_option('turnoffupdates') == '1'){ add_action('plugins_loaded', 'turnoffupdates'); } 

// Initiate GZIP on WordPress site
if(get_option('gzip') == '1'){ add_action('init', 'gzip'); } 

// Disable Emoji icons if not in use
if(get_option('disableemojis') == '1'){ add_action('init', 'disable_emojis'); } 

// Remove Category, Post and Comment feeds
if(get_option('wprss') == '1'){ remove_action('wp_head', 'feed_links_extra', 3); remove_action('wp_head', 'feed_links', 2); }

// Remove the EditURI link (Really Simple Discovery service endpoint)
if(get_option('rsd') == '1'){ remove_action('wp_head', 'rsd_link'); }

// Remove Windows Live Writer manifest file
if(get_option('wlw') == '1'){ remove_action('wp_head', 'wlwmanifest_link'); }

// Remove WordPress / WooCommerce generator tag
if(get_option('gen') == '1'){ remove_action('wp_head', 'wp_generator'); }

// Remove q-Translate-X generator meta tag
if(get_option('qtxgen') == '1'){ add_action('init', 'remove_qtx_generator'); }

// Remove Index link
if(get_option('irelink') == '1'){ remove_action('wp_head', 'index_rel_link'); }

// Remove Prev link
if(get_option('prevlink') == '1'){ remove_action('wp_head', 'parent_post_rel_link', 10, 0); }

// Remove Start link
if(get_option('startlink') == '1'){ remove_action('wp_head', 'start_post_rel_link', 10, 0); }

// Remove relational links for the Posts
if(get_option('adjlinks') == '1'){ remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0); }

// Remove WordPress Shortlink
if(get_option('shortlink') == '1'){ remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0); }

// Remove Pings from header
if(get_option('pings') == '1'){ add_filter('wp_headers', 'remove_pings'); }

// Remove Canonical link
if(get_option('canonical') == '1'){ remove_action('wp_head', 'rel_canonical'); }

// Remove Screen Options link from dashboard
if(get_option('wpsol') == '1'){ add_action('admin_head', 'remove_screen_options'); }

// Remove Help link from dashboard
if(get_option('wpchl') == '1'){ add_action('admin_head', 'remove_help_link'); }

// Remove Welcome to WordPress from dashboard
if(get_option('welcomewp') == '1'){ remove_action('welcome_panel', 'wp_welcome_panel'); }

// Remove At a Glance from dashboard
if(get_option('wpdbaag') == '1'){ add_action('wp_dashboard_setup', 'remove_aag_dashboard'); }

// Remove Activity from dashboard
if(get_option('wpdbactivity') == '1'){ add_action('wp_dashboard_setup', 'remove_activity_dashboard'); }

// Remove Quick Press from dashboard
if(get_option('wpdbqpress') == '1'){ add_action('wp_dashboard_setup', 'remove_quick_press_dashboard'); }

// Remove WordPress News from dashboard
if(get_option('wpdbrn') == '1'){ add_action('wp_dashboard_setup', 'remove_wpnews_dashboard'); }

// Remove Yoast SEO Posts Overview
if(get_option('yseopo') == '1'){ add_action('wp_dashboard_setup', 'remove_yseopo_dashboard'); }

// Remove WooCommerce Reviews from dashboard
if(get_option('wpbe_remove_woocoomerce_reviews_dasbhoard') == '1'){ add_action('wp_dashboard_setup', 'wpbe_remove_woocoomerce_reviews_dasbhoard'); }

// Remove bbPress Right Now from dashboard
if(get_option('wpdbbprn') == '1'){ add_action('wp_dashboard_setup', 'remove_wpbprn_dashboard'); }

// Add WPB Elements in main admin menu
if(get_option('wpbemenu') == '1'){ add_action('admin_menu', 'wpb_shortcut'); }

// Add shortcode ability to widgets
if(get_option('widgetshortcode') == '1'){ add_filter('widget_text', 'do_widgetshortcode'); } 

// Add shortcode ability to manual excerpts
if(get_option('excerptshortcode') == '1'){ add_filter('the_excerpt', 'do_shortcode'); } 

// Remove WP Logo in toolbar
if(get_option('wplogo') == '1'){ add_action('wp_before_admin_bar_render', 'remove_wplogo'); } 

// Remove WP New Content in toolbar
if(get_option('wpnewcontent') == '1'){ add_action('wp_before_admin_bar_render', 'remove_wpnewcontent'); } 

// Remove Site Name in toolbar
if(get_option('sitename') == '1'){ add_action('wp_before_admin_bar_render', 'remove_sitename'); } 

// Remove WP Customize in toolbar
if(get_option('customize') == '1'){ add_action('wp_before_admin_bar_render', 'remove_customize'); } 

// Remove WP Edit in toolbar
if(get_option('edit') == '1'){ add_action('wp_before_admin_bar_render', 'remove_edit'); } 

// Remove WP Updates in toolbar
if(get_option('wpupdates') == '1'){ add_action('wp_before_admin_bar_render', 'remove_wpupdates'); } 

// Remove WP Search in toolbar
if(get_option('wpsearch') == '1'){ add_action('wp_before_admin_bar_render', 'remove_wpsearch'); } 

// Remove WP Comments in toolbar
if(get_option('wpcomments') == '1'){ add_action('wp_before_admin_bar_render', 'remove_wpcomments'); } 

// Remove W3 Total Cache in toolbar
if(get_option('wp3tc') == '1'){ add_action('wp_before_admin_bar_render', 'remove_wp3tc'); } 

// Remove All in One Seo Pack in toolbar
if(get_option('a1s') == '1'){ add_action('wp_before_admin_bar_render', 'remove_a1s'); }

// Remove Yoast SEO in toolbar
if(get_option('yseo') == '1'){ add_action('wp_before_admin_bar_render', 'remove_yseo'); }

// Remove WP Zoom Framework in toolbar
if(get_option('wpzoom') == '1'){ add_action('wp_before_admin_bar_render', 'remove_wpzoom'); }

// Remove Visual Form Builder in toolbar
if(get_option('vfb') == '1'){ add_action('wp_before_admin_bar_render', 'remove_vfb'); }

// Remove NextGen Gallery from  the toolbar
if(get_option('ngg') == '1'){ add_action('wp_before_admin_bar_render', 'remove_ngg'); }

// Remove Website URL from Users contact info
if(get_option('colorsch') == '1'){ remove_action('admin_color_scheme_picker', 'admin_color_scheme_picker'); }

// Remove AIM from Users contact info
if(get_option('haim') == '1'){ add_filter('user_contactmethods', 'hide_aim', 999, 1); }

// Remove Jabber from Users contact info
if(get_option('hjabber') == '1'){ add_filter('user_contactmethods', 'hide_jabber', 999, 1); }

// Remove Yahoo IM from Users contact info
if(get_option('hyim') == '1'){ add_filter('user_contactmethods', 'hide_yim', 999, 1); }

// Remove Google Plus from Users contact info
if(get_option('hgplus') == '1'){ add_filter('user_contactmethods', 'hide_gplus', 999, 1); }

// Add custom text to the admin footer left
if(get_option('footerleft') != ''){ add_filter('admin_footer_text', 'admin_footer_left'); } 

// Add custom text to the admin footer right
if(get_option('footerright') != ''){ add_filter('update_footer', 'admin_footer_right', '1234'); }

// Change WordPress custom name to your own name
if(get_option('mailname') != ''){ add_filter('wp_mail_from_name', 'new_mail_from_name'); }

// Change WordPress cusotm name
if(get_option('mailadress') != ''){ add_filter('wp_mail_from', 'new_mail_from'); }