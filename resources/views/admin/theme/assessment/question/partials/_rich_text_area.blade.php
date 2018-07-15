<textarea name="{{ $name }}" rows="5" id="{{ $id }}" class="form-control" contenteditable="true">{!! $content or "" !!}</textarea>
<script>
    $(document).ready(function(){
        CKEDITOR.inline("{{ $id }}",{ customConfig: $configPath });
    });
</script>