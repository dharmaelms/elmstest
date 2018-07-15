@section('content')
<div class="col-md-8 col-sm-8 col-xs-12">
    <div>
      <div class="row">
        <div class="col-xs-8 col-sm-8 col-md-8 col-lg-8">
          <span class="title">{{ Lang::get('dashboard.courses')}}</span>
        </div>
        <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 sub-title-container">
          <span class="l-gray">|</span>
          <span class="sub-title"><a href="{{ url('program/what-to-watch')}}"><?php echo Lang::get('assessment.view_all'); ?></a></span>
        </div>
      </div>
    </div>
    <div class="title-border1"></div>
    @if($quizzes->count() > 0)
      <div class="row">
        @foreach($quizzes as $quiz)
          <div class="col-lg-3 col-md-4 col-sm-6 col-xs-6 sm-margin">
          <a href="{{ url('program/packet/test-introduction')}}">
            <div class="packet">
              <figure>
                <a href="{{ URL::to('assessment/detail/'.$quiz->quiz_id)}}" title="{{ $quiz->quiz_name }}">
                  <img src="{{URL::asset($theme.'/img/assessment-default.png')}}" alt="Channel" class="packet-img img-responsive">
              </figure>
              <div>
                  <p class="assessment-title">
                      <a href="{{ URL::to('assessment/detail/'.$quiz->quiz_id) }}">
                          <strong>
                              {{ $quiz->quiz_name}}
                          </strong>
                      </a>
                  </p>
                  <p class="xs-margin font-12">
                    @if($quiz->end_time)
                    {{ $quiz->end_time}}
                    @else
                    {{ Lang::get('dashboard.assessment_no_time_limit')}}
                    @endif
                  </p>
              </div>
              <p class="packet-title">{{ $quiz->quiz_name}}</p>
            </div><!--packet-->
          </a>
        </div>
        @endforeach
      </div>
    @else
      {{ Lang::get('dashboard.no_courses')}}
    @endif
    
    </div>
@stop