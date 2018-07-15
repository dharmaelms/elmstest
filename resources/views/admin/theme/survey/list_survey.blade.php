@section('content')

    @if ( Session::get('success'))
        <div class="alert alert-success">
            <button class="close" data-dismiss="alert" id="alert-success">×</button>
            {{ Session::get('success') }}
        </div>
    @endif
    @if ( Session::get('error'))
        <div class="alert alert-danger">
            <button class="close" data-dismiss="alert">×</button>
            {{ Session::get('error') }}
        </div>
    @endif
    @if ( Session::get('warning'))
        <div class="alert alert-warning">
        <button class="close" data-dismiss="alert">×</button>
        {{ Session::get('warning') }}
        </div>
        <?php Session::forget('warning'); ?>
    @endif
    <script>
        /* Function to remove specific value from array */
        if (!Array.prototype.remove) {
            Array.prototype.remove = function(val) {
                var i = this.indexOf(val);
                return i>-1 ? this.splice(i, 1) : [];
            };
        }
        var $targetarr = [0, 2, 3, 4, 5, 8];
    </script>
     <style type="text/css">
        .survey-header th{
            text-align: left !important;
        }
    </style>
    <script src="{{ URL::asset('admin/assets/jquery/jquery-2.1.1.min.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/jquery-ui/jquery-ui.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-ui/jquery-ui.min.css')}}">

    <script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>

    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-timepicker/compiled/timepicker.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-timepicker/js/bootstrap-timepicker.js')}}"></script>

    <link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
    <script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>

    <script src="{{ URL::asset('admin/js/readmore.js')}}"></script>

    <style type="text/css">
        @media (min-width: 1200px){
            .wdthChange {
                margin-left: 3.667%;
                width: 100%;
            }
        }
            .table-advance tbody > tr:nth-child(even) > .readmore-js-toggle {
                background-color: #f6f6f6;
            }
            .font-14{
                font-size: 14px;
            }
            .form-group .control-label{width: 40%;}
            .form-group .controls{width: 60%;}
            .top-field{width: 30%;}
        @media screen and (-webkit-min-device-pixel-ratio:0) {
            .form-group .control-label{width: 47%;}
            .form-group .controls{width: 53%;}
            .top-field{width: 34.5% !important;}
      }
    </style>
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-content">
                    <div class="col-md-12 margin-bottom-20">
                        <div class="btn-toolbar pull-right clearfix margin-bottom-20">
                            <div class="btn-group">
                                <div class="btn-group">
                                    <a class="btn btn-primary btn-sm" href="{{url::to('/cp/survey/add-survey')}}">
                                        <span class="btn btn-circle blue show-tooltip custom-btm">
                                            <i class="fa fa-plus"></i>
                                        </span>&nbsp;{{trans('admin/survey.add')}}
                                    </a>&nbsp;&nbsp;
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <table class="table table-advance" id="datatable-survey">
                        <thead>
                            <tr class="survey-header">
                                <th style="width: 5% !important;"><input type="checkbox" id="checkall" /></th>
                                <th style="width: 20% !important;">{{trans('admin/survey.survey_title')}}</th>
                                <th style="width: 10% !important;">{{trans('admin/survey.questions')}}</th>
                                <th style="width: 13% !important;">{{trans('admin/survey.posts')}}</th>
                                <th style="width: 5% !important;">{{trans('admin/survey.users')}}</th>
                                <th style="width: 10% !important;">{{trans('admin/survey.usergroup')}}</th>
                                <th style="width: 10% !important;">{{trans('admin/survey.created_on')}}</th>
                                <th style="width: 12% !important;">{{trans('admin/survey.expiry_date')}}</th>
                                <th style="width: 15% !important;">{{trans('admin/survey.actions')}}</th>
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
                                        <h3 class="modal-header-title">
                                            <i class="icon-file"></i>
                                            {{trans('admin/survey.delete_survey')}}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="padding: 20px">
                        Are you sure you want to delete {{strtolower(trans('admin/survey.survey'))}} ?
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-danger">{{trans('admin/survey.yes')}}</a>
                        <a class="btn btn-success" data-dismiss="modal">{{trans('admin/survey.close')}}</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="unassign-post" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
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
                                            {{trans('admin/survey.unassign_post_madal')}}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="padding: 20px;margin:20px;border: 1px solid #E6E6E6;">
                        <div><b>{{ trans("admin/program.channel") }} {{ trans("admin/program.name") }}: <span id="channel_name"></span></b></div>
                        <div><b>{{ trans("admin/program.packet") }} {{ trans("admin/program.name") }} : <span id="post_name"></span></b></div>
                    </div>

                    <div class="modal-footer" style="text-align: center !important">
                        <b>{{trans('admin/survey.confir_unassign_post')}}</b>&nbsp;&nbsp;&nbsp;
                        <a class="btn btn-danger">{{trans('admin/survey.yes')}}</a>
                        <a class="btn btn-success" data-dismiss="modal">{{trans('admin/survey.close')}}</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="triggermodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="row custom-box">
                            <div class="col-md-12">
                                <div class="box">
                                    <div class="box-title">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                        <h3 class="modal-header-title">
                                            <i class="icon-file"></i>
                                            {{trans('admin/program.view_program_details')}}
                                        </h3>
                                    </div>
                                </div>
                                <div class="feed-list" style="display:none;">
                                <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b><?php echo trans('admin/program.programs');?> :</b></label>
                                <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
                                    <select name="feed" class="chosen">
                                        @foreach($feeds as $feed)
                                        <option value="{{ $feed->program_slug }}" data-type="{{ $feed->program_type }}" data-id="{{ $feed->program_id }}">{{ $feed->program_title }}</option>
                                        @endforeach
                                    </select>
                                </div>`
                           </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-body">
                        ...
                    </div>
                    <div class="modal-footer" style="padding-right: 38px">
                        <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{trans('admin/survey.assign')}}</a>
                        <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{trans('admin/survey.close')}}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        var  order_by_var= "{{Input::get('order_by','6 desc')}}";
        var  order = order_by_var.split(' ')[0];
        var  _by   = order_by_var.split(' ')[1];

        function updateCheckBoxVals(){
            $allcheckBoxes = $('#datatable-survey td input[type="checkbox"]');
            if(typeof window.checkedBoxes != 'undefined'){
                $allcheckBoxes.each(function(index,value){
                var $value = $(value);
                if(typeof checkedBoxes[$value.val()] != "undefined")
                    $('[value="'+$value.val()+'"]').prop('checked',true);
                })
            }
            if($allcheckBoxes.length > 0)
                if($allcheckBoxes.not(':checked').length > 0)
                    $('#datatable-survey thead tr th:first input[type="checkbox"]').prop('checked',false);
                else
                    $('#datatable-survey thead tr th:first input[type="checkbox"]').prop('checked',true);
        }

          /* Simple Loader */
        (function(){var e={defaults:{id:"simpleloader",init:false,opacity:.5,autoopen:false,selector:false},init:function(e){if(typeof jQuery=="undefined"){this.log("jQuery is not found. Make sure you have jquery included");return false}else if(typeof $=="undefined"){$=jQuery}$.fn.extend({exists:function(){return this.length!==0}});if(!this.isEmpty(e)){this.updatedefaults(e)}if(!$("#"+this.defaults.id).exists()){var e=this.defaults.selector?this.defaults.selector:$("body");e.prepend('<div class="'+this.defaults.id+'" style="display:none;position:absolute;width:100%;top:0;background-color: ;height:100%;left:0;z-index:99999;opacity:'+this.defaults.opacity+';" ></div>');e.prepend('<div class="'+this.defaults.id+' progress progress-striped active" style="display:none; position: fixed !important; width: 16%; z-index: 100000; height: 20px;margin: auto; left: 0; top: 0; right: 0; bottom: 0; "> <div style="width: 100%;" class="progress-bar progress-bar-success">Loading...</div> </div>');this.defaults.init=true;if(this.autoopen){this.fadeIn()}}},show:function(e){if(this.defaults.init)$("."+this.defaults.id).show(e);else this.log("Please initialize the loader using simpleloader.init()")},hide:function(e){if(this.defaults.init)$("."+this.defaults.id).hide(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeIn:function(e){this.fadein(e)},fadeOut:function(e){this.fadeout(e)},fadein:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeIn(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeout:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeOut(e);else this.log("Please initialize the loader using simpleloader.init()")},remove:function(){$("."+this.defaults.id).remove();this.defaults.init=false},log:function(e){if(window.console)console.log(e)},updatedefaults:function(e){for(var t in e){this.defaults[t]=e[t]}},isEmpty:function(e){for(var t in e){if(e.hasOwnProperty(t))return false}return true}};window.simpleloader=e})();
            simpleloader.init();

            $(document).ready(function(){
                /*Code to hide success message after 2 seconds*/
                $('.alert-success').delay(2000).fadeOut();

            /* code for survey DataTable begins here */
                var $datatable = $('#datatable-survey');
                var datatableOBJ  = $('#datatable-survey').on('processing.dt',function(event,settings,flag){
                    if(flag == true)
                        simpleloader.fadeIn();
                    else
                        simpleloader.fadeOut();
                }).on('draw.dt',function(event,settings,flag){
                    $('.show-tooltip').tooltip({container: 'body', delay: {show:500}});
                    $('td:nth-child(2) div', '#datatable-survey tr').each(function(){
                    if($(this).height() > 75){
                        $(this).readmore({maxHeight: 54, moreLink: '<a href="#">Read more</a>',lessLink: '<a href="#">{{trans('admin/program.close')}}</a>'});
                        }
                    })
                }).dataTable({
                    "serverSide": true,
                    "destroy": true,
                    "ajax": {
                      "url": "{{URL::to('cp/survey/list-survey-ajax')}}"
                    },
                    "columns": [
                                {data: "checkbox", render: function ( data, type, row ) {
                                        return '<input type="checkbox">';
                                    }
                                },
                                { data: 'survey_title' },
                                { data: 'survey_questions' },
                                { data: 'posts' },
                                { data: 'users' },
                                { data: 'usergroups' },
                                { data: 'created_at' },
                                { data: 'end_time' },
                                { data: 'actions'},
                        ],
                    "aaSorting": [[ Number(order), _by]],
                    "drawCallback" : updateCheckBoxVals,
                    "columnDefs": [ { "targets": $targetarr, "orderable": false } ],
                    "language": {  /* To remove (filtered from 1 total entries) msg from datatable */    "infoFiltered": ""
                    },
                });

               $('#datatable-survey_filter input').unbind();
                $('#datatable-survey_filter input').bind('keyup', function(e) {
                    if(e.keyCode == 13) {
                         datatableOBJ.fnFilter(this.value);
                    }
                });
                /* code for survey DataTable ends here */


            /* Code to get the selected checkboxes in datatable starts here*/
            if(typeof window.checkedBoxes == 'undefined')
                window.checkedBoxes = {};
                $datatable.on('change','td input[type="checkbox"]',function(){
                var $this = $(this);
                if($this.prop('checked'))
                    checkedBoxes[$this.val()] = $($this).parent().next().text();
                else
                    delete checkedBoxes[$this.val()];
            });

            $('#checkall').change(function(e){
                $('#datatable-survey td input[type="checkbox"]').prop('checked',$(this).prop('checked'));
                $('#datatable-survey td input[type="checkbox"]').trigger('change');
                e.stopImmediatePropagation();
            });
        /* Code to get the selected checkboxes in datatable ends here*/

            //Delete individual survey
            $(document).on('click','.deletesurvey',function(e){
              e.preventDefault();
              var $this = $(this);
              var $deletemodal = $('#deletemodal');
                $deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href'))
              $deletemodal.modal('show');
            });

        /* Code for loading the posts in the iframe for survey starts here */
            var survey_id = null;
            $("select[name='feed']").change(function() {
                $('iframe').attr(
                    'src', "{{ URL::to('cp/contentfeedmanagement/packets') }}/"+$(this).find("option[value='"+$(this).val()+"']").data("type")+"/"+this.value+
                    "?input_type=radio&view=iframe&from=survey&relid="+survey_id
                );
            });
        /* Code forloading the posts in the iframe for survey ends here */

        /* Code for assigning user/usergroups starts here */
            $datatable.on('click','.survey-assign',function(e){
                e.preventDefault();
                simpleloader.fadeIn();
                var $this = $(this);
                survey_id = $this.data("key");
                var $triggermodal = $('#triggermodal');
                var $iframeobj = $('<iframe src="'+$this.attr('href')+'" width="100%" height="" frameBorder="0"></iframe>');

                $iframeobj.unbind('load').load(function(){
                    //css code for the alignment
                    var a = $('#triggermodal .modal-content .modal-body iframe').get(0).contentDocument;
                    if($(a).find('.box-content select.form-control').parent().parent().find('label').is(':visible')){
                    } else
                        $triggermodal.find('.modal-assign').css({"top": "8px"});
                        if($triggermodal.find('.feed-list').is(':visible')) {
                            $triggermodal.find('.feed-list').css({"padding-top":"12px","padding-bottom":"26px"});
                            $triggermodal.find('.modal-assign').css({"top": "-45px"});
                        } else{
                        if($(a).find('.box-content select.form-control').parent().parent().find('label').is(':visible'))
                            $triggermodal.find('.modal-assign').css({"top": "30px"});
                    }
                    //code ends here

                    if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
                        $triggermodal.modal('show');
                        simpleloader.fadeOut();

                    /* Code to Set Default checkedboxes starts here*/
                    $iframeobj.get(0).contentWindow.checkedBoxes = {};
                    if($this.data('info') == 'feed') {
                        var feed_id = $("select[name='feed']").find(':selected').data('id');
                        var json = $this.data('json')[feed_id];
                        if(json === undefined) json = [];
                        console.log(json);
                        $.each(json, function(index,value){
                            $iframeobj.get(0).contentWindow.checkedBoxes[value] = "";
                        })
                    } else {
                        $.each($this.data('json'),function(index,value){
                            $iframeobj.get(0).contentWindow.checkedBoxes[value] = "";
                        })
                    }
                    /* Code to Set Default checkedboxes ends here*/

                })
                $triggermodal.find('.modal-body').html($iframeobj);
                $triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.data('text'));
                //code for top assign button click
                $('.modal-assign .btn-success',$triggermodal).unbind('click').click(function(){
                    $(this).parents().find('.modal-footer .btn-success').click();
                });
                //code for top assign button ends here

                if($this.data('info') == 'feed') {
                    $(".feed-list").show();
                    $("select[name='feed']").trigger('change');
                } else {
                    $(".feed-list").hide();
                }
                $('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
                    var $checkedboxes = $iframeobj.get(0).contentWindow.checkedBoxes;
                        var $postdata = "";
                        if(!$.isEmptyObject($checkedboxes)){
                            $.each($checkedboxes,function(index,value){
                                if(!$postdata)
                                    $postdata += index;
                                else
                                    $postdata += "," + index;
                            });
                        }
                        // Post to server
                        var action = $this.data('info');
                        var feed = '';
                        if(action == 'feed')
                            var feed = $("select[name='feed']").val();

                        simpleloader.fadeIn();
                        $.ajax({
                            type: "POST",
                            url: '{{ URL::to("/cp/survey/assign-survey") }}/'+action+'/'+$this.data('key'),
                            data: 'ids='+$postdata+'&empty=true&feed='+feed
                        })
                        .done(function( response ) {
                            if(response.flag == "success")
                                $('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button>'+response.message+'</div>').insertAfter($('.page-title'));
                            else
                                $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button>'+response.message+'</div>').insertAfter($('.page-title'));
                            $triggermodal.modal('hide');
                            setTimeout(function(){
                                $('.alert').alert('close');
                            },5000);
                            datatableOBJ.fnDraw(true);
                            simpleloader.fadeOut(200);
                        })
                        .fail(function() {
                            $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/manageweb.server_error');?></div>').insertAfter($('.page-title'));
                            datatableOBJ.fnDraw(true);
                            simpleloader.fadeOut(200);
                        })
                })
            });

        $(document).on('click','.survey-post-unassign',function(e){
            e.preventDefault();
            var $this = $(this);
            survey_id = $this.data("key");
            post_name = $this.data("postname");
            channel_name = $this.data("channelname");
            var $unassign_post = $('#unassign-post');
            $('#unassign-post').find('#post_name').html(post_name);
            $('#unassign-post').find('#channel_name').html(channel_name);
            $unassign_post.find('.modal-footer .btn-danger').prop('href',$this.prop('href'))
            $unassign_post.modal('show');
        });

        /* Code for assigning posts starts here */
        $datatable.on('click','.survey-post-assign',function(e){
            e.preventDefault();
            simpleloader.fadeIn();
            var $this = $(this);
            survey_id = $this.data("key");
            var $triggermodal = $('#triggermodal');
            var $iframeobj = $('<iframe src="'+$this.attr('href')+'" width="100%" height="" frameBorder="0"></iframe>');

            $iframeobj.unbind('load').load(function(){
                //css code for the alignment
                var a = $('#triggermodal .modal-content .modal-body iframe').get(0).contentDocument;
                if($(a).find('.box-content select.form-control').parent().parent().find('label').is(':visible')){
                } else
                    $triggermodal.find('.modal-assign').css({"top": "8px"});
                    if($triggermodal.find('.feed-list').is(':visible')) {
                        $triggermodal.find('.feed-list').css({"padding-top":"12px","padding-bottom":"26px"});
                        $triggermodal.find('.modal-assign').css({"top": "-45px"});
                    } else{
                    if($(a).find('.box-content select.form-control').parent().parent().find('label').is(':visible'))
                        $triggermodal.find('.modal-assign').css({"top": "30px"});
                }
                //code ends here

                if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
                    $triggermodal.modal('show');
                    simpleloader.fadeOut();


            })
            $triggermodal.find('.modal-body').html($iframeobj);
            $triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.data('text'));
            //code for top assign button click
            $('.modal-assign .btn-success',$triggermodal).unbind('click').click(function(){
                $(this).parents().find('.modal-footer .btn-success').click();
            });
            //code for top assign button ends here

            if($this.data('info') == 'feed') {
                $(".feed-list").show();
                $("select[name='feed']").trigger('change');
            } else {
                $(".feed-list").hide();
            }
            $('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
                var $selectedRadio = $iframeobj.contents().find('#datatable input[type="radio"]:checked');
                var $postdata = $selectedRadio.val();
                // Post to server
                var action = $this.data('info');
                var feed = '';
                if(action == 'feed')
                    var feed = $("select[name='feed']").val();

                simpleloader.fadeIn();
                $.ajax({
                    type: "POST",
                    url: '{{ URL::to("/cp/survey/assign-survey") }}/'+action+'/'+$this.data('key'),
                    data: 'ids='+$postdata+'&empty=true&feed='+feed
                })
                .done(function( response ) {
                    if(response.flag == "success")
                        $('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button>'+response.message+'</div>').insertAfter($('.page-title'));
                    else
                        $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button>'+response.message+'</div>').insertAfter($('.page-title'));
                    $triggermodal.modal('hide');
                    setTimeout(function(){
                        $('.alert').alert('close');
                    },5000);
                    datatableOBJ.fnDraw(true);
                    simpleloader.fadeOut(200);
                })
                .fail(function() {
                    $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/manageweb.server_error');?></div>').insertAfter($('.page-title'));
                    datatableOBJ.fnDraw(true);
                    simpleloader.fadeOut(200);
                })
            })
        });
    });

    </script>

@stop