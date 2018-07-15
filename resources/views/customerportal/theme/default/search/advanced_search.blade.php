
@section('content') 
      <!-- BEGIN PAGE HEADER-->
      <div class="page-bar">
        <ul class="page-breadcrumb">
          <li><a href="{{URL::to('/')}}">Home</a><i class="fa fa-angle-right"></i></li>
          <li><a href="#">Advanced Search</a></li>
        </ul>
      </div>
      
      <div class="search-data">
        <div class="row md-margin">
          <div class="col-md-12 col-sm-12 col-xs-12">
            <h3 class="margin-top-0"><strong>Advanced Search</strong></h3>
          </div>
        </div>

        <div class="row">
          <form action='search' class="form-horizontal" id="validation-form" method="get" role="form" >
            <div class="col-md-6 col-sm-6 col-xs-12">
              <div class="form-body">
                <div class="form-group">
                  <label class="col-md-4 control-label">Title</label>
                  <div class="col-md-8">
                    <input type="text" name="title" class="form-control">
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-md-4 control-label">Description</label>
                  <div class="col-md-8">
                    <input type="text" name="description" class="form-control">
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-md-4 control-label">Keywords</label>
                  <div class="col-md-8">
                    <input type="text" name="keywords" class="form-control">
                  </div>
                </div>
                <div class="form-group">
                    <label for="numberfield" class="col-sm-4  control-label">Category</label>
                    <div class="col-sm-8">
                       <select name="category[]" class="form-control input-sm" multiple >
                         @foreach($categories as $each)
                          @if($each['parents']==null)
                               <option value="{{ $each['category_name']}}" <?php if( isset($cat_ids) && in_array($each['category_id'],$cat_ids)){?> checked="checked" <?php } ?>> {{ ucwords(strtolower($each['category_name']))}} </option>
                            @endif
                            @if(isset($each['children']))
                                  @foreach($each['children'] as $sub_cat)
                                      @if(isset($cat_id_name[$sub_cat['category_id']]))
                                         <option value="{{$cat_id_name[$sub_cat['category_id']]}}" <?php if( isset($cat_ids) && in_array($sub_cat['category_id'],$cat_ids)){?> checked="checked" <?php } ?>>{{ ucwords(strtolower($each['category_name']))}} / {{ucwords(strtolower($cat_id_name[$sub_cat['category_id']]))}} </option>
                                      @endif
                                  @endforeach
                              @endif
                        @endforeach 
                    </select>
                   </div>
                </div>
                <div class="form-group">
                    <label for="numberfield" class="col-sm-4 control-label">Format</label>
                    <div class="col-sm-8 controls">
                        <select name="format[]" class="form-control input-sm" multiple>


                            <option value="channel" > {{Lang::get('search.facet_a')}} </option>
                            <option value="image"> {{Lang::get('search.facet_b')}} </option>
                            <option value="audio"> {{Lang::get('search.facet_c')}} </option>
                            <option value="video"> {{Lang::get('search.facet_d')}} </option>
                            <option value="document"> {{Lang::get('search.facet_e')}} </option>
                            <option value="event" > {{Lang::get('search.facet_f')}} </option>
                            <option value="post"> {{Lang::get('search.facet_g')}} </option>
                            <option value="quiz"> {{Lang::get('search.facet_h')}}</option>

                        </select>     
                   </div>
                </div>
              </div>
            </div>

            <div class="col-md-offset-1 col-md-5 col-sm-6 col-xs-12">
              <h4><strong>Specify Date</strong></h4>

              <div class="form-group">
                <div class="col-md-12">
                  <div class="radio-list">
                    <label>
                    <input type="radio" name="date" id="all_date" class="dates" value="All" checked> All Dates </label>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <div class="radio-list">
                  <label>
                    <div class="col-lg-4 col-md-6">
                      <input type="radio" name="date" id="num_days" class="dates" value="days" onclick=EnableField('num_days')> 
                      In the last
                    </div>
                    <div class="col-lg-6 col-md-6">
                      <select class="form-control input-sm" name="days" id="days">
                        <option value="15" >15 Days</option>
                        <option value="30" >1 Month</option>
                        <option value="90" >3 Months</option>
                        <option value="180" >6 Months</option>
                      </select>
                    </div>
                  </label>
                </div>
              </div>

              <div class="form-group">
                <div class="radio-list">
                  <label>
                    <div class="col-lg-4 col-md-6">
                      <input type="radio" name="date" id="dates" value="dates" class="dates" onclick=EnableField('dates')> 
                      Between
                    </div>
                    <div class="col-lg-4 col-md-12">
                      <input type="text" class="form-control todo-taskbody-due input-sm date-picker" name="start" placeholder="Satrt Date" id="sdate">
                    </div>
                    <div class="col-lg-4 col-md-12">
                      <input type="text" class="form-control todo-taskbody-due input-sm date-picker" name="end" placeholder="End Date" id="edate">
                    </div>
                    <span id='error'></span>
                  </label>
                </div>
              </div>
            </div>
            <div class="col-md-offset-2 col-md-10 col-sm-offset-2 col-sm-10 col-xs-12">
              <div class="form-group">&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="button" class="btn btn-success" value="Search" onclick='Search()';>
                <input type="reset" class="btn btn-success" value="Clear">
              </div>
                <input type="hidden" name="type" value="advanced">
            </div>
          </form>
        </div>
      </div><!--main row-->

<script type="text/javascript">
  $(document).ready(function () {
    document.getElementById('days').disabled = true;
    document.getElementById('sdate').disabled=true;  
    document.getElementById('edate').disabled=true;  
});
function EnableField(id)
{
  if(id == 'num_days')
  {
    document.getElementById('days').disabled = false;
    document.getElementById('sdate').disabled=true;  
    document.getElementById('edate').disabled=true  
  }

  else if(id == 'dates')
  {
    document.getElementById('days').disabled = true;
    document.getElementById('sdate').disabled=false;  
    document.getElementById('edate').disabled=false;
  }

}
function Search()
{
  if($('#dates').is(':checked'))
  {
    var sdate=$('#sdate').val();
    var edate=$('#edate').val();
    
    if(sdate=='' || edate=='')
    {
      $('#error').html("<span class='help-inline' style='color:#f00'>Please Specify Start and End dates.</span>");

    }
    else if(sdate > edate)
    {
      $('#error').html("<span class='help-inline' style='color:#f00'>End date should be greater than Start date.</span>");
    }
    else
    {
      $('#validation-form').submit();
    }
    
  }
  else
  {
    $('#validation-form').submit();
  }
    
}
</script>
@stop
