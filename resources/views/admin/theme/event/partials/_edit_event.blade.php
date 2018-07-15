<input type="hidden" name="_eid" value="{{ $event['event_id'] }}">
<div class="form-group">
    <label for="event_name" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/event.event_name') }} <span class="red">*</span></label>
    <div class="col-sm-9 col-lg-6 controls">
        <input type="text" name="event_name" class="form-control" value="{{ $event->event_name }}" >
        {!! $errors->first('event_name', '<span class="help-inline" style="color:#f00">:message</span>') !!}
    </div>
</div>
<div class="form-group">
    <label for="event_description" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/event.event_description') }}</label>
    <div class="col-sm-9 col-lg-6 controls">
        <textarea name="event_description" rows="5" id="{{ $editor }}" class="form-control ckeditor">{!! $event->event_description !!}</textarea>
        {!! $errors->first('event_description', '<span class="help-inline" style="color:#f00">:message</span>') !!}
    </div>
</div>
<input type="hidden" name="event_short_description" value="">
<div class="form-group">
    <label for="keywords" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/event.keyword_tags') }}</label>
    <div class="col-sm-9 col-lg-6 controls">
        <input type="text" name="keywords" class="form-control tags" value="@if(is_array($event->keywords)) {{ implode(',', $event->keywords) }} @endif">
        {!! $errors->first('keywords', '<span class="help-inline" style="color:#f00">:message</span>') !!}
    </div>
</div>

