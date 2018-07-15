    <?php
        $start = Input::get('start', 0);
        $limit = Input::get('limit', 10);
        $search   =  Input::get('search','');
        $order_by =  Input::get('order_by','3 desc');
    ?>
    <script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-title">
                    <!-- <h3 style="color:black"><i class="fa fa-file"></i> Edit {{trans('admin/program.packet')}}</h3> -->
                    <div class="box-tool">
                        <a data-action="collapse" href="#"><i class="fa fa-chevron-up"></i></a>
                        <a data-action="close" href="#"><i class="fa fa-times"></i></a>
                    </div>
                </div>
                <div class="box-content">
                    <form action="" class="form-horizontal form-bordered form-row-stripped" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="packet_name" class="col-sm-3 col-lg-2 control-label">{{trans('admin/program.packet_name')}} <span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-10 controls">
                                <input type="text" name="packet_name" id="packet_name" placeholder="{{trans('admin/program.packet_name')}}" class="form-control" value="{{$packet['packet_title']}}">
                                <input type="hidden" name="packet_slug" id="packet_slug" placeholder="{{trans('admin/program.packet')}} Slug" class="form-control" value="{{$packet['packet_slug']}}">
                                <?php $msg = $errors->first('packet_name', '<span class="help-inline" style="color:#f00">:message</span>'); ?>
                                <?php if($msg == "") echo $errors->first('packet_slug', '<span class="help-inline" style="color:#f00">:message</span>'); else echo $msg; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="packet_publish_date" class="col-sm-3 col-lg-2 control-label">{{trans('admin/program.packet_publish_date')}} <span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-10 controls">
                                <input readonly type="text" name="packet_publish_date" id="packet_publish_date" placeholder="{{trans('admin/program.packet_publish_date')}}" class="form-control datepicker" value="{{ Timezone::convertFromUTC("@".$packet['packet_publish_date'],Auth::user()->timezone,'d-m-Y') }}" style="cursor: pointer" >
                                {!! $errors->first('packet_publish_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="select" class="col-sm-2 col-lg-2 control-label">{{trans('admin/program.status')}} <span class="red">*</span></label>
                            <div class="col-sm-4 col-lg-4 controls">
                                <select class="form-control" name="status" id="status" data-rule-required="true">
                                    <option <?php if($packet['status'] == "ACTIVE") echo "selected"?> value="active">{{trans('admin/program.active')}}</option>
                                    <option <?php if($packet['status'] == "IN-ACTIVE") echo "selected"?> value="inactive">{{trans('admin/program.in_active')}}</option>
                                </select>
                                {!! $errors->first('status', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                            <label for="qanda" class="col-sm-1 col-lg-1 control-label">{{trans('admin/program.q_and_a')}} <span class="red">*</span></label>
                            <div class="col-sm-5 col-lg-5 controls">
                                <select class="form-control" name="qanda" id="qanda" data-rule-required="true">
                                    <option <?php if($packet['qanda'] == "yes") echo "selected"?> value="yes">{{trans('admin/program.yes')}}</option>
                                    <option <?php if($packet['qanda'] == "no") echo "selected"?> value="no">{{trans('admin/program.no')}}</option>
                                </select>
                                {!! $errors->first('qanda', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="access" class="col-sm-2 col-lg-2 control-label">{{trans('admin/program.sequential_access')}} <span class="red">*</span></label>
                            <div class="col-sm-4 col-lg-4 controls">
                                <select class="form-control" name="access" id="access" data-rule-required="true">
                                    <option <?php if($packet['sequential_access'] == "yes") echo "selected"?> value="yes">{{trans('admin/program.yes')}}</option>
                                    <option <?php if($packet['sequential_access'] == "no") echo "selected"?> value="no">{{trans('admin/program.no')}}</option>
                                </select>
                                {!! $errors->first('access', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                            <div id='quiz_result_div' style="display:none">
                            <?php 
                                if(isset($packet['quiz_result']) && $packet['quiz_result'] == "yes"){
                                    $quiz_result = 'yes';
                                }else{
                                    $quiz_result= 'no';
                                }
                            ?>
                                <div class="col-sm-6 col-lg-6 controls">
                                    <input type="checkbox" name="quiz_result" id="quiz_result" value="yes" @if($quiz_result == 'yes') checked @endif> {{trans('admin/program.check_quiz_result')}}
                                    {!! $errors->first('quiz_result', '<span class="help-inline" style="color:#f00">:message</span>') !!}

                                    <br><span style="font-size: 12px;color: #777;"> {{trans('admin/program.note')}}: {{trans('admin/program.note_check_quiz_result')}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="packet_description" class="col-sm-3 col-lg-2 control-label">{{trans('admin/program.packet_description')}} </label>
                            <div class="col-sm-9 col-lg-10 controls">
                                <textarea name="packet_description" id="packet_description" rows="5" class="form-control" placeholder="{{trans('admin/program.packet_description')}}">{{$packet['packet_description']}}</textarea>
                                {!! $errors->first('packet_description', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/program.display_image')}}</label>
                            <div class="col-sm-9 col-lg-10 controls">
                                <div class="fileupload fileupload-new">
                                    <div class="fileupload-new img-thumbnail" style="width: 200px;padding:0">
                                        <?php if($packet['packet_cover_media']){ ?>
                                            <img src="{{URL::to('/cp/dams/show-media/'.$packet['packet_cover_media'])}}" width="100%" alt="" style="cursor:pointer;" id="bannerplaceholder" onclick="$('#selectfromdams').trigger('click');"/>
                                        <?php } else{ ?>
                                            <img src="{{URL::asset('admin/img/demo/200x150.png')}}" alt="" style="cursor:pointer;" id="bannerplaceholder" onclick="$('#selectfromdams').trigger('click');"/>
                                        <?php } ?>
                                    </div>
                                    <div class="fileupload-preview fileupload-exists img-thumbnail" style="max-width: 200px; max-height: 150px; line-height: 20px;"></div>
                                    <div>
                                        <button class="btn" type="button" id="selectfromdams" data-url="{{URL::to("/cp/dams?view=iframe&from=post&program_type={$program["program_type"]}&program_slug={$program["program_slug"]}&post_slug={$packet["packet_slug"]}&filter=image&select=radio")}}">
                                            {{trans('admin/program.select')}}
                                        </button>
                                        @if (has_admin_permission(ModuleEnum::DAMS, DAMSPermission::ADD_MEDIA))
                                        <button class="btn" type="button" id="upload" data-url="{{URL::to("cp/dams/add-media?view=iframe&from=post&program_type={$program["program_type"]}&program_slug={$program["program_slug"]}&post_slug={$packet["packet_slug"]}&filter=image")}}">{{trans('admin/program.upload_new')}}</button>
                                        @endif
                                        @if($packet['packet_cover_media'])
                                            <button class="btn btn-danger" type="button" id="removethumbnail">
                                                {{trans('admin/program.remove')}}
                                            </button>
                                        @endif
                                        <input type="hidden" name="banner" value="{{($packet['packet_cover_media']) ? $packet['packet_cover_media'] : ""}}" >
                                    </div>
                                </div>
                                {!! $errors->first('banner', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group last">
                            <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                               <button type="submit" class="btn btn-success" id="post_submit">
                                   <i class="fa fa-check"></i> {{trans('admin/program.save')}}
                               </button>
                               <a href="{{URL::to("/cp/contentfeedmanagement/packets/{$program["program_type"]}/{$program["program_slug"]}")}}?start={{$start}}&limit={{$limit}}&search={{$search}}&order_by={{$order_by}}">
                                   <button type="button" class="btn">
                                       {{trans('admin/program.cancel')}}
                                   </button>
                               </a>
                            </div>
                        </div>
                     </form>
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
                                    <h3 class="modal-header-title" >
                                        <i class="icon-file"></i>
                                            {{trans('admin/program.view_media_details')}}
                                    </h3>                                                
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    ...
                </div>
                <div class="modal-footer">
                 <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{trans('admin/program.assign')}}</a>
                    <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{trans('admin/program.close')}}</a>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function(){
            $('.datepicker').datepicker({
            format : "dd-mm-yyyy",
            startDate: '+0d'
            }).on('changeDate',function(){
                    $(this).datepicker('hide')
                });
            $('[name="packet_name"]').on("blur",function(){
                if($(this).val().trim() != ""){
                    var slug=$('[name="packet_slug"]').val($(this).val().toLowerCase().replace(/[^\w ]+/g,'').replace(/ +/g,'-'))
                    while(1){
                        if(slug.val().trim().charAt(0) == '-'){
                            slug.val(slug.val().slice(1,slug.val().length).trim())
                        }else{
                            break;
                        }    
                    }
                    if(!slug.val())
                    {
                        $('[name="packet_slug"]').val('$*&');
                    }
                }
            });
           $('#post_submit').click(function(){
                if($('[name="packet_name"]').val().trim() != ""){
                    var slug=$('[name="packet_slug"]').val($('[name="packet_name"]').val().toLowerCase().replace(/[^\w ]+/g,'').replace(/ +/g,'-'))
                    //If name contains special characters,generated slug will be empty.Sending slug as special character to get proper validation.
                    while(1){
                        if(slug.val().trim().charAt(0) == '-'){
                            slug.val(slug.val().slice(1,slug.val().length).trim())
                        }else{
                            break;
                        }    
                    }
                     
                    if(!slug.val())
                    {
                        $('[name="packet_slug"]').val('$*&');
                    }
                }
            });

            /* Code for selecting banner image/video for packet starts here*/
            $('#selectfromdams, #upload').click(function(e){
                e.preventDefault();
                simpleloader.fadeIn();
                var $this = $(this);
                var $triggermodal = $('#triggermodal');
                var $iframeobj = $('<iframe src="'+$this.data('url')+'" width="100%" height="500px" style="max-height:500px !important" frameBorder="0"></iframe>');
                $iframeobj.unbind('load').load(function(){
                    if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
                        $triggermodal.modal('show');
                    simpleloader.fadeOut();
                });
                $triggermodal.find('.modal-body').html($iframeobj);
                $triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.text());
                $('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
                    var $selectedRadio = $iframeobj.contents().find('#datatable input[type="radio"]:checked');
                    if($selectedRadio.length){
                        $('#bannerplaceholder').attr('src','{{URL::to('/cp/dams/show-media/')}}/'+$selectedRadio.val()).width("100%");
                        $('#removethumbnail').remove();
                        $('<button class="btn btn-danger" type="button" id="removethumbnail"> {{trans('admin/program.remove')}} </button>').insertBefore($('input[name="banner"]').val($selectedRadio.val()));
                        $triggermodal.modal('hide');
                    }
                    else{
                        alert('Please select atleast one entry');
                    }
                });
            });
            /* Code for selecting banner image/video for packet ends here*/
        
            $(document).on('click','#removethumbnail',function(){
                $('#bannerplaceholder').attr('src', '{{URL::asset("admin/img/demo/200x150.png")}}');
                $('input[name="banner"]').val('');
                $(this).remove();
            });

            $("#access").change(function () {
                if($("#access option:selected").val() == 'yes'){
                    $('#quiz_result_div').show();
                }else{
                    $('#quiz_result_div').hide();
                }
            });
            if($("#access option:selected").val() == 'yes'){
                $('#quiz_result_div').show();
            }else{
                $('#quiz_result_div').hide();
            }
        })
    </script>
