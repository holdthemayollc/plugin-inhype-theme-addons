<?php
/**
 * Theme Widgets
 */

if(!function_exists('inhype_widgets_register')):
function inhype_widgets_register() {

    // Custom widgets
    if(function_exists('inhype_get_wpquery_args')) {
        register_widget('InHype_Widget_List_Posts');
        register_widget('InHype_Widget_Posts_Slider');
    }

    if(function_exists('inhype_social_display')) {
        register_widget('InHype_Widget_Social_Icons');
    }

    register_widget('InHype_Widget_Recent_Comments');
    register_widget('InHype_Widget_Content');
    register_widget('InHype_Widget_Categories');

}
endif;
add_action('widgets_init', 'inhype_widgets_register');

/* Custom widgets */

/**
 * List_Posts widget class
 */
class InHype_Widget_List_Posts extends WP_Widget {

    public function __construct() {
        $widget_ops = array('classname' => 'widget_inhype_list_entries', 'description' => esc_html__( "Your site&#8217;s Posts List by criteria with thumbnails.", 'inhype') );
        parent::__construct('inhype-list-posts', esc_html__('InHype List Posts', 'inhype'), $widget_ops);
        $this->alt_option_name = 'widget_inhype_list_entries';
    }

    public function widget($args, $instance) {
        $cache = array();
        if ( ! $this->is_preview() ) {
            $cache = wp_cache_get( 'widget_inhype_list_posts', 'widget' );
        }

        if ( ! is_array( $cache ) ) {
            $cache = array();
        }

        if ( ! isset( $args['widget_id'] ) ) {
            $args['widget_id'] = $this->id;
        }

        if ( isset( $cache[ $args['widget_id'] ] ) ) {
            echo wp_kses_post($cache[ $args['widget_id'] ]);
            return;
        }

        ob_start();

        $title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';

        /** This filter is documented in wp-includes/default-widgets.php */
        $title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

        $number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
        if ( ! $number )
            $number = 5;

        $catid = ( ! empty( $instance['catid'] ) ) ? ( $instance['catid'] ) : '';

        $posts_type = strip_tags($instance['posts_type']);

        $post_template = strip_tags($instance['post_template']);

        $post_template = explode('/', $post_template);

        /**
         * Filter the arguments for the List Posts widget.
         *
         * @since 3.4.0
         *
         * @see WP_Query::get_posts()
         *
         * @param array $args An array of arguments used to retrieve the recent posts.
         */

        $settings['block_posts_limit'] = $number;
        $settings['block_posts_type'] = $posts_type;
        $settings['block_categories'] = $catid;

        $wpquery_args = inhype_get_wpquery_args($settings);

        $r = new WP_Query( apply_filters( 'widget_inhype_posts_args', $wpquery_args ) );

        if ($r->have_posts()) :
?>
        <?php echo wp_kses_post($args['before_widget']); ?>
        <?php if ( $title ) {
            echo wp_kses_post($args['before_title'] . $title . $args['after_title']);
        } ?>
        <ul class="template-<?php echo esc_attr($post_template[1]); ?>-inside">

        <?php while ( $r->have_posts() ) : $r->the_post(); ?>
        <?php if($post_template[0] == '2col'): ?>
        <li class="template-<?php echo esc_attr($post_template[1]); ?>">
            <?php get_template_part( 'inc/templates/post/content', 'grid-short' ); ?>
        </li>
        <?php else: ?>
        <li class="template-<?php echo esc_attr($post_template[1]); ?>">
            <?php get_template_part( 'inc/templates/'.$post_template[0].'/content', $post_template[1] ); ?>
        </li>
        <?php endif; ?>
        <?php endwhile; ?>

        </ul>
        <?php echo wp_kses_post($args['after_widget']); ?>
<?php
        // Reset the global $the_post as this query will have stomped on it
        wp_reset_postdata();

        endif;

        if ( ! $this->is_preview() ) {
            $cache[ $args['widget_id'] ] = ob_get_flush();
            wp_cache_set( 'widget_inhype_list_posts', $cache, 'widget' );
        } else {
            ob_end_flush();
        }
    }

    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['number'] = (int) $new_instance['number'];
        $instance['catid'] = $new_instance['catid'];
        $instance['posts_type'] = strip_tags($new_instance['posts_type']);
        $instance['post_template'] = strip_tags($new_instance['post_template']);
        $alloptions = wp_cache_get( 'alloptions', 'options' );
        if ( isset($alloptions['widget_inhype_list_entries']) )
            delete_option('widget_inhype_list_entries');

