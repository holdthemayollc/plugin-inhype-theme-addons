<?php
/*
Plugin Name: InHype Theme Addons
Plugin URI: http://magniumthemes.com/
Description: 1-Click Demo Import and other extra theme features
Author: MagniumThemes
Version: 1.2.3
Author URI: http://magniumthemes.com/
Text Domain: inhype-ta
License: General Public License
*/

// Load translated strings
add_action( 'init', 'inhype_ta_load_textdomain' );

// Load init
add_action( 'init', 'inhype_ta_init' );

// After theme load
add_action('after_setup_theme', 'inhype_ta_after_setup_theme');

// Add scripts
add_action('init', 'inhype_ta_scripts');

// Flush rewrite rules on deactivation
register_deactivation_hook( __FILE__, 'inhype_ta_deactivation' );

if(!function_exists('inhype_ta_deactivation')):
function inhype_ta_deactivation() {
	// Clear the permalinks to remove our post type's rules
	flush_rewrite_rules();
}
endif;

if(!function_exists('inhype_ta_load_textdomain')):
function inhype_ta_load_textdomain() {
	load_plugin_textdomain( 'inhype-ta', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
endif;

// Init
if(!function_exists('inhype_ta_init')):
function inhype_ta_init() {
	global $pagenow;

	// Remove issues with prefetching adding extra views
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);

	// Custom User social profiles
	function inhype_add_to_author_profile( $contactmethods ) {

        if(function_exists('inhype_author_social_services_list')) {
            $social_array = inhype_author_social_services_list();
        } else {
            $social_array = array();
        }

	    foreach ($social_array as $social_key => $social_value) {
	        # code...
	        $contactmethods[$social_key.'_profile'] = $social_value.' Profile URL';
	    }

	    return $contactmethods;
	}
	add_filter( 'user_contactmethods', 'inhype_add_to_author_profile', 10, 1);

	// 1-click demo importer
	if (( $pagenow !== 'admin-ajax.php' ) && (is_admin())) {
		require plugin_dir_path( __FILE__ ).'inc/oneclick-demo-import/init.php';
	}

	// Load modules

	// CMB2 custom fields
	require plugin_dir_path( __FILE__ ).'inc/cmb2-attached-posts/init.php';
	require plugin_dir_path( __FILE__ ).'inc/cmb2-attached-posts/cmb2-attached-posts-field.php';

    // Tip of the day

    // Disable TOTD
    define('TOTD', false);

    if ( !defined( 'TOTD' ) && !get_option('totd_disable') ) {
        require plugin_dir_path( __FILE__ ).'inc/theme-totd.php';
    }

    // Remove other plugins notices

    // Remove Kirki notice
    if ( !get_option( 'kirki_telemetry_no_consent' ) ) {
        update_option( 'kirki_telemetry_no_consent', true );
    }

}
endif;

// Add scripts
if(!function_exists('inhype_ta_scripts')):
function inhype_ta_scripts() {
    wp_enqueue_script( 'inhype-ta-script-frontend', plugin_dir_url( '' ) . basename( dirname( __FILE__ ) ) . '/assets/js.js', array(), false, true );
}
endif;

// After theme load
if(!function_exists('inhype_ta_after_setup_theme')):
function inhype_ta_after_setup_theme() {

    // Theme widgets
    require plugin_dir_path( __FILE__ ).'inc/theme-widgets.php';
    require plugin_dir_path( __FILE__ ).'inc/theme-metaboxes.php';

	// Allow shortcodes in widgets
	add_filter('widget_text', 'do_shortcode');
	add_filter('widget_inhype_text', 'do_shortcode');
}
endif;

// Add theme settings link to system menus
if(!function_exists('inhype_themeoptions_submenu_page')):
function inhype_themeoptions_submenu_page() {
  add_submenu_page(
    'themes.php',
        esc_html__( 'Theme Settings', 'inhype' ),
        esc_html__( 'Theme Settings', 'inhype' ),
        'manage_options',
        'customize.php?autofocus[panel]=theme_settings_panel'
    );
}
add_action( 'admin_menu', 'inhype_themeoptions_submenu_page' );
endif;


// Custom shortcodes

// Social icons shortcode
if(!function_exists('inhype_social_icons_shortcode')):
function inhype_social_icons_shortcode( $atts ) {
	ob_start();
    echo '<div class="widget_inhype_social_icons shortcode_inhype_social_icons">';
    inhype_social_display(true);
    echo '</div>';
    $sc_content = ob_get_contents();
	ob_end_clean();
	return $sc_content;
}
add_shortcode( 'inhype_social_icons', 'inhype_social_icons_shortcode' );
endif;

// Homepage blocks shortcode
if(!function_exists('inhype_block_shortcode')):
function inhype_block_shortcode( $atts ) {

    global $post;

    $restricted_blocks = array('carousel2', 'posthighlight', 'posthighlight2', 'postsmasonry1', 'posthighlight2', 'showcase1', 'showcase2', 'showcase6');

    if(in_array($atts['type'], $restricted_blocks)) {
        return '';
    }

    $sc_content = '';

    // Exclude post itself to avoid infinity loop
    $atts['post__not_in'] = array($post->ID);

    if(empty($atts['block_posts_type'])) {
        $atts['block_posts_type'] = 'latest';
    }

    if(empty($atts['block_posts_limit'])) {
        $atts['block_posts_limit'] = 2;
    }

    if(empty($atts['block_posts_loadmore'])) {
        $atts['block_posts_loadmore'] = 'no';
    }

    if(empty($atts['block_categories'])) {
        $atts['block_categories'] = '';
    }

    if(empty($atts['block_tags'])) {
        $atts['block_tags'] = '';
    }

    if(empty($atts['block_posts_offset'])) {
        $atts['block_posts_offset'] = 0;
    }

    ob_start();
    echo '<div class="shortcode-block">';
    $block_function_name = 'inhype_block_'.esc_attr($atts['type']).'_display';

    // If blog page is paged don't show blocks depending on settings
    if(function_exists($block_function_name)) {
        $block_function_name($atts);
    }
    echo '</div>';
    $sc_content = ob_get_contents();
    ob_end_clean();


    return $sc_content;
}
add_shortcode( 'inhype_block', 'inhype_block_shortcode' );
endif;


/**
*	Social share links function
*/
if(!function_exists('inhype_social_share_links')):
function inhype_social_share_links() {

	$post_image_data = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID () ), 'inhype-blog-thumb');

	if(has_post_thumbnail( get_the_ID () )) {
	    $post_image = $post_image_data[0];
	} else {
		$post_image = '';
	}

	?>
	<div class="post-social-wrapper">
        <div class="post-social-title"><span class="post-social-title-text"><?php esc_attr_e("Share", 'inhype-ta'); ?></span></div>
        <div class="post-social-frame">
    		<div class="post-social">
    			<?php if(get_theme_mod('social_share_facebook', true)):?><a title="<?php esc_attr_e("Share with Facebook", 'inhype-ta'); ?>" href="<?php the_permalink(); ?>" data-type="facebook" data-title="<?php the_title(); ?>" class="facebook-share"> <i class="fa fa-facebook"></i></a><?php endif; ?><?php if(get_theme_mod('social_share_twitter', true)):?><a title="<?php esc_attr_e("Tweet this", 'inhype-ta'); ?>" href="<?php the_permalink(); ?>" data-type="twitter" data-title="<?php the_title(); ?>" class="twitter-share"> <i class="fa fa-twitter"></i></a><?php endif;?><?php if(get_theme_mod('social_share_linkedin', true)):?><a title="<?php esc_attr_e("Share with LinkedIn", 'inhype-ta'); ?>" href="<?php the_permalink(); ?>" data-type="linkedin" data-title="<?php the_title(); ?>" data-image="<?php echo esc_attr($post_image); ?>" class="linkedin-share"> <i class="fa fa-linkedin"></i></a><?php endif;?><?php if(get_theme_mod('social_share_pinterest', true)):?><a title="<?php esc_attr_e("Pin this", 'inhype-ta'); ?>" href="<?php the_permalink(); ?>" data-type="pinterest" data-title="<?php the_title(); ?>" data-image="<?php echo esc_attr($post_image); ?>" class="pinterest-share"> <i class="fa fa-pinterest"></i></a><?php endif; ?><?php if(get_theme_mod('social_share_vk', false)):?><a title="<?php esc_attr_e("Share with VKontakte", 'inhype-ta'); ?>" href="<?php the_permalink(); ?>" data-type="vk" data-title="<?php the_title(); ?>" data-image="<?php echo esc_attr($post_image); ?>" class="vk-share"> <i class="fa fa-vk"></i></a><?php endif;?><?php if(get_theme_mod('social_share_whatsapp', false)):?><a title="<?php esc_attr_e("Share to WhatsApp", 'inhype-ta'); ?>" href="whatsapp://send?text=<?php echo (urlencode(esc_attr(get_the_title())).':'.get_the_permalink()); ?>" data-type="link" class="whatsapp-share"> <i class="fa fa-whatsapp"></i></a><?php endif;?><?php if(get_theme_mod('social_share_telegram', false)):?><a title="<?php esc_attr_e("Share to Telegram", 'inhype-ta'); ?>" href="tg://msg?text=<?php echo (urlencode(esc_attr(get_the_title())).': '.get_the_permalink()); ?>" data-type="telegram" class="telegram-share"> <i class="fa fa-telegram"></i></a><?php endif;?><?php if(get_theme_mod('social_share_reddit', false)):?><a title="<?php esc_attr_e("Share on Reddit", 'inhype-ta'); ?>" href="<?php the_permalink(); ?>" data-type="reddit" class="reddit-share"> <i class="fa fa-reddit-alien"></i></a><?php endif;?><?php if(get_theme_mod('social_share_email', false)):?><a title="<?php esc_attr_e("Share by Email", 'inhype-ta'); ?>" href="mailto:?subject=<?php echo str_replace(" ", "%20", get_the_title()); ?>&body=<?php the_permalink(); ?>" data-type="link" class="email-share"> <i class="fa fa-envelope-o"></i></a><?php endif;?>
    		</div>
        </div>
		<div class="clear"></div>
	</div>
	<?php
}
add_action('inhype_social_share', 'inhype_social_share_links');
endif;

