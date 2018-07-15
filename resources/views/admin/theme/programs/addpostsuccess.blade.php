@section('content')
<?php
    use App\Model\Assignment\Entity\Assignment;
    use App\Model\Dam;
    use App\Model\Quiz;
    use App\Model\Event;
    use App\Model\FlashCard;
    use App\Model\Survey\Entity\Survey;
?>
<style type="text/css">
    .well-sm { padding: 6px;margin-bottom: 12px;cursor: pointer; }
</style>
@if ( Session::get('success') )
    <div class="alert alert-success">
        <button class="close" data-dismiss="alert">×</button>
        <!-- <strong>Success!</strong> -->
        {{ Session::get('success') }}
    </div>
    <?php Session::forget('success'); ?>
@endif
@if ( Session::get('error'))
    <div class="alert alert-danger">
        <button class="close" data-dismiss="alert">×</button>
        <!-- <strong>Error!</strong> -->
        {{ Session::get('error') }}
    </div>
    <?php Session::forget('error'); ?>
@endif

<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
<link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-ui/jquery-ui.min.css')}}">
<script src="{{ URL::asset('admin/assets/jquery-ui/jquery-ui.min.js')}}"></script>

    <div class="tabbable">
        <ul id="myTab1" class="nav nav-tabs">
            <li class="active">
                <a href="#generalinfo" data-toggle="tab">
                    <i class="fa fa-home"></i> {{trans('admin/program.general_info')}}
                </a>
            </li>
            <li>
                <a href="#items" data-toggle="tab" id="items-tab">
                    <i class="fa fa-rss"></i> {{trans('admin/program.items')}}
                </a>
            </li>
        </ul>
    </div>

    <div id="myTabContent1" class="tab-content">
        <div class="tab-pane fade active in" id="generalinfo">
            @include(
                'admin.theme.programs.editpacket',
                 ['slug' => $packet['packet_slug'], 'packet' => $packet, 'type' => $program['program_type']]
            )
        </div>
        <div class="tab-pane fade" id="items">
            <div class="row">
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-title">
                            <div class="box-tool">
                                <a data-action="collapse" href="#"><i class="icon-chevron-up"></i></a>
                            </div>
                        </div>                    
                        <div class="box-content">
                            <div class="pull-right" id="btn-group-item-options">
                                <a class="btn btn-primary btn-sm" id="upload_button">
                                    <span class="btn btn-circle blue show-tooltip custom-btm">
                                        <i class="fa fa-plus"></i>
                                    </span>&nbsp;<?php echo trans('admin/program.upload_new'); ?>
                                </a>&nbsp;&nbsp;
                                <a class="btn btn-primary btn-sm" id="add_button">
                                    <span class="btn btn-circle blue show-tooltip custom-btm">
                                        <i class="fa fa-plus"></i>
                                    </span>&nbsp;<?php echo trans('admin/program.from_library'); ?>
                                </a>&nbsp;&nbsp;
                            </div>
                            <div class="pull-right" id="btn-back" style="display:none;">
                                <a class="btn btn-primary btn-sm">
                                    <span class="btn btn-circle blue show-tooltip custom-btm">
                                    </span>&nbsp;<?php echo trans('admin/program.back_button'); ?>
                                </a>&nbsp;&nbsp;
                            </div>
                            <br><br><br>
                            <div id="items_list">
                                <script type="text/javascript">media_types = {};</script>
                                @if(isset($packet['elements']) && is_array($packet['elements']) && count($packet['elements']))            
                                    <div class="clearfix"></div>
                                    <fieldset>
                                        <ul style="list-style-type: none;padding:0; margin:0; min-height: 20px;">
                                            <li>
                                                <table width="100%">
                                                    <tr>
                                                        <th width="200"><?php echo trans('admin/program.item_name'); ?></th>
                                                        <th width="200"><?php echo trans('admin/program.display_name'); ?></th>
                                                        <th width="100"><?php echo trans('admin/program.date_added'); ?></th> 
                                                        <th width="200"><?php echo trans('admin/program.added_by'); ?></th>
                                                        <th width="100"><?php echo trans('admin/program.item_type'); ?></th>
                                                        <th width="100"><?php echo trans('admin/program.action'); ?></th>
                                                    </tr>
                                                </table>
                                                <br>
                                            </li>
                                        </ul>
                                    </fieldset>
                                    <?php $elements = array_values(array_sort($packet['elements'], function ($value) {return $value['order'];}));?>
                                    <ul id="sortable" class="connectedSortable" data-action="{{URL::to("cp/contentfeedmanagement/sort-items/{$program["program_type"]}/{$program["program_slug"]}/{$packet["packet_slug"]}")}}" style="list-style-type: none;padding:0; margin:0; min-height: 20px;">
                                        @foreach ($elements as $key => $value)
                                                @if($value['type'] == "media") 
                                                    <?php 
                                                        $media_record = Dam::getDAMSMediaUsingID($value['id']);
                                                    ?>

                                                    @if(is_array($media_record) && !empty($media_record))
                                                    <li class="well well-sm" data-sort="{{$value['order']}}" data-id="{{$value['id']}}" data-type="{{$value['type']}}" data-name="{{$value['name']}}" data-display="{{array_get($value, 'display_name', '')}}" >
                                                        <table width="100%">
                                                            <script type="text/javascript">
                                                            <?php if(isset($value['media_type'])): ?>
                                                                media_types[<?php echo (int)$value['id']; ?>] = "<?php echo $value['media_type'] ?>";
                                                            <?php endif; ?>
                                                            </script>
                                                            <tr>
                                                                <td width="200">{{ wordwrap($data['mediaElements']['names'][$value['id']], 16, "\n", TRUE) }}</td>
                                                                <td width="200">{{ wordwrap($data['mediaElements']['display_name'][$value['id']], 16, "\n", TRUE) }}</td>
                                                                <td width="100">{{ $data['mediaElements']['created_at'][$value['id']] }}</td>
                                                                <td width="200">{{ $data['mediaElements']['created_by'][$value['id']] }}</td>
                                                                <td width="100">@if($data['mediaElements']['types'][$value['id']] == 'scorm') {{trans('admin/dams.scorm')}} @else {{ ucwords(strtolower($data['mediaElements']['types'][$value['id']])) }} @endif</td>
                                                                <td width="100"><a class="btn btn-circle show-tooltip openedittabb" title="<?php echo trans('admin/program.edit_item') ?>" data-toggle="modal" data-target="#edittab" data-action="{{ URL::to('cp/contentfeedmanagement/edit-items/'.$packet['packet_slug'].'/media/'.$value['id'].'/'.$program['program_type'])}}" data-name="{{ $data['mediaElements']['names'][$value['id']] }}" data-display="{{ $data['mediaElements']['display_name'][$value['id']] }}"><i class="fa fa-edit"></i></a>
                                                                    <a class="btn btn-circle show-tooltip removeitem" title="<?php echo trans('admin/program.remove_item') ?>"
                                                                       href="{{ URL::to("cp/contentfeedmanagement/remove-items/{$program["program_type"]}/{$program["program_slug"]}/{$packet["packet_slug"]}/media/{$value["id"]}")}}">
                                                                        <i class="fa fa-trash-o"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </li>
                                                    @endif  
                                                @elseif($value['type'] == "assessment")
                                                    <?php $assessment_record = Quiz::getQuizAssetsUsingAutoID($value['id']); ?>
                                                    @if(is_array($assessment_record) && !empty($assessment_record))
                                                    <li class="well well-sm" data-sort="{{$value['order']}}" data-id="{{$value['id']}}" data-type="{{$value['type']}}" data-name="{{$value['name']}}" data-display="{{array_get($value, 'display_name', '')}}">
                                                        <table width="100%">
                                                            <tr>
                                                                <td width="200">{{ wordwrap($data['assessmentElements']['names'][$value['id']], 16, "\n", TRUE) }}</td>
                                                                <td width="200">{{ wordwrap($data['assessmentElements']['display_name'][$value['id']], 16, "\n", TRUE) }}</td>
                                                                <td width="100">{{ $data['assessmentElements']['created_at'][$value['id']] }}</td>
                                                                <td width="200">{{ $data['assessmentElements']['created_by'][$value['id']] }}</td>
                                                                <td width="100">{{trans('admin/program.assessment_type')}}</td>
                                                                <td width="100"><a class="btn btn-circle show-tooltip openedittabb" title="<?php echo trans('admin/program.edit_item') ?>" data-toggle="modal" data-target="#edittab" data-action="{{ URL::to('cp/contentfeedmanagement/edit-items/'.$packet['packet_slug'].'/assessment/'.$value['id'].'/'.$program['program_type'])}}" data-name="{{ $data['assessmentElements']['names'][$value['id']] }}" data-display="{{ $data['assessmentElements']['display_name'][$value['id']] }}"><i class="fa fa-edit"></i></a>
                                                                    <a class="btn btn-circle show-tooltip removeitem" title="<?php echo trans('admin/program.remove_item') ?>" href="{{ URL::to("cp/contentfeedmanagement/remove-items/{$program["program_type"]}/{$program["program_slug"]}/{$packet["packet_slug"]}/assessment/{$value["id"]}")}}">
                                                                        <i class="fa fa-trash-o"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </li>
                                                    @endif
                                                @elseif($value['type'] == "event")
                                                    <?php $event_record = Event::getEventsAssetsUsingAutoID($value['id']); ?>
                                                    @if(is_array($event_record) && !empty($event_record))
                                                    <li class="well well-sm" data-sort="{{$value['order']}}" data-id="{{$value['id']}}" data-type="{{$value['type']}}" data-name="{{$value['name']}}" data-display="{{array_get($value, 'display_name', '')}}">
                                                        <table width="100%">
                                                            <tr>
                                                                <td width="200">{{ wordwrap($data['eventElements']['names'][$value['id']], 16, "\n", TRUE) }}</td>
                                                                <td width="200">{{ wordwrap($data['eventElements']['display_name'][$value['id']], 16, "\n", TRUE) }}</td>
                                                                <td width="100">{{ $data['eventElements']['created_at'][$value['id']] }}</td>
                                                                <td width="200">{{ $data['eventElements']['created_by'][$value['id']] }}</td>
                                                                <td width="100">{{trans('admin/program.event_type')}}</td>
                                                                <td width="100"><a class="btn btn-circle show-tooltip openedittabb" title="<?php echo trans('admin/program.edit_item') ?>" data-toggle="modal" data-target="#edittab" data-action="{{ URL::to('cp/contentfeedmanagement/edit-items/'.$packet['packet_slug'].'/event/'.$value['id'].'/'.$program['program_type'])}}" data-name="{{ $data['eventElements']['names'][$value['id']] }}" data-display="{{ $data['eventElements']['display_name'][$value['id']] }}"><i class="fa fa-edit"></i></a>
                                                                    <a class="btn btn-circle show-tooltip removeitem" title="<?php echo trans('admin/program.remove_item') ?>" href="{{ URL::to("cp/contentfeedmanagement/remove-items/{$program["program_type"]}/{$program["program_slug"]}/{$packet["packet_slug"]}/event/{$value["id"]}")}}">
                                                                        <i class="fa fa-trash-o"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </li>
                                                    @endif
                                                @elseif($value['type'] == "flashcard")
                                                    <?php $flashcard_record = FlashCard::getFlashcardsAssetsUsingAutoID($value['id']); ?>
                                                    @if(is_array($flashcard_record) && !empty($flashcard_record))
                                                    <li class="well well-sm" data-sort="{{$value['order']}}" data-id="{{$value['id']}}" data-type="{{$value['type']}}" data-name="{{$value['name']}}" data-display="{{array_get($value, 'display_name', '')}}">
                                                        <table width="100%">
                                                            <tr>
                                                                <td width="200">{{ wordwrap($data['flashcardElements']['names'][$value['id']], 16, "\n", TRUE) }}</td>
                                                                <td width="200">{{ wordwrap($data['flashcardElements']['display_name'][$value['id']], 16, "\n", TRUE) }}</td>
                                                                <td width="100">{{ $data['flashcardElements']['created_at'][$value['id']] }}</td>
                                                                <td width="200">{{ $data['flashcardElements']['created_by'][$value['id']] }}</td>
                                                                <td width="100">{{trans('admin/program.flash_type')}}</td>
                                                                <td width="100"><a class="btn btn-circle show-tooltip openedittabb" title="<?php echo trans('admin/program.edit_item') ?>" data-toggle="modal" data-target="#edittab" data-action="{{ URL::to('cp/contentfeedmanagement/edit-items/'.$packet['packet_slug'].'/flashcard/'.$value['id'].'/'.$program['program_type'])}}" data-name="{{ $data['flashcardElements']['names'][$value['id']] }}" data-display="{{ $data['flashcardElements']['display_name'][$value['id']] }}"><i class="fa fa-edit"></i></a>
                                                                    <a class="btn btn-circle show-tooltip removeitem" title="<?php echo trans('admin/program.remove_item') ?>" href="{{ URL::to("cp/contentfeedmanagement/remove-items/{$program["program_type"]}/{$program["program_slug"]}/{$packet["packet_slug"]}/flashcard/{$value["id"]}")}}">
                                                                        <i class="fa fa-trash-o"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </li>
                                                    @endif
                                                @elseif($value['type'] == "survey")
                                                    <?php $survey_record = Survey::getSurveyByIds($value['id'])->toArray(); ?>
                                                    @if(is_array($survey_record) && !empty($survey_record))
                                                    <li class="well well-sm" data-sort="{{$value['order']}}" data-id="{{$value['id']}}" data-type="{{$value['type']}}" data-name="{{$value['name']}}" data-display="{{array_get($value, 'display_name', '')}}">
                                                        <table width="100%">
                                                            <tr>
                                                                <td width="200">{{ wordwrap($data['surveyElements']['names'][$value['id']], 16, "\n", TRUE) }}</td>
                                                                <td width="200">{{ wordwrap($data['surveyElements']['display_name'][$value['id']], 16, "\n", TRUE) }}</td>
                                                                <td width="100">{{ $data['surveyElements']['created_at'][$value['id']] }}</td>
                                                                <td width="200">{{ $data['surveyElements']['created_by'][$value['id']] }}</td>
                                                                <td width="100">{{trans('admin/survey.survey_type')}}</td>
                                                                <td width="100">
                                                                <a href="#" style='cursor:not-allowed' class="btn btn-circle show-tooltip" title="<?php echo trans('admin/survey.cant_perform_this_opera') ?>"><i class="fa fa-edit"></i></a>
                                                                <a href="#" style='cursor:not-allowed' style='cursor:not-allowed' class="btn btn-circle show-tooltip" title="<?php echo trans('admin/survey.cant_perform_this_opera') ?>">
                                                                <i class="fa fa-trash-o"></i>
                                                                </a>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </li>
                                                    @endif
                                                @elseif($value['type'] == "assignment")
                                                    <?php $assignment_record = Assignment::getAssignmentByIds($value['id'])->toArray(); ?>
                                                    @if(is_array($assignment_record) && !empty($assignment_record))
                                                    <li class="well well-sm" data-sort="{{$value['order']}}" data-id="{{$value['id']}}" data-type="{{$value['type']}}" data-name="{{$value['name']}}" data-display="{{array_get($value, 'display_name', '')}}">
                                                        <table width="100%">
                                                            <tr>
                                                                <td width="200">{{ wordwrap($data['assignmentElements']['names'][$value['id']], 16, "\n", TRUE) }}</td>
                                                                <td width="200">{{ wordwrap($data['assignmentElements']['display_name'][$value['id']], 16, "\n", TRUE) }}</td>
                                                                <td width="100">{{ $data['assignmentElements']['created_at'][$value['id']] }}</td>
                                                                <td width="200">{{ $data['assignmentElements']['created_by'][$value['id']] }}</td>
                                                                <td width="100">{{trans('admin/assignment.assignment')}}</td>
                                                                <td width="100">
                                                                <a href="#" style='cursor:not-allowed' class="btn btn-circle show-tooltip" title="<?php echo trans('admin/assignment.cant_perform_this_opera') ?>"><i class="fa fa-edit"></i></a>
                                                                <a href="#" style='cursor:not-allowed' style='cursor:not-allowed' class="btn btn-circle show-tooltip" title="<?php echo trans('admin/assignment.cant_perform_this_opera') ?>">
                                                                <i class="fa fa-trash-o"></i>
                                                                </a>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </li>
                                                    @endif
                                                 @endif
                                        @endforeach 
                                    </ul>
                                @endif
                            </div>
                            <div id="items_add" style="display:none;">
                                <?php $file1 = '';
                                $file2 = '';
                                $file3 = '';
                                $file4 = ''; ?>
                                <table width="100%" class="table">
                                    <tr>
                                        <td width="130">
                                            <a style="text-align: left;" class="btn btn-primary btn-sm addelements btn-block" data-value="media"
                                               data-url="{{URL::to("/cp/dams?view=iframe&id=id&from=post&program_type={$program["program_type"]}&program_slug={$program["program_slug"]}&post_slug={$packet["packet_slug"]}")}}"
                                               data-json="{{isset($data['mediaElements']['ids']) ?
                                                json_encode($data['mediaElements']['ids']) : '[]'}}"
                                               data-media-names="{{isset($data['mediaElements']['names']) ?
                                                json_encode($data['mediaElements']['names']) : '[]'}}"
                                               data-text="Add Media">
                                                <span class="btn btn-circle blue show-tooltip custom-btm">
                                                    <i class="fa fa-plus"></i>
                                                </span>&nbsp;<?php echo trans('admin/program.media'); ?>
                                            </a>
                                        </td>
                                        <td width="120">
                                            <span id="media_count">
                                                @if(isset($data['mediaElements']['ids']))
                                                    {{count($data['mediaElements']['ids'])}}
                                                @else
                                                    0
                                                @endif selected
                                            </span>
                                        </td>
                                        <td>
                                            <span id="media_files">
                                                @if(isset($data['mediaElements']['names']))
                                                    @foreach($data['mediaElements']['names'] as $name)
                                                        <?php $file1 = $file1.''.$name.', '; ?>
                                                    @endforeach {{rtrim($file1, ', ')}}
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="130">
                                            <a style="text-align: left;" class="btn btn-primary btn-sm addelements btn-block" data-value="assessment"
                                               data-url="{{URL::to("/cp/assessment/list-quiz?view=iframe&from=post&program_type={$program["program_type"]}&program_slug={$program["program_slug"]}&post_slug={$packet["packet_slug"]}")}}"
                                               data-json="{{isset($data['assessmentElements']['ids']) ?
                                                json_encode($data['assessmentElements']['ids']) : '[]'}}"
                                               data-media-names="{{isset($data['assessmentElements']['names']) ?
                                                json_encode($data['assessmentElements']['names']) : '[]'}}"
                                               data-text="Add Assessment" data-from="channel">
                                            <span class="btn btn-circle blue show-tooltip custom-btm">
                                                <i class="fa fa-plus"></i>
                                            </span>&nbsp;<?php echo trans('admin/program.assessment'); ?>
                                            </a>
                                        </td>
                                        <td width="120">
                                            <span id="assessment_count">
                                                @if(isset($data['assessmentElements']['ids']))
                                                    {{count($data['assessmentElements']['ids'])}}
                                                @else
                                                    0
                                                @endif selected
                                            </span>
                                        </td>
                                        <td>
                                            <span id="assessment_files">
                                                @if(isset($data['assessmentElements']['names']))
                                                    @foreach($data['assessmentElements']['names'] as $name)
                                                        <?php $file2 = $file2.''.$name.', '; ?>
                                                    @endforeach {{rtrim($file2, ', ')}}
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="130">
                                            <a style="text-align: left;" class="btn btn-primary btn-sm addelements btn-block" data-value="event"
                                               data-url="{{URL::to("/cp/event?view=iframe&from=post&program_type={$program["program_type"]}&program_slug={$program["program_slug"]}&post_slug={$packet["packet_slug"]}")}}"
                                               data-json="{{isset($data['eventElements']['ids']) ?
                                                json_encode($data['eventElements']['ids']) : '[]'}}"
                                               data-media-names="{{isset($data['eventElements']['names']) ?
                                                json_encode($data['eventElements']['names']) : '[]'}}"
                                               data-text="Add Event">
                                                <span class="btn btn-circle blue show-tooltip custom-btm">
                                                    <i class="fa fa-plus"></i>
                                                </span>&nbsp;<?php echo trans('admin/program.event_type'); ?>
                                            </a>
                                        </td>
                                        <td width="120">
                                            <span id="event_count">
                                                @if(isset($data['eventElements']['ids']))
                                                    {{count($data['eventElements']['ids'])}}
                                                @else
                                                    0
                                                @endif selected
                                            </span>
                                        </td>
                                        <td>
                                            <span id="event_files">
                                                @if(isset($data['eventElements']['names']))
                                                    @foreach($data['eventElements']['names'] as $name)
                                                        <?php $file3 = $file3.''.$name.', '; ?>
                                                    @endforeach {{rtrim($file3, ', ')}}
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="130">
                                            <a style="text-align: left;" class="btn btn-primary btn-sm addelements btn-block" data-value="flashcard"
                                               data-url="{{URL::to("/cp/flashcards/list-iframe?view=iframe&from=post&program_type={$program["program_type"]}&program_slug={$program["program_slug"]}&post_slug={$packet["packet_slug"]}")}}"
                                               data-json="{{isset($data['flashcardElements']['ids']) ?
                                                json_encode($data['flashcardElements']['ids']) : '[]'}}"
                                               data-media-names="{{isset($data['flashcardElements']['names']) ?
                                                json_encode($data['flashcardElements']['names']) : '[]'}}"
                                               data-text="Add Flashcard">
                                                <span class="btn btn-circle blue show-tooltip custom-btm">
                                                    <i class="fa fa-plus"></i>
                                                </span>&nbsp;<?php echo trans('admin/program.flash_type'); ?>
                                            </a>
                                        </td>
                                        <td width="120">
                                            <span id="flashcard_count">
                                                @if(isset($data['flashcardElements']['ids']))
                                                    {{count($data['flashcardElements']['ids'])}}
                                                @else 0 @endif selected
                                            </span>
                                        </td>
                                        <td>
                                            <span id="flashcard_files">
                                                @if(isset($data['flashcardElements']['names']))
                                                    @foreach($data['flashcardElements']['names'] as $name)
                                                        <?php $file4 = $file4.''.$name.', '; ?>
                                                    @endforeach {{rtrim($file4, ', ')}}
                                                @endif
                                            </span>
                                            <div class="hide" id="flashcard_check">
                                                @if(isset($data['flashcardElements']['ids']))
                                                    @foreach($data['flashcardElements']['ids'] as $id)
                                                        <span class="flashcarddata" data-id="{{ $id }}"></span>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                                <form action="{{ URL::to("cp/contentfeedmanagement/add-elements-library/{$program["program_type"]}/{$program["program_slug"]}/{$packet["packet_slug"]}")}}"
                                      class="form-horizontal form-bordered form-row-stripped" method="post">
                                    <input type="hidden" value="@if(isset($data['mediaElements']['ids'])){{implode(",", $data['mediaElements']['ids'])}}@endif" id="mediaupload" name="media">
                                    <input type="hidden" value="@if(isset($data['assessmentElements']['ids'])){{implode(",", $data['assessmentElements']['ids'])}}@endif" id="assessmentupload" name="assessment">
                                    <input type="hidden" value="@if(isset($data['eventElements']['ids'])){{implode(",", $data['eventElements']['ids'])}}@endif" id="eventupload" name="event">
                                    <input type="hidden" value="@if(isset($data['flashcardElements']['ids'])){{implode(",", $data['flashcardElements']['ids'])}}@endif" id="flashcardupload" name="flashcard">

                                    <div style="margin-left: 30% ;">
                                        <button class="btn btn-success" type="submit" id="updateelements">{{trans('admin/program.save')}}</button>&nbsp;&nbsp;
                                        <button type="button" class="btn btn-cancel" data-btn-belongs-to="items_add">
                                            {{trans('admin/program.cancel')}}
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div id="items_upload" style="display:none;">
                                @include(
                                    'admin.theme.programs.addmedia',
                                    [
                                        'program_type' => $program['program_type'],
                                        'program_slug' => $program['program_slug'],
                                        'packet_slug' => $packet['packet_slug'],
                                    ]
                                )
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- delete window -->
<div id="deletemodal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <!--header-->
            <div class="modal-header">
                <div class="row custom-box">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-title">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h3><i class="icon-file"></i>{{trans('admin/program.remove_item')}}</h3>                                                 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--content-->
            <div class="modal-body" style="padding: 20px">               
                {{trans('admin/program.modal_delete_item')}}
            </div>
            <!--footer-->
            <div class="modal-footer">
              <a class="btn btn-danger">{{trans('admin/program.yes')}}</a>
              <a class="btn btn-success" data-dismiss="modal">{{trans('admin/program.close')}}</a>
            </div>
        </div>
    </div>
