jQuery(function ($) {
  'use strict';

  var $selects = $('select.icon-select-field');

  if (!$selects.length) {
    return;
  }

  function formatOption(icon) {
    if (!icon.id) {
      return icon.text;
    }

    var $icon = $(
      '<span class="icon-preview">' + icon.element.getAttribute('data-icon-tag') + icon.text + '</span>'
    );

    return $icon;
  }

  $selects.select2({
    templateSelection: formatOption,
    templateResult: formatOption
  });
});
