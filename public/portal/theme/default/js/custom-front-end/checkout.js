$( document ).ready(function() {
   var loginstatus = $('#checkout_login').val();
   switch(loginstatus)
   {
   		case 'checkout_login' :
   			 $('#checkout-content').collapse('show');
   			 $('#payment-address-content').collapse('hide');
   			 $('#confirm-content').collapse('hide');
   			 $('#payment-method-content').collapse('hide');
   			 break;
   		case 'checkout_biling_address' :
   			 $('#checkout-content').collapse('hide');
   			 $('#payment-address-content').collapse('show');
   			 $('#confirm-content').collapse('hide');
   			 $('#payment-method-content').collapse('hide');
   			 break;
   		case 'checkout_order_summary' :
   			 $('#checkout-content').collapse('hide');
   			 $('#payment-address-content').collapse('hide');
   			 $('#confirm-content').collapse('show');
   			 $('#payment-method-content').collapse('hide');
   			 break;
   		case 'checkout_make_payment' :
   			 $('#checkout-content').collapse('hide');
   			 $('#payment-address-content').collapse('hide');
   			 $('#confirm-content').collapse('hide');
   			 $('#payment-method-content').collapse('show');
   			 break;
   }
}
);

function saveAddress()
{
	 clearErrors();
	 $.ajax(
			 {
		      url: $('#base_address_url').val(),
		      type: "post",
		      data: {
		     	 		'address': $('#address').val(),
		      			'region_state': $('#region_state').val(),
		      			'telephone':  $('#telephone').val(),
		      			'country' : $('#country').val(),
		      			'city' : $('#city').val(),
		      			'post_code': $('#post_code').val()
		      		},
		      success: function(data)
		      {
		        if(!data['success'])
		        {		     
		        	
		        	if(data['message']['address']){
		        		$('#address').after("<p class='error'>"+data['message']['address']+"</p>");
		        	}
		        	if(data['message']['region_state']){
		        		$('#region_state').after("<p class='error'>"+data['message']['region_state']+"</p>");
		        	}
		        	if(data['message']['telephone']){
		        		$('#telephone').after("<p class='error'>"+data['message']['telephone']+"</p>");
		        	}
		        	if(data['message']['country']){
		        		$('#country').after("<p class='error'>"+data['message']['country']+"</p>");
		        	}
		        	if(data['message']['city']){
		        		$('#city').after("<p class='error'>"+data['message']['city']+"</p>");
		        	}
		        	if(data['message']['post_code']){
		        		$('#post_code').after("<p class='error'>"+data['message']['post_code']+"</p>");
		        	}
		        }
		      }
		    }
	   );
}

function clearErrors(inputid)
{
	$(".error").remove();
}



function applyPromoCode()
{
	if($('#promo_code').val() != ""){
	var loginURL = $('#promocode_url').val();
        $.ajax({
            type : 'post', // define the type of HTTP verb we want to use (POST for our form)
            url : $('#promocode_url').val(), // the url where we want to POST
            data : {
            	'coupanCode':$('#promo_code').val()	
            }, 
        }).success(function(data) {
        	var errors = $.parseJSON(data);
        	// console.log(errors);
        	if(typeof errors['success'] != 'undefined' && errors['success'])
        	{
        		$('#coupan_section').addClass('hide');
        		$('#success_coupanCode').text(errors['success']);
        		$('#net_total').text(errors['net_total']);
        		$('#discount_availed').text(errors['discount']);
        		$('#net_total_input').val(errors['net_total']);
        		$('#discount_availed_input').val(errors['discount']);
        		$('#error_coupanCode').text('');
        	}
        	else
        	{
        		$('#error_coupanCode').text(errors['error']);
        	}
      });
    }
    else
    {
    	alert("Please enter the text to the right promocode.");
    }
}
