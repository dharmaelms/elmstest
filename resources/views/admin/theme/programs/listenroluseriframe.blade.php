@section('content')
@if ( Session::get('success') )
  <div class="alert alert-success">
  <button class="close" data-dismiss="alert">×</button>
  <!-- <strong>Success!</strong><br> -->
  {{ Session::get('success') }}
  </div>
  <?php Session::forget('success'); ?>
@endif

<script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
<link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
<script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
<script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
<script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
  
  
    <style>
	#main-content{
		background:white !important;
	}
	#datatable_info{
		display:none;
	}
	</style>
<!-- BEGIN Main Content -->
<div class="row">
    <div class="col-md-12">
        <div class="box">                  
            <div class="box-content"> 
             
                <div class="col-md-6">
                    <form class="form-horizontal" action="">
                        <div class="form-group">
                          <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b>{{trans('admin/program.showing')}} :</b></label>
                          <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
                            <?php $relfilter = Input::get('relfilter'); $relfilter = strtolower($relfilter);?>
                             <select class="form-control chosen" name="relfilter" onchange="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);" tabindex="1">
	                                    <option value="nonassigned" <?php if ($relfilter == 'nonassigned') echo 'selected';?>>{{trans('admin/program.non_assigned')}}</option>
										<option value="assigned" <?php if ($relfilter == 'assigned') echo 'selected';?>>{{trans('admin/program.assigned')}}</option>
	                                   
	                                </select>
                          </div>
                       </div>
                    </form>
                </div> 
            
			 
			  <div class="clearfix"></div>
              <table class="table table-advance" id="datatable">
                  <thead>
                      <tr>
                          <th style="width:18px"><input type="checkbox" id="allselect" /></th>
                          <th>{{trans('admin/user.username')}}</th>
                          <th>{{trans('admin/program.fullname')}}</th>
                          <th>{{trans('admin/program.email_id')}}</th> 
                          <th>{{trans('admin/program.created_on')}}</th>
                          <th>{{trans('admin/program.status')}}</th>
                      </tr>
                  </thead>
              </table>
            </div>
        </div>
    </div>
</div>
<!-- END Main Content -->

<script type="text/javascript">
  var flag_ck = 0;
 function updateCheckBoxVals(){
				$allcheckBoxes = $('#datatable td input[type="checkbox"]');
				if(typeof window.checkedBoxes != 'undefined'){
					$('#datatable td input[type="checkbox"]').each(function(index,value){
						var $value = $(value);
						if(typeof checkedBoxes[$value.val()] != "undefined")
							$('[value="'+$value.val()+'"]').prop('checked',true);
					})
				}
				if($allcheckBoxes.length > 0)
					if($allcheckBoxes.not(':checked').length > 0)
						$('#datatable thead tr th:first input[type="checkbox"]').prop('checked',false);
					else
						$('#datatable thead tr th:first input[type="checkbox"]').prop('checked',true);

				updateHeight();
			}


    simpleloader.init();
    $(document).ready(function(){
	
      /* code for DataTable begins here */
      window.datatableOBJ = $('#datatable').on('processing.dt',function(event,settings,flag){
        $('#datatable_processing').hide();
        if(flag == true)
          simpleloader.fadeIn();
        else
          simpleloader.fadeOut();
      }).on('draw.dt',function(event,settings,flag){
        $('.show-tooltip').tooltip({container: 'body', delay: {show:500}});
      }).dataTable({
		"autoWidth": false,
      "serverSide": true,
      "ajax": {
              "url": "{{URL::to('/cp/contentfeedmanagement/user-list-ajax')}}",
              "data": function ( d ) {
                  d.filter = $('[name="filter"]').val();
                  d.view = "iframe";
                  d.relfilter = $('[name="relfilter"]').val();
				  
<?php if(isset($relid) && preg_match('/^\d+$/',$relid)) echo 'd.relid = "'.$relid.'"' ?>;
<?php if(isset($relfilter) && preg_match('/^\d+$/',$relfilter)) echo 'd.relfilter = "'.$relfilter.'"' ?>;
  
              }
          },
            "aaSorting": [[ 4, 'desc' ]],
            "columnDefs": [ { "targets": [0,5], "orderable": false } ],
            "drawCallback" : updateCheckBoxVals
        });

    $('#datatable_filter input').unbind().bind('keyup', function(e) {
      if(e.keyCode == 13) {
        datatableOBJ.fnFilter(this.value);
        // datatableOBJ.fnStandingRedraw();
      }
    });
  /* Code for dataTable ends here */

	/* Code to get the selected checkboxes in datatable starts here*/
	if(typeof window.checkedBoxes == 'undefined')
		window.checkedBoxes = {};
	$('#datatable').on('change','td input[type="checkbox"]',function(){
		var $this = $(this);
		if($this.prop('checked'))
			checkedBoxes[$this.val()] = $($this).parent().next().text();
		else
			delete checkedBoxes[$this.val()];
    if(flag_ck == 0){
          updateCheckBoxVals();
      }
	});
	

	$('#allselect').change(function(e){
		$('#datatable td input[type="checkbox"]').prop('checked',$(this).prop('checked'));
    flag_ck = 1;
		$('#datatable td input[type="checkbox"]').trigger('change');
    flag_ck = 0;
		e.stopImmediatePropagation();
	});
	
	
	/* Code to get the selected checkboxes in datatable ends here*/
});

</script>
@stop
 
