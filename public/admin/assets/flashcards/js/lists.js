$(document).ready(function() {	
	var cardIds = [], searchText = '', itemsPerPage = 10, status = 'ACTIVE';
	simpleloader.fadeIn();
    var lists = $.get(chost + pageUrl);
    lists.done(function(response) {
    	$('.select-all').prop('checked', false);
        updateTable(response);
        simpleloader.fadeOut();
        return false;
    })
    lists.fail(function(response){
    	simpleloader.fadeOut();
    });
    <!-- select all -->
    $(document).on('click', '.select-all', function(){
        var allChecked = $(this).prop('checked');
    	if(allChecked){
    		$('.cards-id').each(function(){
               $(this).prop('checked', true);
               if($.inArray(this.value, cardIds) === -1){
                    cardIds.push(this.value);
                }
            });            
    	}else {
    		$('.cards-id').each(function(){
               $(this).prop('checked', false);
               if($.inArray(this.value, cardIds) !== -1){
                    var index = cardIds.indexOf(this.value);
                    if (index >= 0) {
                      cardIds.splice( index, 1 );
                    }
                }
            });
    	}
    });

    <!-- select specific questions -->
    $(document).on('click', '.cards-id', function(){
        var checked = $(this).prop('checked'),
            value = $(this).val();
        if(checked){
            if($.inArray(this.value, cardIds) === -1){
                cardIds.push(value);
            }
        }else {
            if($.inArray(this.value, cardIds) !== -1){
                var index = cardIds.indexOf(this.value);
                if (index >= 0) {
                  cardIds.splice( index, 1 );
                }
            }
        }   
    });
    <!-- pagination -->
    $(document).on('click','.list.pagination li a', function(e){
    	e.preventDefault();
    	if(!$(this).hasClass('disabled')){
    		simpleloader.fadeIn();
    		var page = $(this).attr('href').split('page=')[1],
    			search = $('#search').val(),
    			itemsPerPage = $('#count').val(),
                status = $('#status').val();
    		var search = $.ajax({url: chost+pageUrl,data:{ 'search': search , 'page': page, 'itemsPerPage': itemsPerPage, 'status': status}, type:'get'});
			search.done(function(response){
				updateTable(response);
				simpleloader.fadeOut();
				return false;
			});
			search.fail(function(response){
				console.log(response);
				simpleloader.fadeOut();
			});
	    	return false;
	   	}
	   	return false;    	
    });
    <!-- table size -->
    $(document).on('change', '#count, #status', function(e){
    	search(e);
    });
    <!-- search -->
    $(document).on('submit', '#search-all', function(e){
    	search(e);
    });
    $(document).on('click','table th', function(e){
        var $this = $(this),
            $class = 'sorting',
            order = 'ASC',
            column = $this.data('column');
            sort= $this.data('sort');
        if(sort){
            simpleloader.fadeIn(); 
            if($this.hasClass('sorting')){
                $class = 'sorting_asc',order = 'ASC';
            }else if($this.hasClass('sorting_asc')){
                $class = 'sorting_desc',order = 'DESC';
            }else if($this.hasClass('sorting_desc')){
                $class = 'sorting_asc',order = 'ASC';
            }
            initializeData();        
            var sorting = $.ajax({url: chost+pageUrl,data:{ 'search' : searchText, 'itemsPerPage':itemsPerPage, 'status': status, 'column' : column, 'order': order }, type:'get'});
            sorting.done(function(response){
                updateTable(response);
                $('[data-column='+column+']').removeClass().addClass($class); 
            });
            sorting.fail(function(response){
                console.log(response);
            });       
            simpleloader.fadeOut();        
        } 
        
        
         
    });
    <!-- delete -->
    $(document).on('click','.delete',function(e){
        e.preventDefault();
        if(confirm('Are you sure to delete?')){
            var deleteCard = $.ajax({
                url: chost+'/cp/flashcards/delete',
                type: 'POST',
                data:{'slug':$(this).data('slug')},
                dataType: 'json',
            });
            deleteCard.done(function(response){
                if(response.status == 'success'){
                    alert(response.message);
                }
                else if(response.status == 'failure'){
                    alert(response.message);
                }
                $('#search-all').submit();
            });
            deleteCard.fail(function(response){
                console.log(response);
            });
        }
    });
    function initializeData(){
        searchText = $('#search').val(),
        itemsPerPage = $('#count').val(),
        status = $('#status').val();
    }
    function search(event){
        simpleloader.fadeIn();
        initializeData();
        var search = $.ajax({url: chost+pageUrl,data:{ 'search' : searchText, 'itemsPerPage':itemsPerPage, 'status': status }, type:'get'});
        search.done(function(response){
            updateTable(response);
        });
        search.fail(function(response){
            console.log(response);
        });
        simpleloader.fadeOut(); 
        event.preventDefault();       
    }
    function updateTable(response) {
        if (response == '') {
            $('table > tbody').html('<tr><td colspan="2">No Results found</td></tr>');
            $('#table_info, .pagination').hide();
        } else {
            $('#table').html(response);
            updateSelected();
        }
    }
    function updateSelected(){
        $('.cards-id').each(function(){
            if( $.inArray(this.value, cardIds) != -1){
                $(this).prop('checked', true);
            }
        });
        if($('#datatable tbody tr').length === $('.cards-id:checked').length){
        	$('.select-all').prop('checked', true);
    	}            
    }
    if(window.location.hash.substr(1) == 'created'){
        $('.created').show(function(){
            setTimeout(function(){$('.created').fadeOut();},2000);
        });
        updateState();
    }
    if(window.location.hash.substr(1) == 'updated'){
        $('.updated').show(function(){
            setTimeout(function(){$('.updated').fadeOut();},2000);
        });
        updateState();
    }
    function updateState(){
        history.pushState(null, null,'#hide');
    }
    
});