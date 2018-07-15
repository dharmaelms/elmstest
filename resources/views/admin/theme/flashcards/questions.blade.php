@section('content')
<div>
    <table class="table table-advance" id="datatable">
        <thead>
            <tr>
                <th style="width:18px"><input type="checkbox" class="select-all"></th>
                <th><a href="#" class="sort-asc sort-desc">{{ trans('admin/flashcards.question_name') }}</a></th>
                <th><a href="#" class="sort-asc sort-desc">{{ trans('admin/flashcards.question_type') }}</a></th>
            </tr>
        </thead>
        <tbody>
            @if(!count($questions))
            <tr>
                <td colspan="2">{{ trans('admin/flashcards.no_result_found') }}</td>
            </tr>
            @else
            @foreach($questions as $question)
            <tr>
                <td> <input type="checkbox" value="{{ $question->question_id }}"></td>
                <td>{!! $question->question_text !!}</td>
                <td>{{ $question->question_type }}</td>
            </tr>
            @endforeach
            @endif
        </tbody>
    </table>
</div>
@stop