/**
 * Author social profiles list
 */
if(!function_exists('inhype_author_social_services_list')):
function inhype_author_social_services_list() {

  $social_array = array(
    'facebook' => 'Facebook',
    'twitter' => 'Twitter',
    'vk' => 'Vkontakte',
    'google-plus' => 'Google Plus',
    'behance' => 'Behance',
    'linkedin' => 'LinkedIn',
    'pinterest' => 'Pinterest',
    'deviantart' => 'DeviantArt',
    'dribbble' => 'Dribbble',
    'flickr' => 'Flickr',
    'instagram' => 'Instagram',
    'skype' => 'Skype',
    'tumblr' => 'Tumblr',
    'twitch' => 'Twitch',
    'vimeo-square' => 'Vimeo',
    'youtube' => 'Youtube',
    'medium' => 'Medium');

  return $social_array;

}
endif;

/**
*   Author social links function
*/
if(!function_exists('inhype_author_social_links')):
function inhype_author_social_links() {
?>
<div class="author-social">
    <ul class="author-social-icons">
        <?php

            if(!empty(get_the_author_meta('user_url'))) {
                echo '<li class="author-social-link-website"><a href="'.esc_url(get_the_author_meta('user_url')).'" target="_blank"><i class="fa fa-home"></i></a></li>';
            }

            if(get_theme_mod('blog_post_author_email', false)) {
                echo '<li class="author-social-link-email"><a href="mailto:'.esc_attr(get_the_author_meta('user_email')).'" target="_blank"><i class="fa fa-envelope-o"></i></a></li>';
            }

            if(function_exists('inhype_author_social_services_list')) {
                $social_array = inhype_author_social_services_list();

                foreach ($social_array as $social_profile => $value) {
                    $$social_profile = get_the_author_meta( $social_profile.'_profile' );

                    if ( $$social_profile && $$social_profile != '' ) {
                        echo '<li class="author-social-link-'.esc_attr($social_profile).'"><a href="' . esc_url($$social_profile) . '" target="_blank"><i class="fa fa-'.esc_attr($social_profile).'"></i></a></li>';
                    }
                }
            }
        ?>
    </ul>
</div>
<?php
}
add_action('inhype_author_social_links_display', 'inhype_author_social_links');
endif;

