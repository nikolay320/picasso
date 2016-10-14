<?php
/*
Plugin Name: Campaigns plugin
Description: Custom Campaigns plugin
Version: 1.0
Author: Alex Karev
*/


function campaigns_load_assets(){
	wp_enqueue_style('campaigns_metabox-styles', plugins_url('/assets/css/metaboxes.css?v='.time(),__FILE__));
	wp_enqueue_script('campaigns_custom-js', plugins_url('/assets/js/metaboxes.js?v='.time(),__FILE__), 'jquery-ui-core', '1.0', false);
	wp_enqueue_style('campaigns_jquery-select2', plugins_url('/assets/css/select2.css?v='.time(),__FILE__));
	wp_enqueue_script('campaigns_select2-js', plugins_url('/assets/js/select2.min.js?v='.time(),__FILE__), array('jquery'), time(), false);
	wp_enqueue_style('campaigns_datetimepicker-styles', plugins_url('/assets/css/jquery.datetimepicker.css?v='.time(),__FILE__));
	wp_enqueue_script('campaigns_datetimepicker-js', plugins_url('/assets/js/jquery.datetimepicker.full.min.js?v='.time(),__FILE__),'jquery','1.0',true);
}
add_action( 'admin_enqueue_scripts','campaigns_load_assets' );

function campaigns_front_assets(){
	wp_enqueue_style('campaigns_flipclock_styles', plugins_url('/assets/css/flipclock.css?v='.time(),__FILE__));
	wp_enqueue_style('campaigns_styles', plugins_url('/assets/css/style.css?v='.time(),__FILE__));
	wp_enqueue_script('campaigns_flipclock_js', plugins_url('/assets/js/flipclock.min.js?v='.time(),__FILE__), array('jquery'), time(), false);
}
add_action( 'wp_enqueue_scripts','campaigns_front_assets' );

function create_pt_campaigns() {
	register_post_type( 'campaigns', /* (http://codex.wordpress.org/Function_Reference/register_post_type) */
	 	array('labels' => array(
			'name' => __('Campaigns', IDEAS_TEXT_DOMAIN), /* This is the Title of the Group */
			'singular_name' => __('Campaign'), /* This is the individual type */
			'all_items' => __('All Campaigns'), /* the all items menu item */
			'add_new' => __('Add New Campaign'), /* The add new menu item */
			'add_new_item' => __('Add New Campaign'), /* Add New Display Title */
			'edit' => __( 'Edit' ), /* Edit Dialog */
			'edit_item' => __('Edit Campaign'), /* Edit Display Title */
			'new_item' => __('New Campaign'), /* New Display Title */
			'view_item' => __('View Campaign'), /* View Display Title */
			'search_items' => __('Search Campaign'), /* Search Product Title */ 
			'not_found' =>  __('Nothing found in the Database.'), /* This displays if there are no entries yet */ 
			'not_found_in_trash' => __('Nothing found in Trash'), /* This displays if there is nothing in the trash */
			'parent_item_colon' => ''
			), 
			'description' => __( 'Homepage campaigns for '.get_bloginfo() ), /* Product Description */
			'public' => true,
			'menu_icon' => 'dashicons-flag',
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'query_var' => true,
			'rewrite'	=> array( 'slug' => 'campaigns', 'with_front' => false ), /* you can specify its url slug */
			'has_archive' => true, /* you can rename the slug here */
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => array( 'title','post-options','editor','page-attributes','comments'),
	 	) /* end of options */
	); /* end of register post type */
}

add_action( 'init', 'create_campaigns_cat_tax' );

function create_campaigns_cat_tax() {
	$args = array(
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'campaigns_category' ),
	);
	register_taxonomy( 'campaigns_category', array( 'campaigns' ), $args );
}

function campaigns_meta_boxes(){
	add_meta_box( 'campaigns_meta_box',
        'Campaign settings',
        'display_campaigns_meta_box',
        'campaigns', 
        'normal', 
        'high'
    );
}

