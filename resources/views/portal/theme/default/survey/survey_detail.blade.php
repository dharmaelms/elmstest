@section('content')
<style>
.detail-table tr td {
	padding-bottom: 5px !important;
}
</style>
<?php
    $userNow = Carbon::now(Auth::user()->timezone);
?>
<div class="page-bar">
	<ul class="page-breadcrumb">
		<li><a href="{{url('dashboard')}}">{{ trans('dashboard.dashboard') }}</a><i class="fa fa-angle-right"></i></li>
		<li><a href="{{ URL::to('/survey?filter=unattempted') }}">{{ trans('survey.survey2') }}</a><i class="fa fa-angle-right"></i></li>
		<li><a href="#">{{$survey->survey_title}}</a></li>
	</ul>
</div>
<h3 class="page-title-small uppercase margin-top-0">
{{ $survey->survey_title }}
</h3>
<div class="row quiz-details">
	<div class="col-md-2 col-lg-2">
		<img src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/img/survey-default.png') }}" class="img-responsive center center-align" width="180px" alt="">
	</div>
	<div class="col-md-10 col-lg-10" style="border: 1px dotted #888888; background: beige;">
		<table class="detail-table">
			<tbody>
				<tr>
					<td width="40px"><span class="start font-16">{{ trans('survey.starts') }}:</span></td>
					<td class="font-14">
					@if(!empty($survey->start_time))
						{{ $survey->start_time->timezone(Auth::user()->timezone)->format('D, d M, h:i A') }}
					@else
						{{ Lang::get('assessment/detail.not_available') }}
					@endif
					</td>
				</tr>
				<tr>
					<td width="40px"><span class="end font-16">{{ trans('survey.ends') }}:</span></td>
					<td class="font-14">
					@if(!empty($survey->end_time))
						{{ $survey->end_time->timezone(Auth::user()->timezone)->format('D, d M, h:i A') }}
					@else
						{{ Lang::get('assessment/detail.not_available') }}
					@endif
					</td>
				</tr>
				<tr>
					<td width="200px"><span class="font-16">{{ trans('survey.total_no_que') }}:</span></td>
					<td class="font-14">
					@if(isset($survey->survey_question) && !empty($survey->survey_question))
						{{count($survey->survey_question)}}
					@else
						0
					@endif
					</td>
				</tr>
				@if(!empty($survey->description))
				<tr>
					<td width="200px" style="vertical-align: top"><span class="font-16">{{ trans('survey.description') }}:</span></td>
					<td class="font-14">
						<div style="max-height: 120px;overflow: auto">{{$survey->description}}</div>
					</td>
				</tr>
				@endif
				<tr><td></td></tr>
				<tr>
				@if(isset($survey->start_time)
                && $survey->start_time->timestamp != 0
                && (Timezone::getTimeStamp($survey->start_time->timestamp) >= $userNow->timestamp))
					<td><button class="btn btn-default1 btn-lg" ><i class="fa fa-hand-o-right font-20"></i> {{ trans('survey.take_survey') }}</button></td>
				@else
					@if(isset($survey_attempt) && $survey_attempt->status == 'OPEN')
					<td><a href="{{url('survey/start-survey/unattempted/'.$survey->id)}}"><button type="submit" class="btn btn-success btn-lg" ><i class="fa fa-hand-o-right font-20"></i> {{ trans('survey.take_survey') }}</button></a></td>
					@else
					<td><a href="{{url('survey/start-survey/unattempted/'.$survey->id)}}"><button type="submit" class="btn btn-success btn-lg" ><i class="fa fa-hand-o-right font-20"></i> {{ trans('survey.take_survey') }}</button></a></td>
					@endif
				@endif
				</tr>
			</tbody>
		</table>
	</div>
</div>
@stop