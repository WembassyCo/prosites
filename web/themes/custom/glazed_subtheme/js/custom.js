(function ($, Drupal) {
  Drupal.behaviors.glazed_custom = {
    init_classic_menu_resize: function () {
      if ($(window).width() <= 1024) {
        //$(".nav-collapse").addClass("collapse");
        $("#navbar").addClass("mobile-on");
      } else if ($(window).width() > 1023) {
        //$(".nav-collapse").removeClass("collapse");
        $("#navbar").removeClass("mobile-on");
      }
    },
    anchorScroll: function (elem) {
      var bodyTop = document.getElementById("rocket").style.paddingTop;
      bodyTop = (bodyTop != '') ? parseInt(bodyTop.replace('px', '')) : 0;
      var scrollNav = ($('.product-nav').length > 0) ? $('.product-nav').outerHeight() : 0;

      if ($(elem).length > 0) {
        $('html, body').animate({
          scrollTop: $(elem).offset().top - bodyTop - scrollNav
        }, 1000);
      } else {
        $('html, body').animate({
          scrollTop: 0
        }, 1000);
      }
    },
    productTabs: function () {
      // Hide tabs if empty
      var tabs = $('.product-nav');
      if ($('#block-views-block-product-related-block-1').length == 0) {
        tabs.find('a[href="#block-views-block-product-related-block-1"]').parent().remove();
      }

    },
    attach: function (context, settings) {
      once('glazed_custom', 'html', context).forEach(function (element) {
        console.log('run glazed_custom js once');

        setInterval(function () {
          Drupal.behaviors.glazed_custom.init_classic_menu_resize();
        }, 100);

        if ($('div').attr('id') === "block-rockettabs") {
          if (jQuery('div').hasClass('home-scroll')) {
            var navpos = $('#block-rockettabs .home-scroll').offset();

            $(window).bind('scroll', function () {
              if ($(window).scrollTop() > navpos.top) {
                $('#block-rockettabs .home-scroll').addClass('fixed');
              } else {
                $('#block-rockettabs .home-scroll').removeClass('fixed');
              }
            });
          }
        }

        
        if (jQuery('body').hasClass('node-1826')) {
          var navpos1826 = $('.node-1826 .home-scroll').offset();
          $(window).bind('scroll', function () {
            if ($(window).scrollTop() > navpos1826.top) {
              $('.node-1826 .home-scroll').addClass('fixed');
            } else {
              $('.node-1826 .home-scroll').removeClass('fixed');
            }
          });
        }

        if (jQuery('div').hasClass('owl-slider-wrapper')) {
          $(".owl-slider-wrapper").owlCarousel({
            loop: true,
            autoplay: true,
            autoplayTimeout: 5000,
            autoplayHoverPause: true,
            animateIn: 'fadeIn',
            animateOut: 'fadeOut',
            items: 1,
          });
        }

        if(jQuery('div').hasClass('owl-carousel-block_116')) {
          $(".owl-carousel").owlCarousel({
            loop: false,
            autoplay: true,
            autoplayTimeout: 4000,
            autoplayHoverPause: false,
            items: 4,
            nav: false,
            dots: false,
            responsiveClass: true,
            responsive: {
              0: {
                items: 1,
                nav: true
              },
              768: {
                items: 3,
                nav: false
              },
              1179: {
                items: 4,
                nav: false
              }
            }
          });
        }

        // Custom content fade in effect
        $(window).scroll(function () {
          $('.fadeIn').each(function (i) {
            var elementHeight = ($(this).outerHeight() / 2);
            var bottom_of_element = $(this).offset().top + $(this).outerHeight();
            var bottom_of_window = $(window).scrollTop() + $(window).height();

            if (bottom_of_window > (bottom_of_element - elementHeight)) {
              $(this).animate({ 'opacity': '1' }, 1000);
            }
          });
        });

        // For Drag and Drop block, if there is no image we will show the first column in full width.
        if ($('section[id*="rocketlandingcontentwithimage"]').length > 0 &&
          $('body.node-139').length == 0) {
          var emptyView = $('.view-landing-image .view-content > .views-row');
          var image = $('.view-landing-image .views-field-field-main-photo > .field-content');
          if (image.html() == '' || emptyView.html() == '') {
            $('.view-landing-image').parents('section[id*="rocketlandingcontentwithimage"]').addClass('fullwidth-onecol');
          }
        }

        // Product Nav Section
        var nav = $('.product-nav');
        if (nav.length) {
          var fixmeTop = $('.product-nav').offset().top;
          Drupal.behaviors.glazed_custom.productTabs();

          $(window).scroll(function () {
            var currentScroll = $(window).scrollTop();
            if (currentScroll >= fixmeTop) {
              $('.product-nav').addClass("fixed");
            } else {
              $('.product-nav').removeClass("fixed");
            }
          });

          $('.az-section.product-nav a').on('click', function (ev) {
            ev.preventDefault();
            $('.az-section.product-nav a').removeClass('active');
            $(this).addClass('active');
            var target = $(this).attr('href');
            Drupal.behaviors.glazed_custom.anchorScroll(target);
          });
        }
      });

      $(document).ready(function () {
        var page_title = $("h1.page-title").html();
        /*$("body.path-search h1.page-title").empty().html('<span property="schema:name">'+page_title+'</span>');*/
        $("body.path-search h1.page-title").empty().html('<span property="schema:name">PAGES</span>');
      });

      $(window).on("load", function () {

      });

      $(window).resize(function () {
        Drupal.behaviors.glazed_custom.init_classic_menu_resize();
      });
    }
  }
})(jQuery, Drupal);

