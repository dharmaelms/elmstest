
/**
 *
 *  Every update to this file has to be replicate to public/portal/theme/default/js/akamaiRegenerateToken.js
 *
**/

	function callAjaxToCreateNewToken(key,jwplayerId)
	{
		//var jwplayer.key = "{{ config("app.jwplayer.key") }}";
		var regeneratedToken = null;
		var video = null;
		var errorMsg = "Request failed refresh the page to view the video again";

		var request = $.ajax({
				  url: '/regenerateakamaitoken/'+key,
				  method: "POST",
				  cache: false,
				  ContentType: 'application/json',		  
			});

		request.success(function( result,status,jqXHR ) {	
		 // process token from result and return it to calling function		 
		 if(typeof(result) === 'object')
		 {
		 	if(result.status === 200 && result.error === false && result.authorized === true)
		 	{
		 		regeneratedToken 	= result.token;
				var playerInstance 	= jwplayer(jwplayerId);		 		
		 		var asset = result.asset;		 		
		 		try
		 		{
			 		if( asset.hasOwnProperty('akamai_details') && (typeof asset.akamai_details == 'object') )
			 		{		 			
			 			if(asset.akamai_details.hasOwnProperty('delivery_html5_url'))
			 			{
			 				video = asset.akamai_details.delivery_html5_url+'?'+regeneratedToken;
			 			}
			 			else if(asset.akamai_details.hasOwnProperty('stream_success_html5'))
			 			{
			 				video = asset.akamai_details.stream_success_html5+'?'+regeneratedToken;
			 			}

			 			playerInstance.load(
		 				[{
					    	file: video,
					    	
					  	}]);

						playerInstance.play();
			 			
			 		}
			 		else
			 		{
			 			alert(errorMsg);
			 		}		 		
		 			
		 		}

		 		catch ( e ) {
                  //alert("Error: " + e.description );
                  alert(errorMsg);
               }

		 	}
		 }
		 
		});

		request.fail(function( jqXHR, textStatus ) {
		  //alert( "Request failed please try again : " + textStatus );
		  alert(errorMsg);
		});

	}