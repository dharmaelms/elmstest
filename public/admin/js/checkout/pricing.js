function savePrice()
{
	$('#pr-save').attr("disabled", true);
	$('#pr-save').text('Updating.....');
	//$('#price_list').html('...');
	$.post( $('#pricing_url').val(), 
			{ 
				sellable_id: $('#Sellable_id').val(),
				sellable_type: $('#Sellable_type').val(),
				country: $('#country').val(),
				price: $('#price').val()
		    }
		  ).done(
		  	function( data ) {
				$('#pr-save').attr("disabled", false);
				$('#pr-save').html('<i class="fa fa-check"></i> Save');
		    	$('#price_list').html(data);
		  });


}

function loadPricelist()
{
	$.post( $('#pricing_list_url').val(), 
			{ 
				sellable_id: $('#Sellable_id').val(),
				sellable_type: $('#Sellable_type').val(),
		    }
		  ).done(
		  	function( data ) {
		    	$('#price_list').html(data);
		  });
}

$("#sel-tab").submit(function(e){
    e.preventDefault();
	savePrice();
    $("#sel-tab").on("submit",savePrice());
});
