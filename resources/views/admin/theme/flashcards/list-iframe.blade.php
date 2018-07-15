@section('content')
<script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}"/>
<div class="box">
    <div class="box-content">
        <div class="row dataTables_wrapper form-inline no-footer" id="datatable_wrapper" >
                    <div class="col-xs-12 col-sm-6">
                        <div class="dataTables_length" id="datatable_length">
                            <label>
                                <select name="datatable_length form-control" aria-controls="datatable" class="" id="count">
                                    <option value="10">
                                        10
                                    </option>
                                    <option value="25">
                                        25
                                    </option>
                                    <option value="50">
                                        50
                                    </option>
                                    <option value="100">
                                        100
                                    </option>
                                </select>
                                Records per Page
                            </label>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-3 pull-right dataTables_filter">
                        <form id="search-all">
                            <label>
                                {{ trans('admin/flashcards.search') }}:
                                <input type="text" class="form-control" id="search"/>
                            </label>
                        </form>
                    </div>
                </div>
        <div id="table"></div>
    </div>
</div>

<script type="text/javascript">
var chost = '{{ URL::to('') }}',pageUrl = "/cp/flashcards/list-ajax-iframe?program_type={{ Input::get("program_type", null) }}&program_slug={{ Input::get("program_slug", null) }}&post_slug={{ Input::get("post_slug", null) }}";
$(document).ready(function() {
    simpleloader.fadeIn();
    var cardIds = [];
    $allcheckBoxes = $('#datatable td input[type="checkbox"]');
    if($allcheckBoxes.length > 0)
        if($allcheckBoxes.not(':checked').length > 0)
            $('#datatable thead tr th:first input[type="checkbox"]').prop('checked',false);
        else
            $('#datatable thead tr th:first input[type="checkbox"]').prop('checked',true);
    if(typeof window.checkedBoxes == 'undefined')
        window.checkedBoxes = {};
    if(typeof window.media_types == 'undefined')
        window.media_types = {};
    var $datatable = $('#datatable');

    var selected = $('.flashcarddata', window.parent.document);
    
    $.each(selected, function(index, value){
        cardIds.push($(this).data('id').toString());
    });
    $('#checkall').change(function(e){
        $('#datatable td input[type="checkbox"]').prop('checked',$(this).prop('checked'));
        $('#datatable td input[type="checkbox"]').trigger('change');
        e.stopImmediatePropagation();
    });
    
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
                    AddCheckedValues($(this).attr('data-id'),$(this).attr('data-slug'),$(this).attr('data-title'));
                }
            });            
        }else {
            $('.cards-id').each(function(){
               $(this).prop('checked', false);
               if($.inArray(this.value, cardIds) !== -1){
                    var index = cardIds.indexOf(this.value);
                    if (index >= 0) {
                      cardIds.splice( index, 1 );
                      RemoveCheckedValues($(this).attr('data-id'));
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
                AddCheckedValues($(this).attr('data-id'),$(this).attr('data-slug'),$(this).attr('data-title'));
            }
        }else {
            if($.inArray(this.value, cardIds) !== -1){
                var index = cardIds.indexOf(this.value);
                if (index >= 0) {
                  cardIds.splice( index, 1 );
                  RemoveCheckedValues($(this).attr('data-id'));
                }
            }
        }
    });
    $(document).on('click','.pagination li a', function(e){
        e.preventDefault();
        if(!$(this).hasClass('disabled')){
            simpleloader.fadeIn();
            var page = $(this).attr('href').split('page=')[1],
                search = $('#search').val(),
                itemsPerPage = $('#count').val();
            var search = $.ajax({url: chost+pageUrl,data:{ 'search': search , 'page': page, 'itemsPerPage': itemsPerPage}, type:'get'});
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
    $(document).on('change', '#count', function(e){
        simpleloader.fadeIn();
        var search = $('#search').val(),
                itemsPerPage = $('#count').val();
        var search = $.ajax({url: chost+pageUrl,data:{ 'search' : search, 'itemsPerPage':itemsPerPage }, type:'get'});
        search.done(function(response){
            updateTable(response);
            simpleloader.fadeOut();
        });
        search.fail(function(response){
            console.log(response);
            simpleloader.fadeOut();
        });
        e.preventDefault();
    })
    $(document).on('submit', '#search-all', function(e){
        simpleloader.fadeIn();
        var search = search = $('#search').val(),
                itemsPerPage = $('#count').val();
        var search = $.ajax({url: chost+pageUrl,data:{ 'search' : search, 'itemsPerPage':itemsPerPage }, type:'get'});
        search.done(function(response){
            updateTable(response);
            simpleloader.fadeOut();
        });
        search.fail(function(response){
            simpleloader.fadeOut();
            console.log(response);
        });
        e.preventDefault();
    })
    function updateTable(response) {
        if (response == '') {
            $('table > tbody').html('<tr><td colspan="2">No Results found</td></tr>');
            $('#table_info, .pagination').hide();
        } else {
            $('#table').html(response);
            updateSelected();
            updateHeight();
        }
    }
    function updateSelected(){
        if(cardIds.length > 0){
            $('.cards-id').each(function(){
                if( $.inArray(this.value, cardIds) != -1){
                    $(this).prop('checked', true);
                    AddCheckedValues($(this).attr('data-id'),$(this).attr('data-slug'),$(this).attr('data-title'))
                }
            });
        }  
        if($('#datatable tbody tr').length === $('.cards-id:checked').length){
            $('.select-all').prop('checked', true);
        }     
    }
    function AddCheckedValues(id, slug, title){
        checkedBoxes[parseInt(id)]= {'slug':slug,'title': title};
        $('#selectedcount', window.parent.document).html(Object.keys(checkedBoxes).length+' selected');
    }
    function RemoveCheckedValues(id){
        delete checkedBoxes[id];
        $('#selectedcount', window.parent.document).html(Object.keys(checkedBoxes).length+' selected'); 
    }
});
</script>
@stop