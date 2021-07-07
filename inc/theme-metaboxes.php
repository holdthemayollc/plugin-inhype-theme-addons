<?php
/**
 * Theme custom metaboxes
 **/

// CMB2 METABOXES

// CATEGORY SETTINGS METABOX
add_action( 'cmb2_admin_init', 'inhype_register_category_settings_metabox' );
/**
 * Hook in and add a metabox to add fields to taxonomy terms
 */
function inhype_register_category_settings_metabox() {
  $prefix = '_inhype_';

  /**
   * Metabox to add fields to categories
   */

  $cmb_category_settings = new_cmb2_box( array(
    'id'               => $prefix . 'category_settings_metabox',
    'title'            => esc_html__( 'Category settings', 'inhype-ta' ), // Doesn't output for term boxes
    'object_types'     => array( 'term' ),
    'taxonomies'       => array( 'category' )
  ) );

  $cmb_category_settings->add_field( array(
    'name' => esc_html__( 'Enable parallax effect for category page header', 'inhype-ta' ),
    'id'   => $prefix . 'header_parallax',
    'type' => 'checkbox',
  ) );

  $cmb_category_settings->add_field( array(
    'name'             => 'Category listing layout',
    'desc'             => '',
    'id'               => $prefix . 'category_layout',
    'type'             => 'select',
    'show_option_none' => false,
    'default'          => '',
    'options'          => array(
      '' => esc_html__( 'Use theme settings', 'inhype-ta' ),
      'large-grid'   => esc_html__( 'First large then grid', 'inhype-ta' ),
      'overlay-grid'   => esc_html__( 'First large overlay then grid', 'inhype-ta' ),
      'large-list'   => esc_html__( 'First large then list', 'inhype-ta' ),
      'overlay-list'   => esc_html__( 'First large overlay then list', 'inhype-ta' ),
      'mixed-overlays'   => esc_html__( 'Mixed overlays', 'inhype-ta' ),
      'grid'   => esc_html__( 'Grid', 'inhype-ta' ),
      'list'   => esc_html__( 'List', 'inhype-ta' ),
      'standard'   => esc_html__( 'Classic', 'inhype-ta' ),
      'overlay'   => esc_html__( 'Grid overlay', 'inhype-ta' ),
      'mixed-large-grid'   => esc_html__( 'Mixed large and grid', 'inhype-ta' ),
      'masonry'   => esc_html__( 'Masonry', 'inhype-ta' ),
    ),
  ));

}

