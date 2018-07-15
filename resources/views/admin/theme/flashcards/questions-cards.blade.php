<?php $range = range('a', 'z');?>
@foreach($questions as $key => $question)
<?php $correct_ans = [];?>
@if($question->question_type == 'MCQ')
<div class="col-md-12 slide row card-column">
    <div class="col-md-5">
        <div class="col-sm-9 col-lg-12 controls">
            <textarea class="form-control dynamic front hide" rows="5" id="textarea_{{ $count+$key }}_front" name="slides[{{ $count+$key }}][front]"  contenteditable="true">
            	{!! $question->question_text !!} <br/>
            	@foreach($question->answers as $index => $answer)
                    @if(strpos($answer['answer'], '<p>'))
            		{!! substr_replace($answer['answer'], "$range[$index] ) ", 0, 0) !!}
                    @else
                    {!! $range[$index].") ".$answer['answer'] !!}
                    @endif
            	@endforeach
            </textarea>
            <div class="panel panel-default">
                <div id="editor_front_{{ $count+$key }}" contenteditable="true" class="editor panel-body ">
                {!! $question->question_text !!} <br/>
            	@foreach($question->answers as $index => $answer)
            		{!! substr_replace($answer['answer'], "$range[$index] ) ", 0, 0) !!}<br>
            	@endforeach
                </div>
            </div>
            <span class="help-block error required" id="slides_{{ $count+$key }}_front_error"></span>
        </div>
    </div>
    <div class="col-md-5">
        <div class="col-sm-9 col-lg-12 controls">
            <textarea class="form-control dynamic back hide" rows="5" id="textarea_{{ $count+$key }}_back" name="slides[{{ $count+$key }}][back]"  contenteditable="true">
            @foreach($question->answers as $index => $answer)
                @if($answer['correct_answer'] == true)
                <p>Correct Answer</p>
                @if(strpos($answer['answer'], '<p>'))
                    {!! substr_replace($answer['answer'], "$range[$index] ) ", 0, 0) !!}
                    @else
                    {!! $range[$index].") ".$answer['answer'] !!}
                @endif
                @endif
            @endforeach
            </textarea>
            <div class="panel panel-default">                
                <div id="editor_back_{{ $count+$key }}" contenteditable="true" class="editor panel-body ">
                @foreach($question->answers as $index => $answer)
                    @if($answer['correct_answer'] == true)
                    <p>Correct Answer</p>
                    @if(strpos($answer['answer'], '<p>'))
                        {!! substr_replace($answer['answer'], "$range[$index] ) ", 0, 0) !!}
                        @else
                        {!! $range[$index].") ".$answer['answer'] !!}
                    @endif
                    @endif
                @endforeach
                </div>
            </div>
            <span  class="help-block error required" id="slides_{{ $count+$key }}_back_error"></span>
        </div>
    </div>
    <div class="col-md-2">
        <a href="#" class="pull-left delete-card" ><i class="glyphicon glyphicon-trash"></i></a>
    </div>
</div>
@elseif($question->question_type == 'DESCRIPTIVE')
<div class="col-md-12 slide row card-column">
    <div class="col-md-5">
        <div class="col-sm-9 col-lg-12 controls">
            <textarea class="form-control dynamic front hide" rows="5" id="textarea_{{ $count+$key }}_front" name="slides[{{ $count+$key }}][front]"  contenteditable="true">{!! $question->question_text !!}</textarea>
            <div class="panel panel-default">
                <div id="editor_front_{{ $count+$key }}" contenteditable="true" class="editor panel-body ">
                {!! $question->question_text !!}
                </div>
            </div>
            <span class="help-block error required" id="slides_{{ $count+$key }}_front_error"></span>
        </div>
    </div>
    <div class="col-md-5">
        <div class="col-sm-9 col-lg-12 controls">
            <textarea class="form-control dynamic back hide" rows="5" id="textarea_{{ $count+$key }}_back" name="slides[{{ $count+$key }}][back]"  contenteditable="true"></textarea>
            <div class="panel panel-default">                
                <div id="editor_back_{{ $count+$key }}" contenteditable="true" class="editor panel-body ">
                </div>
            </div>
            <span  class="help-block error required" id="slides_{{ $count+$key }}_back_error"></span>
        </div>
    </div>
    <div class="col-md-2">
        <a href="#" class="pull-left delete-card" ><i class="glyphicon glyphicon-trash"></i></a>
    </div>
</div>
@endif
<script type="text/javascript">
  CKEDITOR.inline( "editor_front_{{ $count+$key }}", {
    on: {
        change:function(){
            $("#editor_front_{{ $count+$key }}").parent().parent().find('textarea').html(this.getData());
        }
    },
    customConfig: "{{ URL::asset('admin/assets/ckeditor/config.js')}}"
  });
  CKEDITOR.inline( "editor_back_{{ $count+$key }}", {
    on: {
        change:function(){
            $("#editor_back_{{ $count+$key }}").parent().parent().find('textarea').html(this.getData());
        }
    },
    customConfig: "{{ URL::asset('admin/assets/ckeditor/config.js')}}"
  });  
</script>
@endforeach