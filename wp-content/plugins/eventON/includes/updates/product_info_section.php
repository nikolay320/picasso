<?php
/**
 * EventON Product Information for Sections in new update information
 * @version 0.1
 */
$data = array();
ob_start();?>                
<p>EventOn <b>#1 Best Selling</b> WordPress Event Calendar in codecanyon!</p><p>EventOn provide a stylish and minimal calendar design that address to the needs of your visitors and audience. It is also packed with awesome features such as: Repeat events, multi-day events, google map locations, smooth month navigation, featured images, and the list goes on.</p><p>To learn more about eventON please visit <a href="http://www.myeventon.com">myeventon.com</a>
<?php $data['description'] = ob_get_clean();

if(!function_exists('eventon_get_addon_installation')){
	function eventon_get_addon_installation($name='', $slug=''){
	    ob_start();
	    ?>
	    <h4>Minimum Requirements:</h4>
	    <p>WordPress 3.8 or higher, PHP 5.2.4 or higher, MySQL 5.0 or higher</p>

	    <h4>Automatic Installation</h4>
	    <p>In order to get automatic updates you will need to activate your version of <?php echo $name;?>. You can learn how to activate this plugin <a href='http://www.myeventon.com/documentation/how-to-get-new-auto-updates-for-eventon/' target='_blank'>in here</a>. Automatic updates will allow you to perform one-click updates to EventOn products direct from your wordpress dashboard.</p>

	    <h4>Manual Installation</h4>
	    <p><strong>Step 1:</strong></p>
	    <p>Download <code><?php echo $slug;?>.zip</code> from <?php echo ($slug=='eventon')? 'codecanyon > my downloads':'<a href="http://myeventon.com/my-account" target="_blank">myeventon.com/my-account</a>';?></p>
	    <p><strong>Step 2:</strong></p>
	    <p>Unzip the zip file content into your computer. </p>
	    <p><strong>Step 3:</strong></p>
	    <p>Open your FTP client and remove files inside <code>wp-content/plugins/<?php echo $slug;?>/</code> folder. </p>
	    <p><strong>Step 4:</strong></p>
	    <p>Update the zip file content into the above mentioned folder in your FTP client. </p>
	    <p><strong>Step 5:</strong></p>
	    <p>Go to <code>../wp-admin</code> of your website and confirm the new version has indeed been updated.</p>

	    <p><a href="http://www.myeventon.com/documentation/can-download-addon-updates/" target="_blank">More information on how to download & update eventON plugins and addons</a></p>

	    
	    <?php
	    return ob_get_clean();
	}
}

$eventon_product_information['eventon'] = array(
	'description'=>$data['description'],
	'installation'=>eventon_get_addon_installation('EventOn', 'eventon'),
	'register_license'=>'<p><strong>Get free updates</strong></p><p>In order to get free EventON updates and download them directly in here <strong>activate</strong> your copy of EventON with proper license.</p><p><strong>How to get your license key</strong></p><ol><li>Login into your Envato account</li><li>Go to Download tab</li><li>Under EventON click "License Cerificate"</li><li>Open text file and copy the <strong>Item Purchase Code</strong></li><li>Go to myEventON in your website admin</li><li>Under "Licenses" tab find the EventON license and click "Activate Now"</li><li>Paste the copied purchased code from envato, and click "Activate Now"</li><li>Once the license if verified and activated you will be able to download updates automatically</li></ol><br/><br/><p><a href="http://www.myeventon.com/documentation/how-to-find-eventon-license-key/" target="_blank">Updated Documentation</a></p>', 
);