</div>
<!-- delete window ends -->

    <div class="modal fade" id="media-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="row custom-box">
                        <div class="col-md-12">
                            <div class="box">
                                <div class="box-title">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    <h3 class="modal-header-title" >
                                        <i class="icon-file"></i>
                                            {{trans('admin/program.view_media_details')}}
                                    </h3>                                                
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    ...
                </div>
                <div class="modal-footer">
                    <div style="float: left;" id="selectedcount"> 0 selected</div>
                    <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{trans('admin/program.assign')}}</a>
                    <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{trans('admin/program.close')}}</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit tab -->
<div id="edittab" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row custom-box">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-title">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h3 class="modal-header-title">{{trans('admin/program.edit_item')}}</h3>                                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <div id="edittabBody">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box">
                                <div class="box-title">
                                    <div class="box-content">
                                        <form action="" class="form-horizontal form-bordered" id="edit-item" name="edit-item" method="post">
                                            <div class="form-group">
                                                <label for="textfield1" class="col-sm-3 col-lg-2 control-label">{{trans('admin/program.item_name')}} <span class="red">*</span></label>
                                                <div class="col-sm-6 col-lg-4 controls">
                                                    <input type="text" name="item_name" id="item_name" class="form-control" value="">
                                                    <input type="hidden" name="slug" id="slug" value="">
                                                    <input type="hidden" name="type" id="type" value="">
                                                <span class="help-inline" style="color:#f00" id="e_item_name" name="e_item_name"></span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="textfield1" class="col-sm-3 col-lg-2 control-label">{{trans('admin/program.display_name')}} </label>
                                                <div class="col-sm-6 col-lg-4 controls">
                                                    <input type="text" name="display_name" id="display_name" class="form-control" value="">
                                                <span class="help-inline" style="color:#f00" id="e_display_name" name="e_display_name"></span>
                                                </div>
                                            </div>
                                            <div class="form-group last">
                                                <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                                                   <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> {{trans('admin/program.save')}}</button>                                   
                                                   <button type="button" class="btn btn-danger" data-dismiss="modal" aria-hidden="true"> {{trans('admin/program.cancel')}}</button>
                                                </div>
                                            </div>
                                          <!-- END Left Side -->
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

    <script>
        $(document).ready(function(){
            var active_tab = window.location.hash;
            if (active_tab !== undefined) {
                $(active_tab).tab("show");
            }
            @if(array_get($packet, 'sequential_access', 'no') == 'no')
            $("#sortable").sortable({
                connectWith: ".connectedSortable",
                update: function(event, ui) {
                    var element= $(this);
                    var arr = [];
                    $('#sortable').find('li').each(function(index, data){
                        var current = $(this);
                        arr.push({
                            type: current.attr('data-type'), 
                            id: current.attr('data-id'),
                            name: current.attr('data-name'),
                            display_name : current.attr('data-display')
                        });
                    }); 
                    var action = $('#sortable').attr('data-action');
                    console.log(action);
                    console.log(arr); 
                    $.ajax({
                        type: "POST",
                        url: action,
                        data: {
                            data : arr,
                        }
                    }).fail(function(response) {
                        alert( "Error while sorting. Please try again" );
                    });  
                }
            }).disableSelection();
            @endif
            var $triggermodal = $('#media-modal');
            $('.addelements').click(function(e){
                e.preventDefault();
                var $this = $(this);
                var $value = $this.data('value');
                if($value == "media"){
                    simpleloader.fadeIn();
                    var $iframeobj = $('<iframe src="'+$this.data('url')+'" width="100%" height="" frameBorder="0"></iframe>');
                    $iframeobj.unbind('load').load(function(){
                        $('#selectedcount').text('0 selected');
                        if(typeof $iframeobj.get(0).contentWindow.checkedBoxes == "undefined")
                            $iframeobj.get(0).contentWindow.checkedBoxes = {};

                        if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
                            $triggermodal.modal('show');
                        simpleloader.fadeOut();
                        // console.log($this.data('mediaNames'));
                        /* Code to Set Default checkedboxes starts here*/
                        $.each($this.data('json'),function(index,value){
                            // console.log(typeof $this.data('mediaNames')[value]);
                            if(typeof $this.data('mediaNames')[value] != "undefined"){
                                $iframeobj.get(0).contentWindow.checkedBoxes[value] = $this.data('mediaNames')[value];
                                $iframeobj.get(0).contentWindow.media_types[value] = media_types[value];
                            }
                            else
                                $iframeobj.get(0).contentWindow.checkedBoxes[value] = "";
                        })
                        /* Code to Set Default checkedboxes ends here*/

                        /* Code to refresh selected count starts here*/
                        $iframeobj.contents().click(function(){
                            setTimeout(function(){
                                var count = 0;
                                $.each($iframeobj.get(0).contentWindow.checkedBoxes,function(){
                                    count++;
                                });
                                $('#selectedcount').text(count+ ' selected');
                            },10);
                        });
                        $iframeobj.contents().trigger('click'); // Trigger the assigned event once on load
                        /* Code to refresh selected count ends here*/
                    })
                    $triggermodal.find('.modal-body').html($iframeobj);
                    $triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.data('text'));
                    $('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
                        var $checkedboxes = $iframeobj.get(0).contentWindow.checkedBoxes;
                        var $media_types = $iframeobj.get(0).contentWindow.media_types;
                        if(!$.isEmptyObject($checkedboxes)){
                            var $placeholder = $('#media_count');
                            var jsonarr = [];
                            var jsonNameArr = {};
                            //$placeholder.find('[id^="data_media"]').remove();
                            $.each($checkedboxes,function(index,value){
                                index = parseInt(index,10);
                                jsonarr.push(index);
                                var temptext = $checkedboxes[index];
                                jsonNameArr[index] = temptext;
                                var labeltext = $media_types[index];
                                temptext = (temptext.length > 18) ? temptext.substr(0,30) + '...' : temptext;
                                
                                //$placeholder.append($('<div style="margin-bottom:40px;" class="col-xs-2 col-md-2 mediadata" id="data_media.'+index+'">').append('<div class="thumbnail" style="height: 150px;"><img class="" style="max-width: 100%; display: block; max-height: 100%;" src="{{URL::to('/cp/dams/show-media/')}}/' + index + '?preview=true&'+ new Date().getTime() +'&id=id&width=185&height=150" > </div><div class="caption"> <h5 style="text-align: center;margin-bottom:10px;">' + temptext + '</h5></div><a class="btn btn-circle btn-danger thumbnailclose"><i class="fa fa-times"></i></a><a class="label label-success elementsoverlay" onclick="return false">'+labeltext+'</a></div>'));
                            });

                            var count_m = jsonarr.length;
                            var names = '';
                            $.each(jsonNameArr,function(index,value){
                                names = names+value+', ';
                            });
                            names = names.replace(/( $)/g, "");
                            names = names.replace(/(,$)/g, "");
                            if(count_m > 0)
                            {
                                $('#media_count').html(count_m+' selected');
                                $('#media_files').html(names);
                            }
                            
                            $('#mediaupload').val(jsonarr);
                            $this.data('json',jsonarr)
                            $this.data('mediaNames',jsonNameArr)
                            //if($placeholder.hasClass('ui-sortable'))
                                //$placeholder.sortable('destroy');
                            //$placeholder.sortable();
                            //$placeholder.disableSelection();
                            $triggermodal.modal('hide');
                            media_types = $media_types;
                        }
                        else{
                            $('#media_count').text('0 selected');
                            $('#media_files').text('');
                            $('#mediaupload').val('');
                            $this.data('json',[])
                            $this.data('mediaNames',[])
                            $triggermodal.modal('hide');
                        }
                    })
                    /* Code for user media rel ends here */
                }
                else if($value == 'assessment'){
                    simpleloader.fadeIn();
                    var $iframeobj = $('<iframe id="assessmentiframe" src="'+$this.data('url')+'" width="100%" height="" frameBorder="0"></iframe>');
                    $iframeobj.unbind('load').load(function(){
                        $('#selectedcount').text('0 selected');

                        if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
                            $triggermodal.modal('show');
                        simpleloader.fadeOut();

                        /* Code to Set Default checkedboxes starts here*/
                        $.each($this.data('json'),function(index,value){
                            if(typeof $this.data('mediaNames')[value] != "undefined"){
                                $iframeobj.get(0).contentWindow.checkedBoxes[value] = $this.data('mediaNames')[value];
                                $iframeobj.get(0).contentWindow.media_types[value] = media_types[value];
                            }
                            else
                                $iframeobj.get(0).contentWindow.checkedBoxes[value] = "";
                        })
                        /* Code to Set Default checkedboxes ends here*/

                        /* Code to refresh selected count starts here*/
                        $iframeobj.contents().click(function(){
                            setTimeout(function(){
                                var count = 0;
                                $.each($iframeobj.get(0).contentWindow.checkedBoxes,function(){
                                    count++;
                                });
                                $('#selectedcount').text(count+ ' selected');
                            },10);
                        });
                        $iframeobj.contents().trigger('click'); // Trigger the assigned event once on load
                        /* Code to refresh selected count ends here*/
                    })
                    $triggermodal.find('.modal-body').html($iframeobj);
                    $triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.data('text'));
                    $('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
                        var $checkedboxes = $iframeobj.get(0).contentWindow.checkedBoxes;
                        if(!$.isEmptyObject($checkedboxes)){
                            //var $placeholder = $('#elementsplaceholder');
                            var jsonarr = [];
                            var jsonNameArr = {};
                            //$placeholder.find('[id^="data_assessment"]').remove();
                            $.each($checkedboxes,function(index,value){
                                index = parseInt(index,10);
                                jsonarr.push(index);
                                var temptext = $checkedboxes[index];
                                jsonNameArr[index] = temptext;
                                temptext = (temptext.length > 18) ? temptext.substr(0,15) + '...' : temptext;
                                //$placeholder.append($('<div style="margin-bottom:40px;" class="col-xs-2 col-md-2 assessmentdata" id="data_assessment.'+index+'">').append('<div class="thumbnail" style="height: 150px;"><img class="" style="max-width: 100%; display: block; max-height: 100%;" src="{{URL::to('/admin/img/icons/quiz.png')}}" > </div><div class="caption"> <h5 style="text-align: center;margin-bottom:10px;">' + temptext + '</h5></div><a class="btn btn-circle btn-danger thumbnailclose"><i class="fa fa-times"></i></a><a class="label label-success elementsoverlay" onclick="return false">Assessment</a></div>'));
                            });
                            var count_a = jsonarr.length;
                            var names = '';

                            $.each(jsonNameArr,function(index,value){
                                names = names+value+', ';
                            });

                            names = names.replace(/( $)/g, "");
                            names = names.replace(/(,$)/g, "");

                            if(count_a > 0)
                            {
                                $('#assessment_count').html(count_a+' selected');
                                $('#assessment_files').html(names);
                            }

                            $('#assessmentupload').val(jsonarr);

                            $this.data('json',jsonarr)
                            $this.data('mediaNames',jsonNameArr)
                            /*if($placeholder.hasClass('ui-sortable'))
                                $placeholder.sortable('destroy');
                            $placeholder.sortable();
                            $placeholder.disableSelection();*/
                            $triggermodal.modal('hide');
                        }
                        else{
                            $('#assessment_count').text('0 selected');
                            $('#assessment_files').text('');
                            $('#assessmentupload').val('');
                            $this.data('json',[])
                            $this.data('mediaNames',[])
                            $triggermodal.modal('hide');
                        }
                    })
                    /* Code for user assessment rel ends here */
                }
                else if($value == 'event'){
                    simpleloader.fadeIn();
                    var $iframeobj = $('<iframe id="eventiframe" src="'+$this.data('url')+'" width="100%" height="" frameBorder="0"></iframe>');
                    $iframeobj.unbind('load').load(function(){
                        $('#selectedcount').text('0 selected');

                        if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
                            $triggermodal.modal('show');
                        simpleloader.fadeOut();

                        /* Code to Set Default checkedboxes starts here*/
                        $.each($this.data('json'),function(index,value){
                            if(typeof $this.data('mediaNames')[value] != "undefined"){
                                $iframeobj.get(0).contentWindow.checkedBoxes[value] = $this.data('mediaNames')[value];
                                //$iframeobj.get(0).contentWindow.media_types[value] = media_types[value];
                            }
                            else
                                $iframeobj.get(0).contentWindow.checkedBoxes[value] = "";
                        })
                        /* Code to Set Default checkedboxes ends here*/

                        /* Code to refresh selected count starts here*/
                        $iframeobj.contents().click(function(){
                            setTimeout(function(){
                                var count = 0;
                                $.each($iframeobj.get(0).contentWindow.checkedBoxes,function(){
                                    count++;
                                });
                                $('#selectedcount').text(count+ ' selected');
                            },10);
                        });
                        $iframeobj.contents().trigger('click'); // Trigger the assigned event once on load
                        /* Code to refresh selected count ends here*/
                    })
                    $triggermodal.find('.modal-body').html($iframeobj);
                    $triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.data('text'));
                    $('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
                        var $checkedboxes = $iframeobj.get(0).contentWindow.checkedBoxes;
                        if(!$.isEmptyObject($checkedboxes)){
                            //var $placeholder = $('#elementsplaceholder');
                            var jsonarr = [];
                            var jsonNameArr = {};
                            //$placeholder.find('[id^="data_event"]').remove();
                            $.each($checkedboxes,function(index,value){
                                index = parseInt(index,10);
                                jsonarr.push(index);
                                var temptext = $checkedboxes[index];
                                jsonNameArr[index] = temptext;
                                temptext = (temptext.length > 18) ? temptext.substr(0,15) + '...' : temptext;
                                //$placeholder.append($('<div style="margin-bottom:40px;" class="col-xs-2 col-md-2 eventdata" id="data_event.'+index+'">').append('<div class="thumbnail" style="height: 150px;"><img class="" style="max-width: 100%; display: block; max-height: 100%;" src="{{URL::to('/admin/img/icons/intro_events.png')}}" > </div><div class="caption"> <h5 style="text-align: center;margin-bottom:10px;">' + temptext + '</h5></div><a class="btn btn-circle btn-danger thumbnailclose"><i class="fa fa-times"></i></a><a class="label label-success elementsoverlay" onclick="return false">Event</a></div>'));
                            });

                            var count_e = jsonarr.length;
                            var names = '';
                            
                            $.each(jsonNameArr,function(index,value){
                                names = names+value+', ';
                            });

                            names = names.replace(/( $)/g, "");
                            names = names.replace(/(,$)/g, "");

                            if(count_e > 0)
                            {
                                $('#event_count').html(count_e+' selected');
                                $('#event_files').html(names);
                            }

                            $('#eventupload').val(jsonarr);

                            $this.data('json',jsonarr)
                            $this.data('mediaNames',jsonNameArr)
                            /*if($placeholder.hasClass('ui-sortable'))
                                $placeholder.sortable('destroy');
                            $placeholder.sortable();
                            $placeholder.disableSelection();*/
                            $triggermodal.modal('hide');
                        }
                        else{
                            $('#event_count').text('0 selected');
                            $('#event_files').text('');
                            $('#eventupload').val('');
                            $this.data('json',[])
                            $this.data('mediaNames',[])
                            $triggermodal.modal('hide');
                        }
                    })
                    /* Code for user event rel ends here */
                }
                else if($value == 'flashcard'){

                    simpleloader.fadeIn();
                    var $iframeobj = $('<iframe src="'+$this.data('url')+'" width="100%" height="" frameBorder="0"></iframe>');
                    $iframeobj.unbind('load').load(function(){

                        $('#selectedcount').text('0 selected');
                        if(typeof $iframeobj.get(0).contentWindow.checkedBoxes == "undefined")
                            $iframeobj.get(0).contentWindow.checkedBoxes = {};

                        if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
                            $triggermodal.modal('show');
                        simpleloader.fadeOut();
                         // console.log($this.data('mediaNames'));
                        /* Code to Set Default checkedboxes starts here*/
                        $.each($this.data('json'),function(index,value){
                            // console.log(typeof $this.data('mediaNames')[value]);
                            if(typeof $this.data('mediaNames')[value] != "undefined"){
                                $iframeobj.get(0).contentWindow.checkedBoxes[value] = $this.data('mediaNames')[value];
                                $iframeobj.get(0).contentWindow.media_types[value] = media_types[value];
                            }
                            else
                                $iframeobj.get(0).contentWindow.checkedBoxes[value] = "";
                        })
                        /* Code to Set Default checkedboxes ends here*/

                        /* Code to refresh selected count starts here*/
                        $('#selectedcount').text(Object.keys($iframeobj.get(0).contentWindow.checkedBoxes).length+ ' selected');
                        $iframeobj.contents().click(function(){

                            setTimeout(function(){
                                var count = 0;
                                $.each($iframeobj.get(0).contentWindow.checkedBoxes,function(){
                                    count++;
                                });
                                $('#selectedcount').text(count+ ' selected');
                            },10);
                        });
                        $iframeobj.contents().trigger('click'); // Trigger the assigned event once on load
                        /* Code to refresh selected count ends here*/
                    })
                    $triggermodal.find('.modal-body').html($iframeobj);
                    $triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.data('text'));
                    $('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
                        var $checkedboxes = $iframeobj.get(0).contentWindow.checkedBoxes;
                        var $media_types = $iframeobj.get(0).contentWindow.media_types;
                        if(!$.isEmptyObject($checkedboxes)){
                            //var $placeholder = $('#elementsplaceholder');
                            var jsonarr = [];
                            var jsonNameArr = {};
                            $('#flashcard_check').empty();
                            //$placeholder.find('[id^="data_flashcard"]').remove();
                            $.each($checkedboxes,function(index,value){
                                //console.log(index);
                                index = parseInt(index,10);
                                jsonarr.push(index);
                                var temptext = value.title;
                                jsonNameArr[index] = temptext;
                                var labeltext = 'flashcard';
                                /*temptext = (temptext.length > 18) ? temptext.substr(0,30) + '...' : temptext;*/
                                $('#flashcard_check').append($('<span class="flashcarddata" data-id="'+index+'"></span>'));
                                //$placeholder.append($('<div style="margin-bottom:40px;" class="col-xs-2 col-md-2 flashcarddata" data-id="'+index+'" id="data_flashcard.'+index+'">').append('<div class="thumbnail" style="height: 150px;"><img class="" style="max-width: 100%; display: block; max-height: 100%;" src="{{URL::to('/admin/img/icons/intro_events.png')}}" > </div><div class="caption"> <h5 style="text-align: center;margin-bottom:10px;">' + temptext + '</h5></div><a class="btn btn-circle btn-danger thumbnailclose"><i class="fa fa-times"></i></a><a class="label label-success elementsoverlay" onclick="return false">'+labeltext+'</a></div>'));
                            });

                            var count_f = jsonarr.length;
                            var names = '';
                            
                            $.each(jsonNameArr,function(index,value){
                                names = names+value+', ';
                            });
                            
                            names = names.replace(/( $)/g, "");
                            names = names.replace(/(,$)/g, "");

                            if(count_f > 0)
                            {
                                $('#flashcard_count').html(count_f+' selected');
                                $('#flashcard_files').html(names);
                            }

                            $('#flashcardupload').val(jsonarr);

                            $this.data('json',jsonarr)
                            $this.data('mediaNames',jsonNameArr)
                            /*if($placeholder.hasClass('ui-sortable'))
                                $placeholder.sortable('destroy');
                            $placeholder.sortable();
                            $placeholder.disableSelection();*/
                            $triggermodal.modal('hide');
                            media_types = $media_types;
                        }
                        else{
                            $('#flashcard_count').text('0 selected');
                            $('#flashcard_files').text('');
                            $('#flashcardupload').val([]);
                            $this.data('json',[])
                            $this.data('mediaNames',[])
                            $('#flashcard_check').empty();
                            $triggermodal.modal('hide');
                        }
                    })
                    /* Code for user media rel ends here */
                
                }
                else{
                    //alert('Please select an {{ trans("admin/flashcards.channel") }} type');
                    alert('Please select item type');
                }
            });

            $('#alert-success').delay(5000).fadeOut();
        })

        $(document).on('click','.removeitem',function(e){
            e.preventDefault();
            var $this = $(this);
            var $deletemodal = $('#deletemodal');
            $deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href'))
            $deletemodal.modal('show');
        });

        var elementEditUrl = "{{ URL::to("/cp/contentfeedmanagement/edit-names/{$program["program_type"]}/{$program["program_slug"]}/{packet_slug}/{element_type}/{element_id}") }}";

        $(document).on("click", ".openedittabb", function (e) {
            e.preventDefault();
            $('#e_item_name').html('');
            $('#e_display_name').html('');
            var $this = $(this);
            var action = $this.data('action');
            var item_name = $this.data('name');
            var display_name = $this.data('display');
            $.ajax({
                method: "POST",
                url: action,
                data:{
                    item_name:item_name,
                    display_name:display_name,
                }
            }).done(function(data) {
                $element = '#edittab #edit-item';
                $($element+' #item_name').val(data.item_name);
                $($element+' #slug').val(data.packet_slug);
                $($element+' #type').val(data.type);
                $($element+' #display_name').val(data.display_name);
                $($element).attr('action', elementEditUrl.replace(/{packet_slug}/g, data.packet_slug)
                                .replace(/{element_type}/g, data.element_type)
                                .replace(/{element_id}/g, data.element_id));
            }).fail(function(response) {
                alert( "Error while editing. Please try again" );
            });
        });

        $('#edit-item').on('submit', function(e){
            e.preventDefault();
            var $this = $(this);
            var action = $( '#edit-item' ).attr('action');

            var slug = $('#slug').val();
            var type = $('#type').val();
            $.ajax({
                method: "POST",
                url: action,
                data: { 
                    item_name : $('#item_name').val(),
                    display_name : $('#display_name').val()
                }
            }).done(function(response) {
                if(response.status == "error")
                {
                    $('#e_item_name').html(response.item_name);
                    $('#e_display_name').html(response.display_name);
                }
                else
                {
                    window.location.reload(true);
                }
            });
        });

        $("#upload_button").click(function () {
            $("#items_list, #btn-group-item-options").slideUp({
                duration : 400,
                complete : function () {
                    $("#items_upload, #btn-back").slideDown({
                        duration : 600
                    });
                }
            });
        });

        $("#add_button").click(function () {
            $("#items_list, #btn-group-item-options").slideUp({
                duration : 400,
                complete : function () {
                    $("#items_add, #btn-back").slideDown({
                        duration : 600
                    });
                }
            });
        });

        $(".btn-cancel, #btn-back").click(function () {
            $("#items_add, #items_upload, #btn-back").slideUp({
                duration : 400,
                complete : function () {
                    $("#items_list, #btn-group-item-options").slideDown({
                        duration : 600
                    });
                }
            });
        });

    </script>

@stop
