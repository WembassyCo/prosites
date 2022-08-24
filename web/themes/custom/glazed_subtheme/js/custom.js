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
    anchorScroll: function(elem) {
      var bodyTop = document.getElementById("rocket").style.paddingTop;
      bodyTop = (bodyTop != '') ? parseInt(bodyTop.replace('px', '')) : 0;
      var scrollNav = ($('.product-nav').length > 0) ? $('.product-nav').outerHeight() : 0;
      
      if($(elem).length > 0) {
        $('html, body').animate({
          scrollTop: $(elem).offset().top - bodyTop - scrollNav
        }, 1000);
      } else {
        $('html, body').animate({
          scrollTop: 0
        }, 1000);
      }
    },
    productTabs: function() {
      // Hide tabs if empty
      var tabs = $('.product-nav');
      if($('#block-views-block-product-related-block-1').length == 0) {
        tabs.find('a[href="#block-views-block-product-related-block-1"]').parent().remove();
      }
      
    },
    attach: function (context, settings) {
      once('glazed_custom', 'html', context).forEach(function (element) {
        console.log('run glazed_custom js once');

        setInterval(function () {
          Drupal.behaviors.glazed_custom.init_classic_menu_resize();
        }, 100);

        if(jQuery('div').hasClass('home-scroll')){
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

        if(jQuery('div').hasClass('owl-slider-wrapper')){
          $(".owl-slider-wrapper").owlCarousel({
           loop:true,
           autoplay:true,
           autoplayTimeout:5000,
           autoplayHoverPause:true,
           animateIn: 'fadeIn', 
           animateOut: 'fadeOut', 
           items:1,
         });
        }

        // For Drag and Drop block, if there is no image we will show the first column in full width.
        if($('section[id*="rocketlandingcontentwithimage"]').length > 0) {
          var emptyView = $('.view-landing-image .view-content > .views-row');
          var image = $('.view-landing-image .views-field-field-main-photo > .field-content');
          if(image.html() == '' || emptyView.html() == '') {
            $('.view-landing-image').parents('section[id*="rocketlandingcontentwithimage"]').addClass('fullwidth-onecol');
          }
        }

        // Product Nav Section
        var nav = $('.product-nav');
        if (nav.length) {
          var fixmeTop = $('.product-nav').offset().top;
          Drupal.behaviors.glazed_custom.productTabs();
          
          $(window).scroll(function() {
            var currentScroll = $(window).scrollTop();
            if (currentScroll >= fixmeTop) {
              $('.product-nav').addClass( "fixed" );
            } else {
              $('.product-nav').removeClass( "fixed" );
            }
          });

          $('.az-section.product-nav a').on('click', function(ev) {
            ev.preventDefault();
            $('.az-section.product-nav a').removeClass('active');
            $(this).addClass('active');
            var target = $(this).attr('href');
            Drupal.behaviors.glazed_custom.anchorScroll(target);
          });
        }
      });

      $(document).ready(function () {
        var page_title=$("h1.page-title").html();
        /*$("body.path-search h1.page-title").empty().html('<span property="schema:name">'+page_title+'</span>');*/
        $("body.path-search h1.page-title").empty().html('<span property="schema:name">PAGES</span>');
      });

	  $( window ).on("load", function() {
		 if($('.custom-home-page').length > 0) {
			$('.custom-home-page').owlCarousel({
			  items: 1,
			  loop: true,
			  margin: 0,
			  nav: false,
			  autoplay: 5000
			});
		 }
	  });

      $(window).resize(function () {
        Drupal.behaviors.glazed_custom.init_classic_menu_resize();
      });
    }
  }
})(jQuery, Drupal);
