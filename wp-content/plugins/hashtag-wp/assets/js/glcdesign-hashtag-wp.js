//building the plugin object
var glcdesign_hashtag_wp = typeof glcdesign_hashtag_wp !== 'undefined' ? glcdesign_hashtag_wp : {};

jQuery( document ).ready( function( $ ) {

    "use strict";

    //creating a regexp
    glcdesign_hashtag_wp.regex = new RegExp( glcdesign_hashtag_wp.regexRule, 'gi' );

    //building the functions
    glcdesign_hashtag_wp.functions = {

        //generating links
        generateLinks: function() {

            //node selector
            if( glcdesign_hashtag_wp.commentsEnabled )
            {
                var selector = '.glcdesign-hashtag-wp-node, .glcdesign-hashtag-wp-comment-node';
            }
            else
            {
                var selector = '.glcdesign-hashtag-wp-node';
            }

            //getting node
            var nodes = $( selector );

            //foreach nodes (some themes are displaying content instead of excerpt)
            nodes.each( function( index, node ) {

                //parsing hashtags and generating links
                //-----------------------------------------------------------------------------------
                //INTRODUCED VERSION 1.0.3
                //:not(:has(*)) means any node that has no children.
                //when you replace HTML with jQuery, all events are not rebinded after.
                //by replacing only the text nodes, themes that bind the content won't be affected.
                //-----------------------------------------------------------------------------------
                $( node ).parent().find( ':not(:has(*:not(br)))' ).html( function( i, n ) {

                    return n.replace(
                        glcdesign_hashtag_wp.regex,
                        glcdesign_hashtag_wp.functions.getLinkMarkup
                    );

                } );

            } );

        },
        //link markup
        getLinkMarkup: function( link, hashtag ) {

            //if the hashtag is defined
            if( hashtag )
            {

                //removing hash
                hashtag = hashtag.replace( '#', '' );

                //checking existence of tag
                var exists = typeof glcdesign_hashtag_wp.existingTags !== 'undefined' &&
                                glcdesign_hashtag_wp.existingTags.indexOf( hashtag ) != -1;

                //generating hashtag
                if( exists )
                {

                    //the hashtag exists, linking to it
                    var html =  '<a href="' + glcdesign_hashtag_wp.tagUrl + '/' + hashtag + '" class="glcdesign-hashtag-wp">';
                    html     +=     '<i>#</i>';
                    html     +=     hashtag;
                    html     += '</a>';

                }
                else
                {

                    //the hashtag doesn't exist, so no link
                    var html =  '<a class="glcdesign-hashtag-wp">';
                    html     +=     '<i>#</i>';
                    html     +=     hashtag;
                    html     += '</a>';

                }



                //returning html
                return html;

            }
            else
            {
                //else, this is a link
                return link; //returning the link

            }



        },
        //bootstrap
        bootstrap: function() {

            //calling a bunch of functions
            glcdesign_hashtag_wp.functions.generateLinks();

        }

    };

    //launching the bootstrap
    glcdesign_hashtag_wp.functions.bootstrap();

} );