        return $instance;
    }

    public function form( $instance ) {
        $title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
        $number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
        $catid     = isset( $instance['catid'] ) ? ( $instance['catid'] ) : '';
        $posts_type     = isset( $instance['posts_type'] ) ? ( $instance['posts_type'] ) : 'latest';
        $post_template     = isset( $instance['post_template'] ) ? ( $instance['post_template'] ) : 'latest';

?>
        <p><label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php esc_html_e( 'Title:', 'inhype' ); ?></label>
        <input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

        <p><label for="<?php echo esc_attr($this->get_field_id( 'number' )); ?>"><?php esc_html_e( 'Number of posts to show:', 'inhype' ); ?></label>
        <input id="<?php echo esc_attr($this->get_field_id( 'number' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'number' )); ?>" type="text" value="<?php echo esc_attr($number); ?>" size="3" /></p>

        <p><label for="<?php echo esc_attr($this->get_field_id('post_template')); ?>"><?php esc_html_e('Post template:', 'inhype'); ?></label>
        <select id="<?php echo esc_attr($this->get_field_id('post_template')); ?>" name="<?php echo esc_attr($this->get_field_name('post_template')); ?>">

            <option value="post/grid-short"<?php if($post_template == 'post/grid-short') { echo ' selected';} ?>><?php esc_html_e('Grid', 'inhype'); ?></option>
            <option value="post/text"<?php if($post_template == 'post/text') { echo ' selected';} ?>><?php esc_html_e('Text', 'inhype'); ?></option>
            <option value="post/shortline"<?php if($post_template == 'post/shortline') { echo ' selected';} ?>><?php esc_html_e('Shortline', 'inhype'); ?></option>
            <option value="post/overlay-short"<?php if($post_template == 'post/overlay-short') { echo ' selected';} ?>><?php esc_html_e('Overlay', 'inhype'); ?></option>
            <option value="2col/2col"<?php if($post_template == '2col/2col') { echo ' selected';} ?>><?php esc_html_e('2 columns', 'inhype'); ?></option>

        </select>

        <p><label for="<?php echo esc_attr($this->get_field_id('posts_type')); ?>"><?php esc_html_e('Posts type:', 'inhype'); ?></label>
        <select id="<?php echo esc_attr($this->get_field_id('posts_type')); ?>" name="<?php echo esc_attr($this->get_field_name('posts_type')); ?>">

            <option value="latest"<?php if($posts_type == 'latest') { echo ' selected';} ?>><?php esc_html_e('Latest', 'inhype'); ?></option>
            <option value="featured"<?php if($posts_type == 'featured') { echo ' selected';} ?>><?php esc_html_e('Featured', 'inhype'); ?></option>
            <option value="editorspicks"<?php if($posts_type == 'editorspicks') { echo ' selected';} ?>><?php esc_html_e('Editor\'s Picks', 'inhype'); ?></option>
            <option value="promoted"<?php if($posts_type == 'promoted') { echo ' selected';} ?>><?php esc_html_e('Promoted', 'inhype'); ?></option>
            <option value="popular"<?php if($posts_type == 'popular') { echo ' selected';} ?>><?php esc_html_e('Popular', 'inhype'); ?></option>
            <option value="liked"<?php if($posts_type == 'liked') { echo ' selected';} ?>><?php esc_html_e('Most liked', 'inhype'); ?></option>
            <option value="trending"<?php if($posts_type == 'trending') { echo ' selected';} ?>><?php esc_html_e('Trending', 'inhype'); ?></option>
            <option value="random"<?php if($posts_type == 'random') { echo ' selected';} ?>><?php esc_html_e('Random', 'inhype'); ?></option>

        </select>

        <p><label for="<?php echo esc_attr($this->get_field_id( 'catid' )); ?>"><?php esc_html_e( 'Category ID:', 'inhype' ); ?></label>
        <input id="<?php echo esc_attr($this->get_field_id( 'catid' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'catid' )); ?>" type="text" value="<?php echo esc_attr($catid); ?>" size="3" /> <span>(show posts from this category only)</span></p>

<?php
    }
}

/**
 * Posts_Slider widget class
 */
class InHype_Widget_Posts_Slider extends WP_Widget {

    public function __construct() {
        $widget_ops = array('classname' => 'widget_inhype_posts_slider', 'description' => esc_html__( "Slider with posts details and different settings.", 'inhype') );
        parent::__construct('inhype-posts-slider', esc_html__('InHype Posts Slider', 'inhype'), $widget_ops);
        $this->alt_option_name = 'widget_inhype_posts_slider';
    }

