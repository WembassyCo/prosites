jQuery(window).resize(function () {
	init_classic_menu_resize();
});

function init_classic_menu_resize() {

	if (jQuery(window).width() <= 1024) {
		//jQuery(".nav-collapse").addClass("collapse");
		jQuery("#navbar").addClass("mobile-on");
	} else if (jQuery(window).width() > 1023) {
		//jQuery(".nav-collapse").removeClass("collapse");
		jQuery("#navbar").removeClass("mobile-on");
	}
}

jQuery(document).ready(function () {
	setInterval(function () {
		init_classic_menu_resize();
	}, 100);
});

jQuery(window).on("load", function () {
	init_classic_menu_resize();
});

jQuery(document).ready(function () {
	if(jQuery('body').hasClass('.home-scroll')){
		var navpos = jQuery('#block-rockettabs .home-scroll').offset();
		console.log(navpos.top);
		jQuery(window).bind('scroll', function () {
			if (jQuery(window).scrollTop() > navpos.top) {
				jQuery('#block-rockettabs .home-scroll').addClass('fixed');
				// jQuery('#topnav').removeClass('fixed');
			} else {
				// jQuery('#topnav').addClass('fixed');
				jQuery('#block-rockettabs .home-scroll').removeClass('fixed');
			}
		});
	}
	jQuery('.custom-home-page').owlCarousel({
            items: 1,
            loop: true,
            margin: 0,
            nav: false,
            autoplay: 5000
        });
	
	/*jQuery('.landing-carasoual-image .view-content .field-content').owlCarousel({
		loop:true,
		margin:10,
		nav:true,
		responsive:{
			0:{
				items:1
			}
		}
	});*/
	
});

/*(function ($, Drupal) {
	Drupal.behaviors.slider = {
		attach: function (context, settings) {
			exists = Object.values(Drupal.behaviors).includes("owl");
			if(exists){
			// $('.landing-carasoual-image .view-content .field-content').slick({
				// infinite: true,
				// slidesToShow: 1,
				// slidesToScroll: 1,
				// autoplay: true,
				// arrows: true,
				// autoplaySpeed: 3000
			// });
			$('.landing-carasoual-image .view-content .field-content').owlCarousel({
				loop:true,
				margin:10,
				nav:true,
				responsive:{
					0:{
						items:1
					}
				}
			})
			//$('.page-node-type-landing-page .owl-carousel').css("display", "inline-block");
			}
			
		}
	};
})(jQuery, Drupal);*/
/*(function($){
        $('.custom-home-page').owlCarousel({
            items: 1,
            loop: true,
            margin: 0,
            nav: false,
            autoplay: 5000
        });
    
});*/
 
    