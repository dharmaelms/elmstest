<link rel="stylesheet" type="text/css" href="{{ URL::to("admin/css/select2.min.css") }}">
<script type="text/javascript" src="{{ URL::to("admin/js/select2.min.js") }}"></script>
<select id="selected_feed" class="form-control chosen"></select>
<script>
var select_channel_placeholder = '';
<?php
if (isset($channel_name)) {
?>
    select_channel_placeholder = '{{$channel_name}}';
<?php
} else {
?>
    select_channel_placeholder = '{{trans('admin/reports.select_channel_placeholder')}}';
<?php
}
?>

$(document).ready(function(){
    $("#selected_feed").select2({
        placeholder : htmlDecode(select_channel_placeholder),
        allowClear : true,
        ajax : {
            delay : 500,
            type : "GET",
            url : '{{URL::to('/cp/reports/channel-full-name')}}',
            data : function(params){
                return {
                    query: params
                };
            },
            contentType : "application/x-www-form-urlencoded; charset=UTF-8",
            dataType : "json",
            processResults : function(response, params){
                return { results : response };
            },
            cache : false
        }
    });
});

function htmlDecode(value) {
  return $("<textarea/>").html(value).text();
}
</script>