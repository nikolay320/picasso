<?php	
/*
 *	The template for displaying event categoroes - event location 
 *	d
 * 	In order to customize this archive page template
 *	Override this template by coping it to ../yourtheme/eventon/ folder
 
 *	@Author: AJDE
 *	@EventON
 *	@version: 0.1
 */	
	
	global $eventon;

	get_header();

	$tax = get_query_var( 'taxonomy' );
	$term = get_query_var( 'term' );

	$term = get_term_by( 'slug', $term, $tax );

	do_action('eventon_before_main_content');

	$term_meta = get_option( "taxonomy_".$term->term_id );

	// location image
		$img_url = false;
		if(!empty($term_meta['evo_loc_img'])){
			$img_url = wp_get_attachment_image_src($term_meta['evo_loc_img'],'full');
			$img_url = $img_url[0];
		}

	//location address
		$location_address = $location_latlan = false;
		$location_type = 'add';
			$location_latlan = '';

		if(!empty($term_meta['location_lat']) && $term_meta['location_lon']){
			$location_latlan = $term_meta['location_lat'].','.$term_meta['location_lon'];
			$location_type ='latlng';
			$location_address = true;
		}elseif(!empty($term_meta['location_address'])){
			$location_address = stripslashes($term_meta['location_address']);
		}

		
?>

<div id="content" class='evo_location_card'>
	<div class="hentry">
		<div class='eventon entry-content'>
			<div class="evo_location_tax" style='background-image:url(<?php echo $img_url;?>)'>
				<?php if($img_url):?><div class="location_circle" style='background-image:url(<?php echo $img_url;?>)'></div><?php endif;?>
				<h2 class="location_name"><span><?php echo $term->name;?></span></h2>
				<?php if($location_type=='add'):?><p class="location_address"><span><i class='fa fa-map-marker'></i> <?php echo $location_address;?></span></p><?php endif;?>
				<div class='location_description'><?php echo category_description();?></div>
			</div>
			<?php if($location_address):?><div id='evo_locationcard_gmap' class="evo_location_map" data-address='<?php echo $location_address;?>' data-latlng='<?php echo $location_latlan;?>' data-location_type='<?php echo $location_type;?>'data-zoom='16'></div><?php endif;?>
			<h3 class="location_subtitle"><?php evo_lang_e('Events at this location');?></h3>
		
		<?php 
			echo do_shortcode('[add_eventon_list number_of_months="5" '.$tax.'='.$term->term_id.' hide_mult_occur="no" hide_empty_months="yes"]');
		?>
		</div>
	</div>
</div>

<?php	do_action('eventon_after_main_content'); ?>

<?php //get_sidebar(); ?>
<?php get_footer(); ?>