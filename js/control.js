import { toast } from 'https://cdn.skypack.dev/wc-toast';

function validateEmail(email) {
    const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
    return emailRegex.test(email);
}

$(document).ready(function () {
   
    $("#login").click(function () {
        var email = $("#email").val();
        var password = $("#password").val();

        if (email === '' || password === '') {
            toast.error('Enter Email & Password.');
            return; 
        }

        if (!validateEmail(email)) {
            toast.error('Enter Valid Email');
            return;
        }

        $('#login').prop("disabled", true);
        $('#login').html('<span class="animate-spin border-2 border-white border-l-transparent rounded-full w-4 h-4 ltr:mr-1 rtl:ml-1 inline-block align-middle"></span>Sign in...');

        var params = {
            email: email,
            password: password,
        };
        $.ajax({
            type: "POST",
            url: "api/auth/login",
            data: params,
            error: function (e) {
                console.log(e);
                toast.error('An error occurred during login.');
                $('#login').html("Sign In");
                $('#login').prop("disabled", false);
            },
            success: function (data) {
                $('#login').html("Sign In");
                $('#login').prop("disabled", false);
                var json = JSON.parse(data);
                if (json.status === "1") {
                    toast.success(json.msg);
                    setTimeout(function () {
                        window.location.href = 'dashboard';
                    }, 1000);
                } else {
                    toast.error(json.msg);
                }
            }
        });
    });
    $("#forgot").click(function () {
        var email = $("#email").val();

        if (email === '' ) {
            toast.error('Enter Email');
            return; 
        }

        if (!validateEmail(email)) {
            toast.error('Enter Valid Email');
            return;
        }

        $('#forgot').prop("disabled", true);
        $('#forgot').html('<span class="animate-spin border-2 border-white border-l-transparent rounded-full w-4 h-4 ltr:mr-1 rtl:ml-1 inline-block align-middle"></span>Send Recovery Email');

        var params = {
            email: email
        };
        $.ajax({
            type: "POST",
            url: "api/auth/forgot",
            data: params,
            success: function (data) {
                $('#forgot').html("Send Recovery Email");
                $('#forgot').prop("disabled", false);
                var json = JSON.parse(data);
                if (json.status === "1") {
                    toast.success(json.msg);
                } else {
                    toast.error(json.msg);
                }
            }
        });
    });
        $("#change_pass").click(function () {
        var new_password = $("#new_password").val();
        var confirm_password = $("#confirm_password").val();
        var tokens = $("#tokens").val(); // Corrected variable name

        if (confirm_password === '' || new_password === '') {
            toast.error('Please Enter New Password and Confirm Password.');
            return; // Stop execution if old or new password is blank
        }

        // Disable the button and show loading spinner
        $(this).prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="visually-hidden">Loading...</span>');

        var params = {
            new_password: new_password,
            confirm_password: confirm_password,
            token: tokens, // Corrected variable name
        };
        $.ajax({
            type: "POST",
            url: "api/auth/new_password",
            data: params,
            dataType: "json", // Specify JSON response type
            success: function (json) {
                $('#change_pass').html("Set New Password").prop("disabled", false);
                if (json.status === "1") {
                    toast.success(json.msg);
                    setTimeout(function () {
                        window.location.href = 'password_changed';
                    }, 1000);
                } else {
                    toast.error(json.msg);
                }
            }         
        });
    });
        $("#register").click(function () {
        var widgetId;
        var first_name = $("#first_name").val();
        var last_name = $("#last_name").val();
        var email = $("#email").val();
        var password = $("#password").val();
        var refer_id = $("#refer_id").val();
        var name = first_name + " " + last_name;
        if (name === '' || email === '' || password === '' || last_name === '') {
            toast.error('Please Fill All Details');
            return; // Stop execution if old or new password is blank
        }
      if (!validateEmail(email)) {
            toast.error('Please Enter Valid Email');
            return;
        }
         var recaptchaResponse = grecaptcha.getResponse();
            if (!recaptchaResponse) {
          toast.error("Please Tick Captcha");
         return;
            } 
         $('#register').prop("disabled", true).html('<span class="animate-spin border-2 border-white border-l-transparent rounded-full w-4 h-4 ltr:mr-1 rtl:ml-1 inline-block align-middle"></span>Sign Up...');

        var params = {
            name: name,
            email: email,
            password: password, 
            refer_id: refer_id,
           "g-recaptcha-response": recaptchaResponse, 
       };
        $.ajax({
            type: "POST",
            url: "api/auth/register",
            data: params,
            dataType: "json", 
            success: function (json) {
                $('#register').html("Create Account").prop("disabled", false);
                grecaptcha.reset(widgetId);
                if (json.status === "1") {
                    toast.success(json.msg);
                    setTimeout(function () {
                        window.location.href = 'account_created';
                    }, 1000);
                } else {
                    toast.error(json.msg);
                }
            },
            error: function (e) {
                console.log(e);
                toast.error('An error occurred during Registration');
            }
        });
    });
});
