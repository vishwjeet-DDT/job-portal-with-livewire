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

// pincode input validation
document.querySelectorAll('.typeNumberPincode').forEach(function (numberParameter) {
    numberParameter.addEventListener('input', function (e) {
        let num_val = numberParameter.value;
        e.target.value = num_val.slice(0, 6);
     });
});

// password validations
document.querySelectorAll('.typePassword').forEach(function(passwordParameter){
    passwordParameter.addEventListener('input', function (e) { 
           passwordParameter.value = e.target.value.slice(0, 20).trim();
     });
});

 // profile image set function
 document.getElementById('upload_profile_img').addEventListener('change', function (event) { 
        let file =  event.target.files[0];
            if(file){
                const imageUrl = URL.createObjectURL(file);
                document.getElementById('profile_image').src = imageUrl;
            };
 });

 $(document).ready(function () {  
    $('#job_search_btn').on('click', function () { 
       let job_search = $('#job_search');
       job_search.show();
       $('#close_seach').show();
    });
    $('#close_seach').on('click', function () { 
        $('#job_search').hide();
     }); 

 });
