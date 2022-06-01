/**
 * @file
 */

(function ($, _, settings) {
  Drupal.behaviors.initSooperThemesPortfoliokAdmin = {
    attach: function (context, settings) {
      window.wpColorPickerL10n = {
        "clear": Drupal.t("Clear"),
        "defaultString": Drupal.t("Default"),
        "pick": Drupal.t("Select Color"),
        "current": Drupal.t("Current Color")
      }
      $els = $('input[data-drupal-selector="edit-row-options-caption-color"], input[data-drupal-selector="edit-row-options-caption-text-color"]', context);
      $els.once('iris-color-picker').wpColorPicker();
    }
  };
})(jQuery, _, drupalSettings);
