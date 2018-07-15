<label for="difficulty_level" class="col-md-offset-1 col-lg-offset-1 col-md-4 col-lg-4 control-label">{{ trans('admin/assessment.difficulty_level') }}<span class="red">*</span></label>
<div class="col-md-7 col-lg-7 controls">
    <select name="difficulty_level" class="form-control chosen">
        <option value="easy" {{ ($difficulty_level === "EASY")? "selected" : "" }}>{{ trans('admin/assessment.easy') }}</option>
        <option value="medium" {{ ($difficulty_level === "MEDIUM")? "selected" : "" }}>{{ trans('admin/assessment.medium') }}</option>
        <option value="difficult" {{ ($difficulty_level === "DIFFICULT")? "selected" : "" }}>{{ trans('admin/assessment.difficult') }}</option>
    </select>
    {!! $errors->first('difficulty_level', '<span class="help-inline" style="color:#f00">:message</span>') !!}
</div>