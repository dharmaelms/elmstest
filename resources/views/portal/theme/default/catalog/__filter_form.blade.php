<form action="{{URL::to('catalog')}}" id="filter_form" class="hide">
    <input type="hidden" name="category_name" id="category_name">
    <input type="hidden" name="search_item" id="search_item">
</form>
<script type="text/javascript">
  $('.filter-items-in-catalog').click(
    function(){
        var $this = $(this);
        var arr = ['course', 'content_feed', 'collection'];
        if(typeof $(this).data('category-slug') === "undefined")
        {
            $('#category_name').val($('#category_slug').val());
        }
        else
        {
            $('#category_name').val($(this).data('category-slug'));
        }
        if($('input:checkbox').length > 1)
        {
            if ($this.val() === 'all') {
                $('input:checkbox').each(
                    function()
                    {
                        if (jQuery.inArray($(this).val(), arr) != -1) {
                            if (!$this.is(":checked")) {
                                $(this).prop('checked', false);
                            } else {
                                $(this).prop( "checked", true);
                            }
                        }
                    }
                );
            } else if (jQuery.inArray($this.val(), arr) != -1) {
                var count = arr.length;
                $('input:checkbox').each(
                    function()
                    {
                        if (jQuery.inArray($(this).val(), arr) != -1) {
                            if ($(this).is(":checked")) {
                                count--;
                            }
                        }
                        
                    }
                );
                if (count == 0) {
                    $('input:checkbox').each(
                        function()
                        {
                            if($(this).val() === 'all') {
                                $(this).prop( "checked", true);
                            }
                        }
                    );
                } else {
                   $('input:checkbox').each(
                        function()
                        {
                            if($(this).val() === 'all') {
                                $(this).prop( "checked", false);
                            }
                        }
                    );
                }
            }
        }
        $('.product-type').clone().appendTo("#filter_form");
        //$('#program_type').val($('#program_type[]').val());
        $('#search_item').val($('#catalog_search').val());
        $('.catalog-view1').html("<div class='center center-align'> <img src='{{URL::to('portal/theme/default/img/loader1.gif')}}' width='60px' alt='Loader' /></div>");
        $('#filter_form').submit();
        $('#filter_form').empty();
        }
    );  
    $("#catalog_search").keydown(function (e) {
        if (e.keyCode == 13) {
            $('#search_icon').click();
        }
    });
</script>
