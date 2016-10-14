<?php

namespace Glcdesign\HashtagWp;

use WP_Widget;

class Widget extends WP_Widget
{

    public function __construct()
    {

        parent::__construct(
            'hashtag_wp',
            __( 'Hashtags', Plugin::TEXTDOMAIN )
        );

    }

    function widget( $args, $instance )
    {

        //output

        //getting feed
        $feed = ! empty( $instance[ 'feed' ] ) ? $instance[ 'feed' ] : 'both';

        //getting taxonomies
        $taxonomies = array();

        //if buddypress enabled
        if( get_option( 'glcdesign_hashtag_wp_buddypress_enabled', true ) )
        {
            //if show both or buddypress
            if( $feed == 'both' || $feed == 'buddypress' )
            {
                $taxonomies[] = 'hashtag_wp_bp'; //adding bp to taxonimies
            }
        }

        //if both or posts
        if( $feed == 'both' || $feed == 'posts' )
        {
            $taxonomies[] = 'hashtag_wp'; //adding wp to taxonomies
        }

        //if no taxonomies
        if( empty( $taxonomies ) )
        {
            $taxonomies[] = 'hashtag_wp'; //defaulting to hashtag wp
        }

        //getting tag cloud
        $cloud = wp_tag_cloud(  array(
            'echo'      => false,
            'taxonomy'  => $taxonomies
        ) );

        //checking if tag cloud empty
        if( empty( $cloud ) )
        {
            return; //nothing to display
        }

        //before widget
        echo $args['before_widget'];

        //title
        if( !empty( $instance[ 'title' ] ) )
        {
            $title = $instance[ 'title' ];
        }
        else
        {
            $title = __( 'Hashtags', Plugin::TEXTDOMAIN );
        }

        $title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

        echo $args['before_title'] . $title . $args['after_title'];

        echo '<div class="tagcloud">';

        echo $cloud;

        echo "</div>\n";

        //after widget
        echo $args['after_widget'];

    }

    function update( $new_instance, $old_instance )
    {

        //save

        //the current instance is the old instance
        $instance = $old_instance;

        //we update the instance
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['feed'] = strip_tags( esc_attr( $new_instance['feed'] ) );

        //we return the updated instance
        return $instance;

    }

    function form( $instance )
    {

        //form

        //default value
        $title = __( 'Hashtags', Plugin::TEXTDOMAIN );

        //if there is an instance
        if( $instance )
        {
    
            $title = esc_attr( $instance['title'] );
            $feed = ! empty( $instance[ 'feed' ] ) ?  esc_attr( $instance['feed'] ) : 'both';

        }

        //CLOSE PHP TAG
        ?>
    
        <!-- TITLE FIELD -->
        <p>
        
            <label for="<?= $this->get_field_id('title'); ?>">
                <?php _e( 'Title', Plugin::TEXTDOMAIN ); ?>
            </label>
        
            <input
                type="text"
                class="widefat"
                id="<?= $this->get_field_id('title'); ?>"
                name="<?= $this->get_field_name('title'); ?>"
                value="<?= $title; ?>"
                />
    
        </p>
    
        <!-- FEED FIELD -->
        <p>
        
            <label for="<?= $this->get_field_id('feed'); ?>">
                <?php _e( 'Feed', Plugin::TEXTDOMAIN ); ?>
            </label>
        
            <select class="widefat" id="<?= $this->get_field_id('feed'); ?>" name="<?= $this->get_field_name('feed'); ?>">
                <option value="both"<?php if( $feed == 'both' ): ?> selected="selected"<?php endif; ?>><?php _e( 'Both', Plugin::TEXTDOMAIN ); ?></option>
                <?php if( get_option( 'glcdesign_hashtag_wp_buddypress_enabled', true ) ): ?>
                    <option value="buddypress"<?php if( $feed == 'buddypress' ): ?> selected="selected"<?php endif; ?>><?php _e( 'BuddyPress', Plugin::TEXTDOMAIN ); ?></option>
                <?php endif; ?>
                <option value="posts"<?php if( $feed == 'posts' ): ?> selected="selected"<?php endif; ?>><?php _e( 'Posts and Pages', Plugin::TEXTDOMAIN ); ?></option>
            </select>
    
        </p>

        <?php
        //OPEN PHP TAG

    }

}