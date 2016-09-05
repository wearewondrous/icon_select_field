(function ($, Drupal) {
  'use strict';

  function formatOption(icon) {
    if (!icon.id) {
      return icon.text;
    }

    var $icon = $(
      '<span class="icon-preview">' + icon.element.getAttribute('data-icon-tag') + icon.text + '</span>'
    );

    return $icon;
  }

  function init(context) {
    var $selects = $('select.icon-select-field', context);

    if (!$selects.length) {
      return;
    }

    $selects.select2({
      templateSelection: formatOption,
      templateResult: formatOption
    });
  }

  Drupal.behaviors.iconSelectField = {
    attach: init
  };
})(jQuery, Drupal);
