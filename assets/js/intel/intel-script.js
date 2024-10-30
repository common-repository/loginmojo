jQuery(document).ready(function () {
  var input = document.querySelectorAll(
    ".loginmojo-input-mobile_phone, #loginmojo-input-mobile_phone, .user-mobile_phone-wrap #mobile_phone, .mobile_phone #mobile_phone"
  );
  for (var i = 0; i < input.length; i++) {
    if (input[i]) {
      window.intlTelInput(input[i], {
        onlyCountries: loginmojo_intel_tel_input.only_countries,
        preferredCountries: loginmojo_intel_tel_input.preferred_countries,
        autoHideDialCode: loginmojo_intel_tel_input.auto_hide,
        nationalMode: loginmojo_intel_tel_input.national_mode,
        separateDialCode: loginmojo_intel_tel_input.separate_dial,
        utilsScript: loginmojo_intel_tel_input.util_js,
      });
    }
  }
});