    public function widget($args, $instance) {
        $cache = array();
        if ( ! $this->is_preview() ) {
            $cache = wp_cache_get( 'widget_inhype_posts_slider', 'widget' );
        }

        if ( ! is_array( $cache ) ) {
            $cache = array();
        }

        if ( ! isset( $args['widget_id'] ) ) {
            $args['widget_id'] = $this->id;
        }

        if ( isset( $cache[ $args['widget_id'] ] ) ) {
            echo wp_kses_post($cache[ $args['widget_id'] ]);
            return;
        }

        ob_start();

        $title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';

        /** This filter is documented in wp-includes/default-widgets.php */
        $title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

        $number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;

        $catid = ( ! empty( $instance['catid'] ) ) ? ( $instance['catid'] ) : '';

        $autoplay = isset( $instance['autoplay'] ) ? $instance['autoplay'] : false;

        $posts_type = strip_tags($instance['posts_type']);

        $post_template = strip_tags($instance['post_template']);

        $post_template = explode('/', $post_template);

        if ( ! $number )
            $number = 5;

        /**
         * Filter the arguments for the Recent Posts widget.
         *
         * @since 3.4.0
         *
         * @see WP_Query::get_posts()
         *
         * @param array $args An array of arguments used to retrieve the recent posts.
         */

        $settings['block_posts_limit'] = $number;
        $settings['block_posts_type'] = $posts_type;
        $settings['block_categories'] = $catid;

        $wpquery_args = inhype_get_wpquery_args($settings);

        $r = new WP_Query( apply_filters( 'widget_inhype_posts_args', $wpquery_args ) );

        if ($r->have_posts()) :
?>
        <?php echo wp_kses_post($args['before_widget']); ?>
        <?php if (( $title )&&($title !== '')) {
            echo wp_kses_post($args['before_title'] . $title . $args['after_title']);
        }

        $rand_id = rand(10000,10000000);

        ?>
        <div class="widget-post-slider-wrapper owl-carousel widget-post-slider-wrapper-<?php echo esc_attr($rand_id);?> template-<?php echo esc_attr($post_template[1]); ?>">

        <?php while ( $r->have_posts() ) : $r->the_post(); ?>

            <?php get_template_part( 'inc/templates/'.$post_template[0].'/content', $post_template[1] ); ?>

        <?php endwhile; ?>
        </div>
        <?php
            if($autoplay) {
                $autoplay_bool = 'true';
            } else {
                $autoplay_bool = 'false';
            }

            wp_add_inline_script( 'inhype-script', '(function($){
                $(document).ready(function() {
                    "use strict";

                    var owl = $(".sidebar .widget.widget_inhype_posts_slider .widget-post-slider-wrapper.widget-post-slider-wrapper-'.esc_attr($rand_id).'");

                    owl.owlCarousel({
                        loop: true,
                        items:1,
                        autoplay:'.esc_attr($autoplay_bool).',
                        autowidth: false,
                        autoplayTimeout:4000,
                        autoplaySpeed: 1000,
                        navSpeed: 1000,
                        dots: false,
                        responsive: {
                            1199:{
                                items:1
                            },
                            979:{
                                items:1
                            },
                            768:{
                                items:1
                            },
                            479:{
                                items:1
                            },
                            0:{
                                items:1
                            }
                        }
                    });

                });})(jQuery);');

        ?>
        <?php echo wp_kses_post($args['after_widget']); ?>
<?php
        // Reset the global $the_post as this query will have stomped on it
        wp_reset_postdata();

        endif;

