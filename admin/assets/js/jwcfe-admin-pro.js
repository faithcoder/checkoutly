document.addEventListener("DOMContentLoaded", function () {
  const urlParams = new URLSearchParams(window.location.search);
  const tab = urlParams.get("tab");
  const section = urlParams.get("section");

  if (tab === "block" && section === "additional") {
    const elements = document.querySelectorAll(".row_0");

    elements.forEach((el) => (el.style.display = "none"));

    // Check if there are any other rows apart from row_0
    const allRows = document.querySelectorAll(".ui-sortable tr");
    const visibleRows = Array.from(allRows).filter(
      (row) => !row.classList.contains("row_0") && row.style.display !== "none",
    );

    if (visibleRows.length === 0) {
      showNoFieldsMessage();
    }
  }

  function showNoFieldsMessage() {
    const table = document.querySelector(".ui-sortable");

    if (table && !document.querySelector(".no-fields-message")) {
      const messageRow = document.createElement("tr");
      const messageCell = document.createElement("td");

      messageCell.className = "no-fields-message";
      messageCell.setAttribute("colspan", table.rows[0]?.cells.length || 1); // Span all columns
      messageCell.style.textAlign = "center";
      messageCell.style.padding = "10px";
      // messageCell.style.background = "#f8d7da";
      messageCell.style.color = "#721c24";
      messageCell.style.fontWeight = "bold";
      messageCell.textContent =
        "No checkout fields found. Click on Add Field button to create new fields.";

      messageRow.appendChild(messageCell);
      table.appendChild(messageRow); // Append the new row to the table
    }
  }
});

/* ──────────────────────────────────────────────────────────
   JWCFE ACCORDION — Section click to expand / collapse
   ────────────────────────────────────────────────────────── */
document.addEventListener("DOMContentLoaded", function () {

  // Initialize all already-open accordions on page load (set maxHeight so they are visible)
  document.querySelectorAll(".jwcfe-accordion-wrapper.jwcfe-accordion-open").forEach(function (wrapper) {
    var body = wrapper.querySelector(".jwcfe-accordion-body");
    if (body) {
      body.style.maxHeight = "9999px";
      body.style.opacity   = "1";
    }
  });

  // Bind click on every accordion trigger (section card header)
  document.querySelectorAll(".jwcfe-accordion-trigger").forEach(function (trigger) {

    trigger.addEventListener("click", function (e) {
      // Don't collapse when clicking Add Field button or its children
      if (e.target.closest(".jwcfe-section-card-actions")) return;

      var wrapper = trigger.closest(".jwcfe-accordion-wrapper");
      if (!wrapper) return;

      var isOpen = wrapper.classList.contains("jwcfe-accordion-open");

      if (isOpen) {
        // Collapse: measure current height, set it explicitly, then animate to 0
        var body = wrapper.querySelector(".jwcfe-accordion-body");
        if (body) {
          body.style.maxHeight = body.scrollHeight + "px";
          // Force reflow so transition fires
          body.getBoundingClientRect();
          body.style.maxHeight = "0";
          body.style.opacity   = "0";
        }
        wrapper.classList.remove("jwcfe-accordion-open");
      } else {
        // Expand: set maxHeight to scrollHeight, then let CSS transition run
        var body = wrapper.querySelector(".jwcfe-accordion-body");
        wrapper.classList.add("jwcfe-accordion-open");
        if (body) {
          body.style.maxHeight = body.scrollHeight + "px";
          body.style.opacity   = "1";
          // After transition, set back to 9999 so dynamic content (new rows) shows
          body.addEventListener("transitionend", function onEnd(ev) {
            if (ev.propertyName !== "max-height") return;
            body.style.maxHeight = "9999px";
            body.removeEventListener("transitionend", onEnd);
          });
        }
      }
    });
  });
});

// Polyfill for jQuery.isArray
if (typeof jQuery !== "undefined" && !jQuery.isArray) {
  jQuery.isArray = Array.isArray;
}

const getBillingFields = WcfeAdmin.wc_fields.billing;

