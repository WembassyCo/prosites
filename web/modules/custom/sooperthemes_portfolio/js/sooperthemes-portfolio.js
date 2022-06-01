/**
 * @file
 * Sooperthemes portfolio behaviors.
 */

(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.sooperthemesPortfolio = {
    attach: function (context, settings) {

      var portfolios = settings.stPortfolios;
      $('.js-sp-wrapper').each(function () {
        var id = this.id;

        var $container = $(this).find('.js-sp-container');
        if (portfolios[id]) {

          var config = portfolios[id];

          var inline = $container.data('content-page') === 'inline';
          var callback = inline ? 'singlePageInlineCallback' : 'singlePageCallback';
          var updateCallback = inline ? 'updateSinglePageInline' : 'updateSinglePage';

          config[callback] = function (item, element) {
            var that = this;
            $.ajax({
              url: settings.path.baseUrl + 'node/' + item + '/portfolio/' + $(element).data('display-mode'),
              type: 'GET',
              dataType: 'html',
              timeout: 3000
            })
            .done(function (result) {
              that[updateCallback](result);
            })
            .fail(function () {
              that[updateCallback]('Error! Please refresh the page!');
            });
          };

          $container.once(id).cubeportfolio(config)
        }
      });

    }
  };

} (jQuery, Drupal));