        if ( ! $this->is_preview() ) {
            $cache[ $args['widget_id'] ] = ob_get_flush();
            wp_cache_set( 'widget_inhype_posts_slider', $cache, 'widget' );
        } else {
            ob_end_flush();
        }
    }

    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['number'] = (int) $new_instance['number'];
        $instance['catid'] = $new_instance['catid'];
        $instance['posts_type'] = strip_tags($new_instance['posts_type']);
        $instance['post_template'] = strip_tags($new_instance['post_template']);
        $instance['autoplay'] = isset( $new_instance['autoplay'] ) ? (bool) $new_instance['autoplay'] : false;

        $alloptions = wp_cache_get( 'alloptions', 'options' );
        if ( isset($alloptions['widget_inhype_posts_slider']) )
            delete_option('widget_inhype_posts_slider');

        return $instance;
    }

    public function form( $instance ) {
        $title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
        $number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
        $catid     = isset( $instance['catid'] ) ? ( $instance['catid'] ) : '';
        $posts_type     = isset( $instance['posts_type'] ) ? ( $instance['posts_type'] ) : 'latest';
        $post_template     = isset( $instance['post_template'] ) ? ( $instance['post_template'] ) : 'latest';
        $autoplay = isset( $instance['autoplay'] ) ? (bool) $instance['autoplay'] : false;

?>
        <p><label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php esc_html_e( 'Title (Leave empty to disable title):', 'inhype' ); ?></label>
        <input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

        <p><label for="<?php echo esc_attr($this->get_field_id('post_template')); ?>"><?php esc_html_e('Post template:', 'inhype'); ?></label>
        <select id="<?php echo esc_attr($this->get_field_id('post_template')); ?>" name="<?php echo esc_attr($this->get_field_name('post_template')); ?>">

            <option value="post/grid"<?php if($post_template == 'post/grid') { echo ' selected';} ?>><?php esc_html_e('Grid', 'inhype'); ?></option>
            <option value="post/grid-short"<?php if($post_template == 'post/grid-short') { echo ' selected';} ?>><?php esc_html_e('Grid short', 'inhype'); ?></option>
            <option value="post/overlay-short"<?php if($post_template == 'post/overlay-short') { echo ' selected';} ?>><?php esc_html_e('Overlay', 'inhype'); ?></option>

        </select>

        <p><label for="<?php echo esc_attr($this->get_field_id('posts_type')); ?>"><?php esc_html_e('Posts type:', 'inhype'); ?></label>
        <select id="<?php echo esc_attr($this->get_field_id('posts_type')); ?>" name="<?php echo esc_attr($this->get_field_name('posts_type')); ?>">

            <option value="featured"<?php if($posts_type == 'featured') { echo ' selected';} ?>><?php esc_html_e('Featured', 'inhype'); ?></option>
            <option value="editorspicks"<?php if($posts_type == 'editorspicks') { echo ' selected';} ?>><?php esc_html_e('Editor\'s Picks', 'inhype'); ?></option>
            <option value="promoted"<?php if($posts_type == 'promoted') { echo ' selected';} ?>><?php esc_html_e('Promoted', 'inhype'); ?></option>
            <option value="latest"<?php if($posts_type == 'latest') { echo ' selected';} ?>><?php esc_html_e('Latest', 'inhype'); ?></option>
            <option value="liked"<?php if($posts_type == 'liked') { echo ' selected';} ?>><?php esc_html_e('Most liked', 'inhype'); ?></option>
            <option value="trending"<?php if($posts_type == 'trending') { echo ' selected';} ?>><?php esc_html_e('Trending', 'inhype'); ?></option>
            <option value="random"<?php if($posts_type == 'random') { echo ' selected';} ?>><?php esc_html_e('Random', 'inhype'); ?></option>

        </select>

        <p><label for="<?php echo esc_attr($this->get_field_id( 'catid' )); ?>"><?php esc_html_e( 'Category ID:', 'inhype' ); ?></label>
        <input id="<?php echo esc_attr($this->get_field_id( 'catid' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'catid' )); ?>" type="text" value="<?php echo esc_attr($catid); ?>" size="3" /> <span>(show posts from this category only)</span></p>

        <p><label for="<?php echo esc_attr($this->get_field_id( 'number' )); ?>"><?php esc_html_e( 'Number of posts to show:', 'inhype' ); ?></label>
        <input id="<?php echo esc_attr($this->get_field_id( 'number' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'number' )); ?>" type="text" value="<?php echo esc_attr($number); ?>" size="3" /></p>

        <p><input class="checkbox" type="checkbox" <?php checked( $autoplay ); ?> id="<?php echo esc_attr($this->get_field_id( 'autoplay' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'autoplay' )); ?>" />
        <label for="<?php echo esc_attr($this->get_field_id( 'autoplay' )); ?>"><?php esc_html_e( 'Slider autoplay', 'inhype' ); ?></label></p>
<?php
    }
}

/**
 * Recent_Comments widget class
 *
 */
class InHype_Widget_Recent_Comments extends WP_Widget {

    public function __construct() {
        $widget_ops = array('classname' => 'widget_inhype_recent_comments', 'description' => esc_html__( 'Your site&#8217;s most recent comments with date.', 'inhype' ) );
        parent::__construct('inhype-recent-comments', esc_html__('InHype Recent Comments', 'inhype'), $widget_ops);
        $this->alt_option_name = 'widget_inhype_recent_comments';
    }

    public function widget( $args, $instance ) {
        global $comments, $comment;

        $cache = array();
        if ( ! $this->is_preview() ) {
            $cache = wp_cache_get('widget_inhype_recent_comments', 'widget');
        }
        if ( ! is_array( $cache ) ) {
            $cache = array();
        }

        if ( ! isset( $args['widget_id'] ) )
            $args['widget_id'] = $this->id;

        if ( isset( $cache[ $args['widget_id'] ] ) ) {
            echo wp_kses_post($cache[ $args['widget_id'] ]);
            return;
        }

        $output = '';

        $title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : esc_html__( 'Recent Comments', 'inhype' );

        /** This filter is documented in wp-includes/default-widgets.php */
        $title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

        $number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
        if ( ! $number )
            $number = 5;

        /**
         * Filter the arguments for the Recent Comments widget.
         *
         * @since 3.4.0
         *
         * @see WP_Comment_Query::query() for information on accepted arguments.
         *
         * @param array $comment_args An array of arguments used to retrieve the recent comments.
         */
        $comments = get_comments( apply_filters( 'widget_comments_args', array(
            'number'      => $number,
            'status'      => 'approve',
            'post_status' => 'publish'
        ) ) );

        $output .= $args['before_widget'];
        if ( $title ) {
            $output .= $args['before_title'] . $title . $args['after_title'];
        }

        $output .= '<ul id="inhype_recentcomments">';
        if ( $comments ) {
            // Prime cache for associated posts. (Prime post term cache if we need it for permalinks.)
            $post_ids = array_unique( wp_list_pluck( $comments, 'comment_post_ID' ) );
            _prime_post_caches( $post_ids, strpos( get_option( 'permalink_structure' ), '%category%' ), false );

            foreach ( (array) $comments as $comment) {
                $output .= '<li class="inhype_recentcomments"><div class="inhype-post">';

                $output .= '<h3 class="post-title"><a href="' . esc_url( get_comment_link( $comment->comment_ID ) ) . '">' . get_the_title( $comment->comment_post_ID ) . '</a></h3><span class="post-date">'.get_comment_date( '', $comment->comment_ID ).'</span>';

                $output .= '</div></li>';
            }
        }
        $output .= '</ul>';
        $output .= $args['after_widget'];

        echo wp_kses_post($output);

        if ( ! $this->is_preview() ) {
            $cache[ $args['widget_id'] ] = $output;
            wp_cache_set( 'widget_inhype_recent_comments', $cache, 'widget' );
        }
    }

    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['number'] = absint( $new_instance['number'] );

