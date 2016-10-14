<?php

/**
 *  Pro File Upload and create Document
 *
 * @since 1.4.3
 */
class CPM_Pro_Files {

    protected static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    function __construct() {

        add_action( 'cpm_show_file_before', array( $this, 'cpm_show_file_propart' ) );
        add_action( 'cpm_admin_scripts', array( $this, 'files_scripts' ) );

        add_filter( 'cpm_message', array( $this, 'show_message' ) );
        // File Uplaod
        add_action( 'wp_ajax_cpm_pro_file_new', array( $this, 'file_new_uplaod' ) );
        add_action( 'wp_ajax_cpm_delete_uploded_file', array( $this, 'delete_uploded_file' ) );
    }

    function show_message( $message ) {
        $message['delete_file'] = __( 'Are you sure to delete this file?', 'cpm' );
        return $message;
    }

    function cpm_show_file_propart( $project_id ) {
        require_once CPM_PRO_PATH . '/views/files/index.php';
    }

    function files_scripts() {
        wp_enqueue_script( 'cpm_pro_files', plugins_url( '../assets/js/files.js', __FILE__ ) );
        wp_enqueue_style( 'cpm_pro_files', plugins_url( '../assets/css/files.css', __FILE__ ) );
    }

    function uploded_media( $project_id ) {
        require_once CPM_PRO_PATH . '/views/files/filelist.php';
    }

    function file_new_uplaod() {
        check_ajax_referer( 'cpm_pro_file_new' );
        $posted       = $_POST;
        $files        = array();
        $project_id   = isset( $posted['project_id'] ) ? intval( $posted['project_id'] ) : 0;
        $file_privacy = isset( $posted['files_privacy'] ) ? $posted['files_privacy'] : 'no';


        if ( isset( $posted['cpm_attachment'] ) ) {
            $files = $posted['cpm_attachment'];
            foreach ( $files as $file_id ) {
                wp_update_post( array(
                    'ID'          => $file_id,
                    'post_parent' => $project_id
                ) );
                update_post_meta( $file_id, '_project_uploaded', $project_id );
                update_post_meta( $file_id, '_doc_type', 'uploded' );
                update_post_meta( $file_id, '_files_privacy', $file_privacy );
            }

            echo json_encode( array(
                'success' => true
            ) );
        } else {
            echo json_encode( array(
                'success' => false
            ) );
        }
        exit();
    }

    function delete_uploded_file() {
        check_ajax_referer( 'cpm_nonce' );
        $posted              = $_POST;
        $force               = TRUE;
        $file_id             = isset( $posted['file_id'] ) ? intval( $posted['file_id'] ) : 0;
        $project_id          = isset( $posted['project_id'] ) ? intval( $posted['project_id'] ) : 0;
        $response['success'] = FALSE;

        if ( wp_delete_attachment( $file_id, $force ) ) {
            $response['success'] = TRUE;
        }

        do_action( 'cpm_delete_attachment', $file_id, $force );
        echo json_encode( $response );
        exit();
    }

}

//new CPM_Pro_Files();

function cpmprofile() {
    return CPM_Pro_Files::instance();
}

//cpm instance.
$profiles = cpmprofile();