// POST REVIEW SETTINGS METABOX
if(!function_exists('inhype_register_post_review_settings_metabox')):
function inhype_register_post_review_settings_metabox() {

  // Start with an underscore to hide fields from custom fields list
  $prefix = '_inhype_';

  $cmb_post_review_settings = new_cmb2_box( array(
    'id'           => $prefix . 'post_review_metabox',
    'title'        => esc_html__( 'Post Review', 'inhype-ta' ),
    'object_types' => array( 'post' ), // Post type
    'context'      => 'normal',
    'priority'     => 'high',
    'show_names'   => true, // Show field names on the left
  ) );

  $cmb_post_review_settings->add_field( array(
    'name' => esc_html__( 'Enable review block', 'inhype-ta' ),
    'desc' => esc_html__( 'Enable to show review block for post and set review settings below.', 'inhype-ta' ),
    'id'   => $prefix . 'post_review_enabled',
    'type' => 'checkbox',
  ) );

  $cmb_post_review_settings->add_field( array(
    'name'         => esc_html__( 'Review block image', 'inhype-ta' ),
    'id'           => $prefix . 'post_review_image',
    'type'         => 'file',
    'options' => array(
        'url' => false, // Hide the text input for the url
        'add_upload_file_text' => 'Select or Upload Image'
    ),
    'preview_size' => array( 100, 100 ), // Default: array( 50, 50 )
  ) );

  $cmb_post_review_settings->add_field( array(
    'name'    => esc_html__( 'Review block title', 'inhype-ta' ),
    'desc'    => esc_html__( 'Displayed in review block header.', 'inhype-ta' ),
    'default' => '',
    'id'      => $prefix . 'post_review_title',
    'type'    => 'text_medium',
  ) );

  $cmb_post_review_settings->add_field( array(
    'name'    => esc_html__( 'Review block summary', 'inhype-ta' ),
    'desc'    => esc_html__( 'Short summary for review.', 'inhype-ta' ),
    'default' => '',
    'id'      => $prefix . 'post_review_summary',
    'type'    => 'textarea_small',
  ) );

  $cmb_post_review_settings->add_field( array(
    'name'    => esc_html__( 'Review accent color', 'inhype-ta' ),
    'desc'    => esc_html__( 'Used in review block.', 'inhype-ta' ),
    'id'      => $prefix . 'post_review_color',
    'type'    => 'colorpicker',
    'default' => '#6641DB',
  ) );

  $cmb_post_review_settings->add_field( array(
    'name'    => esc_html__( 'Positives', 'inhype-ta' ),
    'desc'    => esc_html__( 'Positives list (1 per line)', 'inhype-ta' ),
    'default' => '',
    'id'      => $prefix . 'post_review_positives',
    'type'    => 'textarea_small',
  ) );

  $cmb_post_review_settings->add_field( array(
    'name'    => esc_html__( 'Negatives', 'inhype-ta' ),
    'desc'    => esc_html__( 'Negatives list (1 per line)', 'inhype-ta' ),
    'default' => '',
    'id'      => $prefix . 'post_review_negatives',
    'type'    => 'textarea_small',
  ) );

  $cmb_post_review_settings->add_field( array(
    'name'    => esc_html__( 'Buy button url', 'inhype-ta' ),
    'desc'    => esc_html__( 'Leave empty to disable "Where to buy" section in review.', 'inhype-ta' ),
    'default' => '',
    'id'      => $prefix . 'post_review_button_url',
    'type'    => 'text_medium',
  ) );

  $cmb_post_review_settings->add_field( array(
    'name'    => esc_html__( 'Buy button title', 'inhype-ta' ),
    'desc'    => esc_html__( 'For ex. "Buy on Amazon"', 'inhype-ta' ),
    'default' => '',
    'id'      => $prefix . 'post_review_button_title',
    'type'    => 'text_medium',
  ) );

  $cmb_review_criteria_group = $cmb_post_review_settings->add_field( array(
    'id'          => $prefix . 'review_criteria_group',
    'type'        => 'group',
    // 'repeatable'  => false, // use false if you want non-repeatable group
    'options'     => array(
      'group_title'       => esc_html__( 'Review criteria {#}', 'inhype-ta' ),
      'add_button'        => esc_html__( 'Add review criteria', 'inhype-ta' ),
      'remove_button'     => esc_html__( 'Remove review criteria', 'inhype-ta' ),
      'sortable'          => true,
      // 'closed'         => true, // true to have the groups closed by default
      // 'remove_confirm' => esc_html__( 'Are you sure you want to remove?', 'cmb2' ), // Performs confirmation before removing group.
    ),
  ) );

  // Id's for group's fields only need to be unique for the group. Prefix is not needed.
  $cmb_post_review_settings->add_group_field( $cmb_review_criteria_group, array(
    'name' => esc_html__( 'Criteria title', 'inhype-ta' ),
    'id'   => 'criteria_title',
    'type' => 'text',
    // 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
  ) );

  $cmb_post_review_settings->add_group_field( $cmb_review_criteria_group, array(
    'name' => esc_html__( 'Criteria rating (%)', 'inhype-ta' ),
    'description' => esc_html__( 'Your rating for this criteria, for ex: 95 (means 95%)', 'inhype-ta' ),
    'id'   => 'criteria_value',
    'type' => 'text_small',
  ) );

}
endif;
add_action( 'cmb2_init', 'inhype_register_post_review_settings_metabox' );

