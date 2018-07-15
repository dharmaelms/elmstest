@section('content')
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
    <script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
	<div class="row">
	    <div class="col-md-12">
	        <div class="box">
	            <div class="box-title">
	                <h3 style="color:black"><i class="fa fa-bars"></i>{{ trans('admin/assessment.attempt_report') }}</h3>
	                <div class="box-tool" style="top:4px">
                        <a class="btn btn-info" style="height: 29px;" href="
							{{ url('cp/assessment/list-quiz') }}
                        ">{{ trans('admin/assessment.back_to_quiz') }}</a>
                    </div>
	            </div>
	            <div class="box-content">
	            <!-- hiddden field is created for the export option -->
					<!--<?php $notAttemptedList = "notAttemptedList"; ?> -->
					<!-- <input type="hidden" id="export_link_user" name="export_link_user" value="{{URL::to('/cp/assessment/report-quiz/'.$quiz->quiz_id.'/'.$notAttemptedList)}}"> -->
            		<input type="hidden" id="export_link_user" name="export_link_user" value="{{URL::to('/cp/assessment/report-quiz/'.$quiz->quiz_id)}}"> 
            	<!-- hiddden ends here -->
	            	<form class="form-horizontal" id="filter_form" action="{{ url('cp/assessment/report-quiz/'.$quiz->quiz_id) }}" method="GET">
		            	<div class="btn-toolbar clearfix">
		            		<div class="col-md-12">
	                            <div class="form-group">
                                    <label class="col-sm-2 col-lg-2 control-label">
                                        <b>{{ trans('admin/assessment.attempts_from') }} :</b>
                                    </label>
                                    <div class="col-sm-2 col-lg-2 controls">
                                    <?php $type = strtolower(Input::get('type', 'all')); ?>
                                    <select class="form-control input-sm chosen" name="type">
                                            <option value="all" <?php if ($type == 'all') echo 'selected';?>>
                                            {{ trans('admin/assessment.all') }}</option>
                                        @if(!array_get($filter, 'user', collect())->isEmpty())
                                            <option value="user" <?php if ($type == 'user') echo 'selected';?>>
                                            {{ trans('admin/assessment.user') }}</option>
                                        @endif
                                        @if(!array_get($filter, 'ug', collect())->isEmpty())
                                            <option value="ug" <?php if ($type == 'ug') echo 'selected';?>>
                                            {{ trans('admin/assessment.user_group') }}</option>
                                        @endif
                                        @if(!array_get($filter, 'cf', collect())->isEmpty())
                                            <option value="cf" <?php if ($type == 'cf') echo 'selected';?>>
                                            {{ trans('admin/program.programs') }}</option>
                                        @endif
                                    </select>
                                    </div>
		                            <?php $value = strtolower(Input::get('value', 0)); ?>
		                            <div id="user-select" class="col-sm-4 col-lg-4 controls" @if($type !="user") style="display:none;" @endif>
		                                <select id="user-select-value" class="form-control input-sm chosen" name="value" @if($type !="user") disabled @endif data-placeholder="Choose a user">
		                                	<option></option>
		                                	@foreach($filter['user'] as $u)
		                                    <option value="{{$u->uid}}" <?php if ($value == $u->uid) echo 'selected';?>>{{$u->firstname.' '.$u->lastname}}</option>
		                                    @endforeach
		                                </select>
		                            </div>
		                            <div id="ug-select" class="col-sm-4 col-lg-4 controls"  @if($type !="ug") style="display:none;" @endif>
		                                <select id="ug-select-value" class="form-control input-sm chosen" name="value" @if($type !="ug") disabled @endif data-placeholder="Choose a user group">
		                                	<option></option>
		                                	@foreach($filter['ug'] as $ug)
		                                    <option value="{{$ug->ugid}}" <?php if ($value == $ug->ugid) echo 'selected';?>>{{$ug->usergroup_name}}</option>
		                                    @endforeach
		                                </select>
		                            </div>
		                            <div id="cf-select" class="col-sm-4 col-lg-4 controls"  @if($type !="cf") style="display:none;" @endif>
		                                <select id="cf-select-value" class="form-control input-sm chosen" name="value" @if($type !="cf") disabled @endif data-placeholder="Choose a <?php echo trans('admin/program.program');?>">
		                                	<option></option>
		                                	@foreach($filter['cf'] as $cf)
		                                    <option value="{{$cf->program_id}}" <?php if ($value == $cf->program_id) echo 'selected';?>>{{$cf->program_title}}</option>
		                                    @endforeach
		                                </select>
		                            </div>
		                            <div id="package-select" class="col-sm-4 col-lg-4 controls"  @if($type !="package") style="display:none;" @endif>
		                                <select id="package-select-value" class="form-control input-sm chosen" name="value" @if($type !="package") disabled @endif data-placeholder="{{trans('admin/program.choose_package')}}">
		                                	<option></option>
		                                	@foreach($filter['pr'] as $package)
		                                    <option value="{{$package->program_id}}" <?php if ($value == $package->program_id) echo 'selected';?>>{{$package->program_title}}</option>
		                                    @endforeach
		                                </select>
		                            </div>
		                            <!-- export option for unattemted quiz -->
								<?php if (has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::EXPORT_QUIZ)) 
            					{ ?>
            					<div class="pull-right">
	            					<div class="col-sm-4 col-lg-4 control-label">
										<button class="btn btn-circle show-tooltip" id= "attempted_export_link" title="Quiz Report for Attempted Users" onclick="exportList(this.id)" ><i class="fa fa-user"></i></button > 
									</div>
	            					<div class="col-sm-4 col-lg-4 control-label">
										<button class="btn btn-circle show-tooltip" id= "unattempted_export_link" title="Quiz Report for Un-Attempted Users" onclick="exportList(this.id)" ><i class="fa fa-sign-out"></i></button > 
									</div>
								</div>
								<?php } ?>
								<!-- export option for unattemted quiz ends here -->
		                        </div>
								
		                        <div class="form-group">
		                        	<label class="col-sm-2 col-lg-2 control-label"><b>{{ trans('admin/assessment.attempts_that_are') }} :</b></label>
	                            	<div class="col-sm-10 col-lg-10 controls">
	                            		<div class="input-group">
		                                    <input type="radio" name="status" value="open" @if(Input::get('status') == 'open') checked @endif></input> {{ trans('admin/assessment.opened') }}
		                                    &nbsp;
		                                    <input type="radio" name="status" value="close" @if(Input::get('status', 'close') == 'close') checked @endif></input>{{ trans('admin/assessment.closed') }}
		                                </div>
	                            	</div>
		                        </div>
		                        @if($quiz->attempts != 1)
		                        <div class="form-group" id="tries" @if(Input::get('status') == 'open') style="display:none;" @endif>
		                        	<label class="col-sm-2 col-lg-2 control-label"><b>{{ trans('admin/assessment.show_only_attempts') }}:</b></label>
	                            	<div class="col-sm-2 col-lg-2 controls">
	                            		<?php $tries = strtolower(Input::get('tries', 'all')); ?>
	                            		<select class="form-control input-sm chosen" name="tries">
		                                    <option value="all" <?php if ($tries == 'all') echo 'selected';?>>{{ trans('admin/assessment.all') }}</option>
		                                    <option value="high" <?php if ($tries == 'high') echo 'selected';?>>{{ trans('admin/assessment.highest_score') }}</option>
		                                    <option value="low" <?php if ($tries == 'low') echo 'selected';?>>{{ trans('admin/assessment.lowest_score') }}</option>
		                                    <option value="first" <?php if ($tries == 'first') echo 'selected';?>>{{ trans('admin/assessment.first_attempt') }}</option>
		                                    <option value="last" <?php if ($tries == 'last') echo 'selected';?>>{{ trans('admin/assessment.last_attempt') }}</option>
		                                </select>
	                            	</div>
		                        </div>
		                        @endif
		                        <div class="form-group">
		                        	<label class="col-sm-2 col-lg-2 control-label"></label>
	                            	<div class="col-sm-2 col-lg-2 controls">
	                            		<button class="btn btn-info" onclick="this.form.submit();">{{ trans('admin/assessment.show_report') }}</button>
	                            	</div>
	                            </div>
	                    	</div>
	                    </div>
	                </form>
                    <br/>
                    <div class="clearfix"></div>
                    <table class="table table-advance" id="datatable">
				        <thead>
				            <tr>
				                <th style="width:18px"><input type="checkbox" id="checkall"/></th>
				                <th>{{ trans('admin/assessment.full_name') }}</th>
								<th>{{ trans('admin/assessment.username') }}</th>
				                <th>{{ trans('admin/assessment.status') }}</th>
				                <th>{{ trans('admin/assessment.time_taken') }}</th>
				                <th>{{ trans('admin/assessment.started_on') }}</th>
				                <th>{{ trans('admin/assessment.completed_on') }}</th>
				                <th>{{ trans('admin/assessment.marks') }} / {{ $quiz->total_mark }}</th>
				                <th>Score</th>
				            </tr>
				        </thead>
				        <tbody>
				        	<?php $user = $filter['user']->keyBy('uid')->toArray(); ?>
				        	@foreach($report as $r)
				        	<?php
				        		if(!isset($user[$r->user_id]))
				        			continue;
				        	?>
				        	<tr>
				        		<td><input type="checkbox"/></td>
				        		<td>
				        			{{ $user[$r->user_id]['firstname'].' '.$user[$r->user_id]['lastname'] }}
				        		</td>
								<td>{{ $user[$r->user_id]['username'] }}</td>
				        		<td>{{ $r->status }}</td>
				        		<td>
									@if(!empty($r->started_on) && !empty($r->completed_on))
										{{ $r->completed_on->diffForHumans($r->started_on, true) }}
									@endif
								</td>
				        		<td>{{ $r->started_on->timezone(Auth::user()->timezone)->format('d-m-Y H:i') }}</td>
								<td>@if(!empty($r->completed_on)) {{ $r->completed_on->timezone(Auth::user()->timezone)->format('d-m-Y H:i') }} @else In Progress @endif</td>
				        		<td>
									@if($r->status == 'CLOSED' || !empty($r->obtained_mark)) 
										{{ $r->obtained_mark }} 
									@endif
								</td>
								<td>
									@if($r->status == 'CLOSED' || !empty($r->obtained_mark)) 
										{{ round(($r->obtained_mark/(($r->total_mark >=1) ? $r->total_mark : 1))*100, 1).'%' }} 
									@endif
								</td>
				        	</tr>
				        	@endforeach
				        </tbody>
				    </table>
                </div>
	        </div>
	    </div>
	</div>
	<div class="modal fade" id="deletemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<div class="row custom-box">
						<div class="col-md-12">
							<div class="box">
								<div class="box-title">
									<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
									<h3 class="modal-header-title" >
										<i class="icon-file"></i>
										{{ trans('admin/assessment.quiz_delete') }}
									</h3>                 
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-body" style="padding-left: 20px;">
					{{ trans('admin/assessment.quiz_delete_confirmation') }}
				</div>
				<div class="modal-footer">
					<a class="btn btn-danger">{{ trans('admin/assessment.yes') }}</a>
					<a class="btn btn-success" data-dismiss="modal">{{ trans('admin/assessment.close') }}</a>
				</div>
			</div>
		</div>
	</div>
    <script type="text/javascript">
    	$(document).ready(function(){
		    $('#datatable').DataTable({
		    	"autoWidth": true,
		    	"searching": false,
	            "aaSorting": [[ 5, 'desc' ]],
	            "columnDefs": [ { "targets": [0,4], "orderable": false } ]
		    });
		    $(document).on('change', 'select[name="type"]', function() {
		    	if(this.value == 'all') {
		    		$('#user-select-value, #ug-select-value, #cf-select-value, #package-select-value').prop("disabled", false);
		    		$('#user-select, #ug-select, #cf-select, #package-select').hide();
		    	}
		    	if(this.value == 'user') {
		    		$('#ug-select-value, #cf-select-value, #package-select-value').prop("disabled", true);
		    		$('#user-select-value').prop("disabled", false);
		    		$('#ug-select, #cf-select, #package-select').hide();
		    		$('#user-select').show();
		    	}
		    	if(this.value == 'ug') {
		    		$('#user-select-value, #cf-select-value, #package-select-value').prop("disabled", true);
		    		$('#ug-select-value').prop("disabled", false);
		    		$('#user-select, #cf-select, #package-select').hide();
		    		$('#ug-select').show();
		    	}
		    	if(this.value == 'cf') {
		    		$('#user-select-value, #ug-select-value, #package-select-value').prop("disabled", true);
		    		$('#cf-select-value').prop('disabled', false);
		    		$('#user-select, #ug-select, #package-select').hide();
		    		$('#cf-select').show();
		    	}
		    	if(this.value == 'package') {
		    		$('#user-select-value, #ug-select-value, #cf-select-value').prop("disabled", true);
		    		$('#package-select-value').prop('disabled', false);
		    		$('#user-select, #ug-select, #cf-select').hide();
		    		$('#package-select').show();
		    	}
		    	$('#user-select-value, #ug-select-value, #cf-select-value, #package-select-value').trigger('chosen:updated');
		    });
			@if($quiz->attempts != 1)
			$(document).on('change', 'input[name="status"]', function(e) {
				if(this.value == 'open') {
					$('#tries').hide();
					$('select[name="tries"]').prop('disabled', true);
				}
				if(this.value == 'close') {
					$('#tries').show();
					$('select[name="tries"]').prop('disabled', false).trigger('chosen:updated');
				}
			});
			$('input[name="status"]:checked').trigger('change');
			@endif
		});

function exportList(clicked_id)
{
	var action = clicked_id == "attempted_export_link" ? "true" : "false";
	var value = $("#filter_form").attr('action');
	var link = $('#export_link_user').val();
	$("#filter_form").attr('action', link+'/'+action);
	$("#filter_form").submit();
	setTimeout(function(){ $("#filter_form").attr('action', value); }, 1000);
}




    </script>
@stop