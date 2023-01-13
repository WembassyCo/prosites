(function ($, Drupal) {

  Drupal.behaviors.portfolio = {
    setupEqualHeight: function() {
      // Setup equal height for Top Challenges/Top Solution rows
      var challenges = $('.challenges-wrapper .challenges').length;
      var solutions = $('.solutions-wrapper .solutions').length;
      if(challenges > solutions) {
        $('.challenges-wrapper .challenges').each(function(index) {
          var height = $('.solutions-wrapper .solutions').eq(index).height();
          var thisHeight = $(this).height();
          var finalHeight = (height > thisHeight) ? height : thisHeight;
          $(this).height(finalHeight);
          $('.solutions-wrapper .solutions').eq(index).height(finalHeight);
        });
      } else {
        $('.solutions-wrapper .solutions').each(function(index) {
          var height = $('.challenges-wrapper .challenges').eq(index).height();
          var thisHeight = $(this).height();
          var finalHeight = (height > thisHeight) ? height : thisHeight;
          $(this).height(finalHeight);
          $('.challenges-wrapper .challenges').eq(index).height(finalHeight);
        });
      }
    },
    attach: function (context, settings) {
      $('.flexslider').once('portfolio').flexslider({
        animation: "slide"
      });

      Drupal.behaviors.portfolio.setupEqualHeight();

      $(window).on('resize', function() {
        var windowWidth = $(window).width();
        if(windowWidth > 768) {
          $('.solutions-wrapper .solutions, .challenges-wrapper .challenges').removeAttr('style');
          Drupal.behaviors.portfolio.setupEqualHeight();
        } else {
          $('.solutions-wrapper .solutions, .challenges-wrapper .challenges').removeAttr('style');
        }
      });
    }
  }

})(jQuery, Drupal);
