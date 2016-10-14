<?php
// HOOKABLE: 
	do_action( $this->WPB_PREFIX."_admin_ui_header_start" );
	
	if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == true )
			echo '<div class="updated"><p>' . __( $this->WPB_PLUGIN_NAME.' options updated.', $this->WPB_PREFIX ) . '</p></div>';
	if ( isset( $_GET['fix_pending_users'] ))
			echo '<div class="updated"><p>' . __( 'Check pending users. Should be fixed now', $this->WPB_PREFIX ) . '</p></div>';
?>
<div class="wsldiv wrap">
	<h1>
		<?php echo $this->WPB_PLUGIN_NAME; ?>
	
		<small><?php echo $this->WPB_VERSION ?></small>
	
	</h1>
	<h2 class="nav-tab-wrapper">
		<div id="ui-tabs">
		<ul class="ui-tabs-nav">
	<?php
			foreach ( $this->sections as $section_slug => $section )
			{
				echo '<li><a href="#' . $section_slug . '" class="nav-tab ">' . $section . '</a></li>';
			}	
	?>
		</ul>
		</div>
	</h2>

<div id="wsl_admin_tab_content" class="metabox-holder" style="min-width:1000px;">
<?php
	// HOOKABLE: 
	do_action( $this->WPB_PREFIX."_admin_ui_header_end" );
?>
<div id="left-content"> 