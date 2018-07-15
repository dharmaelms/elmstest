var URLToRedirect="";
var EnrollProductId="";
var UID='';
var Baseurl='';
var catalog_url = '';
var posts_url = '';
function loginByPopUp()
{
  var loginURL = $('#login_url').val();
  var currentUrl = window.location.href; 
  var siteURL = $('#baseurl').val()+'/';

  var dashboardUrl = $('#dashboard_url').val();

        $.ajax({
            type : 'post', // define the type of HTTP verb we want to use (POST for our form)
            url : $('#login_url').val(), // the url where we want to POST
            data : {
                'email' : $('#email').val(),
                'password' :$('#password').val(),
                'login_popup':'yes'
            }, 
        }).success(function(data) {
          if(data == "yes")
          {
            enrollUserToProduct();
            if(currentUrl == siteURL || currentUrl == siteURL+'login')
            {
              window.location = dashboardUrl;
            }
            else
            {
              window.location = URLToRedirect;
            }
            
          }
          else
          {
            $('#error_text_popup').html(data);
          }
      });
}

function registerByPopUp()
{
    var siteURL = $('#baseurl').val()+'/'; 
    var currentUrl = window.location.pathname; 
    var query_parameter = window.location.search;
    $(document).on("ajaxStart.firstCall", function () {
        $('#loading-image').show();
    });
    $(document).on("ajaxStop.firstCall", function () {
        $('#loading-image').hide();
    });
        $('#err_reg_email').text('');
        $('#err_reg_username').text('');
        $('#err_reg_password').text('');
        $('#err_reg_firstname').text('');
        $('#err_reg_phone').text('');
        $('#err_reg_lastname').text('');
        $('#err_reg_confpassword').text('');
       if($('#terms_and_condition').prop('checked') != true)
        {
            alert("Please read and then agree with terms and conditions");
            return false;
        }
        $.ajax({
            type : 'post', // define the type of HTTP verb we want to use (POST for our form)
            url : $('#register_url').val(), // the url where we want to POST
            data : {
                'email' : $('#reg_email').val(),
                'password' :$('#reg_password').val(),
                'username': $('#reg_username').val(),
                'firstname' : $('#reg_firstname').val(),
                'lastname' : $('#reg_lastname').val(),
                'mobile' : $('#reg_phone').val(),
                'password_confirmation': $('#reg_confpassword').val(),
                'register_popup':'yes',
                'timezone' : 'Asia/Kolkata',
                'current_url' : encodeURIComponent(currentUrl+query_parameter),
                'catalog_url' : catalog_url,
                'posts_url' : posts_url
            }, 
        }).success(function(data) {   
          if(data == "success")
          {
            if($('#email_verification').val() == 'ACTIVE')
            {
              
              $('#signinreg').modal('hide');
              window.location = siteURL+'auth/register-success?email='+$('#reg_email').val();
              return false;
            }
         $.ajax({
                  type : 'post', // define the type of HTTP verb we want to use (POST for our form)
                  url : $('#login_url').val(), // the url where we want to POST
                  data : {
                      'email' : $('#reg_email').val(),
                      'password' :$('#reg_password').val(),
                      'login_popup':'yes'
                  }, 
              }).success(function(data) {
                if(data == "yes")
                {
                  $('#loading-image').hide();
                	enrollUserToProduct();
                  $('#signinreg').hide();
                  window.location = URLToRedirect;
                }
                else
                {
                  $('#error_text').html(data);
                }
            });
          }
          else
          {
            var errors = $.parseJSON(data);
            if(typeof errors['email'] != 'undefined' && errors['email'])
            {
              $('#err_reg_email').text(errors['email'][0]);
              
            }
            if(typeof errors['username'] != 'undefined' && errors['username'])
            {
              $('#err_reg_username').text(errors['username'][0]);
              
            }
            if(typeof errors['password'] != 'undefined' && errors['password'])
            {
              $('#err_reg_password').text(errors['password'][0]);
              
            }
            if(typeof errors['password_confirmation'] != 'undefined' && errors['password_confirmation'])
            {
              $('#err_reg_confpassword').text(errors['password_confirmation'][0]);
              
            }
            if(typeof errors['firstname'] != 'undefined' && errors['firstname'] )
            {
              var str = errors['firstname'][0];
              $('#err_reg_firstname').text(str.replace('firstname','first name'));
            
              
            }
            if(typeof errors['lastname'] != 'undefined' && errors['lastname'] )
            {
              var str = errors['lastname'][0];
              $('#err_reg_lastname').text(str);
            
              
            }
            if(typeof errors['mobile'] != 'undefined' && errors['mobile'])
            {
              $('#err_reg_phone').text(errors['mobile'][0]);
              
            }
          }
      });
}

/** Nayan - Function to enroll user to specific product**/
function enrollUserToProduct()
{
	$.ajax({
            type : 'post', // define the type of HTTP verb we want to use (POST for our form)
            url : Baseurl+'/enroll-user-to-product', // the url where we want to POST
            data : {
            		'product_id' : EnrollProductId,
            		'user_id' : UID
            		
            }, 
        }).success(function(data) {		
        	// alert(data);
      });
	
}

$( "#reg" ).submit(function( event ) {
  registerByPopUp();
  event.preventDefault();
});

$( "#signin_popup" ).submit(function( event ) {
  loginByPopUp();
  event.preventDefault();
});


/**** Password Strenght****/
var pass_strength;
function IsEnoughLength(str,length){
    if ((str == null) || isNaN(length))
        return false;
    else if (str.length < length)
        return false;
    return true;
}
function HasMixedCase(passwd){
    if(passwd.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/))
        if ( passwd.split(/[a-z]/).length >= 3 && passwd.split(/[A-Z]/).length >= 3)
            return true;
        else
            return false;
    else
        return false;
}
function HasNumeral(passwd){
    if(passwd.match(/[0-9]/))
        return true;
    else
        return false;
}
function HasSpecialChars(passwd){
    if(passwd.match(/.[!,@,#,$,%,^,&,*,?,_,~]/))
        return true;
    else
        return false;
}
function CheckPasswordStrength(pwd)
{
    if (IsEnoughLength(pwd,14) && HasMixedCase(pwd) && HasNumeral(pwd) && HasSpecialChars(pwd))
        pass_strength = "<b><font style='color:olive'>Very strong</font></b>";
    else if (IsEnoughLength(pwd,10) && HasMixedCase(pwd) && HasNumeral(pwd) && HasSpecialChars(pwd))
        pass_strength = "<b><font style='color:Blue'>Strong</font></b>";
    else if (IsEnoughLength(pwd,10) && HasMixedCase(pwd) && HasNumeral(pwd))
        pass_strength = "<b><font style='color:Green'>Medium</font></b>";
    else
        pass_strength = "<b><font style='color:red'>Weak</font></b>";
    document.getElementById('pwd_strength').innerHTML =  pass_strength;
}

$('.socialite').on('click',function(event){
  event.preventDefault();
  var subscription_slug = null;
  if(catalog_url){
     subscription_slug = "?subscription_slug=restricted-" +catalog_url;
  } else if (posts_url){
     subscription_slug = "?subscription_slug=general-" +posts_url;
  }
  if(subscription_slug !== null) {
    window.location.href = $(this).attr('href') +subscription_slug;    
  } else {
    window.location.href = $(this).attr('href');
  }
});
/****Ends Here*****/