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
            <!-- <strong>Error!</strong> -->
            {{ Session::get('error') }}
        </div>
        <?php Session::forget('error'); ?>
    @endif
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
    <script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
    <script src="{{ URL::asset('admin/js/readmore.js')}}"></script>
    @if($timed_sections)
        <p class="pull-right">{{ trans('admin/assessment.quiz_duration') }} {{ gmdate('H:i', $duration*60) }}</p>
    @endif
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-title">
                    <div class="box-tool">
                        <a data-action="collapse" href="#"><i class="fa fa-chevron-up"></i></a>
                        <a data-action="close" href="#"><i class="fa fa-times"></i></a>
                    </div>
                </div>
                <div class="box-content">
                    <div class="btn-toolbar pull-right clearfix">
                        <div class="btn-group">                            
                            @if(!$attempted)                                
                                @if($timed_sections && $duration > 0)
                                    @if($remaining_time)
                                        <div class="btn-group"> <a class="btn btn-primary btn-sm" href="{{url::to('/cp/section/add-section/'.$slug)}}"> <span class="btn btn-circle blue custom-btm"> <i class="fa fa-plus"></i> </span>&nbsp;<?php echo trans('admin/assessment.add_new_section');?></a>&nbsp;&nbsp; </div>
                                    @endif
                                @else
                                    <div class="btn-group"> <a class="btn btn-primary btn-sm" href="{{url::to('/cp/section/add-section/'.$slug)}}"> <span class="btn btn-circle blue custom-btm"> <i class="fa fa-plus"></i> </span>&nbsp;<?php echo trans('admin/assessment.add_new_section');?></a>&nbsp;&nbsp; </div>
                                @endif
                            @endif
                            <div class="btn-group"> <a class="btn btn-primary btn-sm" href="{{url::to('/cp/assessment/list-quiz')}}"> <span class="btn btn-circle blue custom-btm"> <i class="fa fa-chevron-left"></i> </span>&nbsp;<?php echo trans('admin/assessment.back_to_quiz');?></a>&nbsp;&nbsp; </div>
                        </div>
                    </div>
                    <br/><br/>
                    <div class="clearfix"></div>
                    <?php
                        $delete_quiz = has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::DELETE_QUIZ);
                        $edit_quiz = has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::EDIT_QUIZ);
                    ?>
                    <table class="table table-advance" id="datatable">
                        <thead>
                            <tr>
                                <th>{{ trans('admin/assessment.section')}}</th>
                                <th>{{ trans('admin/assessment.questions')}}</th>
                                @if($timed_sections)
                                    <th>{{ trans('admin/assessment.duration') }} (hh:mm)</th>
                                @endif
                                <th>{{ trans('admin/assessment.marks')}}</th>
                                @if($edit_quiz == true || $delete_quiz == true)
                                <th>{{ trans('admin/assessment.actions')}}</th>
                                @else
                                <script>$targetarr.pop()</script>
                                @endif
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="deletemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="row custom-box">
                            <div class="col-md-12">
                                <div class="box">
                                    <div class="box-title">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                        <h3 class="modal-header-title" >
                                            <i class="icon-file"></i>
                                                {{ trans('admin/assessment.delete_section') }}
                                        </h3>                                                
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="padding: 20px;">
                        {{ trans('admin/assessment.section_delete_confirmation') }}
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-danger">{{ trans('admin/assessment.yes') }}</a>
                        <a class="btn btn-success" data-dismiss="modal">{{ trans('admin/assessment.close') }}</a>
                    </div>
                </div>
            </div>
        </div>
        <style type="text/css">
        .custom-table1{
            padding: 0 20px;
        }
        .custom-table1 table td,
        .custom-table1 table th{
            padding: 5px !important;
        }
        </style>
                <div class="modal fade" id="deletemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="row custom-box">
                            <div class="col-md-12">
                                <div class="box">
                                    <div class="box-title">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                        <h3 class="modal-header-title">
                                            <i class="icon-file"></i>
                                                {{ trans('admin/assessment.delete_section') }}
                                        </h3>                                                
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="padding: 20px">
                       {{ trans('admin/assessment.section_delete_confirmation') }}
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-danger">{{ trans('admin/assessment.yes') }}</a>
                        <a class="btn btn-success" data-dismiss="modal">{{ trans('admin/assessment.close') }}</a>
                    </div>
                </div>
            </div>
        </div>
        <script>
        /*  var start_page = {{Input::get('start', 0)}};
            var length_page = {{Input::get('limit', 10)}};*/
            var  start_page  = {{Input::get('start',0)}};
            var  length_page = {{Input::get('limit',10)}};
            var  search_var  = "{{Input::get('search','')}}";
            var  order_by_var= "{{Input::get('order_by','0 asc')}}";
            var  order = order_by_var.split(' ')[0];
            var  _by   = order_by_var.split(' ')[1];

            function updateCheckBoxVals(){
                $allcheckBoxes = $('#datatable td input[type="checkbox"]');
                if(typeof window.checkedBoxes != 'undefined'){
                    $allcheckBoxes.each(function(index,value){
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
            }
            $(document).ready(function(){
                $('#alert-success').delay(5000).fadeOut();
                /* code for DataTable begins here */
                var $datatable = $('#datatable');
                window.datatableOBJ = $datatable.on('processing.dt',function(event,settings,flag){
                    if(flag == true)
                        simpleloader.fadeIn();
                    else
                        simpleloader.fadeOut();
                }).on('draw.dt',function(event,settings,flag){
                    $('.show-tooltip').tooltip({container: 'body', delay: {show:500}});
                    $('#datatable tr td:nth-child(1) div').each(function(){
                        if($(this).height() > 75){
                            $(this).readmore({maxHeight: 55,moreLink: '<a href="#" style="padding-left:8px">Read more</a>',lessLink: '<a href="#" style="padding-left:8px">Close</a>'});
                        }
                    })
                }).dataTable({
                    "serverSide": true,
                    "ajax": {
                        "url": "{{URL::to('/cp/section/ajax-list-section/'.$slug)}}",
                        "data": function ( d ) {
                            d.filter = $('[name="filter"]').val();
                        }
                    },
                    
                    "iDisplayLength": 10,
                    "aaSorting": [Number(order), _by],
                    "columnDefs": [ { "targets": [0,3], "orderable": false } ],
                    "drawCallback" : updateCheckBoxVals,
                    "iDisplayStart": start_page,
                    "pageLength": length_page,
                    "oSearch": {"sSearch": search_var}
                });

                $('#datatable_filter input').unbind().bind('keyup', function(e) {
                    if(e.keyCode == 13) {
                        datatableOBJ.fnFilter(this.value);
                    }
                });
                /* Code for dataTable ends here */

                $datatable.on('click','.deletepacket',function(e){
                    e.preventDefault();
                    var $this = $(this);
                    var $deletemodal = $('#deletemodal');
                    $deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href')).end().modal('show');
                })

                /* Code for view content feed details starts here */

                $(document).on('click','.deletemedia',function(e){
                    e.preventDefault();
                    var $this = $(this);
                    var $deletemodal = $('#deletemodal');
                    // if(typeof window.location.href.split('?')[1] != "undefined")
                    //  $deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href') + "?" + window.location.href.split('?')[1])
                    // else
                        $deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href'))
                    $deletemodal.modal('show');
                });

                /* Code for view content feed details ends here */

                /* Code for showing post relations and deleting */
                $datatable.on('click','.postrelations',function(e){
                    e.preventDefault();
                    var $this = $(this);
                    var $postrelations = $('#postrelations');
                    simpleloader.fadeIn(200);
                    $.ajax({
                        type: "GET",
                        url: $(this).attr('href')
                    })
                    .done(function( response ) {
                        $postrelations.find('.modal-body').html(response.rel_detail).end().modal('show');
                        $postrelations.find('.modal-footer .btn-danger').prop('href',response.del_url).end().modal('show');
                        simpleloader.fadeOut(200);
                    })
                    .fail(function() {
                        alert( "Error while fetching data from server. Please try again later" );
                        simpleloader.fadeOut(200);
                    })
                })
            });

                
            

        </script>
    </div>
@stop