        $alloptions = wp_cache_get( 'alloptions', 'options' );
        if ( isset($alloptions['widget_inhype_recent_comments']) )
            delete_option('widget_inhype_recent_comments');

        return $instance;
    }

    public function form( $instance ) {
        $title  = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
        $number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
?>
        <p><label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php esc_html_e( 'Title:', 'inhype' ); ?></label>
        <input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

        <p><label for="<?php echo esc_attr($this->get_field_id( 'number' )); ?>"><?php esc_html_e( 'Number of comments to show:', 'inhype' ); ?></label>
        <input id="<?php echo esc_attr($this->get_field_id( 'number' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'number' )); ?>" type="text" value="<?php echo esc_attr($number); ?>" size="3" /></p>
<?php
    }
}

/**
 * Social buttons widget class
 *
 */
class InHype_Widget_Social_Icons extends WP_Widget {

    public function __construct() {
        $widget_ops = array('classname' => 'widget_inhype_social_icons', 'description' => esc_html__( 'Show social follow icons set in theme admin panel.', 'inhype' ) );
        parent::__construct('inhype-social-icons', esc_html__('InHype Social Icons', 'inhype'), $widget_ops);
        $this->alt_option_name = 'widget_inhype_social_icons';
    }

    public function widget( $args, $instance ) {

        $cache = array();

        if ( ! $this->is_preview() ) {
            $cache = wp_cache_get('widget_inhype_social_icons', 'widget');
        }
        if ( ! is_array( $cache ) ) {
            $cache = array();
        }

        if ( ! isset( $args['widget_id'] ) )
            $args['widget_id'] = $this->id;

        if ( isset( $cache[ $args['widget_id'] ] ) ) {
            echo wp_kses_post($cache[ $args['widget_id'] ]);
            return;
        }

        $output = '';

        $title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : esc_html__( 'Subscribe and follow', 'inhype' );

        /** This filter is documented in wp-includes/default-widgets.php */
        $title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

        $output .= $args['before_widget'];
        if ( $title ) {
            $output .= $args['before_title'] . $title . $args['after_title'];
        }

        $output .= '<div class="textwidget">';

        $output_end = '</div>';
        $output_end .= $args['after_widget'];

        echo wp_kses_post($output); // This variable contains WordPress widget code and can't be escaped with WordPress functions

        inhype_social_display(true, 0, true, true);

        echo wp_kses_post($output_end);

        if ( ! $this->is_preview() ) {
            $cache[ $args['widget_id'] ] = $output;
            wp_cache_set( 'widget_inhype_social_icons', $cache, 'widget' );
        }
    }

    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);

        $alloptions = wp_cache_get( 'alloptions', 'options' );
        if ( isset($alloptions['widget_inhype_social_icons']) )
            delete_option('widget_inhype_social_icons');

        return $instance;
    }

    public function form( $instance ) {
        $title  = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
?>
        <p><label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php esc_html_e( 'Title:', 'inhype' ); ?></label>
        <input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
<?php
    }
}

/**
 * InHype Categories widget class
 *
 * @since 2.8.0
 */