// POST/PAGE SETTINGS METABOXES
if(!function_exists('inhype_register_post_format_settings_metabox')):
function inhype_register_post_format_settings_metabox() {

  // Get user role
  $user = wp_get_current_user();

  if ( in_array( 'contributor', (array) $user->roles ) ) {
    $display_post_additional_fields = false;
  } else {
    $display_post_additional_fields = true;
  }

  // Start with an underscore to hide fields from custom fields list
  $prefix = '_inhype_';

  // POST SETTINGS METABOX
  $cmb_post_settings = new_cmb2_box( array(
    'id'           => $prefix . 'post_settings_metabox',
    'title'        => esc_html__( 'Post Settings', 'inhype-ta' ),
    'object_types' => array( 'post' ), // Post type
    'context'      => 'normal',
    'priority'     => 'high',
    'show_names'   => true, // Show field names on the left
  ) );

  if($display_post_additional_fields):

  $cmb_post_settings->add_field( array(
    'name' => esc_html__( 'Featured post', 'inhype-ta' ),
    'desc' => esc_html__( 'Post will be added to blocks that show "Featured posts".', 'inhype-ta' ),
    'id'   => $prefix . 'post_featured',
    'type' => 'checkbox',
  ) );

  $cmb_post_settings->add_field( array(
    'name' => esc_html__( 'Editors Pick\'s post', 'inhype-ta' ),
    'desc' => esc_html__( 'Post will be added to blocks that show "Editor\'s Picks posts".', 'inhype-ta' ),
    'id'   => $prefix . 'post_editorspicks',
    'type' => 'checkbox',
  ) );

  $cmb_post_settings->add_field( array(
    'name' => esc_html__( 'Promoted post', 'inhype-ta' ),
    'desc' => esc_html__( 'Post will be added to blocks that show "Promoted posts".', 'inhype-ta' ),
    'id'   => $prefix . 'post_promoted',
    'type' => 'checkbox',
  ) );

  $cmb_post_settings->add_field( array(
    'name' => esc_html__( 'Disable featured image', 'inhype-ta' ),
    'desc' => esc_html__( 'Don\'t show featured image on single post page.', 'inhype-ta' ),
    'id'   => $prefix . 'post_image_disable',
    'type' => 'checkbox',
  ) );

  $cmb_post_settings->add_field( array(
    'name' => esc_html__( 'Sponsored post', 'inhype-ta' ),
    'desc' => esc_html__( 'Display "Sponsored" post badge instead of post date in posts listings.', 'inhype-ta' ),
    'id'   => $prefix . 'post_sponsored',
    'type' => 'checkbox',
  ) );

  endif;

  $cmb_post_settings->add_field( array(
    'name'    => esc_html__( 'Post summary', 'inhype-ta' ),
    'desc'    => esc_html__( 'Post summary list (1 per line)', 'inhype-ta' ),
    'default' => '',
    'id'      => $prefix . 'post_summary',
    'type'    => 'textarea_small',
  ) );

  if($display_post_additional_fields):

  $cmb_post_settings->add_field( array(
    'name'             => esc_html__( 'Post sidebar position', 'inhype-ta' ),
    'desc'             => '',
    'id'               => $prefix . 'post_sidebar_position',
    'type'             => 'select',
    'show_option_none' => false,
    'default'          => '0',
    'options'          => array(
      '0' => esc_html__( 'Use theme settings', 'inhype-ta' ),
      'left'   => esc_html__( 'Left', 'inhype-ta' ),
      'right'     => esc_html__( 'Right', 'inhype-ta' ),
      'disable'     => esc_html__( 'Disable', 'inhype-ta' ),
    ),
  ) );

  $cmb_post_settings->add_field( array(
    'name'             => esc_html__( 'Post header layout', 'inhype-ta' ),
    'desc'             => '',
    'id'               => $prefix . 'post_header_layout',
    'type'             => 'select',
    'show_option_none' => false,
    'default'          => '',
    'options'          => array(
      '' => esc_html__( 'Use theme settings', 'inhype-ta' ),
      'inheader'   => esc_html__( 'In header - Style 1 (Info box)', 'inhype-ta' ),
      'inheader2'   => esc_html__( 'In header - Style 2 (Image)', 'inhype-ta' ),
      'inheader3'   => esc_html__( 'In header - Style 3 (2 column)', 'inhype-ta' ),
      'incontent' => esc_html__( "In content - Style 1 (Info box)", 'inhype-ta' ),
      'incontent2' => esc_html__( "In content - Style 2 (Title above image)", 'inhype-ta' ),
      'incontent3' => esc_html__( "In content - Style 3 (Title below image)", 'inhype-ta' ),
    ),
  ));

  $cmb_post_settings->add_field( array(
    'name'             => esc_html__( 'Small content width', 'inhype-ta' ),
    'desc'             => '',
    'id'               => $prefix . 'post_smallwidth',
    'type'             => 'select',
    'show_option_none' => false,
    'default'          => '',
    'desc' => esc_html__( 'This option add left/right margins for this post without sidebar to make your content width smaller.', 'inhype-ta' ),
    'options'          => array(
      '' => esc_html__( 'Use theme settings', 'inhype-ta' ),
      '0'   => esc_html__( 'Disable', 'inhype-ta' ),
      '1'   => esc_html__( 'Enable', 'inhype-ta' )
    ),
  ));

  $cmb_post_settings->add_field( array(
    'name'             => esc_html__( 'Post header width', 'inhype-ta' ),
    'desc'             => '',
    'id'               => $prefix . 'post_header_width',
    'type'             => 'select',
    'show_option_none' => false,
    'default'          => '',
    'desc' => esc_html__( 'This option available only for "In header" post header layouts.', 'inhype-ta' ),
    'options'          => array(
      '' => esc_html__( 'Use theme settings', 'inhype-ta' ),
      'fullwidth'   => esc_html__( 'Fullwidth', 'inhype-ta' ),
      'boxed'   => esc_html__( 'Boxed', 'inhype-ta' ),
    ),
  ));

  $cmb_post_settings->add_field( array(
    'name'    => esc_html__( 'Post views', 'inhype-ta' ),
    'desc'    => esc_html__( 'You can change post views counter value here.', 'inhype-ta' ),
    'default' => '',
    'id'      => '_inhype_post_views_count',
    'type'    => 'text_small',
  ) );

  $cmb_post_settings->add_field( array(
    'name'    => esc_html__( 'Post likes', 'inhype-ta' ),
    'desc'    => esc_html__( 'You can change post likes counter value here.', 'inhype-ta' ),
    'default' => '',
    'id'      => '_inhype_post_likes_count',
    'type'    => 'text_small',
  ) );

  endif;

  // PAGE SETTINGS METABOX
  $cmb_page_settings = new_cmb2_box( array(
    'id'           => $prefix . 'page_settings_metabox',
    'title'        => esc_html__( 'Page Settings', 'inhype-ta' ),
    'object_types' => array( 'page' ), // Post type
    'context'      => 'normal',
    'priority'     => 'high',
    'show_names'   => true, // Show field names on the left
  ) );

  $cmb_page_settings->add_field( array(
    'name'    => esc_html__( 'Page CSS class', 'inhype-ta' ),
    'desc'    => esc_html__( 'You can add CSS class to page for use with your Custom CSS code to change elements styles only on this page.', 'inhype-ta' ),
    'default' => '',
    'id'      => $prefix . 'page_css_class',
    'type'    => 'text',
  ) );

  $cmb_page_settings->add_field( array(
    'name' => esc_html__( 'Don\'t display title', 'inhype-ta' ),
    'desc' => esc_html__( 'Disable page title and show only page content', 'inhype-ta' ),
    'id'   => $prefix . 'page_disable_title',
    'type' => 'checkbox',
  ) );

  $cmb_page_settings->add_field( array(
    'name'             => esc_html__( 'Page sidebar position', 'inhype-ta' ),
    'desc'             => '',
    'id'               => $prefix . 'page_sidebar_position',
    'type'             => 'select',
    'show_option_none' => false,
    'default'          => '0',
    'options'          => array(
      '0' => esc_html__( 'Use theme settings', 'inhype-ta' ),
      'left'   => esc_html__( 'Left', 'inhype-ta' ),
      'right'     => esc_html__( 'Right', 'inhype-ta' ),
      'disable'     => esc_html__( 'Disable', 'inhype-ta' ),
    ),
  ) );

  $cmb_page_settings->add_field( array(
    'name'             => esc_html__( 'Small content width', 'inhype-ta' ),
    'desc'             => '',
    'id'               => $prefix . 'post_smallwidth',
    'type'             => 'select',
    'show_option_none' => false,
    'default'          => '',
    'desc' => esc_html__( 'This option add left/right margins for this page without sidebar to make your content width smaller.', 'inhype-ta' ),
    'options'          => array(
      '' => esc_html__( 'Use theme settings', 'inhype-ta' ),
      '0'   => esc_html__( 'Disable', 'inhype-ta' ),
      '1'   => esc_html__( 'Enable', 'inhype-ta' )
    ),
  ));

  $cmb_page_settings->add_field( array(
    'name'             => esc_html__( 'Page header width', 'inhype-ta' ),
    'desc'             => '',
    'id'               => $prefix . 'page_header_width',
    'type'             => 'select',
    'show_option_none' => false,
    'default'          => '',
    'desc' => esc_html__( 'Use if you uploaded header image for page.', 'inhype-ta' ),
    'options'          => array(
      '' => esc_html__( 'Use theme settings', 'inhype-ta' ),
      'fullwidth'   => esc_html__( 'Fullwidth', 'inhype-ta' ),
      'boxed'   => esc_html__( 'Boxed', 'inhype-ta' ),
    ),
  ));

  // POST/PAGE HEADER BACKGROUND METABOX
  $cmb_post_header_settings = new_cmb2_box( array(
    'id'           => $prefix . 'post_header_settings_metabox',
    'title'        => esc_html__( 'Header Background', 'inhype-ta' ),
    'object_types' => array( 'post', 'page' ), // Post type
    'context'      => 'normal',
    'priority'     => 'high',
    'show_names'   => true, // Show field names on the left
  ) );

  $cmb_post_header_settings->add_field( array(
    'name'         => esc_html__( 'Header Background image', 'inhype-ta' ),
    'desc'         => esc_html__( 'Used with post header layout "In header" set in Theme Settings. Will be displayed in post header or transparent header.', 'inhype-ta' ),
    'id'           => $prefix . 'header_image',
    'type'         => 'file',
    'options' => array(
        'url' => false, // Hide the text input for the url
        'add_upload_file_text' => 'Select or Upload Image'
    ),
    'preview_size' => array( 100, 100 ), // Default: array( 50, 50 )
  ) );

  $cmb_post_header_settings->add_field( array(
    'name' => esc_html__( 'Enable parallax effect', 'inhype-ta' ),
    'desc' => esc_html__( 'For posts this effect will work only for "In header" layouts.', 'inhype-ta' ),
    'id'   => $prefix . 'header_parallax',
    'type' => 'checkbox',
  ) );

  // POST FORMAT SETTINGS METABOX
  $cmb_post_format_settings = new_cmb2_box( array(
    'id'           => $prefix . 'post_format_settings_metabox',
    'title'        => esc_html__( 'Post Formats options', 'inhype-ta' ),
    'object_types' => array( 'post' ), // Post type
    'context'      => 'normal',
    'priority'     => 'high',
    'show_names'   => true, // Show field names on the left
  ) );

  $cmb_post_format_settings->add_field( array(
    'name'         => wp_kses_post(__( 'Gallery images<br> (for <i>Gallery</i> post format).', 'inhype-ta' )),
    'desc'         => esc_html__( 'Use this field to add your images for gallery in Gallery post format. Use SHIFT/CTRL keyboard buttons to select multiple images.', 'inhype-ta' ),
    'id'           => $prefix . 'gallery_file_list',
    'type'         => 'file_list',
    'preview_size' => array( 100, 100 ), // Default: array( 50, 50 )
  ) );

  $cmb_post_format_settings->add_field( array(
    'name' => wp_kses_post(__( 'Video url<br> (for <i>Video</i> post format)', 'inhype-ta' )),
    'desc' => esc_html__( 'Enter a Youtube, Vimeo, Flickr, TED or Vine video page url for Video post format.', 'inhype-ta' ),
    'id'   => $prefix . 'video_embed',
    'type' => 'oembed',
  ) );

  $cmb_post_format_settings->add_field( array(
    'name' => wp_kses_post(__( 'Audio url<br> (for <i>Audio</i> post format)', 'inhype-ta' )),
    'desc' => esc_html__( 'Enter a SoundCloud, Mixcloud, Rdio or Spotify audio page url for Audio post format.', 'inhype-ta' ),
    'id'   => $prefix . 'audio_embed',
    'type' => 'oembed',
  ) );

  // SUGGESTED POST METABOX
  $cmb_post_worthreading_settings = new_cmb2_box( array(
    'id'           => $prefix . 'post_worthreading_settings_metabox',
    'title'        => esc_html__( 'Suggested posts for "Worth reading" block', 'inhype-ta' ),
    'object_types' => array( 'post' ), // Post type
    'context'      => 'normal',
    'priority'     => 'high',
    'show_names'   => false, // Show field names on the left
  ) );

  $cmb_post_worthreading_settings->add_field( array(
    'name'    => esc_html__( 'Suggested posts', 'inhype-ta' ),
    'desc'    => esc_html__( 'Click "+" or drag and drop post to "Attached posts" table to add it. One from selected posts will be randomly displayed in "Worth reading" block on single post page, if you enabled this feature in Theme Settings.', 'inhype-ta' ),
    'id'      => $prefix . 'worthreading_posts',
    'type'    => 'custom_attached_posts',
    'column'  => true, // Output in the admin post-listing as a custom column. https://github.com/CMB2/CMB2/wiki/Field-Parameters#column
    'options' => array(
      'show_thumbnails' => true, // Show thumbnails on the left
      'filter_boxes'    => true, // Show a text box for filtering the results
      'query_args'      => array(
        'posts_per_page' => 10,
        'post_type'      => 'post',
      ), // override the get_posts args
    ),
  ) );

}
endif;
add_action( 'cmb2_init', 'inhype_register_post_format_settings_metabox' );
