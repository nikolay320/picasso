<?php
/**
 * ReduxFramework Sample Config File
 * For full documentation, please visit: http://docs.reduxframework.com/
 */

if ( ! class_exists( 'Redux' ) ) {
    return;
}


// This is your option name where all the Redux data is stored.
$opt_name = "picasso_ideas";

// This line is only for altering the demo. Can be easily removed.
$opt_name = apply_filters( 'picasso_ideas/opt_name', $opt_name );

/**
 * ---> SET ARGUMENTS
 * All the possible arguments for Redux.
 * For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments
 * */

$theme = wp_get_theme(); // For use with some settings. Not necessary.

$args = array(
    // TYPICAL -> Change these values as you need/desire
    'opt_name'             => $opt_name,
    // This is where your data is stored in the database and also becomes your global variable name.
    'display_name'         => __('Idea Settings', 'picasso-ideas'),
    // Name that appears at the top of your panel
    'display_version'      => '1.0.2',
    // Version that appears at the top of your panel
    'menu_type'            => 'submenu',
    //Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
    'allow_sub_menu'       => true,
    // Show the sections below the admin menu item or not
    'menu_title'           => __( 'Settings', 'picasso-ideas' ),
    'page_title'           => __( 'Idea Settings', 'picasso-ideas' ),
    // You will need to generate a Google API key to use this feature.
    // Please visit: https://developers.google.com/fonts/docs/developer_api#Auth
    'google_api_key'       => '',
    // Set it you want google fonts to update weekly. A google_api_key value is required.
    'google_update_weekly' => false,
    // Must be defined to add google fonts to the typography module
    'async_typography'     => true,
    // Use a asynchronous font on the front end or font string
    //'disable_google_fonts_link' => true,                    // Disable this in case you want to create your own google fonts loader
    'admin_bar'            => true,
    // Show the panel pages on the admin bar
    'admin_bar_icon'       => 'dashicons-portfolio',
    // Choose an icon for the admin bar menu
    'admin_bar_priority'   => 50,
    // Choose an priority for the admin bar menu
    'global_variable'      => '',
    // Set a different name for your global variable other than the opt_name
    'dev_mode'             => false,
    // Show the time the page took to load, etc
    'update_notice'        => true,
    // If dev_mode is enabled, will notify developer of updated versions available in the GitHub Repo
    'customizer'           => true,
    // Enable basic customizer support
    //'open_expanded'     => true,                    // Allow you to start the panel in an expanded way initially.
    //'disable_save_warn' => true,                    // Disable the save warning when a user changes a field

    // OPTIONAL -> Give you extra features
    'page_priority'        => null,
    // Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
    'page_parent'          => 'edit.php?post_type=idea',
    // For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
    'page_permissions'     => 'manage_options',
    // Permissions needed to access the options panel.
    'menu_icon'            => '',
    // Specify a custom URL to an icon
    'last_tab'             => '',
    // Force your panel to always open to a specific tab (by id)
    'page_icon'            => 'icon-themes',
    // Icon displayed in the admin panel next to your menu_title
    'page_slug'            => '',
    // Page slug used to denote the panel, will be based off page title then menu title then opt_name if not provided
    'save_defaults'        => true,
    // On load save the defaults to DB before user clicks save or not
    'default_show'         => false,
    // If true, shows the default value next to each field that is not the default value.
    'default_mark'         => '',
    // What to print by the field's title if the value shown is default. Suggested: *
    'show_import_export'   => true,
    // Shows the Import/Export panel when not used as a field.

    // CAREFUL -> These options are for advanced use only
    'transient_time'       => 60 * MINUTE_IN_SECONDS,
    'output'               => true,
    // Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
    'output_tag'           => true,
    // Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
    // 'footer_credit'     => '',                   // Disable the footer credit of Redux. Please leave if you can help it.

    // FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
    'database'             => '',
    // possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
    'use_cdn'              => true,
    // If you prefer not to use the CDN for Select2, Ace Editor, and others, you may download the Redux Vendor Support plugin yourself and run locally or embed it in your code.

    // HINTS
    'hints'                => array(
        'icon'          => 'el el-question-sign',
        'icon_position' => 'right',
        'icon_color'    => 'lightgray',
        'icon_size'     => 'normal',
        'tip_style'     => array(
            'color'   => 'red',
            'shadow'  => true,
            'rounded' => false,
            'style'   => '',
        ),
        'tip_position'  => array(
            'my' => 'top left',
            'at' => 'bottom right',
        ),
        'tip_effect'    => array(
            'show' => array(
                'effect'   => 'slide',
                'duration' => '500',
                'event'    => 'mouseover',
            ),
            'hide' => array(
                'effect'   => 'slide',
                'duration' => '500',
                'event'    => 'click mouseleave',
            ),
        ),
    )
);

