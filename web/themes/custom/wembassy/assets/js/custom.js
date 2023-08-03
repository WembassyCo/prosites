(function ($, Drupal) {

  Drupal.behaviors.wembassy = {
    setupEqualHeight: function () {
      // Setup equal height for each carousel
      var finalHeight = 0;
      $('.owl-carousel .owl-item').each(function (index) {
        var thisHeight = $(this).height();
        console.log(thisHeight);
        finalHeight = (finalHeight < thisHeight) ? thisHeight : finalHeight;
        $(this).height(finalHeight);
      });
      $('.owl-carousel .owl-item').height(finalHeight);
    },
    attach: function (context, settings) {
      $(document).ready(function () {
        Drupal.behaviors.wembassy.setupEqualHeight();
      });
    }
  }
})(jQuery, Drupal);