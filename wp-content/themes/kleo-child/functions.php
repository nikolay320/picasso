<?php
/**
 * @package WordPress
 * @subpackage Kleo
 * @author SeventhQueen <themesupport@seventhqueen.com>
 * @since Kleo 1.0
 */

/**
 * Kleo Child Theme Functions
 * Add custom code below

*/

add_action( 'wp_enqueue_scripts', 'kleo_child_frontend_files' );
if (!function_exists('kleo_child_frontend_files')) {
    // Register some javascript files
    function kleo_child_frontend_files()
    {
        //head scripts
        wp_register_script( 'kleo-child-init', get_stylesheet_directory_uri() . '/assets/js/child_init.js', array('jquery'), KLEO_THEME_VERSION, false );

        wp_enqueue_script('kleo-child-init');
    }
}

// Rank chain. Comment the row bellow to hide rank progressbar.
add_action( 'mycred_bp_profile_before_history', 'kleo_mycred_rank_chain' );
if (!function_exists('kleo_mycred_rank_chain')) {
	function kleo_mycred_rank_chain()
	{
		$kleo_mycred_user_points = mycred_get_users_cred( bp_displayed_user_id() );
		$kleo_mycred_ranks	 = mycred_get_ranks( 'publish', -1, 'ASC' );

		$kleo_mycred_next_status_points = 0;


		// ----- Calculation of progress bar width -----

		//x = (100 - (n - 1)*(95.55/n))/2
		//y = 95.55/n

		$km_chain_common_width = 0;

		$km_chain_first_last_el_width = (100 - (count($kleo_mycred_ranks) - 1)*(95.55/count($kleo_mycred_ranks)))/2;
		$km_chain_el_width = 95.55/(count($kleo_mycred_ranks));

		$kleo_mycred_ranks_counter = 0;

		// ----- Calculation of progress bar width -----



		// ----- Get current rank color -----

		$kleo_mycred_current_rank_image_src = wp_get_attachment_image_src(get_post_thumbnail_id(mycred_get_users_rank( bp_displayed_user_id(), 'ID' )));
		$kleo_mycred_current_rank_image_src = basename($kleo_mycred_current_rank_image_src[0]);
		$kleo_mycred_current_rank_color = '#'.substr($kleo_mycred_current_rank_image_src, 0, strpos($kleo_mycred_current_rank_image_src, '-'));
		$kleo_mycred_current_rank_color = $kleo_mycred_current_rank_color != '#' ? $kleo_mycred_current_rank_color : '#DEDEDE';

		// ----- Get current rank color -----




		$km_chain_html = '';


		$km_chain_html .= '<div class="kleo-mycred-chain-wrap">';

		$km_chain_html .= '<div class="kleo-mycred-chain-current-points">'.intval($kleo_mycred_user_points).' points</div>';
		$km_chain_html .= '<div class="kleo-mycred-chain-line"></div>';

		$km_chain_html .= '    <ul class="kleo-mycred-chain-bar chain-'.count($kleo_mycred_ranks).'">';

		foreach($kleo_mycred_ranks as $kleo_mycred_ranks_item) {
			$km_chain_html .= '<li>';
			$km_chain_html .= '    <span class="kleo-mycred-chain-item-top-title">'.$kleo_mycred_ranks_item->meta_value.'</span>';
			$km_chain_html .= mycred_get_rank_logo($kleo_mycred_ranks_item->ID);
			$km_chain_html .= '    <span class="kleo-mycred-chain-item-bottom-title">'.$kleo_mycred_ranks_item->post_title.'</span>';
			$km_chain_html .=  '</li>';



			$kleo_mycred_rank_max_value = get_post_meta( $kleo_mycred_ranks_item->ID, 'mycred_rank_max', true );

			if(($kleo_mycred_user_points >= $kleo_mycred_ranks_item->meta_value) && ($kleo_mycred_user_points <= $kleo_mycred_rank_max_value)) {
				if(($kleo_mycred_ranks_counter + 1) == count($kleo_mycred_ranks)) {
					//$km_chain_common_width = $km_chain_first_last_el_width*2 + ($kleo_mycred_ranks_counter-1)*$km_chain_el_width;
					$km_chain_common_width = $km_chain_first_last_el_width + $kleo_mycred_ranks_counter*$km_chain_el_width;

					$km_chain_common_width += (($kleo_mycred_user_points - $kleo_mycred_ranks_item->meta_value)/($kleo_mycred_rank_max_value - $kleo_mycred_ranks_item->meta_value))*$km_chain_first_last_el_width;
				} else {
					$km_chain_common_width = $km_chain_first_last_el_width + $kleo_mycred_ranks_counter*$km_chain_el_width;

					$km_chain_common_width += (($kleo_mycred_user_points - $kleo_mycred_ranks_item->meta_value)/($kleo_mycred_rank_max_value - $kleo_mycred_ranks_item->meta_value))*$km_chain_el_width;
				}

				$kleo_mycred_next_status_points = $kleo_mycred_rank_max_value - $kleo_mycred_user_points + 1;
			} else if(($kleo_mycred_user_points >= 0) && ($kleo_mycred_user_points <= $kleo_mycred_ranks_item->meta_value) && $kleo_mycred_ranks_counter == 0) {
				$km_chain_common_width = ($kleo_mycred_user_points/$kleo_mycred_ranks_item->meta_value)*$km_chain_first_last_el_width;

				$kleo_mycred_next_status_points = $kleo_mycred_ranks_item->meta_value - $kleo_mycred_user_points;
			}

			$kleo_mycred_ranks_counter++;
		}

		$km_chain_html .= '    </ul>';
		$km_chain_html .= '    <div class="kleo-mycred-chain-next-status-points">Prochain badge dan '.$kleo_mycred_next_status_points.' points.</div>';
		$km_chain_html .= '</div>';

		echo '<style>';
		echo 'ul.kleo-mycred-chain-bar:before { width:'.$km_chain_common_width.'% !important; }';
		echo '.kleo-mycred-chain-line { margin-left: calc('.$km_chain_common_width.'% - 1px) !important; }';
		echo '.kleo-mycred-chain-current-points { margin-left: calc('.$km_chain_common_width.'% - 50px) !important; }';
		echo '.kleo-mycred-chain-next-status-points { margin-left: calc('.$km_chain_common_width.'% - 1px) !important; }';
		echo '.kleo-mycred-chain-current-points { background: '.$kleo_mycred_current_rank_color.'; }';
		echo '@keyframes bounceInProgressBar { 0% { opacity: 1; width: 0; } 100% { opacity: 1; width: '.$km_chain_common_width.'%; }}';
		echo '@-webkit-keyframes bounceInProgressBar { 0% { opacity: 1; width: 0; } 100% { opacity: 1; width: '.$km_chain_common_width.'%; }}';
		echo '@-moz-keyframes bounceInProgressBar { 0% { opacity: 1; width: 0; } 100% { opacity: 1; width: '.$km_chain_common_width.'%; }}';
		echo '</style>';

		echo $km_chain_html;

	}
}

