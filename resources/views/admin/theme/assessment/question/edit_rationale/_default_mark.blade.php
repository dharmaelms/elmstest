<label for="default_mark" class="col-sm-5 col-lg-5 control-label">{{ trans('admin/assessment.default_mark') }}</label>
<div class="col-sm-6 col-lg-6 controls">
    <label name="default_mark" class="col-md-7 col-lg-7 controls" data-toggle="tooltip" data-placement="top" title="If default mark which you are providing is less than 1, System will consider default mark as 1.">{{ trans('admin/assessment.default_mark') }}</label>
    {!! $errors->first('default_mark', '<span class="help-inline" style="color:#f00">:message</span>') !!}
</div>