@section('content')
<style type="text/css">
    body{
        max-height: 90vh;
    }
    .padTop20{
        padding-top: 20px;
        color: black;
    }
    .hdng-txt{
        font-size: 15px;
    }
    /*.quiz-instructions .quiz-next{
        position: relative;
        text-align: left !important;
    }
    .cancel-btn{
        position: absolute;
        left: 50%;
        top: 0;
    }
    .start-btn{
        margin-left: 44%;
    }
    @media all and (min-width: 480px) and (max-width: 960px), (min-device-width: 480px) and (max-device-width: 960px){
        .start-btn{
        margin-left: 40% !important;
    }
    .cancel-btn{
      margin-left: 15px !important;
    }
    }*/
  </style>
   @if($quiz->duration != 0)
    	<h4 class="padTop20 hdng-txt">
            <center><b style="color: #ed1a23;" >{{ trans("assessment.note") }}</b><span style="font-size:13px">{{ trans("assessment.time_duration") }}</span></center>
     	</h4>
    @endif
    <div class="quiz-instructions">
        <div class="top-container">
            <h4>
                <strong>{{ trans('assessment.instructions') }}</strong>
            </h4>
        </div>
        <div class="instructions">
            @if(!empty($quiz->quiz_description))
                {!! $quiz->quiz_description !!}
            @else
                {{ trans('assessment.no_instructions') }}
            @endif
        </div>
        <div class="quiz-next">
            <form id="quiz-attempt" action="{{ url('assessment/start-attempt/'.$quiz->quiz_id)}}" method="POST" style="display: inline !important">
                <button type="submit" class="btn btn-success start-btn">
                    <strong><i class="fa fa-check" aria-hidden="true"></i> {{ trans('assessment.start') }}</strong>
                </button>
            </form>
            <button onclick="window.close();" class="btn btn-danger cancel-btn ">
                <strong> <i class="fa fa-times" aria-hidden="true"></i> {{ strtoupper(trans('assessment.cancel')) }}</strong>
            </button>
        </div>  
    </div>  
<script type="text/javascript">
    var attemptUrl = "{{ url('assessment/attempt/') }}";
    $('#quiz-attempt').on('submit', function(e){
        $.ajax({
            url: "{{ url('attempt/start-attempt/'.$quiz->quiz_id) }}",
            method: 'POST',
            success: function(response) {
                if(response.status != 'undefined') {
                    window.location = "{{url('assessment/attempt/')}}/"+response.attempt_id;
                    reloadWindow();
                }
            }
        });
        e.preventDefault();
        return false;
    });
</script>
@stop