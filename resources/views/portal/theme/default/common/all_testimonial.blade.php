@section('content')
<?php use App\Model\SiteSetting; 
$quotes = SiteSetting::module('Homepage', 'Quotes');
$profile_pic_path = config('app.testimonials_path');
?>

<div class="row margin-bottom-30 featured-students">
    <div class="col-md-12 center">
      <h2 class="font-weight-500 black myellow-border margin-bottom-30">{{ $quotes['label'] }}</h2>
    </div>
    <div class="col-md-12 col-xs-12">
          <ul class="fs-right-ul">
            @for($i=0;$i < count($testimonials); $i++)
           
            @if(isset($testimonials[$i]['name']))
            
            <li>
              <?php 
              
                $picture = $profile_pic_path.$testimonials[$i]['diamension'];
              ?>
              <div class="fs-img"><img src="{{  URL::to($picture) }}" alt="Student Name"></div>
             
              <div class="fs-data"><h4 class="black font-weight-500">{{ $testimonials[$i]['name'] }}<br><span class="font-13">{!! $testimonials[$i]['short_description'] !!}</span></h4>
              <p>{!! $testimonials[$i]['description'] !!}</p></div>
            </li>
            @endif
            @endfor
          </ul>
    </div>
    {!! $testimonials->render() !!} 
</div>
@stop