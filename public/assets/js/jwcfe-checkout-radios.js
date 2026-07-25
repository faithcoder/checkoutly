(function ($) {
  'use strict';

  var cfg = window.jwcfe_checkout_radios || {
    selectDataAttr: 'data-jwcfe-type',
    selectDataVal: 'radio',
  };

  function isJwcfeAdditionalSelect(select) {
    var name = select.getAttribute('name') || '';
    if (/^(contact|order|billing|shipping|jwcfe-block)_/i.test(name)) {
      return true;
    }
    return name.indexOf('jwcfe-block/') === 0;
  }

  function removeBlankSelectOption(select) {
    if (!select || select.getAttribute('data-jwcfe-type') === 'radio') {
      return;
    }
    if (!isJwcfeAdditionalSelect(select)) {
      return;
    }

    var removed = false;
    Array.prototype.forEach.call(select.options, function (opt) {
      if (opt.value === '' && !String(opt.textContent || '').trim()) {
        opt.remove();
        removed = true;
      }
    });

    if (removed && select.options.length && select.selectedIndex < 0) {
      select.selectedIndex = 0;
      try {
        select.dispatchEvent(new Event('input', { bubbles: true }));
        select.dispatchEvent(new Event('change', { bubbles: true }));
      } catch (e) {
        $(select).trigger('change');
      }
    }
  }

  function cleanupJwcfeSelects() {
    document
      .querySelectorAll('select.wc-blocks-components-select__select')
      .forEach(function (select) {
        removeBlankSelectOption(select);
      });
  }

  function safeNameFromId(id) {
    return id.replace(/[^a-zA-Z0-9_\-:\[\]\/]/g, '_');
  }

  function getFieldDescription(el) {
    if (!el) {
      return '';
    }
    return el.getAttribute('data-jwcfe-description') || '';
  }

  function findFieldRoot(el) {
    var $el = $(el);
    var $root = $el.closest(
      '.wc-blocks-components-select, .wc-block-components-text-input, .wc-block-components-checkbox, .wc-block-components-combobox'
    );

    if ($root.length) {
      return $root;
    }

    return $el.closest('.has-error').length ? $el.closest('.has-error') : $el.parent();
  }

  function renderFieldDescription(el) {
    var description = getFieldDescription(el);

    if (!description) {
      return;
    }

    var $el = $(el);

    if ($el.data('jwcfe-desc-rendered')) {
      return;
    }

    var $fieldRoot = findFieldRoot(el);

    if (!$fieldRoot.length) {
      return;
    }

    if ($fieldRoot.find('.jwcfe-field-description').length) {
      $el.data('jwcfe-desc-rendered', true);
      return;
    }

    var descId = (el.id || 'jwcfe-field') + '-description';
    var $desc = $('<p class="jwcfe-field-description" />')
      .attr('id', descId)
      .text(description);

    if ($fieldRoot.hasClass('jwcfe-radio-field')) {
      var $radioGroup = $fieldRoot.find('.jwcfe-radio-replacement');
      if ($radioGroup.length) {
        $radioGroup.after($desc);
      } else {
        $fieldRoot.append($desc);
      }
    } else {
      $fieldRoot.append($desc);
    }

    if (el.id) {
      var describedBy = el.getAttribute('aria-describedby');
      el.setAttribute(
        'aria-describedby',
        describedBy ? describedBy + ' ' + descId : descId
      );
    }

    $el.data('jwcfe-desc-rendered', true);
  }

  function renderAllDescriptions() {
    document.querySelectorAll('[data-jwcfe-description]').forEach(function (el) {
      renderFieldDescription(el);
    });
  }

  function convertSelectToRadios($select) {
    if ($select.data('jwcfe-converted')) {
      return;
    }
    $select.data('jwcfe-converted', true);

    var id = $select.attr('id');
    if (!id) {
      id = 'jwcfe-' + Math.random().toString(36).substr(2, 9);
      $select.attr('id', id);
    }

    var name = $select.attr('name');
    if (!name || name === '') {
      name = safeNameFromId(id);
      $select.attr('name', name);
    }

    var $root = $select.closest('.wc-blocks-components-select');
    var $container = $select.closest('.wc-blocks-components-select__container');
    var $label = $container.find('.wc-blocks-components-select__label').first();

    if (!$label.length) {
      $label = $root.find('.wc-blocks-components-select__label').first();
    }

    var labelId = id + '-label';
    if ($label.length) {
      if (!$label.attr('id')) {
        $label.attr('id', labelId);
      } else {
        labelId = $label.attr('id');
      }
    } else {
      labelId = '';
    }

    var $wrapper = $('<div class="jwcfe-radio-replacement" role="radiogroup" />').attr(
      'data-for',
      id
    );
    if (labelId) {
      $wrapper.attr('aria-labelledby', labelId);
    }

    $select.find('option').each(function (i, opt) {
      var $opt = $(opt);
      var val = $opt.attr('value');

      if ($opt.prop('disabled') || typeof val === 'undefined' || val === '') {
        return;
      }

      var optionLabel = $opt.text() || val;
      var radioId = id + '-opt-' + i;

      var $labelEl = $('<label class="jwcfe-radio-item" />').attr('for', radioId);
      var $input = $('<input type="radio" />').attr({
        id: radioId,
        name: name,
        value: val,
      });

      if ($opt.prop('selected')) {
        $input.prop('checked', true);
      }

      $input.on('change', function () {
        if (!$(this).is(':checked')) {
          return;
        }

        try {
          var sel = $select[0];
          if (sel) {
            sel.value = val;
            for (var j = 0; j < sel.options.length; j++) {
              sel.options[j].selected = sel.options[j].value == val;
            }
            sel.dispatchEvent(new Event('input', { bubbles: true }));
            sel.dispatchEvent(new Event('change', { bubbles: true }));
          }
        } catch (e) {
          $select.val(val).trigger('change');
        }

        $wrapper.find('input[type="radio"]').each(function () {
          $(this).attr('aria-checked', $(this).is(':checked') ? 'true' : 'false');
        });
      });

      $labelEl
        .append($input)
        .append(' ')
        .append($('<span class="jwcfe-radio-label-text" />').text(optionLabel));
      $wrapper.append($labelEl);
    });

    if ($root.length) {
      $root.addClass('jwcfe-radio-field');
    }

    // Move field label above radios (Woo leaves it absolutely positioned inside the select box).
    if ($label.length && $root.length) {
      $label.prependTo($root);
    }

    if ($container.length) {
      $container.addClass('jwcfe-select-container--converted');
      $container.after($wrapper);
    } else {
      $select.after($wrapper);
    }

    $select.addClass('jwcfe-hidden-select');
    $container.find('.wc-blocks-components-select__expand').hide();

    renderFieldDescription($select[0]);
  }

  function findAndConvert() {
    cleanupJwcfeSelects();
    var attr = cfg.selectDataAttr;
    var val = cfg.selectDataVal;
    if (!attr) {
      return;
    }
    var selector = 'select[' + attr + '="' + val + '"]';
    $(selector).each(function () {
      convertSelectToRadios($(this));
    });

    renderAllDescriptions();
  }

  function initObserver() {
    var observer = new MutationObserver(function () {
      findAndConvert();
      renderAllDescriptions();
    });
    observer.observe(document.body, { childList: true, subtree: true });
    $(function () {
      findAndConvert();
    });
  }

  initObserver();
})(jQuery);