class InHype_Widget_Categories extends WP_Widget {
    /**
     * Sets up a new Categories widget instance.
     */
    public function __construct() {
        $widget_ops = array(
            'classname'                   => 'widget_inhype_categories',
            'description'                 => esc_html__( 'A list of categories.', 'inhype' ),
            'customize_selective_refresh' => true,
        );
        parent::__construct( 'inhype-categories', esc_html__( 'InHype Categories', 'inhype' ), $widget_ops );
    }
    /**
     * Outputs the content for the current Categories widget instance.
     */
    public function widget( $args, $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Categories', 'inhype' );
        /** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
        $title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
        $c = ! empty( $instance['count'] ) ? '1' : '0';
        $h = ! empty( $instance['hierarchical'] ) ? '1' : '0';
        $exclude_catid = ( ! empty( $instance['exclude_catid'] ) ) ? ( $instance['exclude_catid'] ) : '';

        echo wp_kses_post($args['before_widget']);
        if ( $title ) {
            echo wp_kses_post($args['before_title'] . $title . $args['after_title']);
        }
        $cat_args = array(
            'orderby'      => 'name',
            'show_count'   => $c,
            'hierarchical' => $h,
            'exclude' => $exclude_catid
        );

            ?>
        <div class="post-categories-list">
            <?php
            $cat_args['title_li'] = '';
            /**
             * Filters the arguments for the Categories widget.
             */

            $categories = get_categories(apply_filters( 'widget_categories_args', $cat_args, $instance ));

            foreach ($categories as $category) {

                $category_color = get_term_meta ( $category->cat_ID, '_inhype_category_color', true );

                if(isset($category_color) && ($category_color !== '')) {
                    $category_badge_style = 'background-color: '.$category_color.';';
                } else {
                    $category_badge_style = '';
                }

                $category_image = get_term_meta ( $category->cat_ID, '_inhype_category_image', true );

                if(isset($category_image) && ($category_image !== '')) {
                    $category_style = 'background-image: url('.$category_image.');';
                    $cat_class = 'with-bg';
                } else {
                    $category_style = '';
                    $cat_class = '';
                }

                if($c == 1) {
                    $count_html = '<span class="post-categories-counter">'.esc_html($category->count).' '.esc_html__('Posts', 'inhype').'</span>';
                } else {
                    $count_html = '';
                }

                echo '<div class="inhype-post inhype-image-wrapper '.esc_attr($cat_class).'"><a href="'.esc_url(get_category_link( $category->cat_ID )).'" class="inhype-featured-category-link">
               <div class="post-categories-image inhype-image" data-style="'.esc_attr($category_style ? $category_style : $category_badge_style).'"></div>
               <div class="post-categories-overlay">
               <div class="post-categories-bg" data-style="'.esc_attr($category_badge_style).'"></div>
               <div class="post-categories"><div class="post-category"><span class="cat-dot" data-style="'.esc_attr($category_badge_style).'"></span><span class="cat-title">'.esc_html(esc_html($category->name)).'</span></div></div>
               '.wp_kses_post($count_html).'
               </div></a>
               </div>';
            }
            ?>
        </div>
            <?php

        echo wp_kses_post($args['after_widget']);
    }
    /**
     * Handles updating settings for the current Categories widget instance.
     */
    public function update( $new_instance, $old_instance ) {
        $instance                 = $old_instance;
        $instance['title']        = sanitize_text_field( $new_instance['title'] );
        $instance['count']        = ! empty( $new_instance['count'] ) ? 1 : 0;
        $instance['hierarchical'] = ! empty( $new_instance['hierarchical'] ) ? 1 : 0;
        $instance['exclude_catid'] = ! empty( $new_instance['exclude_catid'] ) ? ( $new_instance['exclude_catid'] ) : '';
        return $instance;
    }
    /**
     * Outputs the settings form for the Categories widget.
     */
    public function form( $instance ) {
        //Defaults
        $instance     = wp_parse_args( (array) $instance, array( 'title' => '' ) );
        $title        = sanitize_text_field( $instance['title'] );
        $count        = isset( $instance['count'] ) ? (bool) $instance['count'] : false;
        $hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
        $exclude_catid = isset( $instance['exclude_catid'] ) ? ( $instance['exclude_catid'] ) : '';

        ?>
        <p><label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php echo esc_html__( 'Title:', 'inhype' ); ?></label>
        <input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>

        <input type="checkbox" class="checkbox" id="<?php echo esc_attr($this->get_field_id( 'count' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'count' )); ?>"<?php checked( $count ); ?> />
        <label for="<?php echo esc_attr($this->get_field_id( 'count' )); ?>"><?php echo esc_html__( 'Show post counts', 'inhype' ); ?></label><br />

        <input type="checkbox" class="checkbox" id="<?php echo esc_attr($this->get_field_id( 'hierarchical' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'hierarchical' )); ?>"<?php checked( $hierarchical ); ?> />
        <label for="<?php echo esc_attr($this->get_field_id( 'hierarchical' )); ?>"><?php echo esc_html__( 'Show hierarchy', 'inhype' ); ?></label></p>

        <p><label for="<?php echo esc_attr($this->get_field_id( 'exclude_catid' )); ?>"><?php esc_html_e( 'Exclude categories (ID\'s, comma separated):', 'inhype' ); ?></label>
        <input id="<?php echo esc_attr($this->get_field_id( 'exclude_catid' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'exclude_catid' )); ?>" type="text" value="<?php echo esc_attr($exclude_catid); ?>" size="3" /></p>
        <?php
    }
}

/**
 * InHype Content widget class
 *
 * @since 2.8.0
 */
class InHype_Widget_Content extends WP_Widget {

    public function __construct() {
        $widget_ops = array('classname' => 'widget_inhype_text', 'description' => esc_html__('Add widget with any HTML content or shortcodes inside.', 'inhype'));
        $control_ops = array('width' => 400, 'height' => 350);
        parent::__construct('inhype-text', esc_html__('InHype Content', 'inhype'), $widget_ops, $control_ops);
    }