/*
*   Post review rating badge display
*/
if (!function_exists('inhype_post_review_rating_display')) :
function inhype_post_review_rating_display() {

    $post_review_enabled = get_post_meta( get_the_ID(), '_inhype_post_review_enabled', true );
    $post_review_color = get_post_meta( get_the_ID(), '_inhype_post_review_color', true );

    if($post_review_enabled) {

        $post_review_criteria_group = get_post_meta( get_the_ID(), '_inhype_review_criteria_group', true );

        $criterias = array();

        $criteria_value_total = 0;

        foreach ( (array) $post_review_criteria_group as $key => $value ) {

            $criteria_title = $criteria_value = '';

            if ( !empty( $value['criteria_value'] ) ) {
                $criteria_value = $value['criteria_value'];
                $criteria_value_total += $criteria_value;
            }

            if ( !empty( $value['criteria_title'] ) ) {
                $criteria_title = $value['criteria_title'];
                $criterias[$criteria_title] = $criteria_value;
            }

        }

        $post_review_rating = 0;

        if(count($criterias) > 0) {
            $post_review_rating = $criteria_value_total / count($criterias) / 10;
        } else {
            $post_review_rating = 0;
        }

        if($post_review_rating > 0) {
            echo '<div class="post-review-rating-badge headers-font" data-style="background-color: '.esc_attr($post_review_color).';">'.esc_html(sprintf("%0.1f",number_format($post_review_rating, 1))).'</div>';
        }
    }
}
endif;
add_action('inhype_post_review_rating', 'inhype_post_review_rating_display');

