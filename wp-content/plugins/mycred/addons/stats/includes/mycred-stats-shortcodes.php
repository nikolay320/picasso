<?php
if ( ! defined( 'myCRED_STATS_VERSION' ) ) exit;

/**
 * Shortcode: myCRED Statistics
 * @since 1.6.8
 * @version 1.0
 */
if ( ! function_exists( 'mycred_statistics_shortcode_render' ) ) :
	function mycred_statistics_shortcode_render( $atts ) {

		extract( shortcode_atts( array(
			'id' => 'overallcirculation'
		), $atts ) );

		global $mycred_load_stats;

		$mycred_load_stats = true;

		$args = array( 'ctypes' => 'mycred_deed' );

		ob_start();

		if ( class_exists( 'myCRED_Stats_Widget_Circulation' ) ) {

			$w = new myCRED_Stats_Widget_Circulation( $id, $args );
			
			echo '<div class="mycred-stat-widget">';

			$w->front_widget();

			echo '</div>';

		}

		$content = ob_get_contents();
		ob_end_clean();

		return $content;

	}
endif;

?>