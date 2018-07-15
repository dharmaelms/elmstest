<?php
$start = ( ($flashcards->currentPage()-1) * $flashcards->perPage() ) + 1;
$end = ( $start + $flashcards->perPage() ) - 1;
$total = $flashcards->total();
if ($end > $total) {
    $end = $total;
}
?>
<table class="table table-advance" id="datatable">
    <thead>
        <tr>
            <th style="width:18px" data-sort="false"><input type="checkbox" class="select-all"></th>
            <th class="sorting" data-sort="true" data-column="title">{{trans('admin/flashcards.name')}}</th>
            <th class="sorting" data-sort="true" data-column="created_at">{{trans('admin/flashcards.created_on')}}</th>
            <th data-sort="false">{{trans('admin/flashcards.cards')}}</th>
            <th data-sort="false">{{trans('admin/flashcards.actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @if(count($flashcards) === 0)
        <tr><td colspan="5"> {{ trans('admin/flashcards.no_result_found') }} </td></tr>
        @else
        @foreach($flashcards as $flashcard)
        <tr>
            <td><input class="cards-id" type="checkbox" data-slug="{{ $flashcard->slug }}" data-id="{{ $flashcard->card_id }}" data-title="{{ $flashcard->title }}" value="{{ $flashcard->card_id }}"></td>
            <td>{{ $flashcard->title }}</td>            
            <td>{{ Timezone::convertFromUTC($flashcard->created_at, Auth::user()->timezone, 'd-m-Y H:i:s') }}</td>
            <td>
                <a class="show-tooltip damsrel badge badge-success">{{ count($flashcard->cards) }}</a>
            </td>
            <td>
                @if(has_admin_permission(ModuleEnum::FLASHCARD, FlashCardPermission::VIEW_FLASHCARD))
                    <a class="btn btn-circle show-tooltip" href="{{ URL::to('/cp/flashcards/view/'.$flashcard->slug) }}">
                        <i class="fa fa-eye"></i>
                    </a>
                @else
                    <a class="badge badge-info show-tooltip" title="{{ trans('admin/flashcards.no_view_action') }}">NA</a>
                @endif
                @if(has_admin_permission(ModuleEnum::FLASHCARD, FlashCardPermission::EDIT_FLASHCARD))
                    <a class="btn btn-circle show-tooltip" href="{{ URL::to('/cp/flashcards/edit/'.$flashcard->slug) }}">
                        <i class="fa fa-edit"></i>
                    </a>
                @else
                    <a class="badge badge-info show-tooltip" title="{{ trans('admin/flashcards.no_edit_action') }}">NA</a>
                @endif
                @if($flashcard->status != 'INACTIVE' && has_admin_permission(ModuleEnum::FLASHCARD, FlashCardPermission::DELETE_FLASHCARD))
                    <a data-toggle="modal" href="delete" class="btn btn-circle show-tooltip delete" data-title="{{ $flashcard->title }}" data-slug="{{ $flashcard->slug }}" data-id="{{ $flashcard->card_id }}" href="#">
                        <i class="fa fa-trash-o"></i>
                    </a>
                @else
                    <a class="badge badge-info show-tooltip" title="{{ trans('admin/flashcards.no_delete_action') }}">NA</a>
                @endif
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
        <ul class="list pagination pull-right">
            {!! $flashcards->render() !!}
        </ul>
    </div>
</div>