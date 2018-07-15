<style type="text/css">
	@media all and (min-width: 1200px){
	.custom-width {
	    width: 23% !important;
	}
}
</style>

<form class="form-horizontal" name="myForm" action="">
    <div class="form-group">
        <label class="col-sm-2 col-lg-1 control-label" style="padding-right:0;text-align:right; width: 98px"><b>
        	{{trans('admin/reports.find')}} : &nbsp;</b>
        </label>
        <div class="col-sm-3 col-lg-3" style="padding-left:1%;">
        	@if(isset($channel_name))
				@include('admin.theme.reports.reportschannellists', ['channel_name' => $channel_name])
			@else
				@include('admin.theme.reports.reportschannellists')
			@endif
        </div> 
	    <div class="col-sm-4 col-lg-2 pull-right" style="padding-right:3%;">
	    	<div class="input-group pull-right" style="padding-left: 2px;">
	            <button class="show-tooltip btn btn-sucess" title="{{trans('admin/reports.info')}}" type="button" id="info_report_btn"><i class="fa fa-info"></i></button>
	        </div>
	      	<div class="input-group pull-right">
	            <button class="show-tooltip btn btn-sucess" title="{{trans('admin/reports.report_export')}}" type="button" id="exp_report"><i class="fa fa-download"></i></button>
	        </div>
	    </div>
    </div>
</form>