/* ---------- For BuddyPress Doc ---------- */
if ( ! function_exists( 'kleo_child_excerpt' ) ) {
	function kleo_child_excerpt( $limit = 20, $words = true ) {
		//$excerpt_initial = get_the_excerpt();

		$content = apply_filters( 'the_content', $content );
		//echo '<pre>'.$content.'</pre>';
		//echo get_the_content();
		//$content = str_replace( ']]>', ']]&gt;', $content );

		if( $excerpt_initial == '' ){
			$excerpt_initial = get_the_content();
		}
		$excerpt_initial = preg_replace( '`\[[^\]]*\]`', '', $excerpt_initial );
		$excerpt_initial = strip_tags( $excerpt_initial );

		if ( $words ) {
			$excerpt = explode( ' ', $excerpt_initial, $limit );
			if ( count( $excerpt ) >= $limit ) {
				array_pop( $excerpt );
				$excerpt = implode( " ", $excerpt ) . '...';
			} else {
				$excerpt = implode( " ", $excerpt ) . '';
			}
		} else {
			$excerpt = $excerpt_initial;
			$excerpt = substr( $excerpt, 0, $limit ) . ( strlen( $excerpt ) > $limit ? '...' : '' );
		}

		return '<p>' . $excerpt . '</p>' . $content;
	}
}
if ( ! function_exists( 'kleo_child_bp_docs_icon' ) ) {
	function kleo_child_bp_docs_icon() {
		$atts = bp_docs_get_doc_attachments( get_the_ID() );

		$files_count = 0;

		$html = '';

		$html .= '<span class="kleo-child-bp-docs-multiply-filetype"></span>';

		if ( ! empty( $atts ) ) {
			$files_count = count($atts);
		}

		$html .= $files_count <= 1 ? '' : $files_count;

		return $html;
	}
}
if ( ! function_exists( 'kleo_child_bp_member_get_color_class' ) ) {
	function kleo_child_bp_member_get_color_class($bp_user_id) {
		//$member_type_post_title = '';
//bp_set_member_type( 12, 'r44' );
		$member_type = bp_get_member_type( $bp_user_id );

		/*$bp_member_type_post_ids = kleo_child_get_active_member_types();

		//$member_type_object = bp_get_member_type_object($member_type);

		foreach($bp_member_type_post_ids as $bp_member_type_post_id) {
			if ( $member_type == get_post_meta( $bp_member_type_post_id, '_bp_member_type_name', true ) ) {
				$member_type_post_title = get_the_title($bp_member_type_post_id);
			}
		}

		$kleo_child_bpmt_color_first_position = strpos($member_type_post_title, '(color=') + strlen('(color=');
		$kleo_child_bpmt_color_length = strpos($member_type_post_title, ')') - $kleo_child_bpmt_color_first_position;

		$kleo_child_bpmt_color = '#'.substr($member_type_post_title, $kleo_child_bpmt_color_first_position, $kleo_child_bpmt_color_length);*/


		return 'kleo-child-bpmt-color-class-'.$member_type;
	}
}

