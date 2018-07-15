<div class="col-md-12 slide row card-column">
    <div class="col-md-5">
        <div class="col-sm-9 col-lg-12 controls">
            <textarea class="form-control dynamic front hide" rows="5" id="textarea_{{ $count }}_front" name="slides[{{ $count }}][front]"  contenteditable="true"></textarea>
            <div class="panel panel-default">
                <a href="#" class="windowLabel" data-panel="css">
                  <span class="label">{{ trans('admin/flashcards.front')}}</span>
                </a>
                <div id="editor_front_{{ $count }}" contenteditable="true" class="editor panel-body ">
                </div>                
            </div>
            <span class="help-block error required" id="slides_{{ $count }}_front_error"></span>
        </div>
    </div>
    <div class="col-md-5">
        <div class="col-sm-9 col-lg-12 controls">
            <textarea class="form-control dynamic back hide" rows="5" id="textarea_{{ $count }}_back" name="slides[{{ $count }}][back]"  contenteditable="true"></textarea>            
            <div class="panel panel-default"> 
                <a href="#" class="windowLabel" data-panel="css">
                  <span class="label">{{ trans('admin/flashcards.back')}}</span>
                </a>               
                <div id="editor_back_{{ $count }}" contenteditable="true" class="editor panel-body ">
                </div>                
            </div>
            <span  class="help-block error required" id="slides_{{ $count }}_back_error"></span>
        </div>
    </div>
    <div class="col-md-2">
        <a href="#" class="pull-left delete-card" ><i class="glyphicon glyphicon-trash"></i></a>
    </div>
</div>
<script type="text/javascript">
  CKEDITOR.inline( "editor_front_{{ $count }}", {
    on: {
        change:function(){
            $("#editor_front_{{ $count }}").parent().parent().find('textarea').html(this.getData());
        }
    },
    customConfig: "{{ URL::asset('admin/assets/ckeditor/config.js')}}"
  });
  CKEDITOR.inline( "editor_back_{{ $count }}", {
    on: {
        change:function(){
            $("#editor_back_{{ $count }}").parent().parent().find('textarea').html(this.getData());
        }
    },
    customConfig: "{{ URL::asset('admin/assets/ckeditor/config.js')}}"
  });  
</script>