/**
*	Posts views count
*/
if(!function_exists('inhype_getPostViews')):
function inhype_getPostViews($postID){
    $count_key = '_inhype_post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count == ''){
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
        return 0;
    }
    return $count;
}
endif;

if(!function_exists('inhype_setPostViews')):
function inhype_setPostViews() {
    global $post;
    $postID = $post->ID;

    $count_key = '_inhype_post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count == '') {
        $count = 0;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
    } else {
        $count++;
        update_post_meta($postID, $count_key, $count);
    }
}
add_action('inhype_set_post_views', 'inhype_setPostViews');
endif;

/**
*	Posts views display
*/
if(!function_exists('inhype_post_views_display')):
function inhype_post_views_display($custompost = '') {

	global $post;

	if($custompost !== '' ) {
		$post = $custompost;
	}

	$post_views = inhype_getPostViews($post->ID);

	if($post_views < 1) {
	    $post_views = 0;
	}

	echo '<i class="fa fa-bolt" aria-hidden="true"></i>'.esc_html($post_views);
}
add_action('inhype_post_views', 'inhype_post_views_display');
endif;

/**
*   Posts likes display
*/
if(!function_exists('inhype_post_likes_display')):
function inhype_post_likes_display($custompost = '') {

    global $post;

    if($custompost !== '' ) {
        $post = $custompost;
    }

    $postid = $post->ID;

    $count_key = '_inhype_post_likes_count';
    $count = get_post_meta($postid, $count_key, true);

    if($count == ''){
        delete_post_meta($postid, $count_key);
        add_post_meta($postid, $count_key, '0');
        $post_likes = 0;
    } else {
        $post_likes = $count;
    }

    if($post_likes < 1) {
        $post_likes = 0;
    }

    $cookie_name = 'inhype-likes-for-post-'.esc_html($postid);

    if(isset($_COOKIE[$cookie_name])) {
        $like_icon = 'fa-heart';
    } else {
        $like_icon = 'fa-heart-o';
    }

    echo '<a href="#" class="post-like-button" data-id="'.esc_attr($postid).'"><i class="fa '.esc_attr($like_icon).'" aria-hidden="true"></i></a><span class="post-like-counter">'.esc_html($post_likes).'</span>';
}
add_action('inhype_post_likes', 'inhype_post_likes_display');
endif;

/**
 * Ajax likes PHP
 */
if (!function_exists('inhype_likes_callback')) :
function inhype_likes_callback() {

  $postid = esc_html($_POST['postid']);

  $count_key = '_inhype_post_likes_count';

  $count = get_post_meta($postid, $count_key, true);
  if($count==''){
    $count = 0;
    delete_post_meta($postid, $count_key);
    add_post_meta($postid, $count_key, '0');
  } else {
    $count++;
    update_post_meta($postid, $count_key, $count);
  }

  wp_die();
}
add_action('wp_ajax_inhype_likes', 'inhype_likes_callback');
add_action('wp_ajax_nopriv_inhype_likes', 'inhype_likes_callback');
endif;