Redux::setArgs( $opt_name, $args );


// -> START User Roles fields
Redux::setSection( $opt_name, array(
    'title'  => __( 'User Roles', 'picasso-ideas' ),
    'id'     => 'user-roles',
    'icon'   => 'el el-home',
    'fields' => array(
        array(
            'id'       => 'modifier_roles',
            'type'     => 'select',
            'multi'    => true,
            'data'     => 'roles',
            'title'    => __( 'Idea Modifier Roles', 'picasso-ideas' ),
            'subtitle' => __( 'Who can assign experts and deadline for ideas?.', 'picasso-ideas' ),
        ),
        array(
            'id'       => 'expert_roles',
            'type'     => 'select',
            'multi'    => true,
            'data'     => 'roles',
            'title'    => __( 'Idea Expert Roles', 'picasso-ideas' ),
            'subtitle' => __( 'Who can give review for ideas?.', 'picasso-ideas' ),
        ),
    )
) );


// -> START Idea Settings
Redux::setSection( $opt_name, array(
    'title'  => __( 'Settings', 'picasso-ideas' ),
    'id'     => 'idea-settings',
    'icon'   => 'el el-cog-alt',
    'fields' => array(
        array(
            'id'       => 'idea_create_page',
            'type'     => 'select',
            'data'     => 'pages',
            'title'    => __( 'Create Idea Page', 'picasso-ideas' ),
            'desc'     => __( 'Choose the page that will be used to create idea.', 'picasso-ideas' ),
        ),
        array(
            'id'       => 'idea_edit_page',
            'type'     => 'select',
            'data'     => 'pages',
            'title'    => __( 'Edit Idea Page', 'picasso-ideas' ),
            'desc'     => __( 'Choose the page that will be used to edit idea.', 'picasso-ideas' ),
        ),
        array(
            'id'       => 'modifier_can_edit_reviews',
            'type'     => 'switch',
            'title'    => __( 'Modify Reviews', 'picasso-ideas' ),
            'subtitle' => __( 'Do the modifiers can edit reviews?', 'picasso-ideas' ),
            'default'  => 0,
            'on'       => 'Yes',
            'off'      => 'No',
        ),
        array(
            'id'       => 'modifier_can_post_user_review',
            'type'     => 'switch',
            'title'    => __( 'Modifier User Reviews', 'picasso-ideas' ),
            'subtitle' => __( 'Do the modifiers can post user reviews?', 'picasso-ideas' ),
            'default'  => 0,
            'on'       => 'Yes',
            'off'      => 'No',
        ),
        array(
            'id'       => 'receive_reminder',
            'type'     => 'switch',
            'title'    => __( 'Receive Reminder', 'picasso-ideas' ),
            'subtitle' => __( 'Do the experts will receive reminder before ending the deadline?', 'picasso-ideas' ),
            'default'  => 0,
            'on'       => 'Yes',
            'off'      => 'No',
        ),
        array(
            'id'       => 'reminder_buffer',
            'type'     => 'text',
            'title'    => __( 'Reminder Buffer', 'picasso-ideas' ),
            'subtitle' => __( 'How many days before do your experts will receive alert?', 'picasso-ideas' ),
            'default'  => 3,
            'validate' => 'numeric',
            'required' => array( 'receive_reminder', '=', 1 )
        ),
    )
) );


// -> START Idea Review Criteria
Redux::setSection( $opt_name, array(
    'title'  => __( 'Review Criteria', 'picasso-ideas' ),
    'id'     => 'idea-review-criteria',
    'icon'   => 'el el-leaf',
    'fields' => array(
        array(
            'id'       => 'review_criteria',
            'type'     => 'multi_text',
            'title'    => __( 'Criteria', 'picasso-ideas' ),
            'subtitle' => __( 'You can assign as many criteria as you want.', 'picasso-ideas' ),
        ),
    )
) );


// If Redux is running as a plugin, this will remove the demo notice and links
add_action( 'redux/loaded', 'remove_demo' );

/**
 * Removes the demo link and the notice of integrated demo from the redux-framework plugin
 */
if ( ! function_exists( 'remove_demo' ) ) {
    function remove_demo() {
        // Used to hide the demo mode link from the plugin page. Only used when Redux is a plugin.
        if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
            remove_filter( 'plugin_row_meta', array(
                ReduxFrameworkPlugin::instance(),
                'plugin_metalinks'
            ), null, 2 );

            // Used to hide the activation notice informing users of the demo panel. Only used when Redux is a plugin.
            remove_action( 'admin_notices', array( ReduxFrameworkPlugin::instance(), 'admin_notices' ) );
        }
    }
}