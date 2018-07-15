<?php
$start = ( ($questions->currentPage()-1) * $questions->perPage() ) + 1;
$end = ( $start + $questions->perPage() ) - 1;
$total = $questions->total();
if($end > $total)
$end = $total;
?>
<style type="text/css">
    .modal-dialog,
.modal-content {
    /* 80% of window height */
    height: 80%;
}
.modal-body {
    /* 100% = dialog height, 120px = header + footer */
    max-height: calc(100% - 120px);
    overflow: auto;
}
</style>
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
            @if(count($questions) === 0)
            <tr>
                <td colspan="2">{{ trans('admin/flashcards.no_result_found') }}</td>
            </tr>
            @else
            @foreach($questions as $question)
            <tr>
                <td><input type="checkbox" class="questions-id" value="{{ $question->question_id }}"></td>
                <td>{!! $question->question_text !!}</td>
                <td>{{ $question->question_type }}</td>
            </tr>
            @endforeach
            @endif
        </tbody>
    </table>
    <div class="row">
        <div class="col-xs-6">
            @if(count($questions) > 1 )
            <div id="table_info">
                {{ "Showing $start - $end of $total" }}
            </div>
            @endif
        </div>
        <div class="col-xs-6 question">
            <ul class="pagination pull-right">
                {!! $questions->render() !!}
            </ul>
        </div>
    </div>
</div>