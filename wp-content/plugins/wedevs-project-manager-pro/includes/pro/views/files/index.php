<div class="cpm-pro-file-container">
    <?php if ( cpm_user_can_access( $project_id, 'upload_file_doc' ) ) { ?>
        
 <!--       
        <div class="cpm-uplaod-btn-list">

       <a href="JavaScript:void(0)" data-link="uploadfiles" id="cpm-upload-pickfiles-nd" class=" cpm-doc-btn dashicons-before dashicons-upload">
                <?php _e( "Upload a file", 'cpm' ) ?>
            </a>
	
            <a href="JavaScript:void(0)" data-link="createdoc" class=" cpm-doc-btn dashicons-before dashicons-media-document">
                <?php _e( "Create a doc", 'cpm' ) ?>
            </a>
            <a href="JavaScript:void(0)" data-link="link-google" class=" cpm-doc-btn dashicons-before dashicons-googleplus">
                <?php _e( "Link to google docs", 'cpm' ) ?>
            </a>

 
            <div class="clearfix"></div>
        </div>
    -->      
     
    <?php } ?>
    <div class="cpm-pro-file-uploder cpm-box-shadow " id="createdoc">

        Create  a New Doc.
    </div>

    <div class="cpm-pro-file-uploder" id="link-google">

        Link to Google

    </div>

    <div class="cpm-pro-file-uploder" id="uploadfiles">
        <form class="cpm-pro-file-upload-form">
            <?php wp_nonce_field( 'cpm_pro_file_new' ); ?>
            <input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
            <input type="hidden" name="action" value="cpm_pro_file_new" />
            <div id="cpm-upload-container-nd">
                <div class="cpm-upload-filelist">
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="cpm-privacy"> <label>  <input type="checkbox" name="files_privacy" value="yes" /> <?php _e( 'Make files private.', 'cpm' ) ?> </label> </div>
            <input type="submit" name="submit" class="button-primary" value=" <?php _e( "Submit", 'cpm' ) ?>" />
            <button type="button" class="button-secondary cpm-close-upload">  <?php _e( "Cancel", 'cpm' ) ?> </button>
        </form>
    </div>


</div>
<div class="clearfix"></div>
<div class="cpm-uploaded-content">
    <h3> <?php _e( "Uploaded Media", 'cpm' ); ?> </h3>
    <?php
    $pro_files = new CPM_Pro_Files();
    $pro_files->uploded_media( $project_id );
    ?>
</div>

<h3> <?php _e( "Shared Media", 'cpm' ); ?> </h3>
