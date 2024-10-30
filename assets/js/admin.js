jQuery(document).ready(function () {
  jQuery(".chosen-select").chosen({ width: "25em" });
  // Check about page
  if (jQuery(".loginmojo-welcome").length) {
    jQuery(".nav-tab-wrapper a").click(function () {
      var tab_id = jQuery(this).attr("data-tab");

      if (tab_id == "link") {
        return true;
      }

      jQuery(".nav-tab-wrapper a").removeClass("nav-tab-active");
      jQuery(".tab-content").removeClass("current");

      jQuery("[data-tab=" + tab_id + "]").addClass("nav-tab-active");
      jQuery("[data-content=" + tab_id + "]").addClass("current");

      return false;
    });
  }

  if (jQuery(".loginmojootprepeater").length) {
    jQuery(".loginmojootprepeater").repeater({
      initEmpty: false,
      show: function () {
        jQuery(this).slideDown();
      },
      hide: function (deleteElement) {
        if (confirm("Are you sure you want to delete this item?")) {
          jQuery(this).slideUp(deleteElement);
        }
      },
      isFirstItemUndeletable: true,
    });
  }

  if (jQuery(".loginmojootpblocktimerepeater").length) {
    jQuery(".loginmojootpblocktimerepeater").repeater({
      initEmpty: false,
      show: function () {
        jQuery(this).slideDown();
      },
      hide: function (deleteElement) {
        if (confirm("Are you sure you want to delete this item?")) {
          jQuery(this).slideUp(deleteElement);
        }
      },
      isFirstItemUndeletable: true,
    });
  }
  jQuery("#webhook_url_generate").on("click", function () {
    var templateUrl =
      jQuery("#base_url").val() + "/wp-json/loginmojo/v1/" + generateString(10);
    jQuery(".gateway_loginmojo_webhook_url").val(templateUrl.toString());
    jQuery("#webhook_url_generate").html("Generated");
    jQuery("#webhook_url_generate")
      .addClass("button-primary")
      .removeClass("button-success");
    setTimeout(function () {
      jQuery("#webhook_url_generate").html("Generate");
      jQuery("#webhook_url_generate")
        .addClass("button-success")
        .removeClass("button-primary");
    }, 3000);
  });
  jQuery("#webhook_url_copy").on("click", function () {
    /* Get the text field */
    var copyText = document.getElementsByClassName(
      "gateway_loginmojo_webhook_url"
    )[0];

    /* Select the text field */
    copyText.select();
    copyText.setSelectionRange(0, 99999); /* For mobile devices */

    /* Copy the text inside the text field */
    document.execCommand("copy");

    jQuery("#webhook_url_copy").html("Copied");
    jQuery("#webhook_url_copy")
      .addClass("button-primary")
      .removeClass("button-success");
    setTimeout(function () {
      jQuery("#webhook_url_copy").html("Copy");
      jQuery("#webhook_url_copy")
        .addClass("button-success")
        .removeClass("button-primary");
    }, 3000);
  });
});

function generateString(length) {
  const characters =
    "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
  let result = "";
  const charactersLength = characters.length;
  for (let i = 0; i < length; i++) {
    result += characters.charAt(Math.floor(Math.random() * charactersLength));
  }
  return result;
}