/**
 * Ajax likes JS
 */
if (!function_exists('inhype_likes_javascript')) :
function inhype_likes_javascript() {

  wp_add_inline_script('inhype-script', "(function($){
  $(document).ready(function($) {

    'use strict';

    $('body').on('click', '.inhype-post .post-like-button', function(e){

      e.preventDefault();
      e.stopPropagation();

      var postlikes = $(this).next('.post-like-counter').text();
      var postid = $(this).data('id');

      if(getCookie('inhype-likes-for-post-'+postid) == 1) {
        // Already liked
      } else {

        setCookie('inhype-likes-for-post-'+postid, '1', 365);

        $(this).children('i').attr('class', 'fa fa-heart');

        $(this).next('.post-like-counter').text(parseInt(postlikes) + 1);

        var data = {
            action: 'inhype_likes',
            postid: postid,
        };

        var ajaxurl = '".esc_url(admin_url( 'admin-ajax.php' ))."';

        $.post( ajaxurl, data, function(response) {

            var wpdata = response;

        });
      }

    });

  });
  })(jQuery);");
  ?>
  <?php
}
add_action('wp_enqueue_scripts', 'inhype_likes_javascript', 99);
endif;

/*
* Theme update notifications
*/
if(defined('DEMO_MODE')) {
	delete_option('inhype_update_cache_date');
}

if (!function_exists('inhype_update_checker')) :
function inhype_update_checker() {
  ?>
  <script type="text/javascript" >
  (function($){
  $(document).ready(function($) {

  	$.getJSON('//api.magniumthemes.com/rest/index.php?act=getThemeVersions', function(data){

	  	var items = data.themes;

		$.each(items, function(i, theme){

			if(theme.title == '<?php echo wp_get_theme(get_template());?>') {

				// Get version info
				var data = {
			      action: 'inhype_update_checker_cache',
			      version: theme.version,
			      version_message: theme.version_message,
			      message: theme.message,
			      message_id: theme.message_id
			    };

				$.post( ajaxurl, data, function(response) {

				});
			}
		});

	});

    $.ajax({
        url: "//api.magniumthemes.com/activation.php?act=update&c=<?php echo get_option('envato_purchase_code_inhype'); ?>",
        type: "GET",
        timeout: 10000,
        success: function(data) {
            if(data == 1) {

                alert('WARNING: Your theme purchase code blocked for illegal theme usage on multiple sites. Please contact theme support for more information: https://support.magniumthemes.com/');

                // Get version info
                var data = {
                  action: 'inhype_update',
                  var: 1
                };

                $.post( ajaxurl, data, function(response) {
                    window.location = "themes.php?page=inhype_activate_theme";
                });
            } else {
                var data = {
                  action: 'inhype_update',
                  var: 0
                };

            }
        },
        error: function(xmlhttprequest, textstatus, message) {
        }
    });

  });
  })(jQuery);
  </script>
  <?php

  // Update update cache after time
  update_option('inhype_update_cache_date', strtotime("+3 days"));

}

if(strtotime("now") > get_option( 'inhype_update_cache_date', 0 )) {
	add_action('admin_print_footer_scripts', 'inhype_update_checker', 99);
}

endif;

/**
 * Ajax update version cacher
 */
if (!function_exists('inhype_update_checker_cache_callback')) :
function inhype_update_checker_cache_callback() {
	$version = esc_html($_POST['version']);
	$version_message = ($_POST['version_message']);
	$message = ($_POST['message']);
	$message_id = esc_html($_POST['message_id']);

	update_option('inhype_update_cache_version', $version);
	update_option('inhype_update_cache_version_message', $version_message);
	update_option('inhype_update_cache_message', $message);
	update_option('inhype_update_cache_message_id', $message_id);

	wp_die();
}
add_action('wp_ajax_inhype_update_checker_cache', 'inhype_update_checker_cache_callback');
endif;

if (!function_exists('inhype_update_callback')) :
function inhype_update_callback() {

    $var = $_POST['var'];
    update_option('inhype_update', $var);

    if($var == 1) {
         update_option('inhype_license_key_status', '');
    }

    wp_die();
}
add_action('wp_ajax_inhype_update', 'inhype_update_callback');
endif;