(function ($, Drupal) {
  "use strict";
  var hidden_loader = 1;
  function hide_loader() {
    if (hidden_loader) {
      // Page loader
      $(".page-loader div").delay(0).fadeOut();
      $(".page-loader").delay(600).fadeOut("slow");
      hidden_loader = 0;
    }
  }

  setTimeout(hide_loader, 5000);

  $(window).on("load", function () {
    hide_loader();

    if (jQuery('#block-supplier-menu-block').length > 0) {
      var blocksuppliermenublock = jQuery('#block-supplier-menu-block');

      blocksuppliermenublock.find('li').each(function () {
        if (jQuery(this).hasClass('dropdown') == true) {
          jQuery(this).removeClass('dropdown');
        }
      });

      blocksuppliermenublock.find('a').each(function () {
        if (jQuery(this).hasClass('dropdown-toggle') == true) {
          jQuery(this).removeClass('dropdown-toggle');
        }
      });

      blocksuppliermenublock.find('ul').each(function () {
        if (jQuery(this).hasClass('dropdown-menu') == true) {
          jQuery(this).removeClass('dropdown-menu');
        }
      });

      blocksuppliermenublock.find('span').each(function () {
        if (jQuery(this).hasClass('caret') == true) {
          jQuery(this).remove();
        }
      });


    }

  });



  Drupal.behaviors.initColorboxPlainStyle = {
    attach: function (context, settings) {
      $(context).bind('cbox_complete', function () {

        function addLink() {
          if ($('#cboxDownload').length) {
            $('#cboxDownload').remove();
          }
          var fullHref = $('#cboxLoadedContent > img').attr('src').replace(/styles\/large\/public\//, '');
          var fullLink = $('<a/>');
          fullLink.attr('href', fullHref);
          fullLink.attr('target', 'new');
          fullLink.attr('download', '');
          fullLink.attr('title', 'Right click to download');
          fullLink.addClass("download_link");
          $('#cboxClose').before(fullLink);
          $('.download_link').wrap('<div id="cboxDownload"></div>');
        }

        if ($('#cboxLoadedContent > img').attr('src')) {
          addLink();
        }

      });
    }
  };

})(jQuery, Drupal);

