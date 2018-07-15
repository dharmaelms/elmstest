<style type="text/css">
.table thead tr th, .table tbody tr td{
    padding-left:20px;
}
</style>
    <div>
        @if(!$assignments->isempty() && isset($assignments))
        <table class="table table-bordered">
            <thead style="background: linear-gradient(#219e8d,#05b5a5ed,#b7f7e8b3);">
                <tr>
                    <th width="40%">{{ trans('assignment.assignment_title') }}</th>
                    <th>{{ trans('assignment.status') }}</th>
                    <th>{{ trans('assignment.grade') }}</th>
                    <th>{{ trans('assignment.review_comments') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assignments as $s)
                    <?php
                        $data = $attempted_data->where('assignment_id', $s->id);
                        $data = $data->first();
                        $current_time = Carbon::now(Auth::user()->timezone);
                        $assignment_start_date = $s->start_time;
                        $assignment_cut_off_date = $s->cutoff_time;
                    ?>
                    <tr>
                        <td>{{$s->name}}</td>
                        <td>
                            @if($data->submission_status == 'SAVE_AS_DRAFT')
                                @if(($current_time >= $assignment_start_date) && ($current_time <= $assignment_cut_off_date))
                                    <a href="{{ URL::Route('submit-assignment',['packet_slug' => 'from_reports', 'assignment_id' => $s->id, 'message' => 'undraft']) }}">{{ trans('assignment.saved_as_draft') }}</a>
                                @else
                                    <a href="#" title="Expired">{{ trans('assignment.saved_as_draft') }}</a>
                                @endif

                            @elseif($data->submission_status == 'REVIEWED')
                            <a href="{{ URL::Route('assignment-result',['packet_slug' => 'from_reports', 'assignment_id' => $s['id']]) }}" title="{{ $s['name'] }}">
                                {{ trans('assignment.reviewd') }}
                            </a>
                            @else
                            <a href="{{ URL::Route('assignment-result',['packet_slug' => 'from_reports', 'assignment_id' => $s['id']]) }}" title="{{ $s['name'] }}">
                                {{ trans('assignment.yet_to_review') }}
                            </a>
                            @endif
                        </td>
                        <td>
                        <?php
                                $pass = $data->pass ? "Pass" : "Fail";
                        ?>
                        @if($data->submission_status == 'REVIEWED')
                            {{$data->grade}} | {{$pass}}
                        @else
                            {{ trans('assignment.not_applicable') }}
                        @endif
                        </td>
                        <td>
                            @if($data->review_comments)
                                <a class="my_link" data-val="{{$data->review_comments}}" data-val2="{{$s->name}}" data-toggle="modal" data-target="#comment-modal">{{ trans('assignment.view') }}</a>
                            @else
                                {{ trans('assignment.not_applicable') }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <!-- Modal to display review comments -->
        <div class="modal fade" id="comment-modal" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <span class="font-16"><strong></strong></span>
                    </div>
                    <div class="modal-body">
                    </div>
                    <div class="modal-footer" style="padding: 5px !important;">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div>
            {{ trans('assignment.no_assignments') }}
        </div>
        @endif
        <!-- Modal to display review comments ends -->
    </div>
<script>
$('#comment-modal').on('show.bs.modal', function (event) {
  var myVal = $(event.relatedTarget).data('val');
  var myTitle = $(event.relatedTarget).data('val2');
  $(this).find(".modal-header span strong").html(myTitle);
  $(this).find(".modal-body").html(myVal);
});
</script>

