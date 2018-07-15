<label for="keywords" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.keyword_tags') }}</label>
<div class="col-sm-9 col-lg-7 controls">
    <input type="text" name="keywords" class="form-control tags" value="{{ $tags }}">
    {!! $errors->first('keywords', '<span class="help-inline" style="color:#f00">:message</span>') !!}
</div>