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
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
    <script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
    <div class="row custom-box">
        <div class="col-md-4">
            <div class="box box-lightgreen">
                <div class="box-title">
                    <h3>{{ trans('admin/event.event_management_actions') }}</h3>
                </div>
                <div class="box-content">
                    @if(has_admin_permission(ModuleEnum::EVENT, EventPermission::ADD_EVENT))
                        <a class="btn btn-blue" href="{{ URL::to('/cp/event/add-event') }}" >{{ trans('admin/event.add_another_event') }}</a>
                    @endif
                    @if(has_admin_permission(ModuleEnum::EVENT, EventPermission::LIST_EVENT))
                        <a class="btn btn-blue" href="{{ URL::to('/cp/event') }}" >{{ trans('admin/event.view_all_events') }}</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6"> 
            <div class="box box-lightgray"> 
                <div class="box-title"> 
                    <h3>{{ trans('admin/event.additional_actions') }}</h3> 
                </div> 
                <div class="box-content"> 
                    
                    @if (has_admin_permission(ModuleEnum::CHANNEL,ChannelPermission::MANAGE_CHANNEL_POST)) 
                        @if (count($feeds) > 0) 
                        <a href="" class="btn btn-lightblue triggermodal" id="feed" data-json="" data-info="feed">{{ trans('admin/event.assign_event_to_channel') }}</a> 
                        @else 
                        <span data-title="No channel available" class="show-tooltip" style="display: block;"> 
                            <a href=""  class="btn btn-lightblue disabled" id="feed" data-json="" data-info="feed">{{ trans('admin/event.assign_event_to_channel') }}</a> 
                        </span> 
                        @endif 
                    @endif 
                    
                    @if (has_admin_permission(ModuleEnum::EVENT, EventPermission::ASSIGN_USER)) 
                    <a href="{{URL::to("/cp/usergroupmanagement?filter=ACTIVE&view=iframe&from=event&relid={$event_id}")}}" class="btn btn-lightblue triggermodal" id="user" data-json="" data-info="user">{{ trans('admin/event.assign_this_event_to_user') }}</a> 
                    @endif 
                    
                    @if (has_admin_permission(ModuleEnum::EVENT, EventPermission::ASSIGN_USER_GROUP)) 
                    <a href="{{URL::to("/cp/usergroupmanagement/user-groups?filter=ACTIVE&view=iframe&from=event&relid={$event_id}")}}" class="btn btn-lightblue triggermodal" data-json="" id="user_group" data-info="usergroup">{{ trans('admin/event.assign_this_event_to_ug') }}</a> 
                    @endif 
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
                                            {{ trans('admin/event.view_details') }}
                                    </h3>                                                
                                </div>
                            </div>
                            <div class="feed-list" style="display:none;">
                                <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b><?php echo trans('admin/program.program'); ?> :</b></label>
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
                <div class="modal-footer">
                    <!-- <div style="float: left;" id="selectedcount"> 0 Entrie(s) selected</div> -->
                    <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{ trans('admin/event.assign') }}</a>
                    <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{ trans('admin/event.close') }}</a>
                </div>
            </div>
        </div>
    </div>
    <script>
        var $key = '{{ $event_id }}';
        
        $(document).ready(function(){
            $('#alert-success').delay(5000).fadeOut();
            $('.triggermodal').click(function(e){
                e.preventDefault();
                simpleloader.fadeIn();
                var $this = $(this);
                var $triggermodal = $('#triggermodal');
                var $iframeobj = $('<iframe src="'+$this.attr('href')+'" width="100%" height="" frameBorder="0"></iframe>');
                // console.log($this.attr('href'));
                $iframeobj.unbind('load').load(function(){
                    //css code for the alignment                    
                    
                    var a = $('#triggermodal .modal-content .modal-body iframe').get(0).contentDocument;
                    if($(a).find('.box-content select.form-control').parent().parent().find('label').is(':visible')){                           
                            $triggermodal.find('.modal-body').css({"top":"-27px"});
                    }                       
                    else
                        $triggermodal.find('.modal-assign').css({"top": "8px"});

                    if($triggermodal.find('.feed-list').is(':visible')){
                        $triggermodal.find('.feed-list').css({"padding-top":"12px","padding-bottom":"40px"});                           
                        $triggermodal.find('.modal-body').css({"top":"-30px"});
                        $triggermodal.find('.modal-assign').css({"top": "-45px"});
                    }
                    else{
                        if($(a).find('.box-content select.form-control').parent().parent().find('label').is(':visible'))
                        $triggermodal.find('.modal-assign').css({"top": "30px"});
                        $triggermodal.find('.modal-body').css({"top": "8px"});
                    }
                    //code ends here

                    //$('#selectedcount').text('0 Entrie(s) selected');

                    if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
                        $triggermodal.modal('show');                    
                
                    simpleloader.fadeOut();
                    // var checkeditems = JSON.stringify($this.data('json'));
                    /*console.log('checkeditems'+checkeditems);
                     if(checkeditems){
                        checkeditems = JSON.parse(checkeditems);
                        for(var x in checkeditems)
                          $iframeobj.get(0).contentWindow.checkedBoxes[x] = "";                    
                      }*/
                   $.each($this.data('json'),function(index,value){
                        if($iframeobj.get(0).contentWindow.checkedBoxes){
                            $iframeobj.get(0).contentWindow.checkedBoxes[value] = "";    
                        }
                    })
                    /* Code to refresh selected count starts here*/
                    $iframeobj.contents().click(function(){
                        setTimeout(function(){
                            var count = 0;
                            $.each($iframeobj.get(0).contentWindow.checkedBoxes,function(){
                                count++;
                            });
                            $('#selectedcount').text(count+ ' selected');
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

                if($this.data('info') == 'feed') {
                    $(".feed-list").show();
                    $("select[name='feed']").trigger('change');
                } else {
                    $(".feed-list").hide();
                }
                $('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
                    var $checkedboxes = $iframeobj.get(0).contentWindow.checkedBoxes;
                    var $postdata = "";
                    var $forjson=[];
                    if(!$.isEmptyObject($checkedboxes)) {
                        $.each($checkedboxes, function (index, value) {
                            $forjson.push(index);
                            if (!$postdata)
                                $postdata += index;
                            else
                                $postdata += "," + index;
                        });
                    }
                    // Post to server
                    var action = $this.data('info');
                    $this.data('json',$forjson);
                    var feed = '';
                    if(action == 'feed')
                        var feed = $("select[name='feed']").val();

                    simpleloader.fadeIn();
                    $.ajax({
                        type: "POST",
                        url: '{{URL::to('/cp/event/assign-event/')}}/'+action+'/'+$key,
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
                        simpleloader.fadeOut(200);
                    })
                    .fail(function() {
                        $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/manageweb.server_error');?></div>').insertAfter($('.page-title'));
                        simpleloader.fadeOut(200);
                    })
                })
            });

            $("select[name='feed']").change(function() {
                $('iframe').attr(
                    'src', "{{ URL::to('cp/contentfeedmanagement/packets') }}/"+$(this).find("option[value='"+$(this).val()+"']").data("type")+"/"+this.value+
                    "?view=iframe&from=event&relid="+$key
                );
            });
            window.onload = function(){
                (function(){var e={defaults:{id:"simpleloader",init:false,opacity:.5,autoopen:false,selector:false},init:function(e){if(typeof jQuery=="undefined"){this.log("jQuery is not found. Make sure you have jquery included");return false}else if(typeof $=="undefined"){$=jQuery}$.fn.extend({exists:function(){return this.length!==0}});if(!this.isEmpty(e)){this.updatedefaults(e)}if(!$("#"+this.defaults.id).exists()){var e=this.defaults.selector?this.defaults.selector:$("body");e.prepend('<div class="'+this.defaults.id+'" style="display:none;position:absolute;width:100%;top:0;background-color:black;height:100%;left:0;z-index:99999;opacity:'+this.defaults.opacity+';" ></div>');e.prepend('<div class="'+this.defaults.id+' progress progress-striped active" style="display:none; position: fixed !important; width: 16%; z-index: 100000; height: 20px;margin: auto; left: 0; top: 0; right: 0; bottom: 0; "> <div style="width: 100%;" class="progress-bar progress-bar-success">Loading...</div> </div>');this.defaults.init=true;if(this.autoopen){this.fadeIn()}}},show:function(e){if(this.defaults.init)$("."+this.defaults.id).show(e);else this.log("Please initialize the loader using simpleloader.init()")},hide:function(e){if(this.defaults.init)$("."+this.defaults.id).hide(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeIn:function(e){this.fadein(e)},fadeOut:function(e){this.fadeout(e)},fadein:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeIn(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeout:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeOut(e);else this.log("Please initialize the loader using simpleloader.init()")},remove:function(){$("."+this.defaults.id).remove();this.defaults.init=false},log:function(e){if(window.console)console.log(e)},updatedefaults:function(e){for(var t in e){this.defaults[t]=e[t]}},isEmpty:function(e){for(var t in e){if(e.hasOwnProperty(t))return false}return true}};window.simpleloader=e})();
                simpleloader.init();
            }
        })
    </script>
@stop