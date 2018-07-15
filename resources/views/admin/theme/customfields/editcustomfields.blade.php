@section('content')
@if ( Session::get('success') )
    <div class="alert alert-success" id="alert-success">
      <button class="close" data-dismiss="alert">×</button>
      <strong>Success!</strong>
      {{ Session::get('success') }}
    </div>
<?php Session::forget('success'); ?>
@endif
@if ( Session::get('error'))
    <div class="alert alert-danger">
      <button class="close" data-dismiss="alert">×</button>
      <strong>Error!</strong>
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
        <form action="{{URL::to('cp/customfields/edit-field/'.$data['id'].'?filter='.$filter)}}" class="form-horizontal form-bordered form-row-stripped" method="post" >         
            <div class="form-group">
                <?php
                    if(Input::old('fieldname')) 
                    {
                        $fieldname = Input::old('fieldname');
                    } 
                    elseif($errors->first('fieldname')) 
                    {
                        $fieldname = Input::old('fieldname');
                    } 
                    elseif(isset($data['fieldname'])) 
                    {
                        $fieldname = $data['fieldname'];
                    }
                    else
                    {
                        $fieldname = "";
                    }
                ?>
                <label class="col-sm-4 col-lg-3 control-label" for="fieldname">{{trans('admin/customfields.fieldname')}}<span class="red">*</span></label>
                <div class="col-sm-6 col-lg-5 controls">
                    <input type="text" class="form-control" name="fieldname" value="{{ $fieldname }}" @if($field_exist) readonly @endif> 
                    <input type="hidden" name="oldfield" value="{{$data['fieldname']}}">
                    <input type="hidden" name="program_type" value="{{$data['program_type']}}">
                    <input type="hidden" name="program_sub_type" value="{{$data['program_sub_type']}}">
                    {!! $errors->first('fieldname', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>

            <div class="form-group">
                <?php
                    if(Input::old('fieldlabel')) 
                    {
                        $fieldlabel = Input::old('fieldlabel');
                    } 
                    elseif($errors->first('fieldlabel')) 
                    {
                        $fieldlabel = Input::old('fieldlabel');
                    } 
                    elseif(isset($data['fieldlabel'])) 
                    {
                        $fieldlabel = $data['fieldlabel'];
                    }
                    else
                    {
                        $fieldlabel = "";
                    }
                ?>
                <label class="col-sm-4 col-lg-3 control-label" for="fieldlabel">{{trans('admin/customfields.fieldlabel')}}<span class="red">*</span></label>
                <div class="col-sm-6 col-lg-5 controls">
                    <input type="text" class="form-control" name="fieldlabel" value="{{ $fieldlabel }}"> 
                    {!! $errors->first('fieldlabel', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>

            <div class="form-group">
                <?php
                    if(Input::old('mark_as_mandatory')) 
                    {
                        $ischecked = "yes";
                    } 
                    elseif(isset($data['mark_as_mandatory'])) 
                    {
                        $ischecked = $data['mark_as_mandatory'];
                    }
                    else
                    {
                        $ischecked = "no";
                    }
                ?>
                <label class="col-sm-4 col-lg-3 control-label">{{trans('admin/customfields.mandatory')}}</label>
                <div class="col-sm-6 col-lg-5 controls">                               
                    <input type="checkbox" name="mark_as_mandatory" <?php echo $ischecked=="yes"?"checked":"";?> >
                </div>
            </div>

            @if($filter == 'userfields')
            <div class="form-group">
                <?php
                    if(Input::old('edited_by_user')) 
                    {
                        $ischecked = "yes";
                    }
                    elseif(isset($data['edited_by_user'])) 
                    {
                        $ischecked = $data['edited_by_user'];
                    }
                    else
                    {
                        $ischecked = "no";
                    }
                ?>
                <label class="col-sm-4 col-lg-3 control-label">{{trans('admin/customfields.edited_by_user')}}</label>
                <div class="col-sm-6 col-lg-5 controls">                               
                    <input type="checkbox" name="edited_by_user" <?php echo $ischecked=="yes"?"checked":"";?> >
                </div>
            </div>
            @endif

            <div class="form-group">
              <?php 
                if(Input::old('status'))
                {
                    $status=Input::old('status');
                }
                elseif(isset($data['status'])) 
                {
                    $status = $data['status'];
                }
                else
                {
                    $status='ACTIVE';
                }
            ?>
              <label class="col-sm-4 col-lg-3 control-label" for="status">{{trans('admin/customfields.status')}}</label>
              <div class="col-sm-6 col-lg-5 controls">
                <select name="status" class="chosen gallery-cat form-control" data-placeholder="None">
                  <option value="ACTIVE" <?php if($status == 'ACTIVE') echo "selected"?>>{{ trans('admin/customfields.active')}}</option>
                  <option value="IN-ACTIVE" <?php if($status == 'IN-ACTIVE') echo "selected"?>>{{ trans('admin/customfields.in_active')}}</option>
                </select>
                {!! $errors->first('status', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
            </div>
            <div class="form-group last">
                <div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
                    <input type="submit" class="btn btn-info" value="Save">
                    <a class="btn" href="{{ URL::to('cp/customfields?filter='.$filter) }}">{{ trans('admin/customfields.cancel')}}</a>
                </div>
            </div>
          </div>
        </form>
    </div>
  </div><!--Register-->
</div>
@stop