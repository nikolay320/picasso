;
( function( $ ) {
    var profiles = {
        init: function() {
            $( document ).on( 'click', '.cpm-doc-btn', this.startNewDoc );
            $( document ).on( 'click', '.cpm-close-upload', this.hideFileUploader );
            $( document ).on( 'submit', '.cpm-pro-file-upload-form', this.addNewFiles );
            $( document ).on( 'click', '.cpm-pro-delete-file a', this.deleteUploadsFile );
        },
        startNewDoc: function() {
            var div = "#" + $( this ).attr( 'data-link' );

            if( $( div ).css( 'display' ) == 'none' ) {
                profiles.hideFileUploader();
            }
            $( div ).show( 100 );
        },
        hideFileUploader: function() {
            $( ".cpm-pro-file-uploder" ).hide( 100 );
        },
        addNewFiles: function( e ) {
            e.preventDefault();
            var form = $( this ), data = form.serialize();

            $.post( CPM_Vars.ajaxurl, data, function( res ) {
                res = $.parseJSON( res );
                if( res.success ) {
                    window.location.reload();
                }
            } );

        },
        deleteUploadsFile: function( e ) {
            if( confirm( CPM_Vars.message.delete_file ) ) {
                var file_id = $( this ).attr( 'data-id' );
                var project_id = $( this ).attr( 'data-pid' );
                var spiner = $( "#pro-" + file_id ).find( ".cpm-loading" );
                spiner.show();
                var data = {
                    file_id: file_id,
                    project_id: project_id,
                    action: 'cpm_delete_uploded_file',
                    '_wpnonce': CPM_Vars.nonce
                };

                $.post( CPM_Vars.ajaxurl, data, function( response ) {
                    response = JSON.parse( response );
                    if( response.success ) {
                        $( "#pro-" + file_id ).remove();
                    }
                } );
            }
        }


    } // End JavaScript Class

    profiles.init();

} )( jQuery );