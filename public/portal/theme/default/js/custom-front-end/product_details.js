var URLToRedirect;
 $('.is_loggedin').on('click',function(event){
 	window.URLToRedirect = $(this).attr('href');
 	window.EnrollProductId = $(this).data('productid');
 	window.Baseurl = $(this).data('baseurl');
    event.preventDefault();
      $('#signinreg').modal('show');
});