  <?php use App\Model\SiteSetting;
 ?>
  <div class="row margin-bottom-30 featured-students">
    <div class="col-md-12 center">
    <?php
        $quotes = SiteSetting::module('Homepage', 'Quotes');
     ?>
      <h2 class="font-weight-500 black myellow-border margin-bottom-30">{{ $quotes['label'] }}</h2>
    </div>
    <div class="col-md-6 col-xs-12 border-right1">
      <ul class="fs-left-ul">
      <?php 
      $count = count($testimonials);
      $profile_pic_path = config('app.testimonials_path');

      ?>

        @for($i=0;$i <= $count; $i =$i+2)
        
        @if(isset($testimonials[$i]['name']))
        <?php // strip tags to avoid breaking any html
        $string = limitDescriptionCharacters($testimonials[$i]['description'], $quotes['description_chars'])
        
        ?>
        <li>
         <?php 
          
            $picture = $profile_pic_path.$testimonials[$i]['diamension'];
          ?>
          <div class="fs-img"><img src="{{ URL::to($picture) }}" alt="Student Name"></div>
            
            <div class="fs-data"><h4 class="black font-weight-500">{{ $testimonials[$i]['name'] }}<br><span class="font-13">{!! $testimonials[$i]['short_description'] !!}</span></h4>
            <p>{!! $string !!}</p></div>
        </li>
        @endif
        @endfor
      </ul>
    </div>
    <div class="col-md-6 col-xs-12">
      <ul class="fs-right-ul">
        @for($i=1;$i <= $count;$i =$i+2)
       
        @if(isset($testimonials[$i]['name']))
         <?php // strip tags to avoid breaking any html
        $string = limitDescriptionCharacters($testimonials[$i]['description'], $quotes['description_chars'])
        ?>
        <li>
          <?php 
          
            $picture = $profile_pic_path.$testimonials[$i]['diamension'];
          ?>
          <div class="fs-img"><img src="{{  URL::to($picture) }}" alt="Student Name"></div>
         
          <div class="fs-data"><h4 class="black font-weight-500">{{ $testimonials[$i]['name'] }}<br><span class="font-13">{!! $testimonials[$i]['short_description'] !!}</span></h4>
          <p>{!! $string !!}</p></div>
        </li>
        @endif
        @endfor
      </ul>
    </div><br>
    <div class="col-md-12 center xs-margin">
      <a href="{{ URL::to('/testimonials') }}" class="btn btn-primary">{{ Lang::get('testimonial.view_all') }}</a>
    </div>
  </div>
<?php 
 function limitDescriptionCharacters($string,$limit)
 {
  $string = strip_tags($string);

        if (strlen($string) > $limit) {

            // truncate string
            $stringCut = substr($string, 0, $limit);

            // make sure it ends in a word so assassinate doesn't become ass...
            $string = substr($stringCut, 0, strrpos($stringCut, ' ')).'...'; 
        }
        return $string;
 }

 ?>
  <!-- testimonials -->