<?php
$start = ( ($flashcards->currentPage()-1) * $flashcards->perPage() ) + 1;
$end = ( $start + $flashcards->perPage() ) - 1;
$total = $flashcards->total();
if($end > $total)
$end = $total;

?>
<style type="text/css">
    #main-content {background: white !important;}
</style>
<table class="table table-advance" id="datatable">
    <thead>
        <tr>
            <th style="width:18px"><input type="checkbox" class="select-all"></th>
            <th>{{trans('admin/flashcards.name')}}</th>            
            <th>{{trans('admin/flashcards.created_on')}}</th>
            <th>{{trans('admin/flashcards.cards')}}</th>
        </tr>
    </thead>
    <tbody>
        @if(count($flashcards) === 0)
        <tr><td colspan="5">{{trans('admin/flashcards.no_result_found')}} </td></tr>
        @else
        @foreach($flashcards as $flashcard)
        <tr>
            <td><input class="cards-id" type="checkbox" data-slug="{{ $flashcard->slug }}" data-id="{{ $flashcard->card_id }}" data-title="{{ $flashcard->title }}" value="{{ $flashcard->card_id }}"></td>
            <td>{{ $flashcard->title }}</td>            
            <td>{{ $flashcard->created_at }}</td>
            <td>
                <a class="show-tooltip damsrel badge badge-success">{{ count($flashcard->cards) }}</a>
            </td>
        </tr>
        @endforeach
        @endif
    </tbody>
</table>
<div class="row">
    <div class="col-xs-6">
        @if(count($flashcards) > 1 )
        <div id="table_info">
        {{ "Showing $start - $end of $total" }}
        </div>
        @endif
    </div>
    <div class="col-xs-6">
        <ul class="pagination pull-right">
            {!! $flashcards->render() !!}
        </ul>
    </div>
</div>