(function($) {

    "use strict";

    //background enabled
    wp.customize('glcdesign-hashtag-wp-background-enabled', function(value) {

        value.bind(function(to) {

            if( to )
                $( '.glcdesign-hashtag-wp').css( 'background-color', wp.customize.value( 'glcdesign-hashtag-wp-background-color').get() );
            else
                $( '.glcdesign-hashtag-wp').css( 'background', 'none' );

        });

    });

    //background color
    wp.customize('glcdesign-hashtag-wp-background-color', function(value) {

        value.bind(function(to) {

            if( wp.customize.value( 'glcdesign-hashtag-wp-background-enabled').get() )
                $( '.glcdesign-hashtag-wp').css( 'background-color', to );
            else
                $( '.glcdesign-hashtag-wp').css( 'background', 'none' );

        });

    });
    
    //border enabled
    wp.customize('glcdesign-hashtag-wp-border-enabled', function(value) {

        value.bind(function(to) {

            if( to )
                $( '.glcdesign-hashtag-wp').css( 'border-color', wp.customize.value( 'glcdesign-hashtag-wp-border-color').get() );
            else
                $( '.glcdesign-hashtag-wp').css( 'border-color', 'transparent' );

        });

    });

    //border color
    wp.customize('glcdesign-hashtag-wp-border-color', function(value) {

        value.bind(function(to) {

            if( wp.customize.value( 'glcdesign-hashtag-wp-border-enabled').get() )
                $( '.glcdesign-hashtag-wp').css( 'border-color', to );
            else
                $( '.glcdesign-hashtag-wp').css( 'border-color', 'transparent' );


        });

   });


    //text enabled
    wp.customize('glcdesign-hashtag-wp-text-color-enabled', function(value) {

        value.bind(function(to) {

            if( to )
                $( '.glcdesign-hashtag-wp').css( 'color', wp.customize.value( 'glcdesign-hashtag-wp-text-color').get() );
            else
                $( '.glcdesign-hashtag-wp').css( 'color', '' );

        });

    });

    //text color
    wp.customize('glcdesign-hashtag-wp-text-color', function(value) {

        value.bind(function(to) {

            if( wp.customize.value( 'glcdesign-hashtag-wp-text-color-enabled').get() )
                $( '.glcdesign-hashtag-wp').css( 'color', to );
            else
                $( '.glcdesign-hashtag-wp').css( 'color', '' );


        });

    });
    
    //hash color
    wp.customize('glcdesign-hashtag-wp-hash-color', function(value) {
    
        value.bind(function(to) {
    
            $( '.glcdesign-hashtag-wp i').css( 'color', to );
    
        });
    
    });

    //font size
    wp.customize('glcdesign-hashtag-wp-font-size', function(value) {

        value.bind(function(to) {

            $( '.glcdesign-hashtag-wp').css( 'font-size', to + 'em' );

        });

    });

    //hash spacing
    wp.customize('glcdesign-hashtag-wp-hash-spacing', function(value) {

        value.bind(function(to) {

            $( '.glcdesign-hashtag-wp i').css( 'margin-right', to + 'px' );

        });

    });

    //hash font size
    wp.customize('glcdesign-hashtag-wp-hash-font-size', function(value) {

        value.bind(function(to) {

            $( '.glcdesign-hashtag-wp i').css( 'font-size', to + 'em' );

        });

    });

    //padding
    wp.customize('glcdesign-hashtag-wp-padding', function(value) {

        value.bind(function(to) {

            $( '.glcdesign-hashtag-wp').css( 'padding', to + 'px' );

        });

    });

})(jQuery);