if ( ! function_exists( 'kleo_child_bp_member_set_color_style' ) ) {
	function kleo_child_bp_member_set_color_style() {
		$css_text = '';

		$bp_member_type_post_ids = kleo_child_get_active_member_types();

		//$member_type_object = bp_get_member_type_object($member_type);

		foreach($bp_member_type_post_ids as $bp_member_type_post_id) {
			$member_type_post_title = get_the_title($bp_member_type_post_id);

			$kleo_child_bpmt_color_first_position = strpos($member_type_post_title, '(color=') + strlen('(color=');
			$kleo_child_bpmt_color_length = strpos($member_type_post_title, ')') - $kleo_child_bpmt_color_first_position;

			$kleo_child_bpmt_color = '#'.substr($member_type_post_title, $kleo_child_bpmt_color_first_position, $kleo_child_bpmt_color_length);

			$css_text .= '.kleo-child-bpmt-color-class-'.get_post_meta( $bp_member_type_post_id, '_bp_member_type_name', true ).':before {';
			$css_text .= '    border-top-color: '.$kleo_child_bpmt_color.' !important;';
			$css_text .= '}';

			/* FOR SELECT ELEMENT IN MEMBER TYPE SECTION */
			$css_text .= '#kleo_child_bp_member_type_select.member-type-select-bg-'.get_post_meta( $bp_member_type_post_id, '_bp_member_type_name', true ).' {';
			$css_text .= '    background-image: url("'.get_stylesheet_directory_uri().'/assets/img/select-arrow.png"), -webkit-linear-gradient(left, '.$kleo_child_bpmt_color.' calc(100% - 30px), transparent 30px);';
			$css_text .= '    background-image: url("'.get_stylesheet_directory_uri().'/assets/img/select-arrow.png"), -o-linear-gradient(left, '.$kleo_child_bpmt_color.' calc(100% - 30px), transparent 30px);';
			$css_text .= '    background-image: url("'.get_stylesheet_directory_uri().'/assets/img/select-arrow.png"), -moz-linear-gradient(left, '.$kleo_child_bpmt_color.' calc(100% - 30px), transparent 30px);';
			$css_text .= '    background-image: url("'.get_stylesheet_directory_uri().'/assets/img/select-arrow.png"), linear-gradient(left, '.$kleo_child_bpmt_color.' calc(100% - 30px), transparent 30px);';
			$css_text .= '    background-position: 100% 50%;';
			$css_text .= '    background-repeat: no-repeat;';
			$css_text .= '    color: #fff;';
			$css_text .= '}';
		}

		return $css_text;
	}
}

