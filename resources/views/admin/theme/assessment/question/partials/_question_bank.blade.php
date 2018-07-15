<label for="question_bank" class="col-md-offset-1 col-lg-offset-1 col-sm-4 col-lg-4 control-label">{{ trans('admin/assessment.question_bank') }}<span class="red">*</span></label>
<div class="col-sm-7 col-lg-7 controls">
    <select name="question_bank" class="form-control chosen">
        <option value="null">{{ trans('admin/assessment.select_question_bank ') }}</option>
        @foreach ($questionBanks as $questionBank)
        <option value="{{ $questionBank->question_bank_id }}" {{ (isset($selectedQB))? (($selectedQB == $questionBank->question_bank_id)? "selected" : "") : ((isset($questionBank->default) && ($questionBank->default)) ? "selected" : "") }}>{{ $questionBank->question_bank_name }}</option>
        @endforeach
    </select>
    {!! $errors->first('question_bank', '<span class="help-inline" style="color:#f00">:message</span>') !!}
</div>