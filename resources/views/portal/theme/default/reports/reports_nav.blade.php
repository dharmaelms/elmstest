@section('content')
<link rel="stylesheet" type="text/css" href="{{URL::to('/portal/theme/default/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css')}}" />
<script type="text/javascript" src="{{ URL::asset("portal/theme/default/plugins/jquery.twbsPagination.min.js") }}"></script>


<style type="text/css">
    .user-profile-info{
        clear: both;
    }

    #report_chart_cf{
        float: left;    
    }
    #report_chart{
        float: left;
    }

    #report_chart_cf{
        float: left;
    }
    #report_tbl_fc{
        /*padding-left: 200px;*/
        border:1px solid #eeeeee;
        float: left;
    }
    #report_tbl_fc th, #tbl_aofimp_cf th{
        border-bottom: 2px solid #dddddd;
          padding: 5px 15px !important; 

    }
    #report_tbl_fc td, #tbl_aofimp_cf td {
        border-bottom: 1px solid #eeeeee;
         padding: 5px 15px !important; 
        /*text-align: center;*/
    }
    #report_tbl{
        border:1px solid #eeeeee;
        /*padding: 5px 15px !important;*/
    }
    #report_tbl th, #tbl_quiz th, #tbl_pref th {
        border-bottom: 2px solid #dddddd;
          padding: 5px 15px !important; 
    }
    #report_tbl td, #tbl_quiz td, #tbl_pref td {
        border-bottom: 1px solid #eeeeee;
          padding: 5px 15px !important;
    }
    #tbl_quiz_cf td{
        border-bottom: 1px solid #eeeeee;  
        padding: 15px; 
    }
    #tbl_quiz_cf th{
        border-bottom: 1px solid #eeeeee;   
        padding: 15px;
    }
    #tbl_pref {
        width: 100%;
    }
    #tbl_quiz {
        width: 100%;
    }
    #tbl_quiz_cf{
        width: 100%;
    }
    .resp-tab-content {
        border-bottom: 0px;
        border-left: 0px;
        border-right:0px;
        border-top: 1px solid #5AB1D0;
    }

     #tbl_aofimp_cf .btn{
      background: transparent;
      color: blue;
      }
     .cs-daterange .control-label {
      width: 60px;margin-top: 8px;
     }
  div.radio {
    margin-left: 0;
    margin-right: 0;
    margin-top: -4px;
  }
  .radio-inline , .radio-inline + .radio-inline {
    padding-left: 6px; margin-left: 0;
  }
</style>