if ( ! function_exists( 'kleo_child_get_active_member_types' ) ) {
	function kleo_child_get_active_member_types() {
		
		global $wpdb;
		
		$query = "SELECT DISTINCT ID FROM {$wpdb->posts} WHERE post_type = %s AND ID IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key=%s AND meta_value = %d ) ";
		
		return $wpdb->get_col( $wpdb->prepare( $query, bp_member_type_generator()->get_post_type(),'_bp_member_type_is_active', 1 ) );
	}
}
/* ---------- For BuddyPress Doc ---------- */

//*** Rasheda Work ***//


// Edit comments for ideas.

if (!function_exists('kleo_custom_comments')) {
    function kleo_custom_comments($comment, $args, $depth) {
    	global $current_user;
        $GLOBALS['comment'] = $comment;
        $GLOBALS['comment_depth'] = $depth;
        ?>

        <li id="comment-<?php comment_ID() ?>" <?php comment_class('clearfix') ?>>
        <div class="comment-wrap clearfix">
            <div class="comment-avatar kleo-rounded">
                <?php if(function_exists('get_avatar')) { echo get_avatar($comment, '100'); } ?>
                <?php if ($comment->comment_author_email == get_the_author_meta('email')) { ?>
                    <span class="tooltip"><?php _e("Author", "kleo_framework"); ?><span class="arrow"></span></span>
                <?php } ?>
            </div>
            <div class="comment-content">
                <div class="comment-meta">
                    <?php
                    printf('<span class="comment-author">%1$s</span> <span class="comment-date">%2$s</span>',
                        get_comment_author_link(),
                        human_time_diff( get_comment_time('U'), current_time('timestamp') ) . ' ' . __("ago", "kleo_framework")
                    );
                    ?>
                </div>
                <?php if ($comment->comment_approved == '0') _e("<span class='unapproved'>Your comment is awaiting moderation.</span>\n", 'kleo_framework') ?>
                <div class="comment-body comment_text_<?php comment_ID() ?>">
                    <?php comment_text();?>
                </div>
                <div class="comment-meta-actions">
                	<?php if($comment->user_id == $current_user->ID): ?>
                	<span class="edit-link">
                		<a style="cursor:pointer" class="comment_popup" data-commentid="<?php comment_ID() ?>"><?php _e('Edit', 'kleo_framework')?></a>
                	</span>
                	<?php endif; ?>
                    <?php if($args['type'] == 'all' || get_comment_type() == 'comment') :
                        comment_reply_link(array_merge($args, array(
                            'reply_text' => __('Reply','kleo_framework'),
                            'login_text' => __('Log in to reply.','kleo_framework'),
                            'depth' => $depth,
                            'before' => '<span class="comment-reply">',
                            'after' => '</span>'
                        )));
                    endif; ?>
                </div>
            </div>
        </div>
    <?php }
} // end kleo_custom_comments


/*
if ( ! function_exists('write_log')) {
   function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
         error_log( print_r( $log, true ) );
      } else {
         error_log( $log );
      }
   }
}
*/

/* ---------- For BuddyPress Member Type ---------- */

//add_action( 'bp_members_directory_member_types', 'kleo_bp_member_types_tabs_select' );
function kleo_bp_member_types_tabs_select() {
    $html = '';

    if( ! bp_get_current_member_type() ){
        $member_types = bp_get_member_types( array(), 'objects' );
        if( $member_types ) {
            $html .= '<select id="kleo_child_bp_member_type_select" class="bp-member-type-filter">';
            $html .= '<option value="members-all">'.sprintf( __( 'All Members <span>%s</span>', 'buddypress' ), bp_get_total_member_count() ).'</option>';
            foreach ( $member_types as $member_type ) {
                if ( $member_type->has_directory == 1 ) {
                    $html .= '<option value="members-' . esc_attr($member_type->name) . '">';
                    $html .= '<a href="' . bp_get_members_directory_permalink() . 'type/' . $member_type->directory_slug . '/">' . sprintf('%s <span>%d</span>', $member_type->labels['name'], kleo_bp_count_member_types($member_type->name)) . '</a>';
                    $html .= '</option>';
                }
            }
            $html .= '</select>';
        }
    }

    return $html;
}
/* ---------- For BuddyPress Member Type ---------- */


