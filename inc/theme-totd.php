<?php
/*
Tip of the day
*/
if (!function_exists('inhype_totd_checker')) :
function inhype_totd_checker() {
  ?>
  <script type="text/javascript" >

  (function($){

  $(document).ready(function($) {

    $.getJSON('//api.magniumthemes.com/rest/index.php?act=getTOTD', function(data){

        var items = data.tips;

        var data = {
          action: 'inhype_totd_checker_cache',
          tips: JSON.stringify(items)
        };

        $.post( ajaxurl, data, function(response) {

        });

    });

  });

  })(jQuery);
  </script>
  <?php

  // Update update cache after time
  update_option('inhype_totd_cache_date', strtotime("+5 days"));

}

if(strtotime("now") > get_option( 'inhype_totd_cache_date', 0 )) {
    add_action('admin_print_footer_scripts', 'inhype_totd_checker', 99);
}

endif;

/**
 * Ajax update totd cacher
 */
if (!function_exists('inhype_totd_checker_cache_callback')) :
function inhype_totd_checker_cache_callback() {
    $tips = $_POST['tips'];

    update_option('inhype_totd_cache_tips', $tips);

    wp_die();
}
add_action('wp_ajax_inhype_totd_checker_cache', 'inhype_totd_checker_cache_callback');
endif;

/**
 * Display totd notification
 */
if (!function_exists('inhype_totd_display')) :
function inhype_totd_display() {

    // Hide update notice
    if(isset($_GET['totd-notify-dismiss'])) {
        $notify_id = 'dismiss-totd-notify-id-'.$_GET['totd-notify-dismiss'];
        update_option($notify_id, 1);
    }

    $tips = get_option('inhype_totd_cache_tips');

    if($tips) {
      $tips_array = json_decode( html_entity_decode( stripslashes ($tips ) ), true );

      $tip  = $tips_array[mt_rand(0, count($tips_array) - 1)];

      if(!empty($tip['button_url']) && !empty($tip['button_title'])) {
        $tip_button_html = '<a href="'.esc_url($tip['button_url']).'" target="_blank" class="button button-primary">'.$tip['button_title'].'</a>';
      } else {
        $tip_button_html = '';
      }

      // Tip was not dismissed before
      if(!get_option('dismiss-totd-notify-id-'.$tip['id'])) {
        $message_html = '<div class="notice notice-'.esc_attr($tip['type']).'"><p><strong>'.esc_html__('Daily tip: ', 'inhype-ta').'</strong>'.$tip['text'].'</p>
        <p>
        '.$tip_button_html.'
        <a href="'.esc_url( add_query_arg( 'totd-notify-dismiss', esc_html($tip['id']))).'" class="button button-secondary">'.esc_html__('Dismiss tip', 'inhype-ta').'</a><a href="'.esc_url('themes.php?page=inhype_system_information&act=disabletotd').'" class="notice-dismiss"></a></p></div>';

        echo $message_html;
      }

    }

}
add_action( 'admin_notices', 'inhype_totd_display' );
endif;
