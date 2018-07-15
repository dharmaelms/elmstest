<div class="row" id="player-activity-main-container">
	<div class="col-md-12 col-lg-12">
		<input type="hidden" name="player-activity-filter-option" value="{{$playerActivity["filter"]}}">
	@if($playerActivity["activity_flag"] === true)
		@if(($playerActivity["enable_filter"]))
			<div class="row">
				<div class="col-sm-offset-4 col-md-offset-4 col-lg-offset-4 col-sm-8 col-md-8 col-lg-8">
					<div class="row">
						<label class="col-md-offset-3 col-md-3 control-label">Filter by:&nbsp;</label>
						<select class="form-control input-sm input-small" id="player-activity-filter-option">
							<option value="all">All</option>
							<option value="general">General</option>
							<option value="channel">Channel</option>
							<option value="assessment">Assessment</option>
							<option value="QA_FAQ">QA and FAQ</option>
						</select>
					</div>
				</div>
			</div>
			<script type="text/javascript">
				var selectedOption = "{{ $playerActivity["filter"] }}";
				$("#player-activity-filter-option option").each(function(){
					if($(this).val() === selectedOption)
						$(this).prop({ selected : true });
				});

				$("#player-activity-filter-option").change(function(){
			    	$("input[name=\"player-activity-filter-option\"]").val($(this).val());
			    	loadPlayerActivity($(this).val(), 1, false);
			    });
			</script>
		@endif
		<div class="row">
			<div class="col-sm-12 col-md-12">
			@if($playerActivity["data"]["activity_count"] > 0)
				<div class="row">
					<div class="col-sm-12 col-md-12" id="player-activity-container">
						@include("playlyfe.player_activity_content")	
					</div>
				</div>
			@else
				<div class="row">
					<div class="col-sm-11 col-md-11">
						<div class="alert alert-info" role="alert" style="padding:5px;margin:15px;">
							<span style="padding:20px;">No records found</span>
						</div>
					</div>
				</div>
			@endif
			</div>
		</div>
		@if(($playerActivity["data"]["activity_count"] > 0) && ($playerActivity["enable_paginator"]))
		<div class="row">
			<div class="col-sm-offset-5 col-md-offset-5 col-sm-7 col-md-7">
				<ul id="player-activity-paginator" class="pagination-sm">
				</ul>
			</div>
		</div>
		<script type="text/javascript">
			$(document).ready(function(){
				playerActivityURL = "{{ URL::to("pl/player-activity") }}";
				$("#player-activity-paginator").twbsPagination({
					totalPages : {{ $playerActivity["total_page"] }},
					visiblePages : 3,
					initiateStartPageClick : false,
					startPage : 1,
					onPageClick : function(event, page){
						loadPlayerActivity($("input[name=\"player-activity-filter-option\"]").val(), page, true);			
					},
				});
			});
		</script>
		@endif
	@else
		<div class="alert alert-danger" role="alert">
			{{ $playerActivity["error_info"]["message"] }}
		</div>
	@endif
	</div>
</div>