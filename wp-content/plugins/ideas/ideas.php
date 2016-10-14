<?php
/**
 * Plugin Name: Ideas plugin
 * Plugin URI: https://github.com/shamimmoeen
 * Description: Ideas plugin.
 * Version: 1.1
 * Author: Shamim
 * Author URI: https://github.com/shamimmoeen
 */

if ( ! defined( 'IDEAS_TEXT_DOMAIN' ) ) {
	define( 'IDEAS_TEXT_DOMAIN', 'ideas_plugin' );
}

// Globally define template path
if (!defined('IDEAS_TEMPLATE_PATH')) {
	define('IDEAS_TEMPLATE_PATH', plugin_dir_path(__FILE__) . '/templates/');
}

// Globally define template path
if (!defined('IDEAS_PLUGIN_PATH')) {
	define( 'IDEAS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

function ideas_load_assets() {
	wp_enqueue_style( 'ideas_metabox-styles', plugins_url( '/assets/css/metaboxes.css?v='.time(),__FILE__ ) );
	wp_enqueue_script( 'ideas_custom-js', plugins_url( '/assets/js/metaboxes.js?v='.time(),__FILE__ ), 'jquery-ui-core', '1.0', true );
}

function ideas_front_assets() {
	wp_enqueue_style( 'ideas_front-styles', plugins_url( '/assets/css/style.css?v='.time(),__FILE__ ) );
	wp_enqueue_script( 'ideas_front-js', plugins_url( '/assets/js/ideas-scripts.js?v='.time(),__FILE__ ), 'jquery', '1.0', true );
	wp_localize_script('ideas_front-js', 'klc_params',
		array(
			'ajaxurl' => admin_url('admin-ajax.php'),
		)
	);

	// select2
	wp_register_style( 'select2-styles', plugins_url( '/assets/select2/select2.css?v='.time(),__FILE__ ) );
	wp_register_script( 'select2-js', plugins_url( '/assets/select2/select2.min.js?v='.time(),__FILE__ ), 'jquery', '1.0', true );

	// zebra_datepicker
	wp_register_style( 'zebra_datepicker-styles', plugins_url( '/assets/zebra_datepicker/css/default.css?v='.time(),__FILE__ ) );
	wp_register_script( 'zebra_datepicker-js', plugins_url( '/assets/zebra_datepicker/javascript/zebra_datepicker.js?v='.time(),__FILE__ ), 'jquery', '1.0', true );
	
	// bootstrap notify
	wp_register_script( 'bootstrap_notify-js', plugins_url( '/assets/js/bootstrap-notify.min.js?v='.time(),__FILE__ ), 'jquery', '1.0', true );

	// chart.js
	wp_register_script( 'chart-js', plugins_url( '/assets/js/Chart.bundle.min.js?v='.time(),__FILE__ ), 'jquery', '1.0', true );
	
	// jquery_raty
	wp_register_style( 'jquery_raty-styles', plugins_url( '/assets/raty/jquery.raty.css?v='.time(),__FILE__ ) );
	wp_register_script( 'jquery_raty-js', plugins_url( '/assets/raty/jquery.raty.js?v='.time(),__FILE__ ), 'jquery', '1.0', true );

	wp_register_script( 'jquery.stickytabs', plugins_url( '/assets/js/jquery.stickytabs.js?v='.time(),__FILE__ ), 'jquery', '1.0', true );

	// cmb2-frontend-form
	wp_register_style( 'cmb2-frontend-form', plugins_url( '/assets/css/cmb2-frontend-form.css?v='.time(),__FILE__ ) );

	// jquery modal
	wp_register_style( 'jquery-modal', plugins_url( '/assets/jquery-modal/jquery.modal.css?v='.time(),__FILE__ ) );
	wp_register_script( 'jquery-modal', plugins_url( '/assets/jquery-modal/jquery.modal.js?v='.time(),__FILE__ ), 'jquery', '1.0', true );

	// tingle modal
	wp_register_style( 'tingle-modal', plugins_url( '/assets/tingle/tingle.css?v='.time(),__FILE__ ) );
	wp_register_script( 'tingle-modal', plugins_url( '/assets/tingle/tingle.js?v='.time(),__FILE__ ), 'jquery', '1.0', true );

	// reveal modal
	wp_register_style( 'reveal-modal', plugins_url( '/assets/reveal/reveal.css?v='.time(),__FILE__ ) );
	wp_register_script( 'reveal-modal', plugins_url( '/assets/reveal/reveal.js?v='.time(),__FILE__ ), 'jquery', '1.0', true );

	// remodal
	wp_register_style( 'remodal-style', plugins_url( '/assets/remodal/remodal.css?v='.time(),__FILE__ ) );
	wp_register_style( 'remodal-default-style', plugins_url( '/assets/remodal/remodal-default-theme.css?v='.time(),__FILE__ ) );
	wp_register_script( 'remodal-script', plugins_url( '/assets/remodal/remodal.js?v='.time(),__FILE__ ), 'jquery', '1.0', true );

	// upload media
	wp_register_script( 'custom-upload-media', plugins_url( '/assets/js/custom-upload-media.js?v='.time(),__FILE__ ), 'jquery', '1.0', true );
}
add_action( 'wp_enqueue_scripts','ideas_front_assets' );

add_action( 'wp_head','hook_js' );
function hook_js() {
	?>
<script type="text/javascript">
jQuery(document).ready(function($){   
$('.idea_vote .item-likes').on('click',function(){
var user_id = $(this).parent().attr('user_id') 
if(!$(this).hasClass('liked')){
$.ajax({            
type: "POST",
url: '<?php echo admin_url( 'admin-ajax.php' ) ?>',
data: {
'action': 'add_like_points',
'user_id': user_id
},
success: function ( response ) {
}
})
}
})
});
</script>
<?php }

add_action( 'wp_ajax_add_like_points', 'add_like_points_callback' );
// add_action( 'wp_ajax_nopriv_add_idea', 'add_idea_callback' );
function add_like_points_callback() {
	if ( $_POST['user_id'] ) {
		include_once( 'wp-admin/includes/plugin.php' );
		if ( is_plugin_active( 'mycred/mycred.php' ) ) {
			$myCred_points_for_like = 15;
			mycred_add( 'idea_like', $_POST['user_id'], $myCred_points_for_like, 'Idea like %plural%!' );
		}
	}
	exit;
}

function add_views_count($single_template) {
	global $post;
	if ( $post->post_type == 'ideas' ) {
		$views_count = get_post_meta( $post->ID,'views_count' );

		if ( ! isset( $views_count[0] ) ) {
			add_post_meta( $post->ID, 'views_count', 0 );
		} else {
			update_post_meta( $post->ID, 'views_count', $views_count[0] + 1, $views_count[0] );
		}
		// delete_post_meta($post->ID, 'views_count');
	}
	return $single_template;
}
add_filter( 'single_template', 'add_views_count' );

function create_pt_ideas() {
	register_post_type( 'ideas', /* (http://codex.wordpress.org/Function_Reference/register_post_type) */
		array(
		'labels' => array(
		'name' => __( 'Ideas',IDEAS_TEXT_DOMAIN ), /* This is the Title of the Group */
		'singular_name' => __( 'Idea',IDEAS_TEXT_DOMAIN ), /* This is the individual type */
		'all_items' => __( 'All Ideas',IDEAS_TEXT_DOMAIN ), /* the all items menu item */
		'add_new' => __( 'Add New Idea',IDEAS_TEXT_DOMAIN ), /* The add new menu item */
		'add_new_item' => __( 'Add New Idea',IDEAS_TEXT_DOMAIN ), /* Add New Display Title */
		'edit' => __( 'Edit',IDEAS_TEXT_DOMAIN ), /* Edit Dialog */
		'edit_item' => __( 'Edit Idea',IDEAS_TEXT_DOMAIN ), /* Edit Display Title */
		'new_item' => __( 'New Idea',IDEAS_TEXT_DOMAIN ), /* New Display Title */
		'view_item' => __( 'View Idea',IDEAS_TEXT_DOMAIN ), /* View Display Title */
		'search_items' => __( 'Search Idea',IDEAS_TEXT_DOMAIN ), /* Search Product Title */
		'not_found' => __( 'Nothing found in the Database.',IDEAS_TEXT_DOMAIN ), /* This displays if there are no entries yet */
		'not_found_in_trash' => __( 'Nothing found in Trash',IDEAS_TEXT_DOMAIN ), /* This displays if there is nothing in the trash */
		'parent_item_colon' => '',
		/*BP titles*/
		
        'bp_activity_admin_filter' => __( 'Published a new Idea', IDEAS_TEXT_DOMAIN ),
        'bp_activity_front_filter' => __( 'idée', IDEAS_TEXT_DOMAIN ),
        'contexts'                 => array( 'activity', 'member' ),
        'activity_comment'         => true,
        'bp_activity_new_post'     => __( '%1$s a publié une nouvelle idée <a href="%2$s">idée</a>', IDEAS_TEXT_DOMAIN ),
        'bp_activity_new_post_ms'  => __( '%1$s a publié une nouvelle idée <a href="%2$s">idée</a>, sur le site %3$s', IDEAS_TEXT_DOMAIN ),
		
		'bp_activity_comments_admin_filter' => __( 'Comments about ideas' , IDEAS_TEXT_DOMAIN ), // label for the Admin dropdown filter
		'bp_activity_comments_front_filter' => __( 'Idea Comments' , IDEAS_TEXT_DOMAIN ),        // label for the Front dropdown filter
		'bp_activity_new_comment'           => __( '%1$s a commenté l\'idée <a href="%2$s">idée</a>' , IDEAS_TEXT_DOMAIN ),
		'bp_activity_new_comment_ms'        => __( '%1$s commented on the <a href="%2$s">idée</a>, on the site %3$s' , IDEAS_TEXT_DOMAIN ),
		/*BP titles end*/
		),
		'description' => __( 'Homepage Ideas for ', IDEAS_TEXT_DOMAIN ).get_bloginfo(), /* Product Description */
		'public' => true,
		'menu_icon' => 'dashicons-format-status',
		'publicly_queryable' => true,
		'exclude_from_search' => false,
		'show_ui' => true,
		'query_var' => true,
		'rewrite'	=> array( 'slug' => 'ideas', 'with_front' => false ), /* you can specify its url slug */
		'has_archive' => true, /* you can rename the slug here */
		'capability_type' => 'post',
		'hierarchical' => false,
		'supports' => array( 'title','post-options','editor','page-attributes','comments' ),
		'taxonomies' => array( 'post_tag' ),
		'bp_activity' => array(
		'action_id'         => 'new_idea',                     // The activity type for posts
		'comment_action_id' => 'new_idea_comment',             // The activity type for comments
		),
		) /* end of options */
	); /* end of register post type */
}

add_action( 'init', 'create_ideas_cat_tax' );

function create_ideas_cat_tax() {
	$args = array(
	'hierarchical'      => true,
	'show_ui'           => true,
	'show_admin_column' => true,
	'query_var'         => true,
	'rewrite'           => array( 'slug' => 'ideas_category' ),
	);
	register_taxonomy( 'ideas_category', array( 'ideas' ), $args );
}

function ideas_meta_boxes() {
	// add_meta_box( 'ideas_meta_box',
	// 	__( 'Idea settings',IDEAS_TEXT_DOMAIN ),
	// 	'display_ideas_meta_box',
	// 	'ideas',
	// 	'normal',
	// 	'high'
	// );
}

add_filter( 'mce_buttons', 'extended_editor_mce_buttons', 0 );

function extended_editor_mce_buttons($buttons) {
	global $post;
	if ( $post->post_type == 'ideas' ) {
		$remove = array( 'fullscreen' );
		return array_diff( $buttons,$remove );
	} else { return $buttons; }
}

function display_ideas_meta_box() {
	global $post;
	$idea_image_url = get_post_meta( $post->ID, 'idea_image_url', true );
	$idea_image_id = get_post_meta( $post->ID, 'idea_image_id', true );
	$idea_file_url = get_post_meta( $post->ID, 'idea_file_url', true );
	$idea_file_id = get_post_meta( $post->ID, 'idea_file_id', true );
	$idea_status = get_post_meta( $post->ID, 'idea_status', true );
	$idea_campaign = get_post_meta( $post->ID, 'idea_campaign', true );
	$idea_youtube = get_post_meta( $post->ID, 'idea_youtube', true );
	$idea_video_url = get_post_meta($post->ID, 'idea_video_url', true);
	$idea_video_id = get_post_meta($post->ID, 'idea_video_id', true);
?>
<script>
jQuery(document).ready(function($){
$('#add_image_button').on('click',function() {
showMediaUploader($('#idea_image_url'),$('#idea_image_id'),$('.idea_image_img'));
});
$('#clear_image_button').on('click',function() {
$('#idea_image_url').attr('value','');
$('#idea_image_id').attr('value','');
$('.idea_image_img').attr('src','<?php echo plugins_url( '/assets/img/noimage.png',__FILE__ ) ?>' );
})

$('#add_file_button').on('click',function() {
showMediaUploader($('#idea_file_url'),$('#idea_file_id'),false);
});
$('#clear_file_button').on('click',function() {
$('#idea_file_url').attr('value','');
})
$('#add_video_button').on('click',function() {
    showMediaUploader($('#idea_video_url'),$('#idea_video_id'),false);
});
$('#clear_video_button').on('click',function() {
	$('#idea_video_url').attr('value','');
})

});
</script>
<table class="form-table">
<tr>
<th><label for="ideas_list"><?php _e( 'Image',IDEAS_TEXT_DOMAIN ); ?></label></th>
<td>
<img src="<?php echo $idea_image_url?$idea_image_url:plugins_url( '/assets/img/noimage.png',__FILE__ ) ?>" class="idea_image_img" style="float:left; margin-right:20px; max-width: 100px;">
<label for="idea_image_url">
<input id="idea_image_url" type="text" size="36" name="idea_image_url" value="<?php echo $idea_image_url ?>" readonly/>
<div style="margin:10px 0"><?php _e( 'Enter a URL or upload an image',IDEAS_TEXT_DOMAIN ); ?></div>
<input id="idea_image_id" type="hidden" size="36" name="idea_image_id" value="<?php echo $idea_image_id ?>" />
<input id="add_image_button" class="button button-primary" type="button" value="<?php _e( 'Add Image',IDEAS_TEXT_DOMAIN ); ?>" />
<input id="clear_image_button" class="button button-default" type="button" value="<?php _e( 'Clear',IDEAS_TEXT_DOMAIN ); ?>" />
</label>
</td>
</tr>
<tr>
<th><label for="ideas_list"><?php _e( 'Attachment',IDEAS_TEXT_DOMAIN ); ?></label></th>
<td>
<label for="upload_Idea">
<input id="idea_file_url" type="text" size="36" name="idea_file_url" value="<?php echo $idea_file_url ?>" readonly/>
<input id="add_file_button" class="button button-primary" type="button" value="<?php _e( 'Add File',IDEAS_TEXT_DOMAIN ); ?>" />
<input id="idea_file_id" type="hidden" size="36" name="idea_file_id" value="<?php echo $idea_file_id ?>" />
<input id="clear_file_button" class="button button-default" type="button" value="<?php _e( 'Clear',IDEAS_TEXT_DOMAIN ); ?>" />
</label>
</td>
</tr>
<tr>
	<th><label for="ideas_list"><?php _e( 'Video',IDEAS_TEXT_DOMAIN ); ?></label></th>
	<td>
		<label for="upload_Idea">
			<input id="idea_video_url" type="text" size="36" name="idea_video_url" value="<?php echo $idea_video_url ?>" readonly/>
			<input id="add_video_button" class="button button-primary" type="button" value="Add File" />
			<input id="idea_video_id" type="hidden" size="36" name="idea_video_id" value="<?php echo $idea_video_id ?>" />
			<input id="clear_video_button" class="button button-default" type="button" value="Clear" />
		</label>
	</td>
</tr>
<tr>
<th><label for="ideas_list"><?php _e( 'Status',IDEAS_TEXT_DOMAIN ); ?></label></th>
<td>
<select name="idea_status">
	<option value="" ><?php _e( 'No Status',IDEAS_TEXT_DOMAIN ); ?></option>
	<option value="in discussion" <?php echo $idea_status == 'in discussion'?'selected':'' ?> ><?php _e( 'Idea in discussion',IDEAS_TEXT_DOMAIN ); ?></option>
	<option value="selected" <?php echo $idea_status == 'selected'?'selected':'' ?> ><?php _e( 'Idea selected',IDEAS_TEXT_DOMAIN ); ?></option>
	<option value="rejected" <?php echo $idea_status == 'rejected'?'selected':'' ?> ><?php _e( 'Idea rejected',IDEAS_TEXT_DOMAIN ); ?></option>
	<option value="in project" <?php echo $idea_status == 'in project'?'selected':'' ?> ><?php _e( 'Idea in project',IDEAS_TEXT_DOMAIN ); ?></option>
	<option value="in review" <?php echo $idea_status == 'in review'?'selected':'' ?> ><?php _e( 'Ideas in review',IDEAS_TEXT_DOMAIN ); ?></option>
	<option value="already reviewed" <?php echo $idea_status == 'already reviewed'?'selected':'' ?> ><?php _e( 'Ideas already reviewed',IDEAS_TEXT_DOMAIN ); ?></option>
</select>
</td>
</tr>
<tr>
<th><label for="idea_youtube"><?php _e( 'Video (youtube)',IDEAS_TEXT_DOMAIN ); ?></label></th>
<td>
<input id="idea_youtube" type="text" size="36" name="idea_youtube" value="<?php echo $idea_youtube ?>" />
</td>
</tr>
</table>
<?php
}

function save_ideas_custom_meta( $id, $item ) {
    // Check post type
    if ( $item->post_type == 'ideas' ) {
        // Store data in post meta table if present in post data
        if ( isset($_POST['idea_image_url']) ) {
            update_post_meta( $id, 'idea_image_url',$_POST['idea_image_url'] );
        }
        if ( isset($_POST['idea_image_id']) ) {
            update_post_meta( $id, 'idea_image_id',$_POST['idea_image_id'] );
        }
        if ( isset($_POST['idea_file_url']) ) {
            update_post_meta( $id, 'idea_file_url',$_POST['idea_file_url'] );
        }
        if ( isset($_POST['idea_file_id']) ) {
            update_post_meta( $id, 'idea_file_id',$_POST['idea_file_id'] );
        }
        if ( isset($_POST['idea_video_url']) ) {
            update_post_meta( $id, 'idea_video_url',$_POST['idea_video_url'] );
        }
        if ( isset($_POST['idea_video_id']) ) {
            update_post_meta( $id, 'idea_video_id',$_POST['idea_video_id'] );
        }
        if ( isset($_POST['idea_status']) ) {
            update_post_meta( $id, 'idea_status',$_POST['idea_status'] );
        }
        if ( isset($_POST['idea_youtube']) ) {
            update_post_meta( $id, 'idea_youtube',$_POST['idea_youtube'] );
        }
    }
}

add_action( 'admin_enqueue_scripts','ideas_load_assets' );
add_action( 'init','create_pt_ideas' );
add_action( 'add_meta_boxes','ideas_meta_boxes' );
add_action( 'save_post','save_ideas_custom_meta',10,2 );

function ideas_custom_excerpt_length( $length ) {
	return 25;
}
add_filter( 'excerpt_length', 'ideas_custom_excerpt_length', 999 );

// insert new idea
add_action( 'wp_ajax_add_idea', 'add_idea_callback' );
add_action( 'wp_ajax_nopriv_add_idea', 'add_idea_callback' );
function add_idea_callback() {
	if ( $_POST ) {
		$postarr = array(
		'post_title'	=> $_POST['title'],
		'post_content'	=> $_POST['text'],
		'post_date'     => date('Y-m-d H:i:s'),				
		'post_date_gmt' => date('Y-m-d H:i:s'),	
		'post_type' 	=> 'ideas',
		);
		if ( is_user_logged_in() ) {
			$postarr['post_status'] = 'publish';
		}
		$post_id = wp_insert_post( $postarr );

		if ( $post_id ) {
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			if ( $_FILES['image']['name'] ) {
				add_attachment( $_FILES['image'],$post_id,'image' );
			}
			if ( $_FILES['attachment']['name'] ) {
				add_attachment( $_FILES['attachment'],$post_id,'file' );
			}
			if($_FILES['video']['name']){
				add_attachment($_FILES['video'],$post_id,'video');
			}
			if ( $_POST['idea_campaign'] ) {
				add_post_meta( $_POST['idea_campaign'],'campaign_ideas',$post_id );
			}
			if ( $_POST['youtube'] ) {
				add_post_meta( $post_id, 'idea_youtube', $_POST['youtube'], true );
			}
			if ( ! is_user_logged_in() ) {
				$result = __( 'Thank you. Idea will appear after moderation.',IDEAS_TEXT_DOMAIN );
			} else {
				$result = '<a href="'.get_permalink( $post_id ).'">'.__( 'Thank you. Idea successfully added.',IDEAS_TEXT_DOMAIN ).'</a>';
			}
			echo json_encode( array( 'html' => $result ) );
		}
		do_action( 'after_save_idea', $post_id, get_post( $post_id ) );
	} else {
		echo json_encode( array( 'error' => __( 'Idea not added!',IDEAS_TEXT_DOMAIN ) ) );
	}
	exit;
}

// idea favorites
add_action( 'wp_ajax_idea_favorits', 'idea_favorits_callback' );

function idea_favorits_callback() {
	$meta = get_post_meta( $_POST['post_id'],'idea_favorites' );
	$user_id = get_current_user_id();
	if ( $meta ) {
		if ( ! in_array( $user_id,$meta ) && $_POST['status'] ) {
			add_post_meta( $_POST['post_id'],'idea_favorites',$user_id );
		}
		if ( in_array( $user_id,$meta ) && ! $_POST['status'] ) {
			delete_post_meta( $_POST['post_id'],'idea_favorites',$user_id );
		}
	} else {
		add_post_meta( $_POST['post_id'],'idea_favorites',$user_id );
	}
	echo json_encode( array( 'success' => true ) );
	exit;
}


// idea comments
add_action( 'wp_ajax_add_comment', 'add_comment_callback' );
function add_comment_callback() {
	global $current_user;
	$commentdata = array(
	'comment_post_ID'		=> $_POST['post_id'],
	'comment_content'		=> $_POST['text'],
	'user_id' 				=> $current_user->ID,
	'comment_author_email'	=> $current_user->user_email,
	'comment_author_url'	=> site_url(),
	);
	wp_new_comment( $commentdata );
	echo json_encode( array( 'html' => __( 'Comment successfully added!',IDEAS_TEXT_DOMAIN ) ) );
	exit;
}

add_action( 'wp_ajax_edit_ideas', 'edit_idea_callback' );
function edit_idea_callback(){
	global $current_user;
    $post = get_post( $_POST['idea_id'] );
    if( !$post || $post->post_author != $current_user->ID ) { exit(); }

	$my_post = array(
	    'ID'           => $_POST['idea_id'],
	    'post_content' => $_POST['idea_text'],
	    'post_title'	=> $_POST['idea_title']
	);

	wp_update_post( $my_post );
	
	if(isset($_POST['idea_campaign'])){
		$args = array(
        'post_type'  => 'campaigns',
        'meta_query' => array(
            array(
                    'key'     => 'campaign_ideas',
                    'value'   => $_POST['idea_id'],
                    'compare' => '=',
                ),
            ),
        );

	    $query = new WP_Query( $args );
	    $campaign = $query->get_posts();

	    if(!empty($campaign)){
	    	foreach ($campaign as $key => $value) {
		    	delete_post_meta($value->ID, 'campaign_ideas', $_POST['idea_id']);
		    }
	    }

	    if($_POST['idea_campaign'] != 0) {
	    	add_post_meta($_POST['idea_campaign'], 'campaign_ideas', $_POST['idea_id']);
		}
	}
	update_post_meta($post->ID, 'updated_at', date('Y-m-d H:i:s') );
	exit;
}

add_action( 'wp_ajax_edit_comments', 'edit_comments_callback' );
function edit_comments_callback() {
	global $current_user;
	$comment_id = get_comment( $_POST['comment_id'] );
	if ( $comment_id->user_id != $current_user->ID ) { exit(); }

	$my_post = array(
	'comment_ID'      => $_POST['comment_id'],
	'comment_content' => $_POST['comment_text'],
	);
	wp_update_comment( $my_post );
	exit;
}

function is_author_idea() {
	global $current_user;
	$post = get_post( get_the_ID() );
	if ( $post->post_author == $current_user->ID ) { return true; }
	return false;
}

function add_attachment($file_input,$post_id,$type) {
	$file = wp_handle_upload( $file_input,array( 'test_form' => false ) );
	if ( isset( $file['error'] ) ) {
		echo json_encode( array( 'html' => $file['error'] ) );
		exit;
	}
	$attach_id = wp_insert_attachment(
		array(
		'post_title' => $file_input['name'],
		'post_content' => '',
		'post_type' => 'attachment',
		'post_parent' => $post_id,
		'post_mime_type' => $file_input['type'],
		'guid' => $file['url'],
		),
		$file['file']
	);
	$attach_data = wp_generate_attachment_metadata( $attach_id, $file['file'] );
	wp_update_attachment_metadata( $attach_id, $attach_data );

	update_post_meta( $post_id,'idea_'.$type.'_id',$attach_id );
	update_post_meta( $post_id,'idea_'.$type.'_url',$file['url'] );

	return true;
}

// Frontend functions
function add_idea_button() {
	require( plugin_dir_path( __FILE__ ).'templates/idea_button.php' );
}

function add_idea_modal($current_campaign=false) {
	require( plugin_dir_path( __FILE__ ).'templates/idea_modal.php' );
}

// add_filter( 'pre_get_posts','ideasSearchFilter' );
function ideasSearchFilter($query) {
	if ( isset( $_GET['type'] ) ) {
		$post_type = $_GET['type'];
		if ( $post_type && $query->is_search ) {
			$query->set( 'post_type', $post_type );
		}
	}
	return $query;
};

// ideas sorter
// add_filter( 'pre_get_posts', 'ideasOrdering' );
function ideasOrdering($wp_query) {
	if ( $_GET['sort'] && $wp_query->query['post_type'] == 'ideas' ) {
		switch ( $_GET['sort'] ) {
			case 'newest':
				$wp_query->set( 'orderby', 'date' );
		break;
			case 'oldest':
				$wp_query->set( 'oldest', 'date' );
				$wp_query->set( 'order', 'ASC' );
		break;
			case 'recent':
				add_filter( 'posts_clauses', 'ideas_intercept_query_clauses', 20, 1 );
		break;
			case 'voted':
				$wp_query->set( 'orderby','meta_value' );
				$wp_query->set( 'meta_key','_item_likes' );
		break;
			case 'answers':
				$wp_query->set( 'orderby','comment_count' );
		break;
		}
	}
	return $wp_query;
}

function ideas_intercept_query_clauses( $pieces ) {
	global $wpdb;
	$pieces['fields'] = 'wp_posts.*,
(
select max(comment_date)
from ' . $wpdb->comments .' wpc
where wpc.comment_post_id = wp_posts.id AND wpc.comment_approved = 1
) as mcomment_date';
	$pieces['orderby'] = 'mcomment_date desc';
	return $pieces;
}


// Plugin info
add_filter( 'plugins_api','ideas_plugin_info',10,3 );
function ideas_plugin_info($false, $action, $arg) {
	if ( $arg->slug === 'ideas' ) {
		exit( '<div style="margin:40px">'.__( 'Ideas Plugin. Any info here.',IDEAS_TEXT_DOMAIN ).'</div>' );
	}
	return false;
}

add_action( 'plugins_loaded', 'load_plugin_languages' );
/**
 * Load plugin language file.
 */
function load_plugin_languages() {

	load_plugin_textdomain( IDEAS_TEXT_DOMAIN, false, basename( dirname( __FILE__ ) ) . '/lang/' );
}


/**
 * Slugify strings
 */
if (!function_exists('klc_slugify')) {
	function klc_slugify($str) {
		$str = strtolower($str);
		$str = html_entity_decode($str);
		$str = preg_replace('/[^\w ]+/', '', $str);
		$str = preg_replace('/ +/', '-', $str);

		return $str;
	}
}


/**
 * Register Custom post Review
 */
if (!function_exists('klc_idea_post_type_review')) {
	function klc_idea_post_type_review() {
		$labels = array(
			'name'               => _x( 'Reviews', 'post type general name', IDEAS_TEXT_DOMAIN ),
			'singular_name'      => _x( 'Review', 'post type singular name', IDEAS_TEXT_DOMAIN ),
			'menu_name'          => _x( 'Reviews', 'admin menu', IDEAS_TEXT_DOMAIN ),
			'name_admin_bar'     => _x( 'Review', 'add new on admin bar', IDEAS_TEXT_DOMAIN ),
			'add_new'            => _x( 'Add New', 'review', IDEAS_TEXT_DOMAIN ),
			'add_new_item'       => __( 'Add New Review', IDEAS_TEXT_DOMAIN ),
			'new_item'           => __( 'New Review', IDEAS_TEXT_DOMAIN ),
			'edit_item'          => __( 'Edit Review', IDEAS_TEXT_DOMAIN ),
			'view_item'          => __( 'View Review', IDEAS_TEXT_DOMAIN ),
			'all_items'          => __( 'All Reviews', IDEAS_TEXT_DOMAIN ),
			'search_items'       => __( 'Search Reviews', IDEAS_TEXT_DOMAIN ),
			'parent_item_colon'  => __( 'Parent Reviews:', IDEAS_TEXT_DOMAIN ),
			'not_found'          => __( 'No review found.', IDEAS_TEXT_DOMAIN ),
			'not_found_in_trash' => __( 'No review found in Trash.', IDEAS_TEXT_DOMAIN )
		);

		$args = array(
			'labels'          => $labels,
			'show_in_menu'    => 'edit.php?post_type=ideas',
			'public'          => true,
			'query_var'       => true,
			'rewrite'         => array( 'slug' => 'idea-review' ),
			'capability_type' => 'post',
			'has_archive'     => false,
			'hierarchical'    => false,
			'menu_position'   => null,
			'supports'        => array( 'title', 'author' ),
		);

		register_post_type( 'idea_review', $args );
	}
	add_action( 'init', 'klc_idea_post_type_review' );
}


/**
 * Render template.
 * 
 * @param  string $template_name
 * @param  array  $params
 * 
 * @return response
 */
if (!function_exists('klc_render_template')) {
	function klc_render_template($template_name, $params = array()) {
		global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;

		// template name
		$template = $template_name . '.php';

		$file = IDEAS_TEMPLATE_PATH . $template;

		// load the template
		if (file_exists($file)) {
			extract($params, EXTR_SKIP);
			require($file);
		}
	}
}


/**
 * Include archive template for ideas
 */
if (!function_exists('klc_include_archive_template_for_ideas')) {
	function klc_include_archive_template_for_ideas($archive_template) {
		global $post;

		if (is_post_type_archive('ideas')) {
			$archive_template = plugin_dir_path(__FILE__) . 'templates/archive-ideas.php';
		}

		return $archive_template;
	}
	add_filter('archive_template', 'klc_include_archive_template_for_ideas');
}


/**
 * Include single template for ideas
 */
if (!function_exists('klc_include_single_template_for_ideas')) {
	function klc_include_single_template_for_ideas($single_template) {
		global $post;

		if ($post->post_type === 'ideas') {
			$single_template = plugin_dir_path(__FILE__) . 'templates/single-ideas.php';
		}

		return $single_template;
	}
	add_filter('single_template', 'klc_include_single_template_for_ideas');
}


/**
 * Include single template for post type campaign
 */
if (!function_exists('klc_include_single_template_for_campaign')) {
	function klc_include_single_template_for_campaign($single_template) {
		global $post;

		if ($post->post_type === 'campaigns') {
			$single_template = plugin_dir_path(__FILE__) . 'templates/single-campaigns.php';
		}

		return $single_template;
	}
	add_filter('single_template', 'klc_include_single_template_for_campaign');
}


// check for idea modifier, who can assign experts
if (!function_exists('klc_ideas_modifier')) {
	function klc_ideas_modifier() {
		global $current_user, $klc_ideas;

		$modifier_roles = key_exists('modifier_roles', $klc_ideas) ? $klc_ideas['modifier_roles'] : array();
		$modifier = false;

		if ($modifier_roles) {
			foreach ($modifier_roles as $modifier_role) {
				if (in_array($modifier_role, $current_user->roles)) {
					$modifier = true;
					break;
				}
			}
		}

		return $modifier;
	}
}

// get experts
if (!function_exists('klc_get_idea_experts')) {
	function klc_get_idea_experts() {
		global $klc_ideas;

		$expert_roles = key_exists('expert_roles', $klc_ideas) ? $klc_ideas['expert_roles'] : array();

		$args = array(
			'role__in' => $expert_roles,
		);

		return get_users($args);
	}
}

// check for idea experts, who can post review
if (!function_exists('klc_idea_expert')) {
	function klc_idea_expert() {
		global $current_user, $klc_ideas;

		$expert_roles = key_exists('expert_roles', $klc_ideas) ? $klc_ideas['expert_roles'] : array();
		$expert = false;

		if ($expert_roles) {
			foreach ($expert_roles as $expert_role) {
				if (in_array($expert_role, $current_user->roles)) {
					$expert = true;
					break;
				}
			}
		}

		return $expert;
	}
}

// check if modifier can edit reviews
if (!function_exists('klc_modifier_can_edit_reviews')) {
	function klc_modifier_can_edit_reviews() {
		global $klc_ideas;

		return (klc_ideas_modifier() && $klc_ideas['modifier_can_edit_reviews'] != 0) ? true : false;
	}
}

// get experts for given idea
if (!function_exists('klc_get_experts_for_given_idea')) {
	function klc_get_experts_for_given_idea($idea_id) {
		$idea_experts = array();

		$args = array(
		    'post_type'   => 'idea_review',
		    'post_status' => 'publish',
		    'numberposts' => -1,
		    'fields'      => 'ids',
		    'meta_query'  => array(
		        array(
		            'key'     => '_idea_id',
		            'value'   => $idea_id,
		            'compare' => '=',
		        ),
		        array(
		            'key'     => '_expert_id',
		            'compare' => 'EXISTS',
		        ),
		    ),
		);

		$reviews = get_posts($args);

		if ($reviews) {
		    foreach ($reviews as $review_id) {
		        $idea_experts[] = get_post_meta($review_id, '_expert_id', true);
		    }
		}

		return $idea_experts;
	}
}

// get users who can provide updates for give idea
if (!function_exists('klc_get_idea_update_owners_for_given_idea')) {
	function klc_get_idea_update_owners_for_given_idea($idea_id) {
		$idea_update_owners = get_post_meta($idea_id, '_idea_owners', true);
		return $idea_update_owners ? $idea_update_owners : array();
	}
}

// check if current user are able to provide idea update
if (!function_exists('klc_check_for_idea_update_owner')) {
	function klc_check_for_idea_update_owner($idea_id) {
		if (is_user_logged_in()) {
			$current_user_id = get_current_user_id();
			$idea_update_owners = get_post_meta($idea_id, '_idea_owners', true);

			if ($idea_update_owners && in_array($current_user_id, $idea_update_owners)) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}

// save idea fields
if (!function_exists('klc_assign_experts_and_deadline_for_idea')) {
	function klc_assign_experts_and_deadline_for_idea() {
		$idea_id = (isset($_POST['idea_id'])) ? $_POST['idea_id'] : '';
		$expert_id = (isset($_POST['expert_id'])) ? $_POST['expert_id'] : '';
		$deadline = (isset($_POST['deadline'])) ? $_POST['deadline'] : '';
		$campaign_id = (isset($_POST['campaign_id'])) ? $_POST['campaign_id'] : '';
		$new_expert = false;
		$reload_table = 'false';
		$show_notification = 'false';
		$table_content = '';

		if ($expert_id && $idea_id) {
			// check if review already exists
			$args = array(
				'post_type'   => 'idea_review',
				'post_status' => 'publish',
				'numberposts' => -1,
				'fields'      => 'ids',
				'meta_query'  => array(
					'relation' => 'AND',
					array(
					    'key'     => '_idea_id',
					    'value'   => $idea_id,
					    'compare' => '=',
					),
					array(
					    'key'     => '_expert_id',
					    'value'   => $expert_id,
					    'compare' => '=',
					),
				),
			);

			$review = get_posts($args);

			$review_data = array(
				'post_type'      => 'idea_review',
				'post_title'     => 'Review # idea: ' . $idea_id . ' expert: ' . $expert_id,
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_author'    => get_current_user_id(),
			);

			$review_meta_data = array(
				'_idea_id'       => $idea_id,
				'_expert_id'     => $expert_id,
			);

			// update review
			if ($review) {
				$review_data['ID'] = $review[0];
				$review_id = wp_update_post($review_data, true);
			}
			// create a new one
			else {
				$review_id = wp_insert_post($review_data, true);
				$new_expert = true;
			}

			if (!is_wp_error($review_id) && $review_id > 0) {
				foreach ($review_meta_data as $meta_key => $meta_value) {
					add_post_meta($review_id, $meta_key, $meta_value, true)
					or
					update_post_meta($review_id, $meta_key, $meta_value);
				}
			}
		}

		// add or update deadline value
		if (add_post_meta($idea_id, '_idea_deadline', $deadline, true) || update_post_meta($idea_id, '_idea_deadline', $deadline)) {
			$show_notification = 'true';
		}

		if ($new_expert) {
			// update idea status
			$idea_status = get_post_meta($idea_id, 'idea_status', true);

			if ($idea_status !== 'in review') {
				update_post_meta($idea_id, 'idea_status', 'in review');
			}

			// send mail
			$user_data = get_userdata($expert_id);
			$to = $user_data->user_email;
			$subject = sprintf(__('[%s] You are an expert', IDEAS_TEXT_DOMAIN), get_bloginfo());

			$modifier_id = get_current_user_id();
			$modifier_data = get_userdata($modifier_id);
			$modifier_display_name = $modifier_data->display_name;

			if (function_exists('bp_core_get_userlink')) {
				$modifier_link =  bp_core_get_userlink($modifier_id);
			} else {
				$modifier_link = '<a href="' . get_author_posts_url($modifier_id) . '">' . $modifier_display_name . '</a>';
			}

			$idea_link = '<a href="' . get_the_permalink($idea_id) . '#expert-reviews">' . get_the_title($idea_id) . '</a>';

			if ($campaign_id) {
				$campaign_link = '<a href="' . get_the_permalink($campaign_id) . '">' . get_the_title($campaign_id) . '</a>';
			} else {
				$campaign_link = __('(no campaign assigned)', IDEAS_TEXT_DOMAIN);
			}

			$body = '';
			$body .= sprintf(__('Hello,', IDEAS_TEXT_DOMAIN)) . "\n\n";
			$body .= sprintf(__('You have been invited by %1$s to review the %2$s in the challenge %3$s.', IDEAS_TEXT_DOMAIN), $modifier_link, $idea_link, $campaign_link) . "\n";
			$body .= sprintf(__('You have until %1$s to review the idea.', IDEAS_TEXT_DOMAIN), $deadline) . "\n";
			$body .= sprintf(__('Your individual review and grades will only be seen by admin and your inviter.', IDEAS_TEXT_DOMAIN)) . "\n\n";
			$body .= sprintf(__('Thanks for support!', IDEAS_TEXT_DOMAIN));

			wp_mail($to, $subject, $body);

			// reload experts table
			// fetch experts
			$idea_experts = klc_get_experts_for_given_idea($idea_id);

			if ($idea_experts) {
				$show_notification = 'true';
				$reload_table = 'true';
			}

			ob_start();
			klc_render_template('experts-table', array('idea_id' => $idea_id, 'idea_experts' => $idea_experts));
			$table_content = ob_get_clean();
		}

		echo json_encode(array(
			'show_notification' => $show_notification,
			'reload_table'      => $reload_table,
			'table_content'     => $table_content,
		));

		exit;
	}
	add_action('wp_ajax_klc_assign_experts_and_deadline_for_idea', 'klc_assign_experts_and_deadline_for_idea');
}

// post idea review
if (!function_exists('klc_post_expert_review_for_idea')) {
	function klc_post_expert_review_for_idea() {
		global $klc_ideas;
		$review_criteria = $klc_ideas['review_criteria'];

		$rating = array();

		if ($review_criteria) {
			foreach ($review_criteria as $criteria) {
				$slug = klc_slugify($criteria);

				$score = (isset($_POST[$slug])) ? floatval($_POST[$slug]) : 0;
				$rating[$slug] = $score;
			}
		}

		$review_id = (isset($_POST['review_id'])) ? $_POST['review_id'] : '';
		$comment = (isset($_POST['comment'])) ? $_POST['comment'] : '';

		$idea_review = array(
			'rating'  => $rating,
			'comment' => $comment,
		);

		if ($review_id && get_post($review_id)) {
			update_post_meta($review_id, '_idea_review', $idea_review);
		}

		echo json_encode(array(
			'success' => 'true'
		));

		exit;
	}
	add_action('wp_ajax_klc_post_expert_review_for_idea', 'klc_post_expert_review_for_idea');
}


// delete reviews
if (!function_exists('klc_action_before_delete_reviews')) {
	function klc_action_before_delete_reviews($review_id) {
		global $post_type;

		if ($post_type === 'idea_review') {
			$idea_id = get_post_meta($review_id, '_idea_id', true);
			delete_post_meta($idea_id, '_idea_deadline');
		}
	}
	// add_action('before_delete_post', 'klc_action_before_delete_reviews');
}


// remove expert from idea
if (!function_exists('klc_remove_expert')) {
	function klc_remove_expert() {
		$idea_id = isset($_POST['idea_id']) ? $_POST['idea_id'] : '';
		$expert_id = isset($_POST['expert_id']) ? $_POST['expert_id'] : '';
		$success = 'false';

		// check if review already exists
		$args = array(
			'post_type'   => 'idea_review',
			'post_status' => 'publish',
			'numberposts' => -1,
			'fields'      => 'ids',
			'meta_query'  => array(
				'relation' => 'AND',
				array(
				    'key'     => '_idea_id',
				    'value'   => $idea_id,
				    'compare' => '=',
				),
				array(
				    'key'     => '_expert_id',
				    'value'   => $expert_id,
				    'compare' => '=',
				),
			),
		);

		$review = get_posts($args);

		if ($review) {
			$delete = wp_delete_post($review[0]);

			if (!is_wp_error($delete)) {
				$success = 'true';
			}
		}

		echo json_encode(array(
			'success' => $success,
		));

		exit;
	}
	add_action('wp_ajax_klc_remove_expert', 'klc_remove_expert');
}

// require redux framework and settings page
if (!class_exists('ReduxFramework')) {
	$framework = get_template_directory() . '/kleo-framework/options/framework.php';

	if (file_exists($framework)) {
		require_once($framework);
	}
}

// require plugin settings page
require_once(plugin_dir_path(__FILE__) . 'plugin-settings.php');

// require TGM-Plugin-Activation class
if (!class_exists('TGM_Plugin_Activation')) {
	$file = plugin_dir_path(__FILE__) . 'class-tgm-plugin-activation.php';
	require_once($file);
}

// required plugins
require_once(plugin_dir_path(__FILE__) . 'required-plugins.php');

// get buddypress avatar
if (!function_exists('klc_get_avatar')) {
	function klc_get_avatar($user_id) {
		if (function_exists('bp_core_fetch_avatar')) {
			$args = array(
			    'item_id' => $user_id,
			    'html' => false,
			);

			return bp_core_fetch_avatar($args);
		}
	}
}

// published x days ago
if (!function_exists('klc_review_posted_x_days_ago')) {
	function klc_review_posted_x_days_ago($post_id) {
		$days = round((date('U') - get_the_time('U', $post_id)) / (60*60*24));
		$days = (int)$days;

		if ($days === 0) {
			$published_on = __('posted today', IDEAS_TEXT_DOMAIN);
		} elseif ($days === 1) {
			$published_on = __('posted yesterday', IDEAS_TEXT_DOMAIN);
		} else {
			$published_on = sprintf(__('posted %s %s ago', IDEAS_TEXT_DOMAIN), $days, _n('day', 'days', $days, IDEAS_TEXT_DOMAIN));
		}

		return $published_on;
	}
}

// run cron job at every hour
// add_filter('imcron_interval_id', 'set_imcron_interval');
if (!function_exists('set_imcron_interval')) {
    function set_imcron_interval() {
        return 'hourly';
    }
}

// create a scheduled event for sending alerts to review experts
if (!function_exists('klc_register_review_alert_schedule')) {
	function klc_register_review_alert_schedule() {
		if(!wp_next_scheduled('klc_review_alert')) {
			wp_schedule_event(time(), 'twicedaily', 'klc_review_alert');
		}
	}
	// and make sure it's called whenever WordPress loads
	add_action('init', 'klc_register_review_alert_schedule');
}

// create a scheduled event for setting idea status to 'already reviewed'
if (!function_exists('klc_register_schedule_for_setting_idea_status_to_already_reviewed')) {
	function klc_register_schedule_for_setting_idea_status_to_already_reviewed() {
		if (!wp_next_scheduled('klc_change_idea_status')) {
			wp_schedule_event(time(), 'twicedaily', 'klc_change_idea_status');
		}
	}
	// and make sure it's called whenever WordPress loads
	add_action('init', 'klc_register_schedule_for_setting_idea_status_to_already_reviewed');
}

// send review alerts
if (!function_exists('klc_send_review_alerts')) {
	function klc_send_review_alerts() {
		global $klc_ideas;

		$receive_reminder = ($klc_ideas['receive_reminder'] != 0) ? true : false;
		$buffer = $klc_ideas['reminder_buffer'];

		if ($receive_reminder) {
			$args = array(
				'post_type'   => 'idea_review',
				'post_status' => 'publish',
				'numberposts' => -1,
				'fields'      => 'ids',
			);

			$reviews = get_posts($args);
			$reviews_pending = array();
			$today = current_time('Y-m-d');
			$days_from_now = strtotime('+' . $buffer . ' days', strtotime($today));

			foreach ($reviews as $review_id) {
				$idea_id = get_post_meta($review_id, '_idea_id', true);
				$idea_review = get_post_meta($review_id, '_idea_review', true);
				$idea_deadline = get_post_meta($idea_id, '_idea_deadline', true);
				$review_alert = get_post_meta($review_id, '_review_alert', true);

				if (!$idea_review && !$review_alert && $idea_deadline && strtotime($today) <= strtotime($idea_deadline) && $days_from_now > strtotime($idea_deadline)) {
					// send alert to expert
					$expert_id = get_post_meta($review_id, '_expert_id', true);
					$user_data = get_userdata($expert_id);
					$to = $user_data->user_email;
					$subject = sprintf(__('[%s] You have pending review requiring attention', IDEAS_TEXT_DOMAIN), get_bloginfo());
					$headers = array('Content-Type: text/html; charset=UTF-8');

					$idea_link = get_permalink($idea_id) . '?post-review';

					$body = '';

					$body .= sprintf(__('Hello,', IDEAS_TEXT_DOMAIN));

					$body .= '<br /><br />';

					$body .= sprintf(__('You will have until the date of deadline to post your review. Your review will only be seen by your invite and site admin.', IDEAS_TEXT_DOMAIN));

					$body .= '<br /><br />';

					$body .= sprintf(__('Please see the idea to review here: ', IDEAS_TEXT_DOMAIN));

					$body .= '<br />';

					$body .= sprintf(__('Idea link: %1$s', IDEAS_TEXT_DOMAIN), $idea_link);

					$body .= '<br />';

					$body .= sprintf(__('Deadline: %1$s', IDEAS_TEXT_DOMAIN), $idea_deadline);

					$body .= '<br /><br />';

					$body .= sprintf(__('Thank you', IDEAS_TEXT_DOMAIN));

					wp_mail($to, $subject, $body, $headers);

					// update '_review_alert' to true
					add_post_meta($review_id, '_review_alert', true, true)
					or
					update_post_meta($review_id, '_review_alert', true);
				}
			}
		}
	}
	add_action('klc_review_alert', 'klc_send_review_alerts');
}

if (!function_exists('klc_change_idea_status_to_already_reviewed')) {
	function klc_change_idea_status_to_already_reviewed() {
		$args = array(
			'post_type'   => 'ideas',
			'post_status' => 'published',
			'numberposts' => -1,
			'fields'      => 'ids',
			'meta_query'  => array(
				'relation' => 'AND',
				array(
					'key'     => '_idea_deadline',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => 'idea_status',
					'value'   => 'already reviewed',
					'compare' => '!=',
				),
			),
		);

		$ideas = get_posts($args);

		if ($ideas) {
			$today = current_time('Y-m-d');

			foreach ($ideas as $idea_id) {
				$idea_deadline = get_post_meta($idea_id, '_idea_deadline', true);

				if (strtotime($today) > strtotime($idea_deadline)) {
					update_post_meta($idea_id, 'idea_status', 'already reviewed');
				}
			}
		}
	}
	add_action('klc_change_idea_status', 'klc_change_idea_status_to_already_reviewed');
}

// deactivation hook
if (!function_exists('klc_deactivation_hook')) {
	function klc_deactivation_hook() {
		$timestamp = wp_next_scheduled('klc_review_alert');
		wp_unschedule_event($timestamp, 'klc_review_alert');

		$timestamp = wp_next_scheduled('klc_change_idea_status');
		wp_unschedule_event($timestamp, 'klc_change_idea_status');
	}
	register_deactivation_hook(__FILE__, 'klc_deactivation_hook');
}

// user review
if (!function_exists('klc_post_user_review_for_idea')) {
	function klc_post_user_review_for_idea() {
		$idea_id = isset($_POST['idea_id']) ? $_POST['idea_id'] : '';
		$review_id = isset($_POST['review_id']) ? $_POST['review_id'] : '';
		$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';
		$success = 'false';
		
		if ($idea_id && $user_id && !$review_id) {
			// check if review already exists
			$args = array(
				'post_type'   => 'idea_review',
				'post_status' => 'publish',
				'numberposts' => -1,
				'fields'      => 'ids',
				'meta_query'  => array(
					'relation' => 'AND',
					array(
					    'key'     => '_idea_id',
					    'value'   => $idea_id,
					    'compare' => '=',
					),
					array(
					    'key'     => '_user_id',
					    'value'   => $user_id,
					    'compare' => '=',
					),
				),
			);

			$review = get_posts($args);

			$review_data = array(
				'post_type'      => 'idea_review',
				'post_title'     => 'Review # idea: ' . $idea_id . ' user: ' . $user_id,
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_author'    => get_current_user_id(),
			);

			$review_meta_data = array(
				'_idea_id'       => $idea_id,
				'_user_id'     => $user_id,
			);

			// update review
			if ($review) {
				$review_data['ID'] = $review[0];
				$review_id = wp_update_post($review_data, true);
			}
			// create a new one
			else {
				$review_id = wp_insert_post($review_data, true);
			}

			if (!is_wp_error($review_id) && $review_id > 0) {
				foreach ($review_meta_data as $meta_key => $meta_value) {
					add_post_meta($review_id, $meta_key, $meta_value, true)
					or
					update_post_meta($review_id, $meta_key, $meta_value);
				}
			}
		}

		// post user review
		if ($review_id) {
			global $klc_ideas;
			$review_criteria = $klc_ideas['review_criteria'];

			$rating = array();

			if ($review_criteria) {
				foreach ($review_criteria as $criteria) {
					$slug = klc_slugify($criteria);

					$score = (isset($_POST[$slug])) ? floatval($_POST[$slug]) : 0;
					$rating[$slug] = $score;
				}
			}

			$comment = (isset($_POST['comment'])) ? $_POST['comment'] : '';

			$idea_review = array(
				'rating'  => $rating,
				'comment' => $comment,
			);

			if (get_post($review_id)) {
				update_post_meta($review_id, '_idea_review', $idea_review);
				$success = 'true';
			}
		}

		echo json_encode(array(
			'success' => $success
		));

		exit;
	}
	add_action('wp_ajax_klc_post_user_review_for_idea', 'klc_post_user_review_for_idea');
}

/**
 * Get all reviews and average ratings
 */
if (!function_exists('klc_get_reviews_and_average_rating')) {
	function klc_get_reviews_and_average_rating($idea_id, $type) {
		$type = ($type === 'expert') ? '_expert_id' : '_user_id';

		$args = array(
			'post_type'   => 'idea_review',
			'post_status' => 'publish',
			'numberposts' => -1,
			'fields'      => 'ids',
			'meta_query'  => array(
				'relation' => 'AND',
				array(
				    'key'     => '_idea_id',
				    'value'   => $idea_id,
				    'compare' => '=',
				),
				array(
				    'key'     => $type,
				    'compare' => 'EXISTS',
				),
			),
		);

		$reviews = get_posts($args);
		$reviews2 = array();

		$idea_reviews = array();
		$review_found = false;

		if ($reviews) {
		    foreach ($reviews as $review_id) {
		        if ($idea_review = get_post_meta($review_id, '_idea_review', true)) {
		            $idea_reviews[] = $idea_review['rating'];
		            $reviews2[] = $review_id;
		            $review_found = true;
		        }
		    }
		}

		global $klc_ideas;
		$review_criteria = $klc_ideas['review_criteria'];
		$reviews_in_criteria = array();

		if ($review_criteria && $review_found) {
		    foreach ($review_criteria as $criteria) {
		        $slug = klc_slugify($criteria);

		        foreach ($idea_reviews as $idea_review) {
		            $reviews_in_criteria[$criteria][] = $idea_review[$slug];
		        }
		    }
		}

		// average in each criteria
		$average_in_each_criteria = array();
		$average = '';

		if ($review_found) {
		    foreach ($reviews_in_criteria as $criteria => $values) {
		        $average_in_each_criteria[$criteria] = number_format(array_sum($values) / count($values), 1, '.', '');
		    }

		    // average
		    $average = number_format(array_sum($average_in_each_criteria) / count($average_in_each_criteria), 1, '.', '');
		}

		return array(
			'review_found'             => $review_found,
			'average_in_each_criteria' => $average_in_each_criteria,
			'average'                  => $average,
			'reviews'                  => $reviews2,
		);
	}
}

/**
 * Get review for given idea and user id
 */
if (!function_exists('klc_get_review')) {
	function klc_get_review($idea_id, $user_id, $type) {
		$type = ($type === 'expert') ? '_expert_id' : '_user_id';

		$args = array(
			'post_type'   => 'idea_review',
			'post_status' => 'publish',
			'numberposts' => -1,
			'fields'      => 'ids',
			'meta_query'  => array(
				'relation' => 'AND',
				array(
				    'key'     => '_idea_id',
				    'value'   => $idea_id,
				    'compare' => '=',
				),
				array(
				    'key'     => $type,
				    'value'   => $user_id,
				    'compare' => '=',
				),
			),
		);

		$review = get_posts($args);

		return $review;
	}
}

/**
 * Count reviews for different step
 *
 * It would work like this: 12 people gave 5 stars, 6 people gave 2 stars...
 */
if (!function_exists('klc_count_reviews')) {
	function klc_count_reviews($type) {
		$type = ($type === 'expert') ? '_expert_id' : '_user_id';

		$args = array(
			'post_type'   => 'idea_review',
			'post_status' => 'publish',
			'numberposts' => -1,
			'fields'      => 'ids',
			'meta_query'  => array(
				'relation' => 'AND',
				array(
				    'key'     => '_idea_id',
				    'value'   => get_the_ID(),
				    'compare' => '=',
				),
				array(
				    'key'     => $type,
				    'compare' => 'EXISTS',
				),
			),
		);

		$reviews = get_posts($args);

		$count_ratings = array(
			'5.0' => 0,
			'4.5' => 0,
			'4.0' => 0,
			'3.5' => 0,
			'3.0' => 0,
			'2.5' => 0,
			'2.0' => 0,
			'1.5' => 0,
			'1.0' => 0,
			'0.5' => 0,
			'0.0' => 0,
		);

		$expert_idea_average_ratings = array();

		if ($reviews) {
		    foreach ($reviews as $review_id) {
		        if ($idea_review = get_post_meta($review_id, '_idea_review', true)) {
		        	$idea_rating = $idea_review['rating'];
		        	$average = array_sum($idea_rating) / count($idea_rating);
		        	$average = floor($average * 2) / 2;
		        	$expert_idea_average_ratings[] = number_format($average, 1, '.', '');
		        }
		    }
		}

		// count
		if ($expert_idea_average_ratings) {
			foreach ($expert_idea_average_ratings as $key => $value) {
				$count_ratings[$value] += 1;
			}
		}

		return $count_ratings;
	}
}

// Load required metaboxes
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
if (function_exists('is_plugin_active') && is_plugin_active('cmb2/init.php')) {
	require_once(plugin_dir_path(__FILE__) . 'idea-metaboxes.php');
	// require_once(plugin_dir_path(__FILE__) . 'front-end-submit.php');
	// require_once(plugin_dir_path(__FILE__) . 'raw.php');
}

// CMB2 custom field type 'users_with_avatar'
if (!function_exists('cmb2_render_users_with_avatar')) {
	function cmb2_render_users_with_avatar( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
		wp_enqueue_style('select2-styles');
		wp_enqueue_script('select2-js');

		$values = (array) $field_type_object->field->value();

		$users = get_users();

	    $html = '';
	    $html .= '<select name="' . $field_type_object->_name() . '[]' . '" class="select2-users-with-avatar" id="' . $field_type_object->_id() . '" multiple="multiple">';
	    $html .= '<option></option>';

	    if ($users) {
	    	foreach ($users as $user) {
	    		$user_id = $user->ID;
	    		$display_name = $user->data->display_name;
	    		$placeholder = __('Search user', IDEAS_TEXT_DOMAIN);
	    		$member_type = function_exists('bp_get_member_type') ? bp_get_member_type($user_id) : '';
	    		$avatar_link = klc_get_avatar($user_id);
	    		$selected = (in_array($user_id, $values)) ? 'selected="selected"' : '';

	    		$html .= '<option value="' . $user_id . '" ' . $selected . ' data-member-type="' . $member_type . '" data-avatar="' . $avatar_link . '">' . $display_name . '</option>';
	    	}
	    }

	    $html .= '</select>';

	    $html .= "
	    <style type='text/css'>
	    	.cmb-type-users-with-avatar .select2-container .select2-choice {
	    		height: 60px;
			    line-height: 55px;
	    	}
	    	.cmb-type-users-with-avatar .select2-container .select2-choice .select2-arrow b {
	    		background-position: 0 15px;
	    	}
	    	.select2-search-choice img,
	    	.select2-result img,
	    	.cmb-type-users-with-avatar .select2-choice img {
	    		vertical-align: middle;
	    	}
	    	.cmb-type-users-with-avatar .select2-container-multi .select2-search-choice-close {
	    		top: 10px;
	    	}
	    </style>
	    <script type='text/javascript'>
	    	jQuery(document).ready(function($) {
	    		if (jQuery().select2) {
	    		    function format(state) {
	    		        var originalOption = state.element,
	    		            member_type = '';

	    		        if ($(originalOption).data('member-type')) {
	    		            member_type = ' (' + $(originalOption).data('member-type') + ')';
	    		        }
	    		        return '<span><img src=' + $(originalOption).data('avatar') + ' width=32 height=32 /> ' + state.text + member_type + '</span>';
	    		    }

	    		    $('#" . $field_type_object->_id() . "').select2({
	    		        placeholder: '" . $placeholder . "',
	    		        formatResult: format,
	    		        formatSelection: format,
	    		    });
	    		}
	    	});
	    </script>
	    ";

	    echo $html;
	}
	add_action( 'cmb2_render_users_with_avatar', 'cmb2_render_users_with_avatar', 10, 5 );
}

// post idea update
if (!function_exists('klc_post_idea_update')) {
	function klc_post_idea_update() {
		$idea_update = isset($_POST['idea_update']) ? $_POST['idea_update'] : '';
		$idea_id = isset($_POST['idea_id']) ? $_POST['idea_id'] : '';
		$review_id = isset($_POST['review_id']) ? $_POST['review_id'] : '';
		$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';
		$success = 'false';
		
		if ($idea_id && $user_id && !$review_id) {
			// check if review already exists
			$args = array(
				'post_type'   => 'idea_review',
				'post_status' => 'publish',
				'numberposts' => -1,
				'fields'      => 'ids',
				'meta_query'  => array(
					'relation' => 'AND',
					array(
					    'key'     => '_idea_id',
					    'value'   => $idea_id,
					    'compare' => '=',
					),
					array(
					    'key'     => '_user_id',
					    'value'   => $user_id,
					    'compare' => '=',
					),
				),
			);

			$review = get_posts($args);

			$review_data = array(
				'post_type'      => 'idea_review',
				'post_title'     => 'Update # idea: ' . $idea_id . ' user: ' . $user_id,
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_author'    => get_current_user_id(),
			);

			$review_meta_data = array(
				'_idea_id' => $idea_id,
				'_user_id' => $user_id,
			);

			// update review
			if ($review) {
				$review_data['ID'] = $review[0];
				$review_id = wp_update_post($review_data, true);
			}
			// create a new one
			else {
				$review_id = wp_insert_post($review_data, true);
			}

			if (!is_wp_error($review_id) && $review_id > 0) {
				foreach ($review_meta_data as $meta_key => $meta_value) {
					add_post_meta($review_id, $meta_key, $meta_value, true)
					or
					update_post_meta($review_id, $meta_key, $meta_value);
				}
			}
		}

		// update user update
		if ($review_id && get_post($review_id)) {
			update_post_meta($review_id, '_idea_update', $idea_update);
			$success = 'true';
		}

		echo json_encode(array(
			'success' => $success
		));

		exit;
	}
	add_action('wp_ajax_klc_post_idea_update', 'klc_post_idea_update');
}

// get idea updates
if (!function_exists('klc_get_idea_updates')) {
	function klc_get_idea_updates($idea_id) {
		$args = array(
		    'post_type'   => 'idea_review',
		    'post_status' => 'publish',
		    'numberposts' => -1,
		    'fields'      => 'ids',
		    'meta_query'  => array(
		        'relation' => 'AND',
		        array(
		            'key'     => '_idea_id',
		            'value'   => $idea_id,
		            'compare' => '=',
		        ),
		        array(
		            'key'     => '_idea_update',
		            'compare' => 'EXISTS',
		        ),
		    ),
		);

		return get_posts($args);
	}
}

// read more link
if (!function_exists('idea_read_more_link')) {
	function idea_read_more_link($more) {
		global $post;

		if ($post->post_type === 'ideas') {
			return '<a href="#idea-details" class="go-to-idea-detials"> [' . __('See More', IDEAS_TEMPLATE_PATH) . ']</a>';
		} else {
			return $more;
		}
	}
	add_filter('excerpt_more', 'idea_read_more_link');
}

if (!function_exists('klc_get_idea_modal_content')) {
	function klc_get_idea_modal_content() {
		$idea_id = isset($_POST['idea_id']) ? $_POST['idea_id'] : '';

		$params = array(
			'idea_id' => $idea_id,
		);

		ob_start();
		klc_render_template('frontend-idea-submit', array('idea_id' => $idea_id));
		$content = ob_get_clean();

		echo $content;

		exit;
	}
	add_action('wp_ajax_klc_get_idea_modal_content', 'klc_get_idea_modal_content');
}

// create or update idea from frontend
if (!function_exists('klc_submit_idea_data_from_frontend')) {
	function klc_submit_idea_data_from_frontend() {
		$_idea_id = isset($_POST['_idea_id']) ? $_POST['_idea_id'] : '';
		$_idea_title = isset($_POST['_idea_title']) ? $_POST['_idea_title'] : '';
		$_idea_content = isset($_POST['_idea_content']) ? $_POST['_idea_content'] : '';
		$_idea_campaign = isset($_POST['_idea_campaign']) ? $_POST['_idea_campaign'] : '';
		$_idea_image = isset($_POST['_idea_image']) ? $_POST['_idea_image'] : '';
		$_idea_image_id = isset($_POST['_idea_image_id']) ? $_POST['_idea_image_id'] : '';
		$_idea_file = isset($_POST['_idea_file']) ? $_POST['_idea_file'] : '';
		$_idea_file_id = isset($_POST['_idea_file_id']) ? $_POST['_idea_file_id'] : '';
		$_idea_video = isset($_POST['_idea_video']) ? $_POST['_idea_video'] : '';
		$_idea_video_id = isset($_POST['_idea_video_id']) ? $_POST['_idea_video_id'] : '';
		$_idea_youtube = isset($_POST['_idea_youtube']) ? $_POST['_idea_youtube'] : '';

		$success = 'false';
		$error = '';

		if (!$_idea_title) {
			$error = __('Idea title is required', IDEAS_TEXT_DOMAIN);
		}
		// title found
		else {
			// post data
			$post_data = array(
				'post_title'   => sanitize_text_field($_idea_title),
				'post_content' => $_idea_content,
			);

			// post meta data
			$meta_data = array(
				'idea_campaign' => intval($_idea_campaign),
				'_idea_image'    => sanitize_text_field($_idea_image),
				'_idea_image_id' => intval($_idea_image_id),
				'_idea_file'     => sanitize_text_field($_idea_file),
				'_idea_file_id'  => intval($_idea_file_id),
				'_idea_video'    => sanitize_text_field($_idea_video),
				'_idea_video_id' => intval($_idea_video_id),
				'_idea_youtube'  => sanitize_text_field($_idea_youtube),
			);

			// if idea found for given object id then update idea
			$idea = get_post($_idea_id);

			if ($idea && $idea->post_type === 'ideas') {
				$post_data['ID'] = $idea->ID;
				$post_data['updated_at'] = current_time('mysql');
				$idea_id = wp_update_post($post_data, true);
			}
			// create new one
			else {
				$user_id = get_current_user_id();

				$post_data['post_type'] = 'ideas';
				$post_data['post_status'] = 'publish';
				$post_data['post_author'] = $user_id ? $user_id : 1;
				$post_data['post_date'] = current_time('mysql');

				$idea_id = wp_insert_post($post_data, true);
			}

			// update post meta data
			if (!is_wp_error($idea_id) && $idea_id > 0) {
				foreach ($meta_data as $meta_key => $meta_value) {
					add_post_meta($idea_id, $meta_key, $meta_value, true)
					or
					update_post_meta($idea_id, $meta_key, $meta_value);
				}

				// campaign_ideas
				$args = array(
					'post_type'  => 'campaigns',
					'meta_query' => array(
						array(
							'key'     => 'campaign_ideas',
							'value'   => $idea_id,
							'compare' => '=',
						),
					),
				);

				$campaigns = get_posts($args);

				// delete from existing
				if ($campaigns) {
					foreach ($campaigns as $campaign) {
						$campaign_id = $campaign->ID;
						delete_post_meta($campaign_id, 'campaign_ideas', $idea_id);
					}
				}

				$campaign_id = get_post_meta($idea_id, 'idea_campaign', true);

				// add to campaign
				add_post_meta($campaign_id, 'campaign_ideas', $idea_id);

				$success = 'true';
			}
		}

		echo json_encode(array(
			'error'   => $error,
			'success' => $success,
		));

		exit;
	}
	add_action('wp_ajax_klc_submit_idea_data_from_frontend', 'klc_submit_idea_data_from_frontend');
}

// get all campaigns
if (!function_exists('klc_get_campaigns')) {
	function klc_get_campaigns() {
		$campaigns = get_posts(array(
			'post_type' => 'campaigns',
			'nopaging'  => true,
		));

		$retrun_campaigns = array();

		if ($campaigns) {
			$retrun_campaigns[] = __('Select campaign', IDEAS_TEXT_DOMAIN);

			foreach ($campaigns as $key => $value) {
				$retrun_campaigns[$value->ID] = $value->post_title;
			}
		}

		return $retrun_campaigns;
	}
}

add_action('cmb2_save_field_idea_campaign', 'klc_handle_campaign', 10, 3);

if (!function_exists('klc_handle_campaign')) {
	function klc_handle_campaign($updated, $action, $field) {
		if ($updated) {
			$args = array(
				'post_type'  => 'campaigns',
				'meta_query' => array(
					array(
						'key'     => 'campaign_ideas',
						'value'   => $field->object_id,
						'compare' => '=',
					),
				),
			);

			$campaigns = get_posts($args);

			// delete from existing
			if ($campaigns) {
				foreach ($campaigns as $campaign) {
					$campaign_id = $campaign->ID;
					delete_post_meta($campaign_id, 'campaign_ideas', $field->object_id);
				}
			}

			$campaign_id = get_post_meta($field->object_id, 'idea_campaign', true);

			// add to campaign
			add_post_meta($campaign_id, 'campaign_ideas', $field->object_id);
		}
	}
}

require_once(plugin_dir_path(__FILE__) . 'classes/class-ideas-submission.php');
require_once(plugin_dir_path(__FILE__) . 'classes/class-ideas-session.php');
require_once(plugin_dir_path(__FILE__) . 'includes/helper-functions.php');

/**
 * Run when plugin is loaded
 */
function klc_idea_submission() {}
add_action('plugins_loaded', 'klc_idea_submission');

/**
 * Start Session
 */
function klc_ideas_init() {
	if (!session_id()) {
		session_start();
	}
}
add_action('init', 'klc_ideas_init');