<div class="tab-content">
    <div class="tab-pane active" id="attempted">
      <div id="parentHorizontalTab" class="myactivity-section">
        <ul class="resp-tabs-list hor_1">
          <li> {{Lang::get('reports.perf')}}</li>
          <li> {{Lang::get('reports.compl')}}</li>
        </ul>
        <div class="resp-tabs-container hor_1">
          <div>
            <div class="row">
              <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="portlet box blue margin-bottom-20">
                  <div class="portlet-title">
                    <div class="caption"> {{Lang::get('reports.course_quiz')}}</div><div class="tools"><a href="javascript:;" class="expand"></a></div>
                  </div>
                  <div class="portlet-body black" style="display: none;">
                    <form action="">
                      <div class="row col-md-12 sm-margin">
                        <div class="form-group">
                          <label class="col-md-3 control-label" style="margin-top: 6px;padding-left:0;"> {{Lang::get('reports.link')}}{{Lang::get('reports.all_courses')}}</label>
                          <div class="col-md-6" style="padding-left: 0;">
                            <select class="form-control chosen gallery-cat">
                              <option> {{Lang::get('reports.sports')}}</option>
                              <option> {{Lang::get('reports.science')}}</option>
                              <option> {{Lang::get('reports.maths')}}</option>
                              <option> {{Lang::get('reports.Algebra')}}</option>
                              <option> {{Lang::get('reports.arithmetic')}}</option>
                            </select>
                          </div>
                        </div>
                      </div>
                      <div class="row xs-margin">
                        <div class="col-md-6 col-sm-12 col-xs-12">
                          <div class="form-group">
                            <label> {{Lang::get('reports.quiz_type')}}</label>
                            <div class="radio-list">
                              <label class="radio-inline">
                              <div class="radio" id="uniform-optionsRadios4"><span class="checked"><input type="radio" checked=""></span></div> {{Lang::get('reports.mock_quiz')}}</label>
                              <label class="radio-inline">
                              <div class="radio" id="uniform-optionsRadios5"><span><input type="radio"></span></div> {{Lang::get('reports.practice_quiz')}} </label>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-12 col-xs-12">
                          <div class="form-group">
                            <label> {{Lang::get('reports.perf_criteria')}}</label>
                            <div class="radio-list">
                              <label class="radio-inline">
                              <div class="radio" id="uniform-optionsRadios6"><span class="checked"><input type="radio"  checked=""></span></div> {{Lang::get('reports._score')}}  </label>
                              <label class="radio-inline">
                              <div class="radio" id="uniform-optionsRadios7"><span><input type="radio"></span></div> {{Lang::get('reports.accuracy')}} </label>
                              <label class="radio-inline">
                              <div class="radio" id="uniform-optionsRadios8"><span><input type="radio"></span></div>  {{Lang::get('reports.speed')}} </label>
                            </div>
                          </div>
                        </div>
                      </div>
                      <a href="#" class="btn btn-sm btn-success"> {{Lang::get('reports.apply_now')}}</a>&nbsp;&nbsp;<a href="#" class="btn btn-sm btn-success" title="Export"><i class="fa fa-share-square-o"></i></a>
                    </form>
                  </div>
                </div>
              </div>
              <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="portlet box blue margin-bottom-20">
                  <div class="portlet-title">
                    <div class="caption"> {{Lang::get('reports.direct_quizzes')}}</div><div class="tools"><a href="javascript:;" class="expand"></a></div>
                  </div>
                  <div class="portlet-body black" style="display: none;">
                    <form action="">
                      <div class="row xs-margin">
                        <div class="col-md-6 col-sm-12 col-xs-12">
                          <div class="form-group">
                            <label> {{Lang::get('reports.quiz_type')}}</label>
                            <div class="radio-list">
                              <label class="radio-inline">
                              <div class="radio" id="uniform-optionsRadios4"><span class="checked"><input type="radio" checked=""></span></div> {{Lang::get('reports.mock_quiz')}} </label>
                              <label class="radio-inline">
                              <div class="radio" id="uniform-optionsRadios5"><span><input type="radio"></span></div>  {{Lang::get('reports.practice_quiz')}}</label>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-12 col-xs-12">
                          <div class="form-group">
                            <label> {{Lang::get('reports.perf_criteria')}}</label>
                            <div class="radio-list">
                              <label class="radio-inline">
                              <div class="radio" id="uniform-optionsRadios6"><span class="checked"><input type="radio"  checked=""></span></div>  {{Lang::get('reports.score')}} </label>
                              <label class="radio-inline">
                              <div class="radio" id="uniform-optionsRadios7"><span><input type="radio"></span></div>  {{Lang::get('reports.accuracy')}} </label>
                              <label class="radio-inline">
                              <div class="radio" id="uniform-optionsRadios8"><span><input type="radio"></span></div> {{Lang::get('reports.speed')}}</label>
                            </div>
                          </div>
                        </div>
                      </div>
                      <a href="#" class="btn btn-sm btn-success"> {{Lang::get('reports.apply_now')}}</a>&nbsp;&nbsp;<a href="#" class="btn btn-sm btn-primary" title="Export"><i class="fa fa-share-square-o"></i></a>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- tab 1 -->
          <div>
            <p> {{Lang::get('reports.tab_2_container')}}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
<script type="text/javascript">
    $(document).ready(function() {
        //Horizontal Tab
        $('#parentHorizontalTab').easyResponsiveTabs({
            type: 'default', //Types: default, vertical, accordion
            width: 'auto', //auto or any width like 600px
            fit: true, // 100% fit in a container
            tabidentify: 'hor_1', // The tab groups identifier
            activate: function(event) { // Callback function if tab is switched
                var $tab = $(this);
                var $info = $('#nested-tabInfo');
                var $name = $('span', $info);
                $name.text($tab.text());
                $info.show();
            }
        });
        // Child Tab
        $('#ChildVerticalTab_1').easyResponsiveTabs({
            type: 'vertical',
            width: 'auto',
            fit: true,
            tabidentify: 'ver_1', // The tab groups identifier
            activetab_bg: '#fff', // background color for active tabs in this group
            inactive_bg: '#F5F5F5', // background color for inactive tabs in this group
            active_border_color: '#c1c1c1', // border color for active tabs heads in this group
            active_content_border_color: '#5AB1D0' // border color for active tabs contect in this group so that it matches the tab head border
        });
        //Vertical Tab
        $('#parentVerticalTab').easyResponsiveTabs({
            type: 'vertical', //Types: default, vertical, accordion
            width: 'auto', //auto or any width like 600px
            fit: true, // 100% fit in a container
            closed: 'accordion', // Start closed if in accordion view
            tabidentify: 'hor_1', // The tab groups identifier
            activate: function(event) { // Callback function if tab is switched
                var $tab = $(this);
                var $info = $('#nested-tabInfo2');
                var $name = $('span', $info);
                $name.text($tab.text());
                $info.show();
            }
        });
    });
</script>
@stop