<div class="table">
    <table class="table table-striped table-hover fill-head">
        <thead>
            <tr>
                <th>#</th>
                <th>{{trans('admin/batch/copy_course_content.course_list_title')}}</th>
            </tr>
        </thead>
        <tbody>
        <?php $i = 0;?>
        @foreach($program_list as $each_program)
            <tr>
                <td>
                <input type="radio" name="from_copy" id="from_copy" value="{{$each_program['program_slug']}}" @if($i===0) checked @endif></td>
                <td>{{$each_program['program_title']}}</td>
            </tr>
            <?php $i++; ?>
         @endforeach
        </tbody>
    </table>
</div>