(function ($, Drupal) {
  Drupal.behaviors.glazed_custom = {
    init_classic_menu_resize: function() {
      if ($(window).width() <= 1024) {
        //$(".nav-collapse").addClass("collapse");
        $("#navbar").addClass("mobile-on");
      } else if ($(window).width() > 1023) {
        //$(".nav-collapse").removeClass("collapse");
        $("#navbar").removeClass("mobile-on");
      }
    },
    attach: function (context, settings) {
      once('glazed_custom', 'html', context).forEach(function (element) {
        console.log('run glazed_custom js once');

        setInterval(function () {
          Drupal.behaviors.glazed_custom.init_classic_menu_resize();
        }, 100);

        if($('body').hasClass('.home-scroll')) {
          var navpos = $('#block-rockettabs .home-scroll').offset();
          console.log(navpos.top);
          $(window).bind('scroll', function () {
            if ($(window).scrollTop() > navpos.top) {
              $('#block-rockettabs .home-scroll').addClass('fixed');
              // $('#topnav').removeClass('fixed');
            } else {
              // $('#topnav').addClass('fixed');
              $('#block-rockettabs .home-scroll').removeClass('fixed');
            }
          });
        }

        $('.custom-home-page').owlCarousel({
          items: 1,
          loop: true,
          margin: 0,
          nav: false,
          autoplay: 5000
        });

        // For Drag and Drop block, if there is no image we will show the first column in full width.
        if($('section[id*="rocketlandingcontentwithimage"]').length > 0) {
          var emptyView = $('.view-landing-image .view-content > .views-row');
          var image = $('.view-landing-image .views-field-field-main-photo > .field-content');
          if(image.length > 0 || emptyView.length > 0) {
            image.parents('section[id*="rocketlandingcontentwithimage"]').addClass('fullwidth-onecol');
          }
        }
      });

      $(window).resize(function () {
        Drupal.behaviors.glazed_custom.init_classic_menu_resize();
      });
    }
  }
})(jQuery, Drupal);
