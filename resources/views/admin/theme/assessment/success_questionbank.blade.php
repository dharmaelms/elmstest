@section('content')
    @if (Session::get('success'))
        <div class="alert alert-success" id="alert-success">
            <button class="close" data-dismiss="alert">×</button>
            <!-- <strong>Success!</strong> -->
            {{ Session::get('success') }}
        </div>
        <?php Session::forget('success'); ?>
    @endif
    @if (Session::get('error'))
        <div class="alert alert-danger">
            <button class="close" data-dismiss="alert">×</button>
            <!-- <strong>Error!</strong> -->
            {{ Session::get('error') }}
        </div>
        <?php Session::forget('error'); ?>
    @endif
    <div class="row custom-box">
        <div class="col-md-4">
            <div class="box box-lightgreen">
                <div class="box-title">
                    <h3>Question Bank Management Actions</h3>
                </div>
                <div class="box-content">
                    @if(has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::ADD_QUESTION))
                        <a class="btn btn-blue" href="{{ URL::to('/cp/assessment/add-question?qb='.$questionbank_id) }}" >{{ trans('admin/assessment.add_new_question_to_bank') }}</a>
                    @endif
                    @if(has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::ADD_QUESTION_BANK))
                        <a class="btn btn-blue" href="{{ URL::to('/cp/assessment/add-questionbank') }}" >{{ trans('admin/assessment.add_another_question_Bank') }}</a>
                    @endif
                    @if(has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::LIST_QUESTION_BANK))
                        <a class="btn btn-blue" href="{{ URL::to('/cp/assessment/questionbank-questions/'.$questionbank_id) }}" >{{ trans('admin/assessment.view_this_question_bank') }}</a>
                    @endif
                    @if(has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::LIST_QUESTION_BANK))
                        <a class="btn btn-blue" href="{{ URL::to('/cp/assessment/list-questionbank') }}" >{{ trans('admin/assessment.view_all_question_banks') }}</a>
                    @endif
                </div>
            </div>
        </div>
        <!--
        <div class="col-md-4">
            <div class="box box-lightgray">
                <div class="box-title">
                    <h3>Additional actions</h3>
                </div>
                <div class="box-content">
                    <a href="{{URL::to('/cp/usergroupmanagement?filter=ACTIVE&view=iframe')}}" class="btn btn-lightblue triggermodal" data-info="user">Assign this question bank to User</a>
                    <a href="{{URL::to('/cp/usergroupmanagement/user-groups?filter=ACTIVE&view=iframe')}}" class="btn btn-lightblue triggermodal" data-info="usergroup">Assign this question bank to User Groups</a>
                </div>
            </div>
        </div>
        -->
    </div>
    <!--
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
                                       {{ trans('admin/assessment.view_ques_bank_detail') }}
                                    </h3>                                                
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-assign" style="position: relative; z-index: 9; right: 46px; top: 3px;">
                        <a class="btn btn-success" style="float: right"><em class="fa fa-check-circle"></em>&nbsp;{{ trans('admin/assessment.assign') }}</a>&nbsp;
                    </div> 
                <div class="modal-body">
                    ...
                </div>
                <div class="modal-footer">
                    <div style="float: left;" id="selectedcount"> 0 Entrie(s) selected</div>
                    <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{ trans('admin/assessment.assign') }}</a>
                    <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{ trans('admin/assessment.close') }}</a>
                </div>
            </div>
        </div>
    </div>
    <script>
        var $key = '{{ $questionbank_id }}';
        $(document).ready(function(){
            $('.triggermodal').click(function(e){
                e.preventDefault();
                simpleloader.fadeIn();
                var $this = $(this);
                var $triggermodal = $('#triggermodal');
                var $iframeobj = $('<iframe src="'+$this.attr('href')+'" width="100%" height="" frameBorder="0"></iframe>');
                
                $iframeobj.unbind('load').load(function(){
                    $('#selectedcount').text('0 Entrie(s) selected');

                    if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
                        $triggermodal.modal('show');
                    simpleloader.fadeOut();

                    /* Code to refresh selected count starts here*/
                    $iframeobj.contents().click(function(){
                        setTimeout(function(){
                            var count = 0;
                            $.each($iframeobj.get(0).contentWindow.checkedBoxes,function(){
                                count++;
                            });
                            $('#selectedcount').text(count+ ' Entrie(s) selected');
                        },10);
                    });
                    /* Code to refresh selected count ends here*/
                })
                $triggermodal.find('.modal-body').html($iframeobj);
                $triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.text());

                //code for top assign button click
                    $('.modal-assign .btn-success',$triggermodal).unbind('click').click(function(){
                        $(this).parents().find('.modal-footer .btn-success').click();
                    });
                    //code for top assign button ends here


                $('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
                    var $checkedboxes = $iframeobj.get(0).contentWindow.checkedBoxes;
                    if(!$.isEmptyObject($checkedboxes)){
                        var $postdata = "";
                        $.each($checkedboxes,function(index,value){
                            if(!$postdata)
                                $postdata += index;
                            else
                                $postdata += "," + index;
                        });

                        // Post to server
                        var action = $this.data('info');

                        simpleloader.fadeIn();
                        $.ajax({
                            type: "POST",
                            url: '{{URL::to('/cp/assessment/assign-questionbank/')}}/'+action+'/'+$key,
                            data: 'ids='+$postdata
                        })
                        .done(function( response ) {
                            if(response.flag == "success")
                                $('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/assessment.questionbank_successfully_assigned');?></div>').insertAfter($('.page-title'));
                            else
                                $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/manageweb.server_error');?></div>').insertAfter($('.page-title'));
                            $triggermodal.modal('hide');
                            // setTimeout(function(){
                            //  $('.alert').alert('close');
                            // },5000);
                            simpleloader.fadeOut(200);
                        })
                        .fail(function() {
                            $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/manageweb.server_error');?></div>').insertAfter($('.page-title'));
                            simpleloader.fadeOut(200);
                        })
                    }
                    else{
                        alert('Please select atleast one entry');
                    }
                })
            });
            window.onload = function(){
                (function(){var e={defaults:{id:"simpleloader",init:false,opacity:.5,autoopen:false,selector:false},init:function(e){if(typeof jQuery=="undefined"){this.log("jQuery is not found. Make sure you have jquery included");return false}else if(typeof $=="undefined"){$=jQuery}$.fn.extend({exists:function(){return this.length!==0}});if(!this.isEmpty(e)){this.updatedefaults(e)}if(!$("#"+this.defaults.id).exists()){var e=this.defaults.selector?this.defaults.selector:$("body");e.prepend('<div class="'+this.defaults.id+'" style="display:none;position:absolute;width:100%;top:0;background-color:black;height:100%;left:0;z-index:99999;opacity:'+this.defaults.opacity+';" ></div>');e.prepend('<div class="'+this.defaults.id+' progress progress-striped active" style="display:none; position: fixed !important; width: 16%; z-index: 100000; height: 20px;margin: auto; left: 0; top: 0; right: 0; bottom: 0; "> <div style="width: 100%;" class="progress-bar progress-bar-success">Loading...</div> </div>');this.defaults.init=true;if(this.autoopen){this.fadeIn()}}},show:function(e){if(this.defaults.init)$("."+this.defaults.id).show(e);else this.log("Please initialize the loader using simpleloader.init()")},hide:function(e){if(this.defaults.init)$("."+this.defaults.id).hide(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeIn:function(e){this.fadein(e)},fadeOut:function(e){this.fadeout(e)},fadein:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeIn(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeout:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeOut(e);else this.log("Please initialize the loader using simpleloader.init()")},remove:function(){$("."+this.defaults.id).remove();this.defaults.init=false},log:function(e){if(window.console)console.log(e)},updatedefaults:function(e){for(var t in e){this.defaults[t]=e[t]}},isEmpty:function(e){for(var t in e){if(e.hasOwnProperty(t))return false}return true}};window.simpleloader=e})();
                simpleloader.init();
            }
        })
    </script>
    -->
    <script type="text/javascript">
        $(document).ready(function() {
            $('#alert-success').delay(5000).fadeOut();
        })
    </script>
@stop