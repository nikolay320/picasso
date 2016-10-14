<?php	
/*
 *	The template for displaying event categoroes 
 *
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


	$tax_name = $eventon->frontend->get_localized_event_tax_names_by_slug($tax);

	do_action('eventon_before_main_content');
?>

<div id="content">
	<div class="hentry">
		<header class="entry-header ">
			<h1 class="entry-title"><?php echo $tax_name.': '.single_cat_title( '', false ); ?></h1>

			<?php if ( category_description() ) : // Show an optional category description ?>
			<div class="entry-meta"><?php echo category_description(); ?></div>
			<?php endif; ?>
		</header><!-- .archive-header -->
		
		<div class='eventon entry-content'>
		<?php 
			echo do_shortcode('[add_eventon_list number_of_months="4" '.$tax.'='.$term->term_id.']');
		?>
		</div>
	</div>
</div>

<?php	do_action('eventon_after_main_content'); ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>