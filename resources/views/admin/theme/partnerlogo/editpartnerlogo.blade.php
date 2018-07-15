@section('content')
@if ( Session::get('success') )
    <div class="alert alert-success" id="alert-success">
      <button class="close" data-dismiss="alert">×</button>
     
      {{ Session::get('success') }}
    </div>
<?php Session::forget('success'); ?>
@endif
@if ( Session::get('error'))
    <div class="alert alert-danger">
      <button class="close" data-dismiss="alert">×</button>
    
      {{ Session::get('error') }}
    </div>
<?php Session::forget('error'); ?>
@endif
<div class="row">
  <div class="col-md-12">
    <div class="box">
      <div class="box-title">
      </div>
      <div class="box-content">
      
    
        <form action="{{ URL::to('/cp/partnerlogo/edit-partner-logo/'.$partner['partner_id']) }}" class="form-horizontal form-bordered form-row-stripped" enctype="multipart/form-data" method="post" >         
            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="firstname">{!! trans('admin/partnerlogo.name') !!}<span class="red">*</span></label>
              <div class="col-sm-6 col-lg-5 controls">
              <input type="text" class="form-control" name="partner_name" <?php if(Input::old('partner_name')) {?>value="{{Input::old('partner_name')}}"<?php } elseif($errors->first('partner_name')) {?> value="{{Input::old('partner_name')}}"<?php } elseif(isset($partner['partner_name'])) {?> value="{{$partner['partner_name']}}"<?php } ?>>
               <!--  <input type="text" class="form-control" name="partner_name" value="{{ Input::old('partner_name') }}">  -->
                {!! $errors->first('partner_name', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
            </div>


                  <!-- logo image -->
              <?php 
              $logo = $partner['partner_logopath'];
              $logo_name = $partner['partner_logoname'];
              $logo_diamension = $partner['partner_diamension'];
              ?>
                <div class="form-group">
                <label class="col-sm-4 col-lg-3 control-label">{{ trans('admin/partnerlogo.upload_logo') }}<span class="red">*</span></label>
                <div class="col-sm-2 col-lg-2 controls">
                <?php if(!empty($logo) ) {?>
                <img src="{{URL::to($logo)}}" height="100%" width="100%"  >
                <?php } ?>
                </div>
                <div class="col-sm-6 col-lg-7 controls">
              
                <span >
                    <span class="">{{ trans('admin/partnerlogo.selected_partner') }}<strong>{{ $logo_name }}</strong></span>
                </span>
               
                
                    <div class="fileupload fileupload-new" data-provides="fileupload">
                        <div class="input-group">
                            <div class="input-group-btn">
                                <a class="btn bun-default btn-file">
                                    <span class="fileupload-new">{{ trans('admin/partnerlogo.change') }}</span>
                                    <span class="fileupload-exists">{{ trans('admin/partnerlogo.change') }}</span>
                                    <input type="file" class="file-input" name="file">
                                </a>
                                <a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">{{ trans('admin/partnerlogo.remove') }}</a>
                            </div>
                            <div class="form-control uneditable-input">
                                <i class="fa fa-file fileupload-exists"></i> 
                                <span class="fileupload-preview"></span>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="old_diamension" value="{{ $logo_diamension }}">
                    <input type="hidden" name="old_file" value="{{$logo_name}}">
                    <span class="help-inline">{{ trans('admin/partnerlogo.upload_size_note') }}</span><br /> 
                     {!! $errors->first('file', '<span class="help-inline" style="color:#f00">:message</span>') !!} 
                </div>
                </div>

            <?php
                if(Input::old('partner_description'))
                {
                  $description = Input::old('partner_description');
                }
                elseif(isset($partner['partner_description']))
                {
                  $description = $partner['partner_description'];
                }
                else
                {
                  $description = "";
                }
            ?>


            <div class="form-group">
                <?php 
                  if(Input::old('sort_order'))
                  {
                    $sort_order=Input::old('sort_order');
                  }
                  elseif(isset($partner['sort_order']))
                  {
                    $sort_order=$partner['sort_order'];
                  }
                  else
                  {
                    $l = $sort_order;
                  }
                ?>
                <label class="col-sm-4 col-lg-3 control-label" for="sort_order">{{ trans('admin/partnerlogo.sort_order') }}</label>
                <div class="col-sm-6 col-lg-5 controls">
                    <select name="sort_order" class="chosen gallery-cat form-control" data-placeholder="Sort Order">
                        @for($i=1;$i<=$sort_order;$i++)
                            <option value="{{$i}}" <?php if($sort_order == $i) echo "selected"?>>{{$i}}</option>
                        @endfor
                    </select>
                    <input type="hidden" name="curval" value="{{$partner['sort_order']}}">
                    {!! $errors->first('sort_order', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>

            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="partner_description">{{ trans('admin/partnerlogo.description') }}</label>
              <div class="col-sm-6 col-lg-5 controls">
                  <textarea id="textarea" class="form-control" rows="5" name="partner_description">{{ $description}}</textarea>
                  <span class="help-inline">{{ trans('admin/partnerlogo.max_char_allowed') }}</span><br />
                  {!! $errors->first('partner_description', '<span class="help-inline" style="color:#f00">:message</span>') !!}                            
              </div>
            </div>

              <div class="form-group">
              <?php 
              if(Input::old('status'))
              {
                $status=Input::old('status');
              }
              elseif($errors->first('status'))
              {
                $status=Input::old('status');
              }
              elseif(isset($partner['status']))
              {
                $status=$partner['status'];
              }
              else
              {
                $status='ACTIVE';
              }
            ?>
            
              <label class="col-sm-4 col-lg-3 control-label" for="status">Status</label>
              <div class="col-sm-6 col-lg-5 controls">
                <select name="status" class="chosen gallery-cat form-control" data-placeholder="None">
                  <option value="ACTIVE" <?php if($status == 'ACTIVE') echo "selected"?>>Active</option>
                  <option value="IN-ACTIVE" <?php if($status == 'IN-ACTIVE') echo "selected"?>>In-active</option> 
                </select> 
                {!! $errors->first('status', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
            </div>
            <div class="form-group last">
                <div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
                    <input type="submit" class="btn btn-info" value="Save">
                    <a class="btn" href="{{ URL::to('cp/partnerlogo/') }}">Cancel</a>
                </div>
            </div>
          
        </form>
    </div>
  </div><!--Register-->
</div></div>
@stop