/*add_action('transition_post_status', 'kleo_child_check_function', 10, 3);
function kleo_child_check_function($new_status, $old_status, $post) {
	$kleo_child_check_file = file_get_contents(__DIR__.'/badges_log.txt');

	$post_type = get_post_type($post->ID);

	//file_put_contents(__DIR__.'/badges_log.txt', 'check');

	if( $post_type !== 'ajde_events' )
	return;

	if($old_status == 'draft' && $new_status == 'publish') {
		file_put_contents(__DIR__.'/badges_log.txt', $kleo_child_check_file.' - '.$post->ID.' '.$post_type);
	}
}*/

if ( defined( 'myCRED_VERSION' ) ) {
	include( plugin_dir_path(__FILE__) . 'classes/class-mycred.php');

	add_filter( 'mycred_setup_hooks', 'kleo_child_eventon_register_mycred_hook' );
	function kleo_child_eventon_register_mycred_hook( $installed ) {
		$installed['kleo_child_eventon_adding_event'] = array(
			'title'       => __( '%plural% for Adding an Event', 'eventon' ),
			'description' => __( 'This hook award / deducts points from users who add an event.', 'eventon' ),
			'callback'    => array( 'kleo_child_eventon_mycred_class' )
		);
		return $installed;
	}

	add_filter( 'mycred_all_references', 'kleo_child_eventon_myCRED_references' );
	function kleo_child_eventon_myCRED_references( $hooks ) {
		$hooks['kleo_child_eventon_add'] = __( 'Adding approved event', 'eventon' );
		return $hooks;
	}
}

/* MyCRED badges */
if ( function_exists( 'mycred_get_users_badges' ) ) {
	function kleo_child_mycred_display_users_badge( $current_user_id ) {
		extract( shortcode_atts( array(
			'show'    => 'earned',
			'width'   => MYCRED_BADGE_WIDTH,
			'height'  => MYCRED_BADGE_HEIGHT,
			'user_id' => ''
		), $atts ) );

		/*if ( ! is_user_logged_in() && $user_id == '' ) return $content;

		if ( $user_id == '' )
			$user_id = get_current_user_id();*/

		$users_badges = mycred_get_users_badges( $current_user_id );

		if ( $width != '' )
			$width = ' width="' . $width . '"';

		if ( $height != '' )
			$height = ' height="' . $height . '"';

		//ob_start();

		echo '<div id="mycred-users-badges">';

		$all_badges = mycred_get_badges();
		foreach ( $all_badges as $badge ) {

			//echo '<div class="the-badge">';

			// User has not earned badge
			if ( ! array_key_exists( $badge->ID, $users_badges ) ) {

				if ( $badge->default_img != '' )
					echo '<img src="' . $badge->default_img . '"' . $width . $height . ' class="mycred-badge not-earned" alt="' . $badge->post_title . '" title="' . $badge->post_title . '" />';

			}

			// User has  earned badge
			else {

				$level_image = get_post_meta( $badge->ID, 'level_image' . $users_badges[ $badge->ID ], true );
				if ( $level_image == '' )
					$level_image = $badge->main_img;

				echo '<img src="' . $level_image . '"' . $width . $height . ' class="mycred-badge earned" alt="' . $badge->post_title . '" title="' . $badge->post_title . '" />';
			}

			//echo '</div>';

		}

		echo '</div>';

	}
}
/* MyCRED badges */


