/**
 * @file
 * Global utilities.
 *
 */
(function ($, Drupal) {

  'use strict';

  //////////////// CKEditor settings
  Drupal.behaviors.stackCkeditor = {
    attach: function (context, settings) {
      // Alter CKEditor config to allow empty tags
      if (typeof CKEDITOR !== "undefined") {
        CKEDITOR.dtd.$removeEmpty['i'] = false;
        CKEDITOR.dtd.$removeEmpty['span'] = false;
        console.log('Ckeditor log', CKEDITOR.dtd);
      }
    }
  };

  //////////////// Disable input on Layout Paragraph Builder
  Drupal.behaviors.stackLPB = {
    attach: function (context, settings) {      
      $('.js-lpb-component .block-contact textarea, .js-lpb-component .block-contact input, .js-lpb-component .block-simplenews input, .js-lpb-component .block-user input').each(function() {
        var thisElement = $(this);
        thisElement.attr('disabled', 'disabled');
    });
    }
  };

  //////////////// Move form labels to placeholders
  Drupal.behaviors.stackForms = {
    attach: function (context, settings) {   
      $("form.contact-form :input, form.user-form :input, form.user-login-form :input, form.user-pass :input, .block-simplenews form :input").each(function(index, elem) {
        var eId = $(elem).attr("id");
        var label = null;
        if (eId && (label = $(elem).parents("form").find("label[for="+eId+"]")).length == 1) {
            $(elem).attr("placeholder", $(label).html());
            $(label).remove();
        }      
      });
    }
  };
})(jQuery, Drupal);
