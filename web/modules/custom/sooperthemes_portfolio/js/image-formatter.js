/**
 * @file
 * Sooperthemes portfolio image formatter behavior.
 */

(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.sooperthemesPortfolioImageFormatter = {
    attach: function (context, settings) {

      // Init dummy portfolio.
      $('<div/>').once('sp-formatter').cubeportfolio(
        // Make counter translatable.
        {lightboxCounter: '<div class="cbp-popup-lightbox-counter">{{current}} ' + Drupal.t('of') + ' {{total}}</div>'}
      );
      // Disable clicks on caption so that caption doesn't block lightbox clicks
      $('a.cbp-lightbox').each(function() {
        $(this).parents('.cbp-caption')
          .find('.cbp-caption-activeWrap')
          .once('sp-formatter-pointeractions')
          .addClass('sp-formatter-pointeractions')
          .css('pointer-events', 'none');
      });

    }
  };

} (jQuery, Drupal));
