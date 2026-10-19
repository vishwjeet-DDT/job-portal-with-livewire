


// type text input validation
document.querySelectorAll('.typeText').forEach(function (textParameter) {
    textParameter.addEventListener('input', function () {
      let text_val = this.value;
      this.value = text_val.slice(0, 30).replace(/[^a-zA-Z\s]/g, '');
     });
 });

 /// type number input validation
document.querySelectorAll('.typeNumber').forEach(function (numberParameter) {
    numberParameter.addEventListener('input', function (e) {
        let num_val = numberParameter.value;
        e.target.value = num_val.slice(0, 10);
     });
});

// password validations
document.querySelectorAll('.typePassword').forEach(function(passwordParameter){
    passwordParameter.addEventListener('input', function (e) {
           passwordParameter.value = e.target.value.slice(0, 20).trim();
     });
});

// year experience
document.querySelectorAll('.yearExp').forEach(function (yearEx) {
    yearEx.addEventListener('input', function (e) {
        yearEx.value = e.target.value.slice(0, 2);
     });
 });

 // validate data
document.querySelectorAll(".typeDate").forEach(function (dateInput) {
    let today = new Date().toISOString().split("T")[0];
    dateInput.setAttribute("min", today);
});

 /// countryCode
 document.querySelectorAll('.phoneCode').forEach(function (phocode) {
    phocode.addEventListener('input', function (e) {
        phocode.value = e.target.value.slice(0, 3);
     });
 });

 //shortCode code
  document.querySelectorAll('.shortCode').forEach(function (shortcode) {
    shortcode.addEventListener('input', function (e) {
        shortcode.value = e.target.value.slice(0, 6);
     });
 });


 // formSubmit
//  document.querySelectorAll('.typePassword').forEach(function(current){
//     current.addEventListener('input', function (e) {
//         console.log(this.value);

//      });
//  });
