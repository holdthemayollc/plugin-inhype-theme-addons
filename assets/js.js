/**
* Plugin main Frontend JavaScript file
*/
(function($){
$(document).ready(function() {

  'use strict';

  // Calculations in Single post page
  if($('.single-post').length > 0) {
    var post_container_height = $('.blog-post.blog-post-single').height();
    var post_container_top = $('.blog-post.blog-post-single').offset().top;
    var post_container_bottom = post_container_top + post_container_height;
  }

  // Post reading progress for single post page
  if($('.single-post .blog-post-reading-progress').length > 0) {

    var current_position_inside_post = 0;
    var current_progress = 0;

    $(window).scroll(function () {

      current_position_inside_post = $(window).scrollTop() - post_container_top;
      current_progress = current_position_inside_post * 100 / post_container_height;

      if(current_progress > 100) {
        current_progress = 100;
      }

      if(current_progress < 0) {
        current_progress = 0;
      }

      $('.blog-post-reading-progress').width(current_progress + '%');

    });
  }

  // Fixed social share for single post page
  if($('.single-post .inhype-social-share-fixed').length > 0) {

    var current_position_inside_post2 = 0;
    var current_progress2 = -1;

    inhype_fixedSocialWorker();

    $(window).scroll(function () {

      inhype_fixedSocialWorker();

    });
  }

  function inhype_fixedSocialWorker() {

    if(post_container_height > $('.single-post .inhype-social-share-fixed').height()) {
      current_position_inside_post2 = $(window).scrollTop() - post_container_top;

      current_progress2 = current_position_inside_post2 * 100 / (post_container_height - $('.single-post .inhype-social-share-fixed').height());

      if(current_progress2 > 100) {

        $('.single-post .inhype-social-share-fixed').css('position', 'absolute');
        $('.single-post .inhype-social-share-fixed').css('bottom', 0);
        $('.single-post .inhype-social-share-fixed').css('top', 'auto');
        $('.single-post .inhype-social-share-fixed').css('opacity', 0);

      } else if(current_progress2 > 90) {
        $('.single-post .inhype-social-share-fixed').css('opacity', 0);
      } else if(current_progress2 < 0) {

        $('.single-post .inhype-social-share-fixed').css('position', 'absolute');
        $('.single-post .inhype-social-share-fixed').css('bottom', 'auto');
        $('.single-post .inhype-social-share-fixed').css('top', 0);
        $('.single-post .inhype-social-share-fixed').css('margin-top', 0);
        $('.single-post .inhype-social-share-fixed').css('opacity', 1);

      } else {

        $('.single-post .inhype-social-share-fixed').css('position', 'fixed');
        $('.single-post .inhype-social-share-fixed').css('bottom', 'auto');
        $('.single-post .inhype-social-share-fixed').css('top', 0);
        $('.single-post .inhype-social-share-fixed').css('margin-top', '200px');
        $('.single-post .inhype-social-share-fixed').css('opacity', 1);

      }
    }

  }

  // Worth reading block display
  if($('.post-worthreading-post-wrapper').length > 0) {

    var current_position_inside_post3 = 0;
    var current_progress3 = -1;

    inhype_WorthReadingWorker();

    $(window).scroll(function () {

      inhype_WorthReadingWorker();

    });
  }

  function inhype_WorthReadingWorker() {

      current_position_inside_post3 = $(window).scrollTop() - post_container_top;

      current_progress3 = current_position_inside_post3 * 100 / post_container_height;

      if(current_progress3 > 70) {

        $('.single-post .post-worthreading-post-wrapper .post-worthreading-post-container').addClass('opened');

      } else {

        $('.single-post .post-worthreading-post-wrapper .post-worthreading-post-container').removeClass('opened');

      }

  }

  $('.single-post .post-worthreading-post-wrapper .post-worthreading-post-container .btn-close').on('click', function(e){
        $('.single-post .post-worthreading-post-wrapper .post-worthreading-post-container').addClass('disabled');
  });

  /**
  * Social share for posts
  */

  function inhype_socialshare(type, post_url, post_title, post_image) {

      switch (type) {
        case 'facebook':
          window.open( 'https://www.facebook.com/sharer/sharer.php?u='+post_url, "facebookWindow", "height=380,width=660,resizable=0,toolbar=0,menubar=0,status=0,location=0,scrollbars=0" );
          break;
        case 'twitter':
          window.open( 'http://twitter.com/intent/tweet?text='+post_title + ' ' + post_url, "twitterWindow", "height=370,width=600,resizable=0,toolbar=0,menubar=0,status=0,location=0,scrollbars=0" );
          break;
        case 'pinterest':
          window.open( 'http://pinterest.com/pin/create/button/?url='+post_url+'&media='+post_image+'&description='+post_title, "pinterestWindow", "height=620,width=600,resizable=0,toolbar=0,menubar=0,status=0,location=0,scrollbars=0" );
          break;
        case 'whatsapp':
          window.open( 'https://api.whatsapp.com/send?text='+post_title+' '+post_url, "whatsupWindow", "height=620,width=600,resizable=0,toolbar=0,menubar=0,status=0,location=0,scrollbars=0" );
          break;
        case 'vk':
          window.open( 'https://vk.com/share.php?url='+post_url+'&title='+post_title+'&description=&image='+post_image, "vkWindow", "height=620,width=600,resizable=0,toolbar=0,menubar=0,status=0,location=0,scrollbars=0" );
          break;
        case 'linkedin':
          window.open( 'https://www.linkedin.com/shareArticle?url='+post_url+'&title='+post_title, "linkedinWindow", "height=620,width=600,resizable=0,toolbar=0,menubar=0,status=0,location=0,scrollbars=0" );
          break;
        case 'reddit':
          window.open( 'https://reddit.com/submit?url='+post_url, "linkedinWindow", "height=620,width=600,resizable=0,toolbar=0,menubar=0,status=0,location=0,scrollbars=0" );
          break;
        default:
          window.location.href=post_url;

          break;

      }

      return false;
  }

  $('body').on('click', '.post-social a', function(e){

      if($(this).data('type') !== 'link') {
          e.preventDefault();
          e.stopPropagation();

          var share_type = $(this).data('type');
          var post_image = $(this).data('image');
          var post_title = encodeURIComponent($(this).data('title'));
          var post_url = $(this).attr('href');

          inhype_socialshare(share_type, post_url, post_title, post_image);
      }

  });


});
})(jQuery);
