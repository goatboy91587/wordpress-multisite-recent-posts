<?php
/*
Plugin Name: Multisite Recent Posts Widget
Plugin URI: https://brettwidmann.com
Description: Displays recent posts from a selected site in a WordPress multisite network.
Version: 1.0
Author: Brett Widmann
Author URI: https://brettwidmann.com
License: GPL2
*/

class Custom_Recent_Posts_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'custom_recent_posts_widget',
            __( 'Custom Recent Posts', 'text_domain' ),
            [
                'classname'   => 'custom_recent_posts_widget',
                'description' => __( 'Displays recent posts from a specific site in a WordPress multisite network.', 'text_domain' ),
            ]
        );
    }

    public function widget( $args, $instance ) {
        $title   = $instance['title'] ?? '';
        $number  = absint( $instance['number'] ) ?? 5;
        $site_id = absint( $instance['site_id'] ) ?? get_current_blog_id();

        echo wp_kses_post( $args['before_widget'] );

        if ( $title ) {
            echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
        }

        switch_to_blog( $site_id );

        $query_args = [
            'post_type'      => 'post',
            'posts_per_page' => $number,
        ];

        $recent_posts = new WP_Query( $query_args );

        if ( $recent_posts->have_posts() ) {
            echo '<ul>';
            while ( $recent_posts->have_posts() ) {
                $recent_posts->the_post();
                printf( '<li><a href="%1$s">%2$s</a></li>', esc_url( get_permalink() ), esc_html( get_the_title() ) );
            }
            echo '</ul>';
        }

        wp_reset_postdata();
        restore_current_blog();

        echo wp_kses_post( $args['after_widget'] );
    }

    public function form( $instance ) {
        $title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
        $number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
        $site_id = isset( $instance['site_id'] ) ? absint( $instance['site_id'] ) : get_current_blog_id();
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'text_domain' ); ?></label>
            <input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo $title; ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Number of posts to show:', 'text_domain' ); ?></label>
            <input class="tiny-text" type="number" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" value="<?php echo $number; ?>">
        </p>
        <p>
        <label for="<?php echo esc_attr( $this->get_field_id( 'site_id' ) ); ?>"><?php esc_html_e( 'Site ID:', 'text_domain' ); ?></label>
        <?php $sites = get_sites( [ 'fields' => [ 'blog_id', 'blogname' ] ] ); ?>
        <?php if ( ! empty( $sites ) ) : ?>
            <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'site_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'site_id' ) ); ?>">
                <?php foreach ( $sites as $site ) : ?>
                    <option value="<?php echo esc_attr( $site->blog_id ); ?>" <?php selected( $site_id, $site->blog_id ); ?>><?php echo esc_html( $site->blogname ); ?></option>
                <?php endforeach; ?>
            </select>
        <?php else : ?>
            <p><?php esc_html_e( 'No sites found.', 'text_domain' ); ?></p>
        <?php endif; ?>
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        $instance['title'] = sanitize_text_field( $new_instance['title'] ?? '' );
        $instance['number'] = absint( $new_instance['number'] ?? 5 );
        $instance['site_id'] = absint( $new_instance['site_id'] ?? get_current_blog_id() );

        return $instance;
    }
}

// Register the widget
add_action( 'widgets_init', function(){
    register_widget( 'Custom_Recent_Posts_Widget' );
});

// Deactivate plugin
function mrpw_deactivate() {
    unregister_widget( 'Custom_Recent_Posts_Widget' );
    delete_option( 'widget_custom_recent_posts_widget' );
}
register_deactivation_hook( __FILE__, 'mrpw_deactivate' );

// Delete plugin
function mrpw_delete() {
    unregister_widget( 'Custom_Recent_Posts_Widget' );
    delete_option( 'widget_custom_recent_posts_widget' );
}
register_uninstall_hook( __FILE__, 'mrpw_delete' );