/* ========== ABOUT PAGE ========== */
if (! function_exists( 'kleo_child_show_about_page' ) ) {
	function kleo_child_show_about_page() {
		$html = '';

		$kleo_child_about_page = get_post(1563);

		$html .= apply_filters('the_content', $kleo_child_about_page->post_content);

		return $html;
	}
}
/* bof by janero 2016 05 04 */
/** custom field for actionUser (source:http://www.myeventon.com/documentation/add-additional-fields-actionuser-event-submission-form/) **/
add_filter('evoau_form_fields', 'evoautest_fields_to_form', 10, 1);
function evoautest_fields_to_form($array){
	$array['evotest']=array('RSVP fields', 'evotest', 'evotest','custom','');
	return $array;
}
// only for frontend
if(!is_admin()){
	// actionUser intergration
	add_action('evoau_frontform_evotest',  'evoautest_fields', 10, 6);
	//add_action('evoau_save_formfields',  'evoautest_save_values', 10, 3);
}
// Frontend showing fields and saving values  
function evoautest_fields($field, $event_id, $default_val, $EPMV, $opt2, $lang){
	$helper = new evo_helper();
	echo "<div class='row evotest'><p>";
		echo $helper->html_yesnobtn(array(
			'id'=>'evors_rsvp',
			'input'=>true,
			'label'=>evo_lang_get('evoAUL_rsvp', 'Allow visitors to RSVP to this event', $lang, $opt2),
			'var'=> 'no',
			'lang'=>$lang,
			'afterstatement'=>'evors_details'
		));
	echo "</p></div>";
	echo "<div id='evors_details' style='display:none'><div class='row evotest'><p>";
/*		$evors_max_active = ($EPMV && !empty($EPMV['evors_max_active']) && $EPMV['evors_max_active'][0]=='yes')? true: false;
		echo $helper->html_yesnobtn(array(
			'id'=>'evors_max_active',
			'input'=>true,
			'label'=>evo_lang_get('evoAUL_max_active', 'Limit maximum capacity count per RSVP', $lang, $opt2),
			'var'=> ($evors_max_active?'yes':'no'),
			'lang'=>$lang,
			'afterstatement'=>'evors_max_count_row',
		));
	$evors_max_count_row = ($EPMV['evors_max_active'][0]=='yes')?'':'none';
	echo "</p><div id='evors_max_count_row' class='evors_max_count_row yesnosub' style='display:".$evors_max_count_row."'>".evo_lang_get('evoAUL_max_count', 'Maximum count number', $lang, $opt2)."  <input type='text' id='evors_max_count' style='display:inline' name='evors_max_count' value='".$EPMV['evors_max_count'][0]."'/></div></div>";
*/
		echo $helper->html_yesnobtn(array(
			'id'=>'evors_show_whos_coming',
			'input'=>true,
			'label'=>evo_lang_get('evoAUL_show_whos_coming', 'Show who\'s coming to event', $lang, $opt2),
			'var'=> 'no',
			'lang'=>$lang,
			'afterstatement'=>'evors_show_whos_coming_row'
		));
	echo "</p></div>";

	echo "<div class='row evotest'><p>";
		echo $helper->html_yesnobtn(array(
			'id'=>'evors_capacity',
			'input'=>true,
			'label'=>evo_lang_get('evoAUL_capacity', 'Set capacity limit for RSVP', $lang, $opt2),
			'var'=> 'no',
			'lang'=>$lang,
			'afterstatement'=>'evors_capacity_row',
		));
	echo "</p><div id='evors_capacity_row' class='evors_capacity_row yesnosub' style='display:none'>".evo_lang_get('evoAUL_capacity_count', 'Total available RSVP capacity', $lang, $opt2)."  <input type='text' id='evors_capacity_count' style='display:inline' name='evors_capacity_count' value=''/></div></div>";

	echo "<div class='row evotest'><p>";
		echo $helper->html_yesnobtn(array(
			'id'=>'evors_capacity_show',
			'input'=>true,
			'label'=>evo_lang_get('evoAUL_capacity_show', 'Show available spaces count on front-end', $lang, $opt2),
			'var'=> 'no',
			'lang'=>$lang,
			'afterstatement'=>'evors_capacity_show_row'
		));
	echo "</p></div>";

	echo "<div class='row evotest'><p>";
		echo $helper->html_yesnobtn(array(
			'id'=>'evors_min_cap',
			'input'=>true,
			'label'=>evo_lang_get('evoAUL_min_cap', 'Activate event happening minimum capacity', $lang, $opt2),
			'var'=> 'no',
			'lang'=>$lang,
			'afterstatement'=>'evors_min_count_row'
		));
	echo "</p><div id='evors_min_count_row' class='evors_min_count_row yesnosub' style='display:none'>".evo_lang_get('evoAUL_min_count', 'Minimum capacity for event to happen', $lang, $opt2)."  <input type='text' id='evors_min_count' style='display:inline' name='evors_min_count' value=''/></div></div></div>";
}
function evoautest_save_values($field, $fn, $created_event_id){ //write_log($field.'hey-janero'.$created_event_id);
	if( $field =='evotest'){ //write_log('evotest-janero');
		
		// for each above fields
		foreach(array(
			'evors_rsvp','evors_capacity','evors_capacity_count','evors_capacity_show','evors_min_cap','evors_min_count'
		) as $field){
			if(!empty($_POST[$field]))
				add_post_meta($created_event_id, $field, $_POST[$field]);
		}
	}
}
add_action( 'save_post', 'evoautest_save_values_ajde_events', 1, 2 );