function display_campaigns_meta_box(){
	global $post;
	$campaign_image_url = get_post_meta($post->ID, 'campaign_image_url', true);
	$campaign_image_id = get_post_meta($post->ID, 'campaign_image_id', true);
	$campaign_video_url = get_post_meta($post->ID, 'campaign_video_url', true);
	$campaign_end_date = get_post_meta($post->ID, 'campaign_end_date', true);
	$campaign_ideas = get_post_meta($post->ID, 'campaign_ideas', false);
	$ideas = get_posts( array (
		'post_type'	=> 'ideas',
		'posts_per_page' => -1
	));
?>
    <script>
       jQuery(document).ready(function($){
            $('#add_image_button').on('click',function() {
                showMediaUploader($('#campaign_image_url'),$('#campaign_image_id'),$('.campaign_image_img'));
            });
            $('#clear_image_button').on('click',function() {
            	$('#campaign_image_url').attr('value','');
            	$('#campaign_image_id').attr('value','');
            	$('.campaign_image_img').attr('src','<?php echo plugins_url('/assets/img/noimage.png',__FILE__) ?>' );
            })
        });
    </script>
	<table class="form-table">
		<tr>
			<th><label for="campaigns_list">Image</label></th>
			<td>
				<img src="<?php echo $campaign_image_url?$campaign_image_url:plugins_url('/assets/img/noimage.png',__FILE__) ?>" class="campaign_image_img" style="float:left; margin-right:20px; max-width: 100px;">
				<label for="campaign_image_url">
					<input id="campaign_image_url" type="text" size="36" name="campaign_image_url" value="<?php echo $campaign_image_url ?>" readonly/>
					<div style="margin:10px 0">Enter a URL or upload an image</div>
					<input id="campaign_image_id" type="hidden" size="36" name="campaign_image_id" value="<?php echo $campaign_image_id ?>" />
					<input id="add_image_button" class="button button-primary" type="button" value="Add Image" />
					<input id="clear_image_button" class="button button-default" type="button" value="Clear" />
				</label>
			</td>
		</tr>
		<tr>
			<th><label for="campaigns_list">Video (youtube)</label></th>
			<td>
				<label for="campaign_image_url">
					<input id="campaign_video_url" type="text" size="36" name="campaign_video_url" value="<?php echo $campaign_video_url ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th><label for="campaigns_list">End date</label></th>
			<td>
				<label for="campaign_image_url">
					<input id="campaign_end_date" type="text" size="36" name="campaign_end_date" value="<?php echo $campaign_end_date ?>"/>
				</label>
			</td>
		</tr>
		<?php if( post_type_exists('ideas') ): ?>
			<tr>
				<th><label for="ideas_list">Ideas</label></th>
				<td>
					<select name="campaign_ideas[]" id="campaign_ideas" multiple="multiple">
						<?php foreach($ideas as $child ): ?>
							<option value="<?php echo $child->ID ?>", <?php echo $campaign_ideas&&in_array($child->ID,$campaign_ideas)?' selected="selected"':''?> ><?php echo $child->post_title ?></option>;
						<?php endforeach ?>
					</select>
				</td>
			</tr>
		<?php endif ?>
	</table>
<?php 
}

function save_campaigns_custom_meta( $id, $item ) {
    // Check post type
    if ( $item->post_type == 'campaigns' ) {
        // Store data in post meta table if present in post data
        if ( isset($_POST['campaign_image_url']) ) {
            update_post_meta( $id, 'campaign_image_url',$_POST['campaign_image_url'] );
        }
        if ( isset($_POST['campaign_image_id']) ) {
            update_post_meta( $id, 'campaign_image_id',$_POST['campaign_image_id'] );
        }
        if ( isset($_POST['campaign_video_url']) ) {
            update_post_meta( $id, 'campaign_video_url',$_POST['campaign_video_url'] );
        }
        if ( isset($_POST['campaign_end_date']) ) {
            update_post_meta( $id, 'campaign_end_date',$_POST['campaign_end_date'] );
        }
        if ( post_type_exists('ideas') ) {
        	if(isset( $_POST['campaign_ideas'] )){
        		// var_dump($_POST['campaign_ideas']);exit;
		        // update_post_meta( $id, 'campaign_ideas',$_POST['campaign_ideas'] );
		        delete_post_meta($id, 'campaign_ideas');
		        foreach ($_POST['campaign_ideas'] as $val) {
		            add_post_meta($id, 'campaign_ideas', $val);
		        }
		    } else {
	    		delete_post_meta($id, 'campaign_ideas');
	    	}
    	}
    }
    
}

add_action( 'init','create_pt_campaigns' );
add_action( 'add_meta_boxes','campaigns_meta_boxes' );
add_action( 'save_post','save_campaigns_custom_meta',10,2 );

function campaign_custom_excerpt_length( $length ) {
	return 25;
}
add_filter( 'excerpt_length', 'campaign_custom_excerpt_length', 999 );


//Plugin info
add_filter('plugins_api','campaigns_plugin_info',10,3);
function campaigns_plugin_info($false, $action, $arg){
    if($arg->slug === 'campaigns'){
    	exit ('<div style="margin:40px">Campaigns Plugin. Any info here.</div>');
    }
    return false;
}