    /**
     * @param array $args
     * @param array $instance
     */
    public function widget( $args, $instance ) {
        /** This filter is documented in wp-includes/default-widgets.php */
        $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

        /**
         * Filter the content of the Text widget.
         *
         * @since 2.3.0
         *
         * @param string    $widget_text The widget content.
         * @param WP_Widget $instance    WP_Widget instance.
         */
        $text = apply_filters( 'widget_inhype_text', empty( $instance['text'] ) ? '' : $instance['text'], $instance );
        echo wp_kses_post($args['before_widget']);

        ?>
        <div class="inhype-textwidget-wrapper <?php echo !empty( $instance['paddings'] ) ? ' inhype-textwidget-no-paddings' : ''; ?>">
        <?php
        if ( ! empty( $title ) ) {
            echo wp_kses_post($args['before_title'] . $title . $args['after_title']);
        }
        if(!empty( $instance['button_target'])) {
            $button_target = '_blank';
        } else {
            $button_target = '_self';
        }
        if(!empty( $instance['bg_image'])) {
            $style = 'background-image: url('.esc_url($instance['bg_image']).');';
        } else {
            $style = '';
        }
        if(!empty( $instance['bg_color'])) {
            $style .= 'background-color: '.esc_attr($instance['bg_color']).';';
        }

        if(!empty( $instance['custom_padding'])) {
            $style .= 'padding: '.esc_attr($instance['custom_padding']).';';
        }

        if(!empty( $instance['text_color'])) {
            $style .= 'color: '.esc_attr($instance['text_color']).';';
        }

        if(!empty( $instance['text_align'])) {
            $style .= 'text-align: '.esc_attr($instance['text_align']).';';
        }

        ?>
            <div class="inhype-textwidget" data-style="<?php echo esc_attr($style); ?>"><?php echo !empty( $instance['filter'] ) ? wpautop( $text ) : $text; ?><?php echo !empty( $instance['button_text'] ) ? '<a class="btn" href="'.esc_url($instance['button_url']).'" target="'.esc_attr($button_target).'">'.esc_html($instance['button_text']).'</a>' : ''; ?></div>
        </div>
        <?php
        echo wp_kses_post($args['after_widget']);
    }

    /**
     * @param array $new_instance
     * @param array $old_instance
     * @return array
     */
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['custom_padding'] = strip_tags($new_instance['custom_padding']);
        $instance['text_color'] = strip_tags($new_instance['text_color']);
        $instance['bg_color'] = strip_tags($new_instance['bg_color']);
        $instance['text_align'] = strip_tags($new_instance['text_align']);
        $instance['button_text'] = strip_tags($new_instance['button_text']);
        $instance['button_url'] = strip_tags($new_instance['button_url']);
        $instance['bg_image'] = strip_tags($new_instance['bg_image']);

        if ( current_user_can('unfiltered_html') )
            $instance['text'] =  $new_instance['text'];
        else
            $instance['text'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['text']) ) ); // wp_filter_post_kses() expects slashed
        $instance['filter'] = ! empty( $new_instance['filter'] );
        $instance['paddings'] = ! empty( $new_instance['paddings'] );
        $instance['button_target'] = ! empty( $new_instance['button_target'] );

        return $instance;
    }

    /**
     * @param array $instance
     */
    public function form( $instance ) {

        wp_enqueue_media();
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );

        $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '',  'button_text' => '','bg_image' => '','button_url' => '', 'custom_padding' => '', 'text_color' => '', 'bg_color' => '', 'text_align' => '') );
        $title = strip_tags($instance['title']);
        $button_text = strip_tags($instance['button_text']);
        $custom_padding = strip_tags($instance['custom_padding']);
        $bg_image = strip_tags($instance['bg_image']);
        $text_color = strip_tags($instance['text_color']);
        $bg_color = strip_tags($instance['bg_color']);
        $text_align = strip_tags($instance['text_align']);

        $button_url = strip_tags($instance['button_url']);
        $text = esc_textarea($instance['text']);
