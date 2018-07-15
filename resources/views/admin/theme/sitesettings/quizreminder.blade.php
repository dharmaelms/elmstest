<form action="{{URL::to('cp/sitesetting/update/QuizReminders')}}" method="post" accept-charset="utf-8" class="form-horizontal form-bordered form-row-stripped"> 
@foreach($quiz_reminders as $key => $val)
<div class="box box-black">
    <div class="box-title">
        <h3>{{ $key }} </h3>
    </div>
    <div class="box-content" >
    <div class="tab-pane">
    <div class="row">

        <div class="col-sm-6 col-md-6">
           <div class="form-group">
                <label class="col-sm-6 col-lg-3 control-label">{{ trans('admin/sitesetting.reminder_status') }}</label>
                <div class="col-sm-6 col-lg-4 controls">
                    <input type="radio" id="radio_on_{{$key}}" name="status_{{$key}}" value='on' <?php echo $val['reminder_status'] == 'on' ? "checked" : ""; ?>> {{ trans('admin/sitesetting.on') }} 
                    <input type="radio" id="radio_off_{{$key}}" name="status_{{$key}}" value='off' <?php echo $val['reminder_status'] == 'off' ? "checked" : ""; ?>> {{ trans('admin/sitesetting.off') }} 
                     {!! $errors->first('status_'.$key, '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>                                      
            </div>
            <div class="form-group">
                <label class="col-sm-6 col-lg-3 control-label">{{ trans('admin/sitesetting.reminder') }}</label>
                <div class="col-sm-6 col-lg-4 controls">
                    <input type="text" id="reminder_day_{{$key}}"  name="day_{{$key}}" style="width:20%;" value="{{ $val['reminder_day'] }}" >  {{ trans('admin/sitesetting.days_before_quiz_expiry') }}
                </div>                                      
                {!! $errors->first('day_'.$key, '<span class="help-inline" style="color:#f00">:message</span>') !!}
            </div>

            <div class="form-group">
                <label class="col-sm-6 col-lg-3 control-label">{{ trans('admin/sitesetting.notify_by_email') }}</label>
                <div class="col-sm-6 col-lg-4 controls">                               
                    <input type="checkbox" id="notify_mail_{{$key}}" name="notify_mail_{{$key}}"<?php if ($val['notify_by_mail'] == 'on') {
                        echo "checked";
} ?>>
                </div>
            </div>                            
        </div>

        <div class="col-sm-6 col-md-6 col-lg-6">
            <div class="form-group">
                <label class="col-sm-6 col-lg-3 control-label">{{ trans('admin/sitesetting.quiz_type') }}</label>
                <div class="col-sm-6 col-lg-4 controls">
                                                            
                    <input type="checkbox" id="general_quiz_{{$key}}" name="general_{{$key}}"  <?php if ($val['quiz_type']['general'] == 'on') {
                        echo 'checked';
} ?> >{{ trans('admin/sitesetting.general_quiz') }} {{ trans('admin/sitesetting.live') }}<br>{!! $errors->first('general_'.$key, '<span class="help-inline" style="color:#f00">:message</span>') !!}<br>


                    <input type="checkbox" id="general_practice_{{$key}}" name="general_practice_{{$key}}"  <?php if ($val['quiz_type']['general_practice'] == 'on') {
                        echo 'checked';
} ?> >{{ trans('admin/sitesetting.general_quiz_practise') }} {{ trans('admin/sitesetting.live') }}<br>{!! $errors->first('general_practice_'.$key, '<span class="help-inline" style="color:#f00">:message</span>') !!}<br>

                
                    <input type="checkbox" id="question_generator_{{$key}}" name="question_generator_{{$key}}" <?php if ($val['quiz_type']['question_generator'] == 'on') {
                        echo 'checked';
} ?> >{{ trans('admin/sitesetting.question_generator') }} {{ trans('admin/sitesetting.live') }}<br>{!! $errors->first('question_generator_'.$key, '<span class="help-inline" style="color:#f00">:message</span>') !!}<br>

                </div>
            </div>
        </div>
    </div>
    </div>
</div>
@endforeach
  <div class="form-group">
    <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-5">
        <button type="submit" class="btn btn-info text-right" >{{ trans('admin/sitesetting.update') }} </button>
        <a href="{{URL::to('/cp/sitesetting/')}}" >
        <button type="button" class="btn">{{ trans('admin/sitesetting.cancel') }}</button></a>
    </div>
</div> 
</div>
      </div>         
</form>
 
 <script type="text/javascript">
@foreach($quiz_reminders as $key => $val)
    $(document).ready(function() {
        var reminder_type = '<?php echo $key ?>';
        var remiander_day_value = (reminder_type == "Reminder1") ? "3" : "1";
        
        if($("#radio_off_{{$key}}:checked").val() == "off"){
            disableAllFields();
        }

        $('#radio_off_{{$key}}').click(function() {
          disableAllFields();
        });

        $('#radio_on_{{$key}}').click(function() {
            enableAllFields();
        });
      
        function disableAllFields()
        {
            $('#reminder_day_{{$key}}').prop("disabled", "disabled").val("");
            $('#notify_mail_{{$key}}').prop("disabled", "disabled").prop("checked", false);
            $('#general_quiz_{{$key}}').prop("disabled", "disabled").prop("checked", false);
            $('#general_practice_{{$key}}').prop("disabled", "disabled").prop("checked", false);
            $('#question_generator_{{$key}}').prop("disabled", "disabled").prop("checked", false);
        }

        function enableAllFields()
        {
            $('#reminder_day_{{$key}}').removeAttr("disabled").val(remiander_day_value);
            $('#notify_mail_{{$key}}').removeAttr("disabled");
            $('#general_quiz_{{$key}}').removeAttr("disabled").prop("checked", true);
            $('#general_practice_{{$key}}').removeAttr("disabled");
            $('#question_generator_{{$key}}').removeAttr("disabled").prop("checked", true);
        }

    });
@endforeach
 </script>
 