function evoautest_save_values_ajde_events($post_id, $post){ write_log($post->post_type.'custom-janero');
	if($post->post_type!='ajde_events')
		return;

    if( $post->post_type == "ajde_events" ) {write_log('custom-ajde_events');
        if (isset( $_POST ) ) {write_log('custom-POST');
			// for each above fields
			foreach(array(
				'evors_rsvp','evors_show_whos_coming','evors_capacity','evors_capacity_count','evors_capacity_show','evors_min_cap','evors_min_count'
			) as $field){write_log($field.'custom-'.$_POST[$field]);
				if(!empty($_POST[$field]))
					update_post_meta($post_id, $field, $_POST[$field]);
			}
        }
    }
}
// only admin fields
if(is_admin()){
	add_filter('eventonau_language_fields', 'evoautest_languages', 10, 1);
}
// language
function evoautest_languages($array){
	$newarray = array(
		array('label'=>'ActionUser RSVP Fields','type'=>'subheader'),
			array('label'=>'Allow visitors to RSVP to this event','name'=>'evoAUL_rsvp'),				
			array('label'=>'Show who\'s coming to event','name'=>'evoAUL_show_whos_coming'),				
			array('label'=>'Set capacity limit for RSVP','name'=>'evoAUL_capacity'),				
			array('label'=>'Total available RSVP capacity','name'=>'evoAUL_capacity_count'),				
			array('label'=>'Show available spaces count on front-end','name'=>'evoAUL_capacity_show'),				
			array('label'=>'Activate event happening minimum capacity','name'=>'evoAUL_min_cap'),
			array('label'=>'Minimum capacity for event to happen','name'=>'evoAUL_min_count'),
		array('type'=>'togend'),
	);
	return array_merge($array, $newarray);
}
/*
evors_show_whos_coming
evors_capacity
evors_capacity_count
evors_capacity_show

evors_max_active
evors_max_count

evors_min_cap
evors_min_count
*/
if (!function_exists('write_log')) {
    function write_log ( $log )  {
        if ( true === WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        }
    }
}
/* eof by janero 2016 05 04 */


//csutom function to tweak style of Project Maanager plugin

function picasso_admin_print_script(){
	
	echo '<script>';
	echo "jQuery('.cpm .cpm-activity-list li.cpm-row .cpm-activity-body ul li .date').css('background-image','none')";
	//echo "jQuery(.cpm .cpm-activity-list li.cpm-row .cpm-activity-body ul li .date).css('background','none')";
	echo '</script>';
	
}
add_action('in_admin_footer', 'picasso_admin_print_script');
add_action('wp_footer', 'picasso_admin_print_script');


//add_filter('bp_has_activities', 'bp_get_activity_content_body_two', 10, 3);
function bp_get_activity_content_body_two($a, $b){
	var_dump($a);
	var_dump($b);
	return $a;
}



/* ----- BodyPress Quick Activity Plugin Translation ----- */

function kleo_child_bpqa_load_textdomain() {
	$mofile		= sprintf( 'BPQA-%s.mo', get_locale() );
	
	$mofile_local	= get_stylesheet_directory() . '/languages/' . $mofile;

	return load_textdomain( 'BPQA', $mofile_local );
}

kleo_child_bpqa_load_textdomain();

/* ----- BodyPress Quick Activity Plugin Translation ----- */

?>