?>
        <p><input id="<?php echo esc_attr($this->get_field_id('paddings')); ?>" name="<?php echo esc_attr($this->get_field_name('paddings')); ?>" type="checkbox" <?php checked(isset($instance['paddings']) ? $instance['paddings'] : 0); ?> />&nbsp;<label for="<?php echo esc_attr($this->get_field_id('paddings')); ?>"><?php esc_html_e('Disable paddings in widget', 'inhype'); ?></label></p>
        <p><label for="<?php echo esc_attr($this->get_field_id('custom_padding')); ?>"><?php esc_html_e('Custom padding for content:', 'inhype'); ?></label>
        <input class="" id="<?php echo esc_attr($this->get_field_id('custom_padding')); ?>" name="<?php echo esc_attr($this->get_field_name('custom_padding')); ?>" type="text" placeholder="<?php esc_attr_e('For ex.: 10px 5px 10px 5px', 'inhype'); ?>" value="<?php echo esc_attr($custom_padding); ?>" /></p>
        <p><label for="<?php echo esc_attr($this->get_field_id('text_align')); ?>"><?php esc_html_e('Text align:', 'inhype'); ?></label>
        <select id="<?php echo esc_attr($this->get_field_id('text_align')); ?>" name="<?php echo esc_attr($this->get_field_name('text_align')); ?>">
            <option value="<?php echo esc_attr($text_align); ?>" selected><?php echo esc_attr($text_align); ?></option>
            <option value="left"><?php esc_html_e('Left', 'inhype'); ?></option>
            <option value="center"><?php esc_html_e('Center', 'inhype'); ?></option>
            <option value="right"><?php esc_html_e('Right', 'inhype'); ?></option>
        </select>
        </p>
        <p><label class="label-text-color" for="<?php echo esc_attr($this->get_field_id('text_color')); ?>"><?php esc_html_e('Text color:', 'inhype'); ?></label>
        <input class="select-text-color" id="<?php echo esc_attr($this->get_field_id('text_color')); ?>" name="<?php echo esc_attr($this->get_field_name('text_color')); ?>" placeholder="<?php esc_attr_e('For example: #ffffff', 'inhype'); ?>" type="text" value="<?php echo esc_attr($text_color); ?>" /></p>
        <p><label class="label-bg-color" for="<?php echo esc_attr($this->get_field_id('bg_color')); ?>"><?php esc_html_e('Background color:', 'inhype'); ?></label>
        <input class="select-bg-color" id="<?php echo esc_attr($this->get_field_id('bg_color')); ?>" name="<?php echo esc_attr($this->get_field_name('bg_color')); ?>" placeholder="<?php esc_attr_e('For example: #ffffff', 'inhype'); ?>" type="text" value="<?php echo esc_attr($bg_color); ?>" /></p>
        <p><label for="<?php echo esc_attr($this->get_field_id('bg_image')); ?>"><?php esc_html_e('Background image url:', 'inhype'); ?></label><br/>
        <input class="" id="<?php echo esc_attr($this->get_field_id('bg_image')); ?>" name="<?php echo esc_attr($this->get_field_name('bg_image')); ?>" type="text" value="<?php echo esc_attr($bg_image); ?>" /><a class="button upload-widget-bg-image" data-input_id="<?php echo esc_attr($this->get_field_id('bg_image')); ?>" data-uploader_button_text="<?php esc_attr_e('Select background image', 'inhype'); ?>" data-uploader_title="<?php esc_attr_e('Add background image to widget', 'inhype'); ?>"><?php esc_html_e( 'Select image', 'inhype' ); ?></a></p>
        <p><label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Widget Title:', 'inhype'); ?></label>
        <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

        <p><label for="<?php echo esc_attr($this->get_field_id( 'text' )); ?>"><?php esc_html_e( 'Content:', 'inhype' ); ?></label>
        <p><a class="button upload-widget-image" data-textarea_id="<?php echo esc_attr($this->get_field_id('text')); ?>" data-uploader_button_text="Add image to content" data-uploader_title="Add image to widget content"><?php esc_html_e( 'Add Image to content', 'inhype' ); ?></a></p>
        <textarea class="widefat" rows="16" cols="20" id="<?php echo esc_attr($this->get_field_id('text')); ?>" name="<?php echo esc_attr($this->get_field_name('text')); ?>"><?php echo esc_attr($text); ?></textarea>
        </p>
         <p><input id="<?php echo esc_attr($this->get_field_id('filter')); ?>" name="<?php echo esc_attr($this->get_field_name('filter')); ?>" type="checkbox" <?php checked(isset($instance['filter']) ? $instance['filter'] : 0); ?> />&nbsp;<label for="<?php echo esc_attr($this->get_field_id('filter')); ?>"><?php esc_html_e('Automatically add paragraphs', 'inhype'); ?></label></p>

           <p><label for="<?php echo esc_attr($this->get_field_id('button_text')); ?>"><?php esc_html_e('Button Text:', 'inhype'); ?></label>
        <input class="widefat" id="<?php echo esc_attr($this->get_field_id('button_text')); ?>" placeholder="<?php esc_attr_e('Leave empty to disable button', 'inhype'); ?>" name="<?php echo esc_attr($this->get_field_name('button_text')); ?>" type="text" value="<?php echo esc_attr($button_text); ?>" /></p>
        <p><label for="<?php echo esc_attr($this->get_field_id('button_url')); ?>"><?php esc_html_e('Button URL:', 'inhype'); ?></label>
        <input class="" id="<?php echo esc_attr($this->get_field_id('button_url')); ?>" name="<?php echo esc_attr($this->get_field_name('button_url')); ?>" type="text" value="<?php echo esc_attr($button_url); ?>" /> <input id="<?php echo esc_attr($this->get_field_id('button_target')); ?>" name="<?php echo esc_attr($this->get_field_name('button_target')); ?>" type="checkbox" <?php checked(isset($instance['button_target']) ? $instance['button_target'] : 0); ?> />&nbsp;<label for="<?php echo esc_attr($this->get_field_id('button_target')); ?>"><?php esc_html_e('Open in new tab', 'inhype'); ?></label></p>


<?php

    }
}

?>
