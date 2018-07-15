@section('content')
  @if ( Session::get('success') )
    <div class="alert alert-success" id="alert-success">
      <button class="close" data-dismiss="alert">×</button>
      <!-- <strong>Success!</strong> -->
      {{ Session::get('success') }}
    </div>
    <?php Session::forget('success'); ?>
  @endif
  @if ( Session::get('error'))
    <div class="alert alert-danger">
      <button class="close" data-dismiss="alert">×</button>
     <!--  <strong>Error!</strong> -->
      {{ Session::get('error') }}
    </div>
    <?php Session::forget('error'); ?>
  @endif
  @if ( Session::get('warning'))
    <div class="alert alert-warning">
      <button class="close" data-dismiss="alert">×</button>
      <strong>{{ trans('admin/country.warning') }}</strong>
      {{ Session::get('warning') }}
    </div>
    <?php Session::forget('warning'); ?>
  @endif
<?php use App\Model\Common; ?>
  <link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
  <script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
  <link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
  <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
  <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>

<!-- BEGIN Main Content -->

<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-title">
                <div class="box-tool">
                    <a data-action="collapse" href="#"><i class="icon-chevron-up"></i></a>
                </div>
            </div>                    
            <div class="box-content">               
                <div class="btn-toolbar clearfix">
	                <div class="pull-right">
	                	<?php 
	                           $add_country = has_admin_permission(ModuleEnum::COUNTRY, CountryPermission::ADD_COUNTRY); 
	                            if($add_country == true)
	                            {?>
	                  	<a class="btn btn-primary btn-sm" href="{{ URL::to('cp/country/add-country') }}">
	                          <span class="btn btn-circle blue show-tooltip custom-btm">
	                            <i class="fa fa-plus"></i>
	                          </span>&nbsp;<?php echo trans('admin/country.add_country'); ?>
	                    </a>&nbsp;&nbsp;
	                    <?php } ?>
	                </div>
                </div><br>            
                <div class="clearfix"></div>
                <table class="table table-advance" id="datatable">
                    <thead>
	                    <tr>
	                        <th style="width: 150px;">{{ trans("admin/country.edit_country_name_label") }}</th>
                          <th>{{ trans("admin/country.edit_currency_name_label") }}</th>
                          <th>{{ trans("admin/country.code_2_letters") }}</th> 
                          <th>{{ trans("admin/country.code_3_letters") }}</th>
                          <th>{{ trans("admin/country.add_currency_symbol_label") }}</th>
	                        <?php $edit_country = has_admin_permission(ModuleEnum::COUNTRY, CountryPermission::EDIT_COUNTRY); 
	                            if($edit_country == true)
	                            {?> 
	                        <th>{{ trans("admin/country.actions") }}</th>
	                        <?php } ?>
	                    </tr>
                    </thead>
                    <tbody>
                    	@foreach($data as $countrydata)
                    	<tr>
                    		<td style="width: 150px;">{{ $countrydata['name'] }}</td>
                    		<td>
                    			@if(isset($countrydata['currency_name']))
                    			{{ $countrydata['currency_name'] }}
                    			@endif
                    		</td>
                    		<td>{{ $countrydata['country_code'] }}</td>
                    		<td>{{ $countrydata['iso3'] }}</td>
                    		<td>
                    			@if(isset($countrydata['currency_symbol']))
                    			{{ $countrydata['currency_symbol'] }}
                    			@endif
                    		</td>
                    		<?php $edit_country = has_admin_permission(ModuleEnum::COUNTRY, CountryPermission::EDIT_COUNTRY); 
	                            if($edit_country == true)
	                            {?> 
                    		<td>
                    			<a class="btn btn-circle show-tooltip" title="{{ trans('admin/country.edit_country_title') }}" href="{{ URL::to('cp/country/edit-country/'.$countrydata['iso3']) }}"><i class="fa fa-edit"></i></a>
                    		</td>
                    		<?php } ?>
                    	</tr>
                    	@endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- END Main Content -->

<script type="text/javascript">
	  $('#datatable').DataTable({
      
    });
</script>
@stop