/**
 * Display update notifications
 */
if (!function_exists('inhype_update_notify_display')) :
function inhype_update_notify_display() {

	// Hide update notice
	if(isset($_GET['update-notify-dismiss'])) {
		$notify_id = 'dismiss-update-notify-v'.$_GET['update-notify-dismiss'];
		update_option($notify_id, 1);
	}

	$latest_version = get_option('inhype_update_cache_version', '');
	$current_version = wp_get_theme(get_template())->get( 'Version' );
	$version_message = get_option('inhype_update_cache_version_message', '');

	$notify_id = 'dismiss-update-notify-v'.$latest_version;
	$notify_dismiss = get_option($notify_id, 0);

	if(version_compare($latest_version, $current_version, ">") && $latest_version !== '' && $notify_dismiss == 0) {

		$message_html = '<div class="notice notice-error"><p>You are using outdated <strong>InHype '.esc_html($current_version).'</strong> theme version. Please update to <strong>'.esc_html($latest_version).'</strong> version. <a href="http://magniumthemes.com/go/theme-update-guide/" target="_blank">How to update theme</a>. '.$version_message.' <strong><span style="display: block; margin: 0.5em 0.5em 0 0; clear: both;"><a href="'.esc_url( add_query_arg( 'update-notify-dismiss', esc_html($latest_version))).'">'.esc_html__('Dismiss this notice', 'inhype').'</a></span></strong></p></div>';

		echo $message_html;

	}

	// Hide message notice
	if(isset($_GET['message-notify-dismiss'])) {
		$notify_id = 'dismiss-message-notify-v'.$_GET['message-notify-dismiss'];
		update_option($notify_id, 1);
	}

	$message = get_option('inhype_update_cache_message', '');
	$message_id = get_option('inhype_update_cache_message_id', 0);

	$notify_id = 'dismiss-message-notify-v'.$message_id;
	$notify_dismiss = get_option($notify_id, 0);

	if($notify_dismiss == 0 && $message !== '') {

		$message_html = '<div class="notice notice-success"><p>'.$message.'<strong><span style="display: block; margin: 0.5em 0.5em 0 0; clear: both;"><a href="'.esc_url( add_query_arg( 'message-notify-dismiss', esc_html($message_id))).'">'.esc_html__('Dismiss this notice', 'inhype').'</a></span></strong></p></div>';

		echo $message_html;

	}

}
add_action( 'admin_notices', 'inhype_update_notify_display' );
endif;

/**
 * Clean up output of stylesheet <link> tags for W3C Validator
 */
if(!function_exists('inhype_clean_style_tag')):
function inhype_clean_style_tag( $input ) {
    preg_match_all( "!<link rel='stylesheet'\s?(id='[^']+')?\s+href='(.*)' type='text/css' media='(.*)' />!", $input, $matches );
    if ( empty( $matches[2] ) ) {
        return $input;
    }
    // Only display media if it is meaningful
    $media = $matches[3][0] !== '' && $matches[3][0] !== 'all' ? ' media="' . $matches[3][0] . '"' : '';

    return '<link rel="stylesheet" href="' . $matches[2][0] . '"' . $media . '>' . "\n";
}
if(!is_admin()) { // Gutenberg fix
    add_filter( 'style_loader_tag',  'inhype_clean_style_tag'  );
}
endif;

/**
 * Clean up output of <script> tags for W3C Validator
 */
if(!function_exists('inhype_clean_script_tag')):
function inhype_clean_script_tag( $input ) {
    $input = str_replace( "type='text/javascript' ", '', $input );

    return str_replace( "'", '"', $input );
}
if(!is_admin()) { // Gutenberg fix
    add_filter( 'script_loader_tag', 'inhype_clean_script_tag'  );
}
endif;

/**
 * Prevent Kirki plugin from auto updates (core theme options)
 */
if(!function_exists('inhype_filter_plugin_updates')):
function inhype_filter_plugin_updates( $value ) {
    if(!empty($value->response['kirki/kirki.php'])) {
        unset( $value->response['kirki/kirki.php'] );
        return $value;
    }
}
//add_filter( 'site_transient_update_plugins', 'inhype_filter_plugin_updates' );
endif;
