<?php
$liveChecked    = ($quiz->is_production === 1) ? "checked":'';
$betaChecked    = ($quiz->is_production === 0) ? "checked":'';
$disabled       = ($quiz->is_production === 1) ? "disabled":'';
?>
 <!-- Evnironment setup -->
    <div class="pull-right">
        <label class="radio-inline">Environment: </label>
        <label class="radio-inline">
            <input type="radio" name="environment" value="is_production" <?php echo $liveChecked; ?> onchange="SetEnvironment(this)" >{{ trans('admin/assessment.live_quiz') }}
        </label>
        <label class="radio-inline">
            <input type="radio" name="environment" value="is_beta"  <?php echo $betaChecked; echo $disabled; ?>  onchange="SetEnvironment(this)" >{{ trans('admin/assessment.test_quiz') }}
        </label> 
    </div>
    <!-- environment setup ends here -->

<form action="{{ URL::to("cp/assessment/edit-question-generator/{$quiz->quiz_id}") }}" method="post" accept-Charset="UTF-8" id="form-question-generator" class="form-horizontal form-bordered form-row-stripped">
    <input type="hidden" name="q-g-uid" value="{{ $quiz->_id }}">
    <input type="hidden" id="r-q-g-is_production" name="r-q-g-is_production" value="{{ $quiz->beta?'is_beta':'is_production' }}" >
    <div class="form-group">
        <label for="r-q-g-name" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.name') }} <span class="red">*</span></label>
        <div class="col-sm-9 col-lg-6 controls">
            <input type="text" name="r-q-g-name" class="form-control" value="{{ $quiz->quiz_name }}" >
            {!! $errors->first('r-q-g-name', '<span class="help-inline" style="color:#f00">:message</span>') !!}
        </div>
    </div>
    <div class="form-group">
        <label for="r-q-g-instructions" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.instructions') }}</label>
        <div class="col-sm-9 col-lg-6 controls">
            <textarea name="r-q-g-instructions" id="r-q-g-instructions" rows="5" class="form-control ckeditor">{!! $quiz->quiz_description !!}</textarea>
            {!! $errors->first('r-q-g-instructions', '<span class="help-inline" style="color:#f00">:message</span>') !!}
        </div>
    </div>
    <div class="form-group">
        <label for="r-q-g-keywords" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.keywords_tags') }}</label>
        <div class="col-sm-9 col-lg-6 controls">
            <input type="text" name="r-q-g-keywords" class="form-control tags" value="@if(is_array($quiz->keywords)) {{ implode(',', $quiz->keywords) }} @endif">
            {!! $errors->first('r-q-g-keywords', '<span class="help-inline" style="color:#f00">:message</span>') !!}
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-sm-5 col-md-5 col-lg-5">
                <label for="r-q-g-total-question-limit" class="col-sm-5 col-md-5 col-lg-5 control-label">{{ trans('admin/assessment.total_question_limit') }}<span class="red">&nbsp;*</span></label>
                <div class="col-sm-6 col-md-6 col-lg-6 controls">
                    <input type="text" name="r-q-g-total-question-limit" class="form-control" value="{!! $quiz->total_question_limit !!}" minlength="1" maxlength="4">
                    {!! $errors->first('r-q-g-total-question-limit', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>
            <div class="col-sm-5 col-md-5 col-lg-5">
                <label for="r-q-g-enable-sections" class="col-sm-5 col-md-5 col-lg-5 control-label">{{ trans('admin/assessment.enable_sections') }}</label>
                <div class="col-sm-6 col-md-6 col-lg-6 controls">
                    <input type="checkbox" name="r-q-g-enable-sections" value="TRUE" {{ ($quiz->is_sections_enabled === true)? "checked" : "" }} style="margin-top:5%;"  {{!empty($quiz->questions) ? "disabled" : ""}}>
                    {!! $errors->first('r-q-g-enable-sections', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label for="r-q-g-display-start-date" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.display_start_date') }}</label>
        <div class="col-lg-3 controls">
            <div class="input-group date">
                <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
                <input type="text" name="r-q-g-display-start-date" class="form-control qg-input-start-date" style="cursor: pointer" value="@if(!empty($quiz->start_time)) {{ $quiz->start_time->timezone(Auth::user()->timezone)->format('d-m-Y'
                ) }} @endif" readonly="readonly">
            </div>
            {!! $errors->first('r-q-g-display-start-date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
        </div>
        <div class="col-lg-2 controls">
            <div class="input-group">
                <span class="input-group-addon" href="#"><i class="fa fa-clock-o"></i></span>
                <input type="text" name="r-q-g-display-start-time" onclick="$(this).prev().click()" class="form-control qg-input-start-time" style="cursor: pointer" value="@if(!empty($quiz->start_time)) {{ $quiz->start_time->timezone(Auth::user()->timezone)->format('H:i') }} @endif" readonly="readonly">
                <span class="input-group-btn">
                    <button id="r-q-g-display-start-time-reset" class="btn" ><i class="fa fa-undo"></i></button>
                </span>
            </div>
            {!! $errors->first('r-q-g-display-start-time', '<span class="help-inline" style="color:#f00">:message</span>') !!}
        </div>
    </div>
    <div class="form-group">
        <label for="r-q-g-display-end-date" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.display_end_date') }}</label>
        <div class="col-lg-3 controls">
            <div class="input-group date">
                <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
                <input type="text" name="r-q-g-display-end-date" class="form-control qg-input-end-date" style="cursor: pointer"  value="@if(!empty($quiz->end_time)) {{ $quiz->end_time->timezone(Auth::user()->timezone)->format('d-m-Y'
                ) }} @endif" readonly="readonly">
            </div>
            {!! $errors->first('r-q-g-display-end-date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
        </div>
        <div class="col-lg-2 controls">
            <div class="input-group">   
                <span class="input-group-addon" href="#"><i class="fa fa-clock-o"></i></span>
                <input type="text" name="r-q-g-display-end-time" onclick="$(this).prev().click()" class="form-control qg-input-end-time" style="cursor: pointer" value="@if(!empty($quiz->end_time)) {{ $quiz->end_time->timezone(Auth::user()->timezone)->format('H:i') }} @endif" readonly="readonly">
                <span class="input-group-btn">
                    <button id="r-q-g-display-end-time-reset" class="btn" ><i class="fa fa-undo"></i></button>
                </span>
            </div>
            {!! $errors->first('r-q-g-display-end-time', '<span class="help-inline" style="color:#f00">:message</span>') !!}
        </div>
    </div>
    <div class="form-group last">
        <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
           <button type="submit" class="btn btn-success" ><i class="fa fa-check"></i> {{ trans('admin/assessment.save') }}</button>
           <a href="{{URL::to('/cp/assessment/list-quiz')}}"><button type="button" class="btn">{{ trans('admin/assessment.cancel') }}</button></a>
        </div>
    </div>
</form>
<script>
$(document).ready(function(){

    $("input.tags").tagsInput({
        width: "auto"
    });

    $('#r-q-g-display-start-time-reset').click(function(e) {
        e.preventDefault();
        $('input[name=r-q-g-display-start-date]').val('');
        $('input[name=r-q-g-display-start-time]').val('');
    });

    $('#r-q-g-display-end-time-reset').click(function(e) {
        e.preventDefault();
        $('input[name=r-q-g-display-end-date]').val('');
        $('input[name=r-q-g-display-end-time]').val('');
    });
    
    $("input[name=r-q-g-total-question-limit]").on('keypress', function(event){
        var charCode = (event.which) ? event.which : event.keyCode;
        if (charCode != 46 && charCode > 31
        && (charCode < 48 || charCode > 57))
            return false;
        if(this.value.length > 4)
            this.value = this.value.slice(0,3); 
        return true;
    });
});

    function SetEnvironment(myRadio)
    {
       $("#r-q-g-is_production").val(myRadio.value);
    }

</script>