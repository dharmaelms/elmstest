  @if($general->setting['scorm_reports'] == "on") 
    <div>

  <div class="row scorm_tbl" id="scorm_header" style="display: table;float: left;">
  <button type="button" class="btn btn-sm btn-success scorm_export_btn" title="Export" id='scorm_export'><i class="fa fa-external-link-square"></i></button>
  
  <div class="col-md-1">
    <div class="cs-nav-btn scrom-nav-btn">
      <button type="button" id="prev" class="fa fa-angle-left datasrc btn btn-circle"></button>
    </div>
  </div>

  <div class="col-md-10">
    <div class="box">
      <div class="box-content">
        <table style="width:500px" class="table table-bordered fill-head table-striped scormTableData">
          <thead style="background: linear-gradient(#04b4c3,#f2f2f2);">
            <tr>
              <th>{{ trans('reports.scorm_ele_name') }}</th>
              <th>{{ trans('reports.status') }}</th>
              <th>{{ trans('reports.time_spent') }}</th>
              <th>{{ trans('reports.quiz_score') }}</th>
            </tr>
          </thead>          
          <tbody id="scorm_tbl_id">
          </tbody> 
        </table>
        <div class="alert alert-danger" id='no_record'>
           {{trans('admin/reports.no_more_records_found')}} 
          </div>
      </div>
    </div>
  </div>

  <div class="col-md-1">
    <div class="cs-nav-btn scrom-nav-btn">
      <button type="button" id="last" class="fa fa-angle-right datasrc btn btn-circle"></button>
    </div>
  </div>

  </div>
    </div>
  @endif
<script type="text/javascript">
  var current=0;
  var html_temp = "";
  var urlscorm = '{{URL::to('/reports/ajax-scorm-reports/')}}';
  $('#no_record').hide();
  if(current == 0)
  { 
    $('#prev').hide(); 
  }
  else
  { 
    $('#prev').show();
  }

  $('#prev').click(function()
    {
      if(current >= 1)
      {
        current--;
      }
      ajaxScormReport(urlscorm);
    });

    $('#last').click(function()
    {
      current++;
      ajaxScormReport(urlscorm);
    });

    ajaxScormReport = function(path){
      var no_set = current;
      var limit = 10;
       $.ajax({
        type: "GET",
        url : path+'/'+no_set+'/'+limit
    }).done(function(data){
      if(data.length > 0)
      {
        if(current == 0)
        { 
          $('#prev').hide(); 
        }
        else
        { 
          $('#prev').show();
        }
          
        if(data.length < limit)
        {
          $('#last').hide();
        } else {
          $('#last').show();
        }

        $('#no_record').hide();
        $.each(data, function(key, ele){
          if(ele.scorm_status == 'Completed')
          {
            var class_name = 'green-col';
          }else if(ele.scorm_status == 'In-progress'){
            var class_name = 'yellow-col';
          }else{
            var class_name = 'pink-col';
          }
          html_temp+="<tr><td>"+ele.scorm_name+"</td><td class='"+class_name+"'>"+ele.scorm_status+"</td><td>"+ele.total_time_spent+"</td><td>"+ele.score+"</td></tr>";
        });
        if(html_temp != ""){
          $('#scorm_tbl_id').html(html_temp);
          html_temp = "";
        } 
      } else{
        $('#last').hide();
        $('#no_record').show();
        current --;
      } 
      
    })
  }
  ajaxScormReport(urlscorm);
 var scorm_export = '{{ URL::to('/reports/export-scorm-reports') }}';
    $('#scorm_export').click(function(){
    window.location.href = scorm_export
  });
</script>
