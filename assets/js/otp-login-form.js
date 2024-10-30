window.$ = jQuery;
var authenticate;
var authenticated;
var noOfCycles = 1;
var apiCall = 0;
$(document).ready(function () {
  if ($("#login-mojo-form").length > 0) {
    $("#login-mojo-form").closest("form").append($("#login-mojo-form"));
  }
  $("#login-mojo-form").removeClass("hide");
  $("#login-mojo-button").click(function () {
    $("#login-mojo-form .loading-animate").show();
    $("#login-mojo-form #login-mojo-result").hide();
    $("#login-mojo-form .login-mojo-message").html("");
    $("#login-mojo-form .login-mojo-message")
      .removeClass("show")
      .removeClass("success")
      .removeClass("error")
      .removeClass("processing");
    _authenticate();

    // the requests are performed every 3 seconds
    authenticate = setInterval(function () {
      _authenticate();
    }, 3000);
  });
});

function _authenticate() {
  var ajaxurl = $("#ajax-url").val();
  var data = {
    action: "loginmojo_authenticate_form",
    post_type: "POST",
  };
  if (apiCall == 0) {
    apiCall = 1;
    // Assign handlers immediately after making the request,
    // and remember the jqxhr object for this request
    var jqxhr = jQuery
      .post(
        ajaxurl,
        data,
        function (response) {
          if (response.validate === false) {
            $("#login-mojo-form .login-mojo-message")
              .removeClass("success")
              .removeClass("processing");
            $("#login-mojo-form .login-mojo-message").html(response.message);
            $("#login-mojo-form .login-mojo-message")
              .addClass("show")
              .addClass("error");
            $("#login-mojo-form #login-mojo-result").show();
            $("#login-mojo-form .loading-animate").hide();
            clearInterval(authenticate);
          } else {
            if (response.status) {
              clearInterval(authenticate);
              // Call Whats Login
              apiCall = 0;
              noOfCycles = 1;
              if (response.logged_in) {
                $("#login-mojo-form .login-mojo-message")
                  .removeClass("error")
                  .removeClass("processing");
                $("#login-mojo-form .login-mojo-message").html(
                  response.message
                );
                $("#login-mojo-form .login-mojo-message")
                  .addClass("show")
                  .addClass("success");
                $("#login-mojo-form #login-mojo-result").show();
                $("#login-mojo-form .loading-animate").hide();
                setTimeout(function () {
                  window.location.href = response.redirect_to;
                }, 1000);
              } else {
                authenticated = setInterval(function () {
                  whatsLogin();
                }, 3000);
                window
                  .open(
                    response.redirect_to,
                    "scrollbars=yes,width=400,height=500,top=300"
                  )
                  .focus();
              }
            } else {
              if (noOfCycles > 50) {
                $("#login-mojo-form .login-mojo-message")
                  .removeClass("success")
                  .removeClass("processing");
                $("#login-mojo-form .login-mojo-message").html(
                  "Facing some problem in WhatsApp service. Please try after sometime."
                );
                $("#login-mojo-form .login-mojo-message")
                  .addClass("show")
                  .addClass("error");
                $("#login-mojo-form #login-mojo-result").show();
                $("#login-mojo-form .loading-animate").hide();
                clearInterval(authenticate);
              }
            }
          }
          apiCall = 0;
        },
        "json"
      )
      .done(function () {
        noOfCycles = noOfCycles + 1;
        apiCall = 0;
      });
  }
}

// Whats Login
function whatsLogin() {
  var baseurl = $("#base_url").val();
  if (apiCall == 0 && noOfCycles <= 51) {
    apiCall = 1;
    var ajaxurl = $("#ajax-url").val();
    var data = {
      action: "loginmojo_after_authenticate_form",
      post_type: "POST",
    };
    // Assign handlers immediately after making the request,
    // and remember the jqxhr object for this request
    var jqxhr = jQuery
      .post(
        ajaxurl,
        data,
        function (response) {
          if (response.validate === false) {
            $("#login-mojo-form .login-mojo-message")
              .removeClass("success")
              .removeClass("error");
            $("#login-mojo-form .login-mojo-message")
              .addClass("show")
              .addClass("processing");
            $("#login-mojo-form .login-mojo-message").html(response.message);
            $("#login-mojo-form #login-mojo-result").show();
            $("#login-mojo-form .loading-animate").hide();
            clearInterval(authenticated);
            noOfCycles = 55;
          } else {
            if (response.status) {
              clearInterval(authenticated);
              $("#login-mojo-form .loading-animate").hide();
              $("#login-mojo-form .login-mojo-message")
                .removeClass("error")
                .removeClass("processing");
              $("#login-mojo-form .login-mojo-message").html(response.message);
              $("#login-mojo-form .login-mojo-message")
                .addClass("show")
                .addClass("success");
              $("#login-mojo-form #login-mojo-result").show();
              setTimeout(function () {
                window.location.href = response.redirect_to;
              }, 1000);
            } else {
              if (noOfCycles > 50) {
                $("#login-mojo-form .login-mojo-message")
                  .removeClass("success")
                  .removeClass("processing");
                $("#login-mojo-form .login-mojo-message")
                  .addClass("show")
                  .addClass("error");
                $("#login-mojo-form .login-mojo-message").html(
                  response.message
                );
                $("#login-mojo-form #login-mojo-result").show();
                $("#login-mojo-form .loading-animate").hide();
                clearInterval(authenticated);
              } else {
                $("#login-mojo-form .login-mojo-message")
                  .removeClass("success")
                  .removeClass("processing");
                $("#login-mojo-form .login-mojo-message")
                  .addClass("show")
                  .addClass("processing");
                $("#login-mojo-form .login-mojo-message").html(
                  response.message
                );
                $("#login-mojo-form #login-mojo-result").show();
                $("#login-mojo-form .loading-animate").hide();
              }
            }
          }
          apiCall = 0;
        },
        "json"
      )
      .done(function () {
        noOfCycles = noOfCycles + 1;
        apiCall = 0;
      });
  }
}
