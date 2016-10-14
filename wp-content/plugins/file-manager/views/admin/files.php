<?php
/**
 * 
 * Security check. No one can access without Wordpress itself
 * 
 * */
defined('ABSPATH') or die();

?>

<?php 
	
	
	
?>

<div id='file-manager-wrapper'>

</div>

<script>

PLUGINS_URL = '<?php echo plugins_url();?>';

jQuery(document).ready(function(){
	jQuery('#file-manager-wrapper').elfinder({
		url: ajaxurl,
		customData:{action: 'connector'}
	});
});

</script>