var jwcfe_settings = (function ($, window, document) {
  // Polyfill for jQuery.isArray
  if (typeof jQuery !== "undefined" && !jQuery.isArray) {
    jQuery.isArray = Array.isArray;
  }
  var MSG_INVALID_NAME = WcfeAdmin.MSG_INVALID_NAME;

  var OPTION_ROW_HTML =
    '<div class="jwcfe-opt-row">' +
    '<div class="jwcfe-opt-input-wrap"><input type="text" name="i_options_key[]" placeholder="Option Value" /></div>' +
    '<div class="jwcfe-opt-input-wrap"><input type="text" name="i_options_text[]" placeholder="Option Text" /></div>' +
    '<div class="jwcfe-opt-actions">' +
    '<a href="javascript:void(0)" onclick="jwcfeAddNewOptionRow(this)" class="jwcfe-opt-btn jwcfe-opt-btn-add" title="Add option">+</a>' +
    '<a href="javascript:void(0)" onclick="jwcfeRemoveOptionRow(this)" class="jwcfe-opt-btn jwcfe-opt-btn-remove" title="Remove">×</a>' +
    '<span class="jwcfe-opt-btn jwcfe-opt-btn-sort ui-jwcf-sortable-handle" onclick="jwcfe_handler_OptionRow(this)" title="Drag to sort">⇅</span>' +
    '</div>' +
    '</div>';

  /*------------------------------------
   *---- ON-LOAD FUNCTIONS - SATRT -----
   *------------------------------------*/

  $(function () {
    $("input[name=fname]").on("input", function () {
      $(this).val(function (_, v) {
        return v.replace(/\s+/g, "");
      });
    });

    $(".jwcfe_tabs").tabs();

    $("select.jwcfe-enhanced-multi-select")
      .select2({
        placeholder: "Please Select",
        minimumResultsForSearch: 10,
        allowClear: true,
      })
      .addClass("enhanced");

    $(".jwcfe_remove_field_btn").on("click", function () {
      // FIX: Old selector only targeted one table. In the new layout there
      // are multiple section tables so we search the whole form.
      $("#jwcfe_checkout_fields_form tbody input:checkbox[name=select_field]:checked").each(function () {
        $(this).closest("tr").remove();
      });
    });

    // FIX: Initialize sortable on EACH section table individually.
    // The old code targeted a single #jwcfe_checkout_fields_form tbody which
    // does not exist in the new multi-section layout.
    $(".ui-sortable").sortable({
      items: "tr",
      cursor: "move",
      axis: "y",
      handle: "td.sort",
      scrollSensitivity: 40,
      helper: function (e, ui) {
        ui.children().each(function () {
          $(this).width($(this).width());
        });

        ui.css("left", "0");
        return ui;
      },
    });

    $(".ui-sortable").on("sortstart", function (event, ui) {
      ui.item.css("background-color", "#f6f6f6");
    });

    $(".ui-sortable").on("sortstop", function (event, ui) {
      ui.item.removeAttr("style");
      jwcfe_prepare_field_order_indexes();
    });
  });

  _saveCustomFieldForm = function saveCustomFieldForm(loaderPath, donePath) {
    var formData = $("#jwcfe_custom_options_form").serializeArray();
    var data = {
      formdata: formData,
      action: "save_custom_form_fields",
      _ajax_nonce: (window.WcfeAdmin && WcfeAdmin.nonce) ? WcfeAdmin.nonce : "",
    };

    $.ajax({
      dataType: "html",
      type: "POST",
      url: WcfeAdmin.ajaxurl,
      data: data,
      beforeSend: function () {
        var loaderimg = loaderPath;
        $("body").append(
          "<div class='jwcfe_spinner'><img src='" + loaderimg + "' /></div>",
        );
      },

      success: function (data) {
        // alert(data);
        var loaderimg = donePath;
        $("body .jwcfe_spinner").html("<img src='" + loaderimg + "' />");
        setTimeout(function () {
          $("body .jwcfe_spinner").remove();
        }, 500);
      },
    });
  };

  function setup_enhanced_multi_select(form) {
    form.find("select.jwcfe-enhanced-multi-select").each(function () {
      if ($(this).hasClass("enhanced")) { $(this).select2("destroy"); $(this).removeClass("enhanced"); }
      $(this)
        .select2({
          minimumResultsForSearch: 10,
          allowClear: true,
          placeholder: $(this).data("placeholder"),
          templateSelection: function (state) {
            if (!state.id) {
              return state.text;
            }
            return $("<span>" + state.text + "</span>");
          },
        })
        .addClass("enhanced");
    });
  }

  // _openNewFieldForm = function openNewFieldForm(tabName) {
  //     if (tabName == 'billing' || tabName == 'shipping' || tabName == 'additional' || tabName == 'account') {
  //         tabName = tabName + '_';
  //     }

  // 	// clear all form
  // 	$("#jwcfe_new_field_form_pp form")[0].reset();

  // 	// $("#jwcfe_new_field_form_pp form ul li:first a").click();
  // 	$("#jwcfe_new_field_form_pp form ul li:first a").trigger("click");

  //     var form = $("#jwcfe_new_field_form_pp");

  // 	// enable field
  // 	form.find("input[name=fname]").prop('disabled', false).css({
  // 		'color': '',
  // 		'background-color': '',
  // 		'border-color': ''
  // 	});

  //     form.find("select[name=ftype]").trigger('change');  // Replaces .change() with .trigger('change')
  //     form.find("select[name=fclass]").val('form-row-wide');

  // 	$("#btnaddfield").html('Add New Field');
  // 	$("#btnaddfield").attr('data-type','add');
  // 	$("#btnaddfield").removeAttr('data-rowId');

  // 	$('#jwcfe_new_field_form').find('.jwcfe-enhanced-multi-variations').remove();
  // 	$('#jwcfe_new_field_form').find('select[name="i_rule_operator"], select[name="i_rule_operand_type"]').val("").trigger('change');
  // 	$('#jwcfe_new_field_form').find('input[name="i_rule_operand"]').val("");
  // 	$('#jwcfe_new_field_form #jwcfe-tab-rules_new').find('.jwcfe_rule .jwcfe_condition_set_row:not(:first)').remove();

  //     openjwcfeModal();
  // }
  // FIX: Track which section's "Add Field" button was clicked so the new
  // row can be appended to the correct table and tagged with the right
  // f_section value. This variable is read by jwcfe_add_new_row().
  var _currentSection = "";

  _openNewFieldForm = function openNewFieldForm(tabName) {
    var prefix = "";
    if (
      tabName == "billing" ||
      tabName == "shipping" ||
      tabName == "additional" ||
      tabName == "account"
    ) {
      prefix = tabName + "_"; // prefix save karo
      _currentSection = tabName; // FIX: remember which section triggered
      tabName = prefix;
    }

    // clear all form
    $("#jwcfe_new_field_form_pp form")[0].reset();

    // Clear option inputs manually
    var form = $("#jwcfe_new_field_form_pp");
    form.find(".jwcfe_options .jwcfe-opt-container").html(OPTION_ROW_HTML);

    // reset tab and default field values
    $("#jwcfe_new_field_form_pp form ul li:first a").trigger("click");

    form.find("input[name=fname]").prop("disabled", false).css({
      color: "",
      "background-color": "",
      "border-color": "",
    });

    form.find("select[name=ftype]").prop("disabled", false).trigger("change");
    jwcfe_set_default_value_input_mode(form, form.find("select[name=ftype]").val());
    form.find("select[name=fclass]").val("form-row-wide");
    form.find("input[name=fcustomclass]").val("");
    form.find("select[name=fheading_type]").val("h4");
    form.find("textarea[name=ftexteditor]").val("");
    if (typeof tinymce !== "undefined" && tinymce.get("flabel_editor")) {
      tinymce.get("flabel_editor").setContent("");
    }
    form.find("input[name=flabel]").val("");
    form.find("input[name=fdefault]").val("");
    form.find("input[name=fplaceholder]").val("");
    form.find("textarea[name=ftext]").val("");
    form.find("input[name=fmaxlength]").val("");
    form.find("select[name=fvalidate]").val(null).trigger("change");
    form.find("select[name=fextoptions]").val(null).trigger("change");
    form.find("input[name=frequired]").prop("checked", false);
    form.find("input[name=fenabled]").prop("checked", true);
    form.find("input[name=fshowinorder]").prop("disabled", false).prop("checked", true);
    form.find("input[name=fshowinemail]").prop("disabled", false).prop("checked", true);

    // Update modal header back to "Add New Field" mode
    var $title = $("#jwcfe_new_field_form_pp .ui-dialog-title");
    $title.text("Add New Field");
    $title.removeClass("is-edit");
    // Update section dropdown to match current section
    if (_currentSection) {
      $(".jwcfe-section-display-select").val(_currentSection);
    }

    $("#btnaddfield").html("&#10003; Add Field");
    $("#btnaddfield").attr("data-type", "add");
    $("#btnaddfield").removeAttr("data-rowId");

    $("#jwcfe_new_field_form")
      .find(".jwcfe-enhanced-multi-variations")
      .remove();
    $("#jwcfe_new_field_form")
      .find(
        'select[name="i_rule_operator"], select[name="i_rule_operand_type"]',
      )
      .val("")
      .trigger("change");
    $("#jwcfe_new_field_form").find('input[name="i_rule_operand"]').val("");
    $("#jwcfe_new_field_form #jwcfe-tab-rules_new")
      .find(".jwcfe_rule .jwcfe_condition_set_row:not(:first)")
      .remove();

    // ✅ fname field mein prefix set karo
    // Jab user type kare to prefix hata na sake
    if (prefix) {
      var $fname = form.find("input[name=fname]");
      $fname.val("");
    }

    form.find(".err_msgs").html("");
    form.find(".err_msgs_options").html("");
    form
      .find("input[name='i_options_key[]'], input[name='i_options_text[]']")
      .css("border-color", "");

    openjwcfeModal();
  };

  $(document)
    .find("#btnaddfield")
    .on("click", function (e) {
      var type = $(this).attr("data-type");
      var form = $("#jwcfe_new_field_form");

      $("<input>")
        .attr({
          type: "hidden",
          vaule: "yes",
          id: "foo",
          name: "save_fields",
        })
        .appendTo("#jwcfe_checkout_fields_form");

      var result;
      if (type == "add") {
        result = jwcfe_add_new_row(form);
      } else {
        var rowId = $(this).attr("data-rowId");
        result = jwcfe_update_row(form, rowId);
      }

      var form = $("#jwcfe_checkout_fields_form");

      if (result) {
        form.submit();
      }
    });

  function jwcfe_add_new_row(form) {
    if (typeof tinymce !== "undefined" && tinymce.get("flabel_editor")) {
      tinymce.get("flabel_editor").save();
    }

    var name = $(form).find("input[name=fname]").val();
    var type = $(form).find("select[name=ftype]").val();
    var label = $(form).find("input[name=flabel]").val();
    var text = $(form).find("textarea[name=ftext]").val();
    var texteditor = $(form).find("textarea[name=ftexteditor]").val();
    var placeholder = $(form).find("input[name=fplaceholder]").val();
    var fdefault = $(form).find("input[name=fdefault]").val();
    var min_time = $(form).find("input[name=i_min_time]").val();
    var max_time = $(form).find("input[name=i_max_time]").val();
    var time_step = $(form).find("input[name=i_time_step]").val();
    var time_format = $(form).find("select[name=i_time_format]").val();
    var heading_type = $(form).find("select[name=fheading_type]").val();
    var maxlength = $(form).find("input[name=fmaxlength]").val();
    var options_json = get_options(form);
    var frules_action = $(form).find("select[name=i_rules_action]").val();
    var frules_action_ajax = $(form)
      .find("select[name=i_rules_action_ajax]")
      .val();
    var extoptionsList = $(form).find("select[name=fextoptions]").val();
    var widthClass = $(form).find("select[name=fclass]").val();
    var customCssClass = (
      $(form).find("input[name=fcustomclass]").val() || ""
    ).trim();
    var fieldClass = customCssClass
      ? widthClass + " " + customCssClass
      : widthClass;
    var labelClass = $(form).find("input[name=flabelclass]").val();
    var access = $(form).find("input[name=faccess]").prop("checked");
    var required = $(form).find("input[name=frequired]").is(":checked");
    var isinclude = $(form).find("input[name=fisinclude]").prop("checked");
    var enabled = $(form).find("input[name=fenabled]").prop("checked");
    var showinemail = $(form).find("input[name=fshowinemail]").prop("checked");
    var showinorder = $(form).find("input[name=fshowinorder]").prop("checked");
    var validations = $(form).find("select[name=fvalidate]").val();
    var $nameInput = $(form).find("input[name='fname']");
    var err_msgs = "";

    if (name == "") {
      err_msgs = "Name is required";
    } else if (!isHtmlIdValid(name)) {
      err_msgs = MSG_INVALID_NAME;
    } else if (type == "") {
      err_msgs = "Type is required";
    } else if (type === "select" || type === "radio") {
      var hasOption = false;
      var hasEmptyOption = false;
      $(form)
        .find(".jwcfe-opt-row")
        .each(function () {
          hasOption = true;
          var kv = $(this).find("input[name='i_options_key[]']").val().trim();
          var tv = $(this).find("input[name='i_options_text[]']").val().trim();
          if (kv === "" || tv === "") {
            hasEmptyOption = true;
            return false;
          }
        });
      if (!hasOption) {
        $(form)
          .find(".err_msgs_options")
          .html(
            "Options are required for " +
            type +
            " field. Please add at least one option.",
          );
        $(form)
          .find(".rowOptions")
          .find("input[name='i_options_key[]'], input[name='i_options_text[]']")
          .css("border-color", "red");
        err_msgs = " ";
      } else if (hasEmptyOption) {
        $(form)
          .find(".err_msgs_options")
          .html(
            "Each option must have both Option Value and Option Text filled in.",
          );
        $(form)
          .find(".jwcfe-opt-row")
          .each(function () {
            var kv = $(this).find("input[name='i_options_key[]']");
            var tv = $(this).find("input[name='i_options_text[]']");
            if (kv.val().trim() === "") kv.css("border-color", "red");
            if (tv.val().trim() === "") tv.css("border-color", "red");
          });
        err_msgs = " ";
      } else {
        $(form).find(".err_msgs_options").html("");
        $(form)
          .find("input[name='i_options_key[]'], input[name='i_options_text[]']")
          .css("border-color", "");

        var isDuplicate = false;
        $("#jwcfe_checkout_fields_form tbody tr").each(function () {
          var existingName = $(this).find(".f_name").val();
          var existingNameNew = $(this).find(".f_name_new").val();
          var checkName =
            existingNameNew !== "" ? existingNameNew : existingName;
          if (checkName !== "" && checkName === name) {
            isDuplicate = true;
            return false;
          }
        });
        if (isDuplicate) {
          err_msgs = 'A field with the name "' + name + '" already exists.';
        }
      }
    } else {
      // Duplicate name check - existing rows mein same name check karo
      var isDuplicate = false;
      $("#jwcfe_checkout_fields_form tbody tr").each(function () {
        var existingName = $(this).find(".f_name").val();
        var existingNameNew = $(this).find(".f_name_new").val();
        var checkName = existingNameNew !== "" ? existingNameNew : existingName;
        if (checkName !== "" && checkName === name) {
          isDuplicate = true;
          return false; // break loop
        }
      });
      if (isDuplicate) {
        err_msgs = 'A field with the name "' + name + '" already exists.';
      }
    }

    if (label == "") {
      label = name;
    }

    if (err_msgs != "") {
      $(form).find(".err_msgs").html(err_msgs).css("color", "red");

      //focus on name field
      if(name == "" || !isHtmlIdValid(name)){
        // Find modal body (scroll container)
    var $modalBody = $nameInput.closest('.jwcfe-modal-body');

    if ($modalBody.length) {
        // Scroll inside modal to input
        $modalBody.animate({
            scrollTop: $modalBody.scrollTop() + $nameInput.position().top - 150
        }, 400);
    } else {
        // fallback (if not inside scroll container)
        $('html, body').animate({
            scrollTop: $nameInput.offset().top - 100
        }, 400);
    }

    // Focus the input
    $nameInput.focus();
      }
      
      return false;
    }

    access = access ? 1 : 0;
    required = required ? 1 : 0;
    isinclude = isinclude ? 1 : 0;
    enabled = enabled ? 1 : 0;
    showinemail = showinemail ? 1 : 0;
    showinorder = showinorder ? 1 : 0;
    validations = validations ? validations : "";
    extoptionsList = extoptionsList ? extoptionsList : "";
    text = text.replace(/"/g, "\\'");

    // FIX: Count rows across ALL section tables so the new row gets a
    // globally unique index — not just the count of one table's rows.
    // Old code used "#jwcfe_checkout_fields_form tbody tr" which only targeted
    // a single (often absent) table in the new multi-section layout.
    var index = $("#jwcfe_checkout_fields_form tbody tr").length;

    var newRow = '<tr class="row_' + index + '">';

    newRow += '<td width="1%" class="sort ui-sortable-handle">';

    newRow +=
      '<input type="hidden" name="f_order[' +
      index +
      ']" class="f_order" value="' +
      index +
      '" />';

    newRow +=
      '<input type="hidden" name="f_custom[' +
      index +
      ']" class="f_custom" value="1" />';

    newRow +=
      '<input type="hidden" name="f_name[' +
      index +
      ']" class="f_name" value="' +
      name +
      '" />';

    newRow +=
      '<input type="hidden" name="f_name_new[' +
      index +
      ']" class="f_name_new" value="' +
      name +
      '" />';

    newRow +=
      '<input type="hidden" name="f_type[' +
      index +
      ']" class="f_type" value="' +
      type +
      '" />';

    newRow +=
      '<input type="hidden" name="f_label[' +
      index +
      ']" class="f_label" value="' +
      label +
      '" />';

    newRow +=
      '<input type="hidden" name="f_text[' +
      index +
      ']" class="f_text" value="' +
      text +
      '" />';
    newRow +=
      '<input type="hidden" name="f_texteditor[' +
      index +
      ']" class="f_texteditor" value="' +
      (texteditor || "").replace(/"/g, "\\'") +
      '" />';

    newRow +=
      '<input type="hidden" name="f_placeholder[' +
      index +
      ']" class="f_placeholder" value="' +
      placeholder +
      '" />';

    newRow +=
      '<input type="hidden" name="f_default[' +
      index +
      ']" class="f_default" value="' +
      (fdefault || "") +
      '" />';
    newRow +=
      '<input type="hidden" name="f_maxlength[' +
      index +
      ']" class="f_maxlength" value="' +
      maxlength +
      '" />';

    newRow +=
      '<input type="hidden" name="f_options[' +
      index +
      ']" class="f_options" value="' +
      options_json +
      '" />';

    newRow +=
      '<input type="hidden" name="f_rules_action[' +
      index +
      ']" class="f_rules_action" value="' +
      frules_action +
      '" />';

    newRow +=
      '<input type="hidden" name="f_rules_action_ajax[' +
      index +
      ']" class="f_rules_action_ajax" value="' +
      frules_action_ajax +
      '" />';

    newRow +=
      '<input type="hidden" name="f_extoptions[' +
      index +
      ']" class="f_extoptions" value="' +
      extoptionsList +
      '" />';

    newRow +=
      '<input type="hidden" name="f_heading_type[' +
      index +
      ']" class="f_heading_type" value="' +
      heading_type +
      '" />';

    newRow +=
      '<input type="hidden" name="f_class[' +
      index +
      ']" class="f_class" value="' +
      fieldClass +
      '" />';

    newRow +=
      '<input type="hidden" name="f_label_class[' +
      index +
      ']" class="f_label_class" value="' +
      labelClass +
      '" />';

    newRow +=
      '<input type="hidden" name="f_access[' +
      index +
      ']" class="f_access" value="' +
      access +
      '" />';

    newRow +=
      '<input type="hidden" name="f_required[' +
      index +
      ']" class="f_required" value="' +
      required +
      '" />';

    newRow +=
      '<input type="hidden" name="f_is_include[' +
      index +
      ']" class="f_is_include" value="' +
      isinclude +
      '" />';

    newRow +=
      '<input type="hidden" name="f_enabled[' +
      index +
      ']" class="f_enabled" value="' +
      enabled +
      '" />';

    newRow +=
      '<input type="hidden" name="f_show_in_email[' +
      index +
      ']" class="f_show_in_email" value="' +
      showinemail +
      '" />';
    newRow +=
      '<input type="hidden" name="f_show_in_order[' +
      index +
      ']" class="f_show_in_order" value="' +
      showinorder +
      '" />';

    newRow +=
      '<input type="hidden" name="i_min_time[' +
      index +
      ']" class="i_min_time" value="' +
      min_time +
      '" />';
    newRow +=
      '<input type="hidden" name="i_max_time[' +
      index +
      ']" class="i_max_time" value="' +
      max_time +
      '" />';
    newRow +=
      '<input type="hidden" name="i_time_step[' +
      index +
      ']" class="i_time_step" value="' +
      time_step +
      '" />';
    newRow +=
      '<input type="hidden" name="i_time_format[' +
      index +
      ']" class="i_time_format" value="' +
      time_format +
      '" />';
    newRow +=
      '<input type="hidden" name="f_validation[' +
      index +
      ']" class="f_validation" value="' +
      validations +
      '" />';
    newRow +=
      '<input type="hidden" name="f_deleted[' +
      index +
      ']" class="f_deleted" value="0" />';
    // FIX: Add f_section so PHP save_options knows which section this
    // newly-added field belongs to. Without this the field gets lost on save.
    newRow +=
      '<input type="hidden" name="f_section[' +
      index +
      ']" class="f_section" value="' +
      _currentSection +
      '" />';
    newRow += "</td>";

    var $targetTbody = _currentSection
      ? $("#jwcfe_sortable_" + _currentSection)
      : $("#jwcfe_checkout_fields_form tbody:first");

    var isBlockCheckout = $targetTbody.parent().find('thead tr th').length === 10;

    newRow += '<td class="td_select"><input type="checkbox" name="select_field" /></td>';
    
    if (isBlockCheckout) {
        newRow += '<td class="td_name" style="color:#888;font-size:12px;">' + name + "</td>";
        newRow += '<td class="td_type"><span class="jwcfe-type-badge jwcfe-type-' + type + '">' + type + "</span></td>";
        newRow += '<td class="td_label">' + label + "</td>";
        newRow += '<td class="td_description">' + placeholder + "</td>";
        newRow += '<td class="td_validation">' + validations + "</td>";
    } else {
        newRow += '<td class="td_label">' + label + "</td>";
        newRow += '<td class="td_name" style="color:#888;font-size:12px;">' + name + "</td>";
        newRow += '<td class="td_type"><span class="jwcfe-type-badge jwcfe-type-' + type + '">' + type + "</span></td>";
    }

    if (required == true) {
      newRow += '<td class="td_required status"><span class="jwcfe-status-required">Required</span></td>';
    } else {
      newRow += '<td class="td_required status"><span class="jwcfe-status-optional">Optional</span></td>';
    }

    if (enabled == true) {
      newRow += '<td class="td_enabled status"><label class="pure-material-switch"><input type="checkbox" class="toggle-checkbox" checked><span class="label">No</span></label><span class="toggle-label">yes</span></td>';
    } else {
      newRow += '<td class="td_enabled status"><label class="pure-material-switch"><input type="checkbox" class="toggle-checkbox"><span class="label">No</span></label><span class="toggle-label">yes</span></td>';
    }

    newRow += '<td class="td_edit"><div class="jwcfe-actions-cell"><div class="jwcfe-icon-btn edit" onclick="openEditFieldForm(this,' + index + ')" title="Edit field"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></div><div class="jwcfe-icon-btn delete" onclick="jwcfeDeleteSingleField(this)" title="Delete field"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></div></div></td>';
    newRow += "</tr>";

    $targetTbody.append(newRow);
    return true;
  }

  /*----------------------------------------------

   *---- CONDITIONAL RULES FUNCTIONS - SATRT -----

   *----------------------------------------------*/

  var OP_AND_HTML = '<label class="thpl_logic_label">AND</label>';

  OP_AND_HTML +=
    '<a href="javascript:void(0)" onclick="jwcfeRemoveRuleRow(this)" class="thpl_logic_link" title="Remove">X</a>';

  var OP_OR_HTML =
    '<tr class="thpl_logic_label_or"><td colspan="4" align="center">O R</td></tr>';

  var OP_HTML =
    '<a href="javascript:void(0)" class="thpl_logic_link" onclick="jwcfeAddNewConditionRow(this, 2)" title="">+</a>';

  OP_HTML +=
    '<a href="javascript:void(0)" onclick="jwcfeRemoveRuleRow(this)" class="thpl_logic_link" title="Remove">X</a>';

  var CONDITION_HTML = "",
    CONDITION_SET_HTML = "",
    CONDITION_SET_HTML_WITH_OR = "",
    RULE_HTML = "",
    RULE_SET_HTML = "";

  $(function () {
    CONDITION_HTML = '<tr class="jwcfe_condition condition-rule-div">';

    CONDITION_HTML +=
      '<td width="25%" class="thpladmin_rule_operand"><input type="text" name="i_rule_operand" style="width:200px;"/></td>';

    CONDITION_HTML += '<td class="actions">' + OP_HTML + "</td></tr>";

    CONDITION_SET_HTML = '<tr class="jwcfe_condition_set_row"><td>';
    CONDITION_SET_HTML +=
      '<table class="jwcfe_condition_set" width="100%" style=""><tbody>' +
      CONDITION_HTML +
      "</tbody></table>";
    CONDITION_SET_HTML += "</td></tr>";

    CONDITION_SET_HTML_WITH_OR = '<tr class="jwcfe_condition_set_row"><td>';
    CONDITION_SET_HTML_WITH_OR +=
      '<table class="jwcfe_condition_set" width="100%" style=""><thead>' +
      OP_OR_HTML +
      "</thead><tbody>" +
      CONDITION_HTML +
      "</tbody></table>";
    CONDITION_SET_HTML_WITH_OR += "</td></tr>";

    RULE_HTML = '<tr class="jwcfe_rule_row"><td>';
    RULE_HTML +=
      '<table class="jwcfe_rule" width="100%" style=""><tbody>' +
      CONDITION_SET_HTML +
      "</tbody></table>";
    RULE_HTML += "</td></tr>";

    RULE_SET_HTML = '<tr class="jwcfe_rule_set_row"><td>';
    RULE_SET_HTML +=
      '<table class="jwcfe_rule_set" width="100%"><tbody>' +
      RULE_HTML +
      "</tbody></table>";
    RULE_SET_HTML += "</td></tr>";
  });

  // Event listener for variation selection
  $(document).on("change", 'select[name="product_variation"]', function () {
    var selected_variation_attribute = $(this).val();
    // Update display based on selected_variation_attribute
  });

  _openEditFieldForm = function openEditFieldForm(elm, rowId) {
    var row = $(elm).closest("tr");
    var name = row.find(".f_name").val();
    var label = row.find(".f_label").val();

    // FIX: Update modal header for edit mode
    var $title = $("#jwcfe_new_field_form_pp .ui-dialog-title");
    $title.text("Edit: " + (label || name));
    $title.addClass("is-edit");

    // Update section dropdown to reflect which section this field belongs to
    var section = row.find(".f_section").val() || "";
    if (section) {
      $(".jwcfe-section-display-select").val(section);
    }

    var is_custom = row.find(".f_custom").val();
    var type = row.find(".f_type").val();
    var text = row.find(".f_text").val();

    var texteditor = row.find(".f_texteditor").val();

    var placeholder = row.find(".f_placeholder").val();
    var fdefault = row.find(".f_default").val();

    var min_time = row.find(".i_min_time").val();
    var max_time = row.find(".i_max_time").val();
    var time_step = row.find(".i_time_step").val();
    var time_format = row.find(".i_time_format").val();
    var maxlength = row.find(".f_maxlength").val();
    var optionsList = row.find(".f_options").val();
    var extoptionsList = row.find(".f_extoptions").val();
    var field_classes = row.find(".f_class").val();
    var label_classes = row.find(".f_label_class").val();
    var access = row.find(".f_access").val();
    var required = row.find(".f_required").val();
    var isinclude = row.find(".f_is_include").val();
    var frules_action = row.find(".f_rules_action").val();
    var frules_action_ajax = row.find(".f_rules_action_ajax").val();
    var heading_type = row.find(".f_heading_type").val();

    var enabled = row.find(".f_enabled").val();
    var validations = row.find(".f_validation").val();

    var showinemail = row.find(".f_show_in_email").val();
    var showinorder = row.find(".f_show_in_order").val();

    text = text.replace(/"/g, "\\'");

    is_custom = is_custom == 1 ? true : false;
    access = access == 1 ? true : false;
    required = required == 1 ? true : false;
    isinclude = isinclude == 1 ? true : false;
    enabled = enabled == 1 ? true : false;

    extoptionsList = extoptionsList.split(",");
    validations = validations.split(",");

    showinemail = showinemail == 1 ? true : false;
    showinorder = showinorder == 1 ? true : false;
    showinemail = is_custom == true ? showinemail : true;
    showinorder = is_custom == true ? showinorder : true;

    var form = $("#jwcfe_new_field_form_pp");

    form.find(".err_msgs").html("");
    form.find(".err_msgs_options").html("");
    form
      .find("input[name='i_options_key[]'], input[name='i_options_text[]']")
      .css("border-color", "");
    form.find("input[name=rowId]").val(rowId);
    form.find("input[name=fname]").val(name);
    form.find("input[name=fnameNew]").val(name);
    form.find("select[name=ftype]").val(type);
    jwcfe_set_default_value_input_mode(form, type);
    form.find("input[name=flabel]").val(label);
    form.find("input[name=fdefault]").val(fdefault);
    form.find("textarea[name=ftext]").val(text);
    form.find("input[name=fplaceholder]").val(placeholder);
    form.find("input[name=fmaxlength]").val(maxlength);

    form.find("textarea[name=ftexteditor]").val(texteditor);
    if (typeof tinymce !== "undefined" && tinymce.get("flabel_editor")) {
      tinymce.get("flabel_editor").setContent(texteditor);
    }

    var optionsJson = row.find(".f_options").val();
    populate_options_list(form, optionsJson);
    form.find("input[name=i_min_time]").val(min_time);
    form.find("input[name=i_max_time]").val(max_time);
    form.find("input[name=i_max_time]").val(max_time);
    form.find("select[name=i_time_format]").val(time_format);
    form.find("select[name=fextoptions]").val(extoptionsList).trigger("change");
    // Split stored class into width class + custom classes
    var widthMatch = field_classes.match(/form-row-[\w-]+/);
    var widthClassVal = widthMatch ? widthMatch[0] : "form-row-wide";
    var customCssVal = field_classes.replace(/form-row-[\w-]+/g, "").trim();
    form.find("select[name=fclass]").val(widthClassVal);
    form.find("input[name=fcustomclass]").val(customCssVal);
    form.find("input[name=flabelclass]").val(label_classes);
    form.find("select[name=fvalidate]").val(validations).trigger("change");
    form.find("input[name=faccess]").prop("checked", access);
    form.find("input[name=frequired]").prop("checked", required);
    form.find("input[name=fisinclude]").prop("checked", isinclude);
    form.find("input[name=fenabled]").prop("checked", enabled);
    form.find("input[name=fshowinemail]").prop("checked", showinemail);
    form.find("input[name=fshowinorder]").prop("checked", showinorder);

    var rulesActionAjax = frules_action_ajax;
    var rulesAction = frules_action;

    rulesAction = rulesAction != "" ? rulesAction : "show";
    rulesActionAjax = rulesActionAjax != "" ? rulesActionAjax : "show";

    form.find("select[name=i_rules_action]").val(rulesAction);
    form.find("select[name=i_rules_action_ajax]").val(rulesActionAjax);
    form.find("select[name=fheading_type]").val(heading_type);

    var conditionalRules = row.find(".f_rules").val();
    var conditionalRulesAjax = row.find(".f_rules_ajax").val();

    if (conditionalRules) {
      populate_conditional_rules(form, conditionalRules, false);
    }
    if (conditionalRulesAjax) {
      populate_conditional_rules(form, conditionalRulesAjax, true);
    }

    $(document)
      .find(".jwcfe-enhanced-multi-select2[name=i_rule_operand]")
      .each(function () {
        var has_selected = [];
        $(this)
          .find("option")
          .each(function () {
            var getIdselected = $(this).attr("data-isselected");

            if (getIdselected && getIdselected == "yes") {
              $(this).prop("selected", true);
            }
          });

        $(this).trigger("change");
      });

    form.find("select[name=ftype]").change();

    $("#btnaddfield").html("&#10003; Save Changes");
    $("#btnaddfield").attr("data-type", "update");
    $("#btnaddfield").attr("data-rowId", rowId);

    openjwcfeModal();

    form.find("input[name=fnameNew]").prop("disabled", true).css({
      color: "rgb(209 209 209)",
      "background-color": "rgb(249 249 249)",
      "border-color": "rgb(240 240 240)",
    });

    form.find("input[name=fname]").prop("disabled", true).css({
      color: "rgb(209 209 209)",
      "background-color": "rgb(249 249 249)",
      "border-color": "rgb(240 240 240)",
    });

    if (is_custom == false) {
      form.find("select[name=ftype]").prop("disabled", true);
      form.find("input[name=fshowinemail]").prop("disabled", true);
      form.find("input[name=fshowinorder]").prop("disabled", true);
      form.find("input[name=flabel]").focus();
    } else {
      form.find("select[name=ftype]").prop("disabled", false);
      form.find("input[name=fshowinemail]").prop("disabled", false);
      form.find("input[name=fshowinorder]").prop("disabled", false);
    }
  };

  function jwcfe_update_row(form, rowId_) {
    var rowId = $(form).find("input[name=rowId]").val();
    var name = $(form).find("input[name=fname]").val();

    var type = $(form).find("select[name=ftype]").val();

    var label = $(form).find("input[name=flabel]").val();
    var text = $(form).find("textarea[name=ftext]").val();
    var texteditor = $(form).find("textarea[name=ftexteditor]").val();

    var placeholder = $(form).find("input[name=fplaceholder]").val();
    var fdefault = $(form).find("input[name=fdefault]").val();
    var min_time = $(form).find("input[name=i_min_time]").val();
    var max_time = $(form).find("input[name=i_max_time]").val();
    var time_step = $(form).find("input[name=i_time_step]").val();
    var time_format = $(form).find("select[name=i_time_format]").val();
    var frules_action = $(form).find("select[name=i_rules_action]").val();
    var frules_action_ajax = $(form)
      .find("select[name=i_rules_action_ajax]")
      .val();
    var extoptionsList = $(form).find("select[name=fextoptions]").val();
    var heading_type = $(form).find("select[name=fheading_type]").val();
    var widthClass = $(form).find("select[name=fclass]").val();
    var customCssClass = ($(form).find("input[name=fcustomclass]").val() || "").trim();
    var fieldClass = customCssClass ? widthClass + " " + customCssClass : widthClass;
    var labelClass = $(form).find("input[name=flabelclass]").val();
    var access = $(form).find("input[name=faccess]").prop("checked");
    var maxlength = $(form).find("input[name=fmaxlength]").val();
    var enabled = $(form).find("input[name=fenabled]").prop("checked");
    var required = $(form).find("input[name=frequired]").prop("checked");
    var isinclude = $(form).find("input[name=fisinclude]").prop("checked");
    var showinemail = $(form).find("input[name=fshowinemail]").prop("checked");
    var showinorder = $(form).find("input[name=fshowinorder]").prop("checked");
    var validations = $(form).find("select[name=fvalidate]").val();
    var $nameInput = $(form).find("input[name='fname']");
    var err_msgs = "";

    if (name == "") {
      err_msgs = "Name is required";
    } else if (!isHtmlIdValid(name)) {
      err_msgs = MSG_INVALID_NAME;
    } else if (type == "") {
      err_msgs = "Type is required";
    } else if (type === "select" || type === "radio") {
      var hasOpt = false;
      var hasEmptyOpt = false;
      $(form)
        .find(".jwcfe-opt-row")
        .each(function () {
          hasOpt = true;
          var kv = $(this).find("input[name='i_options_key[]']").val().trim();
          var tv = $(this).find("input[name='i_options_text[]']").val().trim();
          if (kv === "" || tv === "") {
            hasEmptyOpt = true;
            return false;
          }
        });
      if (!hasOpt) {
        $(form)
          .find(".err_msgs_options")
          .html(
            "Options are required for " +
            type +
            " field. Please add at least one option.",
          );
        $(form)
          .find(".rowOptions")
          .find("input[name='i_options_key[]'], input[name='i_options_text[]']")
          .css("border-color", "red");
        err_msgs = " ";
      } else if (hasEmptyOpt) {
        $(form)
          .find(".err_msgs_options")
          .html(
            "Each option must have both Option Value and Option Text filled in.",
          );
        $(form)
          .find(".jwcfe-opt-row")
          .each(function () {
            var kv = $(this).find("input[name='i_options_key[]']");
            var tv = $(this).find("input[name='i_options_text[]']");
            if (kv.val().trim() === "") kv.css("border-color", "red");
            if (tv.val().trim() === "") tv.css("border-color", "red");
          });
        err_msgs = " ";
      } else {
        $(form).find(".err_msgs_options").html("");
        $(form)
          .find("input[name='i_options_key[]'], input[name='i_options_text[]']")
          .css("border-color", "");
      }
    }

    if (err_msgs != "") {

      
      $(form).find(".err_msgs").html(err_msgs);

      return false;
    }

    access = access ? 1 : 0;
    required = required ? 1 : 0;
    isinclude = isinclude ? 1 : 0;
    enabled = enabled ? 1 : 0;
    showinemail = showinemail ? 1 : 0;
    showinorder = showinorder ? 1 : 0;
    validations = validations ? validations : "";
    extoptionsList = extoptionsList ? extoptionsList : "";

    // FIX: Search across ALL section tables, not just a single
    // #jwcfe_checkout_fields_form tbody which doesn't exist in the new layout.
    var row = $("#jwcfe_checkout_fields_form tbody").find(".row_" + rowId_);
    row.find(".f_name").val(name);
    row.find(".f_type").val(type);
    row.find(".f_label").val(label);
    row.find(".f_text").val(text);
    row.find(".f_texteditor").val(texteditor);

    row.find(".f_placeholder").val(placeholder);
    row.find(".f_default").val(fdefault || "");
    row.find(".i_min_time").val(min_time);
    row.find(".i_max_time").val(max_time);
    row.find(".i_time_step").val(time_step);
    row.find(".i_time_format").val(time_format);
    row.find(".f_maxlength").val(maxlength);
    row.find(".f_rules_action").val(frules_action);
    row.find(".f_rules_action_ajax").val(frules_action_ajax);
    row.find(".f_heading_type").val(heading_type);

    var options_json = get_options(form);

    row.find(".f_options").val(options_json);
    row.find(".f_extoptions").val(extoptionsList);
    row.find(".f_class").val(fieldClass);
    row.find(".f_label_class").val(labelClass);
    row.find(".f_access").val(access);
    row.find(".f_required").val(required);
    row.find(".f_is_include").val(isinclude);
    row.find(".f_enabled").val(enabled);
    row.find(".f_show_in_email").val(showinemail);
    row.find(".f_show_in_order").val(showinorder);
    row.find(".f_validation").val(validations);
    row.find(".td_name").html(name);
    row.find(".td_type").html(type);
    row.find(".td_label").html(label);
    row.find(".td_placeholder").html(placeholder);
    row.find(".td_validate").html("" + validations + "");
    row
      .find(".td_required")
      .html(
        required == 1 ? '<span class="status-enabled tips">Yes</span>' : "-",
      );
    row
      .find(".td_enabled")
      .html(
        enabled == 1 ? '<span class="status-enabled tips">Yes</span>' : "-",
      );
    return true;
  }

  _removeSelectedFields = function removeSelectedFields() {
    $("#jwcfe_checkout_fields_form tbody tr").removeClass("strikeout");
    $(
      "#jwcfe_checkout_fields_form tbody input:checkbox[name=select_field]:checked",
    ).each(function () {
      //$(this).closest('tr').remove();

      var row = $(this).closest("tr");
      if (!row.hasClass("strikeout")) {
        row.addClass("strikeout");
        row.fadeOut();
      }

      row.find(".f_deleted").val(1);
      row.find(".f_edit_btn").prop("disabled", true);
      //row.find('.sort').removeClass('sort');
    });
  };

  _deleteSingleField = function deleteSingleField(elm) {
    var row = $(elm).closest("tr");
    if (!row.length) return;

    var fieldName = row.find(".f_name").val() || row.find(".td_name").text().trim();
    var confirmMsg = "Delete this field? This action cannot be undone after saving.";

    if (!confirm(confirmMsg)) return;

    if (!row.hasClass("strikeout")) {
      row.addClass("strikeout");
      row.fadeOut(300);
    }
    row.find(".f_deleted").val(1);
    row.find(".jwcfe-icon-btn.edit").prop("disabled", true).css("pointer-events", "none").css("opacity", "0.4");
    row.find(".jwcfe-icon-btn.delete").prop("disabled", true).css("pointer-events", "none").css("opacity", "0.4");
  };

  _enableDisableSelectedFields = function enableDisableSelectedFields(enabled) {
    $(
      "#jwcfe_checkout_fields_form tbody input:checkbox[name=select_field]:checked",
    ).each(function () {
      var row = $(this).closest("tr");
      if (enabled == 0) {
        if (!row.hasClass("jwcfe-disabled")) {
          row.addClass("jwcfe-disabled");
        }
      } else {
        row.removeClass("jwcfe-disabled");
      }

      row.find(".f_edit_btn").prop("disabled", enabled == 1 ? false : true);

      row.find(".td_enabled .toggle-checkbox").prop("checked", enabled == 1);
      row.find(".td_enabled .toggle-label").text(enabled == 1 ? "Yes" : "No");

      row.find(".f_enabled").val(enabled);
    });
  };

  function handleToggleSwitch(row) {
    var inputField = row.find(".td_enabled .toggle-label");
    var toggleSwitch = row.find(".td_enabled .toggle-checkbox");

    var isEnabled = toggleSwitch.prop("checked");

    if (!isEnabled) {
      if (!row.hasClass("jwcfe-disabled")) {
        row.addClass("jwcfe-disabled");
      }

      inputField.hide();
      // alert("Field is disabled.");
    } else {
      row.removeClass("jwcfe-disabled");
      inputField.show();
      // alert("Field is already enabled.");
    }

    row.find(".f_edit_btn").prop("disabled", !isEnabled);

    row.find(".td_enabled .toggle-label").text(isEnabled ? "Yes" : "No");

    row.find(".f_enabled").val(isEnabled ? 1 : 0);
  }

  $(".td_enabled .toggle-checkbox").on("change", function () {
    var row = $(this).closest("tr");
    handleToggleSwitch(row);
  });

  _enableDisableSelectedFields = function enableDisableSelectedFields(enabled) {
    $(
      "#jwcfe_checkout_fields_form tbody input:checkbox[name=select_field]:checked",
    ).each(function () {
      var row = $(this).closest("tr");
      // Set the state of the toggle switch
      row.find(".td_enabled .toggle-checkbox").prop("checked", enabled == 1);
      handleToggleSwitch(row);
    });
  };

  // Get modal element
  var jwcfemodal = document.getElementById("jwcfeModal");
  const closeBtns = document.querySelectorAll(".jwcfecloseBtn");
  closeBtns.forEach((btn) =>
    btn.addEventListener("click", closejwcfeModalLocal),
  );

  const btnCancel = document.querySelector(".btncancel");
  if (btnCancel) btnCancel.addEventListener("click", closejwcfeModalLocal);

  // optional: close when clicking outside
  window.addEventListener("click", function (e) {
    const modal = document.getElementById("jwcfeModal");
    if (modal && e.target === modal) closejwcfeModalLocal();
  });

  // expose open function to the rest of your module if needed
  window.openjwcfeModal = openjwcfeModalLocal; // if other code calls openjwcfeModal()

  /*------------------------------------

  *---- OPTIONS FUNCTIONS - SATRT -----

  *------------------------------------*/

  function get_options(elm) {
    var optionsKey = $(elm)
      .find("input[name='i_options_key[]']")
      .map(function () {
        return $(this).val();
      })
      .get();
    var optionsText = $(elm)
      .find("input[name='i_options_text[]']")
      .map(function () {
        return $(this).val();
      })
      .get();

    var optionsSize = optionsText.length;
    var optionsArr = [];

    for (var i = 0; i < optionsSize; i++) {
      var optionDetails = {};

      optionDetails["key"] = optionsKey[i];
      optionDetails["text"] = optionsText[i];

      optionsArr.push(optionDetails);
    }

    var optionsJson = optionsArr.length > 0 ? JSON.stringify(optionsArr) : "";
    optionsJson = encodeURIComponent(optionsJson);
    //optionsJson = optionsJson.replace(/"/g, "'");
    return optionsJson;
  }
  // ensure function exists inside IIFE scope
  function closejwcfeModalLocal() {
    var modal = document.getElementById("jwcfeModal");
    if (modal) modal.style.display = "none";
  }

  function openjwcfeModalLocal() {
    var modal = document.getElementById("jwcfeModal");
    if (modal) modal.style.display = "flex";
  }

  document.addEventListener("DOMContentLoaded", function () {
    enableSelectAllInputFields(); // Ensure binding is applied when the DOM is ready
  });

  function enableSelectAllInputFields() {
    // Target all input fields with name "i_options_key[]" and "i_options_text[]"
    const inputs = document.querySelectorAll(
      'input[name="i_options_key[]"], input[name="i_options_text[]"]',
    );

    inputs.forEach((input) => {
      input.addEventListener("keydown", function (event) {
        if (event.ctrlKey && event.key === "a") {
          // Check for Ctrl + A
          event.preventDefault(); // Prevent default behavior
          input.select(); // Select the text in the input field
        }
      });
    });
  }

  function populate_options_list(elm, optionsJson) {
    let optionsHtml = "";

    if (optionsJson) {
      try {
        optionsJson = decodeURIComponent(optionsJson);
        let optionsList = JSON.parse(optionsJson);

        if (optionsList) {
          optionsList.forEach((option) => {
            const newkey = option.key.split("+").join(" ");
            const newtxt = option.text.split("+").join(" ");

            optionsHtml +=
              '<div class="jwcfe-opt-row">' +
              '<div class="jwcfe-opt-input-wrap"><input type="text" name="i_options_key[]" value="' + newkey + '" placeholder="Option Value" /></div>' +
              '<div class="jwcfe-opt-input-wrap"><input type="text" name="i_options_text[]" value="' + newtxt + '" placeholder="Option Text" /></div>' +
              '<div class="jwcfe-opt-actions">' +
              '<a href="javascript:void(0)" onclick="jwcfeAddNewOptionRow(this)" class="jwcfe-opt-btn jwcfe-opt-btn-add" title="Add option">+</a>' +
              '<a href="javascript:void(0)" onclick="jwcfeRemoveOptionRow(this)" class="jwcfe-opt-btn jwcfe-opt-btn-remove" title="Remove">×</a>' +
              '<span class="jwcfe-opt-btn jwcfe-opt-btn-sort ui-jwcf-sortable-handle" onclick="jwcfe_handler_OptionRow(this)" title="Drag to sort">⇅</span>' +
              '</div>' +
              '</div>';
          });
        }
      } catch (err) {
        console.error("JWCFE: Error parsing options:", err.message);
      }
    }

    const optionsTable = $(elm).find(".jwcfe-option-list .jwcfe-opt-container");
    optionsTable.html(optionsHtml || OPTION_ROW_HTML);

    enableSelectAllInputFields(); // Reapply binding for Ctrl + A to new inputs
  }

  addNewOptionRow = function addNewOptionRow(elm) {
    var ptable = $(elm).closest(".jwcfe-option-list");
    var optionsSize = ptable.find(".jwcfe-opt-row").size();

    if (optionsSize > 0) {
      ptable.find(".jwcfe-opt-row:last").after(OPTION_ROW_HTML);
    } else {
      ptable.append(OPTION_ROW_HTML);
    }
  };

  removeOptionRow = function removeOptionRow(elm) {
    var ptable = $(elm).closest(".jwcfe-option-list");
    $(elm).closest(".jwcfe-opt-row").remove();
    var optionsSize = ptable.find(".jwcfe-opt-row").size();

    if (optionsSize == 0) {
      ptable.append(OPTION_ROW_HTML);
    }
  };

  /*------------------------------------
 
   *---- OPTIONS FUNCTIONS - END -------
 
   *------------------------------------*/

  function jwcfe_prepare_field_order_indexes() {
    // FIX: Update f_order values across ALL section tables so drag-drop
    // reordering works in the new multi-section layout. Old code only
    // touched rows in a single (non-existent) #jwcfe_checkout_fields table.
    var globalIndex = 0;
    $("#jwcfe_checkout_fields_form tbody tr").each(function () {
      $("input.f_order", this).val(globalIndex);
      globalIndex++;
    });
  }

  function jwcfe_set_default_value_input_mode(form, type) {
    var $dv = $(form).find("input[name=fdefault]");
    if (!$dv.length) return;

    var inputType = "text";

    if (type === "date") {
      inputType = "date";
    } else if (type === "week") {
      inputType = "week";
    } else if (type === "month") {
      inputType = "month";
    } else if (type === "datetime-local") {
      inputType = "datetime-local";
    } else if (type === "timepicker") {
      inputType = "time";
    }

    $dv.attr("type", inputType);
  }

  _fieldTypeChangeListner = function fieldTypeChangeListner(elm) {
    var type = $(elm).val();

    var form = $(elm).closest("form");
    form.find("#fieldLabelText").text("Label of Field:");

    showAllFields(form);
    jwcfe_set_default_value_input_mode(form, type);
    var requiredCheckboxRow = $("#requiredechk").closest(".checkbox-row");

    if (type === "paragraph" || type === "heading" || type === "hidden") {
      requiredCheckboxRow.hide(); // Hide the required checkbox row
      $("#requiredechk").prop("checked", false); // Uncheck it
    }

    if (
      type === "select" ||
      type === "multiselect" ||
      type === "checkboxgroup"
    ) {
      form.find(".rowValidate").hide();
      form.find(".rowPricing").hide();
      form.find(".rowPlaceholder").hide();
      form.find(".rowDefault").hide();
      form.find(".rowMaxlength").hide();
      form.find(".rowCustomText").hide();
      form.find(".rowLabel").show();
      form.find(".rowDescription").show();
      form.find(".rowOptions").show();
      form.find(".rowClass").show();
      form.find(".pricetxt").hide();
      form.find(".taxtxt").hide();
      form.find(".rowDescription2").hide();
      form.find(".texteditor").hide();

      form.find(".rowLabel1").appendTo(".jwcfe_left_col_child_div");
    } else if (type === "radio") {
      form.find(".rowValidate").hide();
      form.find(".rowPricing").hide();
      form.find(".rowPlaceholder").hide();
      form.find(".rowDefault").hide();
      form.find(".rowMaxlength").hide();
      form.find(".rowCustomText").hide();
      form.find(".rowLabel").show();
      form.find(".rowDescription").show();
      form.find(".rowOptions").show();
      form.find(".rowClass").show();
      form.find(".pricetxt").hide();
      form.find(".taxtxt").hide();
      form.find(".rowDescription2").hide();
      form.find(".texteditor").hide();
    } else if (type === "text") {
      form.find(".rowLabel1").hide();
      form.find(".rowDescription2").hide();
      form.find(".rowOptions").hide();
      form.find(".rowMaxlength").show();
      form.find(".rowDescription").show();
      form.find(".rowValidate").show();
      form.find(".rowClass").show();
      form.find(".pricetxt").hide();
      form.find(".taxtxt").hide();
      form.find(".texteditor").hide();
    } else if (type === "checkbox") {
      form.find(".rowDescription2").hide();
      form.find(".rowRequired").hide();
      form.find(".rowAccess").hide();
      form.find(".rowMaxlength").hide();
      form.find(".rowValidate").hide();
      form.find(".rowCustomText").hide();
      form.find(".rowOptions").hide();
      form.find(".rowPlaceholder").hide();
      form.find(".rowDefault").hide();
      form.find("input[name=fdefault]").val("");
      form.find(".rowDescription").show();
      form.find(".rowClass").show(); //this is for field width
      form.find(".texteditor").hide();
    } else if (type === "textarea") {
      form.find(".rowDescription2").hide();
      form.find(".rowLabel1").hide();
      form.find(".rowDescription2").hide();
      form.find(".rowOptions").hide();
      form.find(".rowMaxlength").hide();
      form.find(".rowValidate").hide();
      form.find(".texteditor").hide();
    } else if (type === "hidden") {
      form.find(".rowDescription2").hide();

      form.find(".rowRequired").hide();
      form.find(".rowAccess").hide();
      form.find(".rowMaxlength").hide();
      form.find(".rowValidate").hide();
      form.find(".rowCustomText").hide();
      form.find(".rowOptions").hide();
      form.find(".rowPlaceholder").hide();
      form.find(".rowDefault").hide();
      form.find(".rowDescription").hide();
      form.find(".rowClass").hide(); //this is for field width
      form.find(".rowLabel1").hide();
      // form.find('.rowName').hide();
      form.find(".rowLabel").hide();
      form.find(".texteditor").hide();
    } else if (type === "url") {
      form.find(".rowDescription2").hide();

      // form.find(".rowRequired").hide();
      form.find(".rowAccess").hide();
      form.find(".rowMaxlength").hide();
      // form.find(".rowValidate").hide();
      form.find(".rowCustomText").hide();
      form.find(".rowOptions").hide();
      // form.find(".rowPlaceholder").hide();
      form.find(".rowDescription").hide();
      form.find(".rowClass").hide(); //this is for field width
      // form.find(".rowLabel1").hide();
      // form.find('.rowName').hide();
      // form.find(".rowLabel").hide();
      form.find(".texteditor").hide();
    } else if (type === "datetime-local") {
      form.find(".rowDescription2").hide();

      form.find(".rowDescription2").hide();

      // form.find(".rowRequired").hide();
      form.find(".rowAccess").hide();
      form.find(".rowMaxlength").hide();
      // form.find(".rowValidate").hide();
      form.find(".rowCustomText").hide();
      form.find(".rowOptions").hide();
      // form.find(".rowPlaceholder").hide();
      form.find(".rowDescription").hide();
      form.find(".rowClass").hide(); //this is for field width
      // form.find(".rowLabel1").hide();
      // form.find('.rowName').hide();
      // form.find(".rowLabel").hide();
      form.find(".texteditor").hide();
    } else if (type === "heading") {
      form.find(".rowDescription2").hide();
      form.find(".rowLabel").show();
      form.find(".rowHeadingType").show();
      // form.find('.rowheading').show();
      form.find("#fieldLabelText").text("Heading Text:");
      form.find(".rowLabel1").hide();

      form.find(".rowRequired").hide();
      form.find(".rowAccess").hide();
      form.find(".rowMaxlength").hide();
      form.find(".rowValidate").hide();
      form.find(".rowCustomText").hide();
      form.find(".rowOptions").hide();
      form.find(".rowPlaceholder").hide();
      form.find(".rowDefault").hide();
      form.find(".rowDescription").hide();
      form.find(".rowClass").hide(); //this is for field width
      form.find(".texteditor").hide();
    } else if (type === "paragraph") {
      form.find("#fieldLabelText").text("Paragraph Text:");
      form.find(".rowLabel").hide();
      form.find(".texteditor").show(); // <-- show the texteditor
      form.find(".rowDescription2").hide();
      form.find(".rowRequired").hide();
      form.find(".rowAccess").hide();
      form.find(".rowMaxlength").hide();
      form.find(".rowValidate").hide();
      form.find(".rowCustomText").hide();
      form.find(".rowOptions").hide();
      form.find(".rowPlaceholder").hide();
      form.find(".rowDefault").hide();
      form.find(".rowDescription").hide();
      form.find(".rowClass").hide(); // for field width
      form.find(".rowLabel1").hide();

      if (typeof tinymce !== "undefined" && !tinymce.get("flabel_editor")) {
        wp.editor.initialize("flabel_editor", {
          tinymce: {
            wpautop: true,
            plugins: "lists,paste,link",
            toolbar1: "bold italic bullist numlist link",
            menubar: false,
            statusbar: false,
          },
          quicktags: false,
        });
      }
    } else if (type === "email") {
      form.find(".rowLabel1").hide();
      form.find(".rowDescription2").hide();
      form.find(".rowOptions").hide();
      form.find(".rowMaxlength").hide();
      form.find(".rowDescription").show();
      form.find(".rowValidate").show();
      form.find(".rowClass").show();
      form.find(".rowDescription2").hide();
      form.find(".texteditor").hide();
    } else if (type === "phone") {
      form.find(".rowLabel1").hide();
      form.find(".rowDescription2").hide();
      form.find(".rowOptions").hide();
      form.find(".rowMaxlength").hide();
      form.find(".rowDescription").show();
      form.find(".rowValidate").show();
      form.find(".rowClass").show();
      form.find(".rowDescription2").hide();
      form.find(".texteditor").hide();
    } else if (type === "password") {
      form.find(".rowRequired").show();
      form.find(".rowValidate").hide();
      form.find(".rowCustomText").hide();
      form.find(".rowOptions").hide();
      form.find(".rowAccess").show();
      form.find(".rowClass").show();
      form.find(".rowLabel1").hide();
      form.find(".rowDescription2").hide();
      form.find(".rowMaxlength").show();
      form.find(".rowDescription2").hide();
      form.find(".rowPlaceholder").hide();
      form.find(".texteditor").hide();
    } else if (type === "timepicker") {
      form.find(".rowRequired").hide();
      form.find(".rowAccess").hide();
      form.find(".rowMaxlength").hide();
      form.find(".rowValidate").hide();
      form.find(".rowCustomText").hide();
      form.find(".rowOptions").hide();
      form.find(".rowPlaceholder").hide();
      form.find(".rowDescription").show();
      form.find(".rowClass").show(); //this is for field width
      form.find(".rowDescription2").hide();
      form.find(".texteditor").hide();
    } else if (type === "date") {
      form.find(".rowRequired").hide();
      form.find(".rowAccess").hide();
      form.find(".rowMaxlength").hide();
      form.find(".rowValidate").hide();
      form.find(".rowCustomText").hide();
      form.find(".rowOptions").hide();
      form.find(".rowPlaceholder").hide();
      form.find(".rowDescription").show();
      form.find(".rowClass").show(); //this is for field width
      form.find(".rowDescription2").hide();
      form.find(".texteditor").hide();
    } else if (type === "month") {
      form.find(".rowRequired").hide();
      form.find(".rowAccess").hide();
      form.find(".rowMaxlength").hide();
      form.find(".rowValidate").hide();
      form.find(".rowCustomText").hide();
      form.find(".rowOptions").hide();
      form.find(".rowPlaceholder").hide();
      form.find(".rowDescription").show();
      form.find(".rowClass").show(); //this is for field width
      form.find(".rowDescription2").hide();
      form.find(".texteditor").hide();
    } else if (type === "week") {
      form.find(".rowRequired").hide();
      form.find(".rowAccess").hide();
      form.find(".rowMaxlength").hide();
      form.find(".rowValidate").hide();
      form.find(".rowCustomText").hide();
      form.find(".rowOptions").hide();
      form.find(".rowPlaceholder").hide();
      form.find(".rowDescription").show();
      form.find(".rowClass").show(); //this is for field width
      form.find(".rowDescription2").hide();
      form.find(".texteditor").hide();
    } else if (type === "number") {
      form.find(".rowLabel1").hide();
      form.find(".rowDescription2").hide();
      form.find(".rowOptions").hide();
      form.find(".rowMaxlength").show();
      form.find(".rowValidate").hide();
      form.find(".rowDescription2").hide();
      form.find(".texteditor").hide();
    } else {
      form.find(".rowOptions").hide();
      form.find(".rowCustomText").hide();
    }

    $(".accountdialog form .rowPricing").hide();

    setup_enhanced_multi_select(form);
  };
  _fieldTypeChangeListnerblock = function fieldTypeChangeListnerblock(elm) {
    var type = $(elm).val();
    var form = $(elm).closest("form");

    showAllFields(form);
    jwcfe_set_default_value_input_mode(form, type);
    form.find("#fieldLabelText").text("Label of Field:");

    // Fix for hiding the required checkbox when type is 'checkbox'
    var requiredCheckboxRow = $("#requiredechk").closest(".checkbox-row");

    if (type === "checkbox") {
      requiredCheckboxRow.hide(); // Hide the required checkbox row
    } else {
      requiredCheckboxRow.show(); // Show it again for other field types
    }

    if (
      type === "select" ||
      type === "multiselect" ||
      type === "checkboxgroup"
    ) {
      form
        .find(
          ".rowValidate, .rowPricing, .rowPlaceholder, .rowMaxlength, .rowCustomText",
        )
        .hide();
      form.find(".rowDefault").hide();
      form.find(".rowLabel, .rowOptions, .rowClass").show();
      form.find(".pricetxt, .taxtxt, .rowDescription").hide();
      form.find(".rowLabel1").appendTo(".jwcfe_left_col_child_div");
    } else if (type === "radio") {
      form
        .find(
          ".rowValidate, .rowPricing, .rowPlaceholder, .rowMaxlength, .rowCustomText",
        )
        .hide();
      form.find(".rowDefault").hide();
      form.find(".rowLabel, .rowDescription, .rowOptions, .rowClass").show();
      form.find(".pricetxt, .taxtxt, .rowDescription2").hide();
    } else if (type === "text") {
      form
        .find(
          ".rowLabel1, .rowDescription2, .rowMaxlength, .rowDescription, .rowOptions",
        )
        .hide();
      form.find(".rowValidate, .rowClass").show();
      form.find(".pricetxt, .taxtxt").hide();
    } else if (type === "checkbox") {
      form
        .find(
          ".rowDescription2, .rowDescription, .rowRequired, .rowAccess, .rowMaxlength, .rowValidate, .rowCustomText, .rowOptions, .rowPlaceholder, .rowDefault",
        )
        .hide();
      form.find("input[name=fdefault]").val("");
      form.find(".rowClass").show();
    } else if (type === "textarea") {
      form
        .find(
          ".rowDescription2, .rowLabel1, .rowOptions, .rowMaxlength, .rowValidate",
        )
        .hide();
    } else if (type === "hidden") {
      form
        .find(
          ".rowDescription2, .rowRequired, .rowAccess, .rowMaxlength, .rowValidate, .rowCustomText, .rowOptions, .rowPlaceholder, .rowDescription, .rowClass, .rowLabel1, .rowLabel",
        )
        .hide();
      form.find(".rowDefault").hide();
    } else if (type === "heading") {
      form
        .find(
          ".rowDescription2, .rowRequired, .rowAccess, .rowMaxlength, .rowValidate, .rowCustomText, .rowOptions, .rowPlaceholder",
        )
        .hide();
      form.find(".rowDefault").hide();
      form.find(".rowDescription, .rowClass, .rowHeadingType").show();
    } else if (type === "paragraph") {
      form
        .find(
          ".rowDescription2, .rowRequired, .rowAccess, .rowMaxlength, .rowValidate, .rowCustomText, .rowOptions",
        )
        .hide();
      form.find(".rowDefault").hide();
      form.find(".rowPlaceholder, .rowDescription, .rowClass").show();
    } else if (type === "email" || type === "phone") {
      form
        .find(".rowLabel1, .rowDescription2, .rowOptions, .rowMaxlength")
        .hide();
      form.find(".rowDescription, .rowValidate, .rowClass").show();
    } else if (type === "password") {
      form.find(".rowRequired, .rowAccess, .rowClass, .rowMaxlength").show();
      form
        .find(
          ".rowValidate, .rowCustomText, .rowOptions, .rowPlaceholder, .rowLabel1, .rowDescription2",
        )
        .hide();
    } else if (
      type === "timepicker" ||
      type === "date" ||
      type === "month" ||
      type === "week"
    ) {
      form
        .find(
          ".rowRequired, .rowAccess, .rowMaxlength, .rowValidate, .rowCustomText, .rowOptions, .rowPlaceholder",
        )
        .hide();
      form.find(".rowDescription, .rowClass").show();
    } else if (type === "number") {
      form.find(".rowLabel1, .rowDescription2, .rowOptions").hide();
      form.find(".rowMaxlength").show();
    } else {
      form.find(".rowOptions, .rowCustomText").hide();
    }

    $(".accountdialog form .rowPricing").hide();
    setup_enhanced_multi_select(form);
  };

  function showAllFields(form) {
    form.find(".rowLabel").show();
    form.find(".rowOptions").show();
    form.find(".rowPlaceholder").show();
    form.find(".rowDefault").show();
    form.find(".rowAccess").show();
    form.find(".rowRequired").show();
    form.find(".rowValidate").show();
    form.find(".rowExtoptions").hide();
    form.find(".rowTimepicker").hide();
    form.find(".rowPricing").show();
    form.find(".rowHeadingType").hide();
    form.find(".texteditor").hide();
    form.find(".rowCustomClass").show();
    form.find(".rowDescription").show();
    form.find(".rowMaxlength").show();
    form.find(".rowClass").show();
  }

  _selectAllCheckoutFields = function selectAllCheckoutFields(elm) {
    var checkAll = $(elm).prop("checked");
    $("#jwcfe_checkout_fields_form tbody input:checkbox[name=select_field]").prop(
      "checked",
      checkAll,
    );
  };

  function isHtmlIdValid(id) {
    var re = /^[a-zA-Z\_]+[a-z0-9\-_]*$/;
    return re.test(id.trim());
  }

  //===================== shorting & draged
  $(document).ready(function () {
    $(".jwcfe-opt-container").on("mousedown", ".sort", function (e) {
      var $draggedElement = $(this).closest(".jwcfe-opt-row");
      var $container = $draggedElement.closest(".jwcfe-opt-container");
      var startY = e.pageY;
      var startOffset = $draggedElement.offset().top;

      $(document).on("mousemove", function (e) {
        var moveY = e.pageY;
        var moveOffset = startOffset + (moveY - startY);
        var containerTop = $container.offset().top;
        var containerBottom =
          containerTop +
          $container.outerHeight() -
          $draggedElement.outerHeight();

        // Constrain the movement within the container
        if (moveOffset >= containerTop && moveOffset <= containerBottom) {
          $draggedElement.offset({ top: moveOffset });
        }
      });

      $(document).on("mouseup", function () {
        $(document).off("mousemove");
        $(document).off("mouseup");

        // Rearrange elements
        var newPosition = $draggedElement.offset().top;
        $container.children(".jwcfe-opt-row").each(function () {
          var $currentRow = $(this);
          if ($currentRow.is($draggedElement)) return;

          var currentTop = $currentRow.offset().top;
          if (newPosition < currentTop) {
            $draggedElement.insertBefore($currentRow);
            return true; // Stop looping once inserted
          }
        });
      });

      // Prevent text selection while dragging
      e.preventDefault();
    });
  });

  $(document).ready(function () {
    $(".jwcfe-opt-container").sortable({
      handle: ".sort",
      placeholder: "ui-state-highlight",
      tolerance: "pointer",
      start: function (event, ui) {
        ui.placeholder.height(ui.item.height());
        ui.placeholder.css({
          visibility: "visible",
          background: "#f0f0f0", // Style the placeholder as needed
          border: "1px dashed #ccc", // Example styling for placeholder
        });

        // Add initial margins for equal spacing
        $(".jwcfe-opt-row").css("margin-bottom", "0px");
        $(".jwcfe-opt-row:last-child").css("margin-bottom", "0");
      },
      sort: function (event, ui) {
        // Adjust margins dynamically during sorting
        $(".jwcfe-opt-row").css("margin-bottom", "0px");
        $(".jwcfe-opt-row:last-child").css("margin-bottom", "0");
      },
      stop: function (event, ui) {
        // Ensure all rows have equal spacing after sorting
        $(".jwcfe-opt-row").css("margin-bottom", "0px"); // Adjust the value as needed
        $(".jwcfe-opt-row:last-child").css("margin-bottom", "0"); // Remove bottom margin for the last item
      },
    });
  });

  return {
    saveCustomFieldForm: _saveCustomFieldForm,

    openNewFieldForm: _openNewFieldForm,

    openEditFieldForm: _openEditFieldForm,

    removeSelectedFields: _removeSelectedFields,
    deleteSingleField: _deleteSingleField,

    enableDisableSelectedFields: _enableDisableSelectedFields,

    fieldTypeChangeListner: _fieldTypeChangeListner,
    fieldTypeChangeListnerblock: _fieldTypeChangeListnerblock,

    selectAllCheckoutFields: _selectAllCheckoutFields,

    addNewOptionRow: addNewOptionRow,

    removeOptionRow: removeOptionRow,
  };
})(window.jQuery, window, document);

function saveCustomFieldForm(loaderPath, donePath) {
  jwcfe_settings.saveCustomFieldForm(loaderPath, donePath);
}

function saveFieldForm(tabName, pluginPath) {
  jwcfe_settings.saveFieldForm(tabName, pluginPath);
}

function jwcfeFieldTypeChangeListner(elm) {
  jwcfe_settings.fieldTypeChangeListner(elm);
}
function jwcfeFieldTypeChangeListnerblock(elm) {
  jwcfe_settings.fieldTypeChangeListnerblock(elm);
}

function jwcfeRuleOperandChangeListner(elm, loaderPath, donePath) {
  jwcfe_settings.RuleOperandChangeListner(elm, loaderPath, donePath);
}

function jwcfeRemoveRuleRow(elm) {
  jwcfe_settings.remove_rule_row(elm);
}

function jwcfeRemoveRuleRowAjax(elm) {
  jwcfe_settings.remove_rule_row_ajax(elm);
}

function openNewFieldForm(tabName) {
  jwcfe_settings.openNewFieldForm(tabName);
}

function openEditFieldForm(elm, rowId) {
  jwcfe_settings.openEditFieldForm(elm, rowId);
}

function removeSelectedFields() {
  jwcfe_settings.removeSelectedFields();
}

function jwcfeDeleteSingleField(elm) {
  jwcfe_settings.deleteSingleField(elm);
}

function enableSelectedFields() {
  jwcfe_settings.enableDisableSelectedFields(1);
}

function disableSelectedFields() {
  jwcfe_settings.enableDisableSelectedFields(0);
}

function jwcfeSelectAllCheckoutFields(elm) {
  jwcfe_settings.selectAllCheckoutFields(elm);
}

function jwcfeAddNewOptionRow(elm) {
  jwcfe_settings.addNewOptionRow(elm);
}

function jwcfeRemoveOptionRow(elm) {
  jwcfe_settings.removeOptionRow(elm);
}
