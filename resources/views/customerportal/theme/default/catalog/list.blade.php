@section('content')
<?php
  use App\Model\Category;
  use App\Model\Banners;
 ?>

<link href="{{ URL::asset($custom_theme.'/plugins/slider-revolution-slider/rs-plugin/css/settings.css')}}" rel="stylesheet">
<link href="{{ URL::asset($custom_theme.'/css/style-revolution-slider.css')}}" rel="stylesheet">

<div class="page-slider margin-bottom-40">
    <div class="fullwidthbanner-container revolution-slider">
      <div class="fullwidthabnner1">
        <ul id="revolutionul">
          <?php $banners=Banners::getAllBanners("ACTIVE","category");  $i = 1;?>
          @if(isset($banners) && ($banners != '') && (!empty($banners)))
            @foreach($banners as $banner)
            <?php
           
                $banner_file_name=$banner['file_client_name'];
                $banner_file_path=config('app.site_banners_path').$banner_file_name; 
            ?>
          <!-- THE NEW SLIDE -->
          <li data-transition="fade" data-slotamount="8" data-masterspeed="700" data-delay="9400" <?php if($i == 1) { ?> data-link="{{URL::to('campaign')}}" <?php } ?>  data-target="_blank">
            <!-- THE MAIN IMAGE IN THE FIRST SLIDE -->
             <img src="{{URL::to($banner_file_path)}}" alt="Category">
             @if(!empty($banner['description']))
            <div class="caption lft slide_title_white slide_item_left"
              data-x="270"
              data-y="40"
              data-speed="400"
              data-start="1500"
              data-easing="easeOutExpo">
              <span class="myellow font-weight-500">{{str_limit($banner['description'], $limit = 100, $end = '...')}}</span>
            </div>
            @endif
          </li> 
          <?php $i++; ?>
          @endforeach
          @else
           <?php $banner = config('app.default_banner_path'); ?>
             <li data-transition="fade" data-slotamount="8" data-masterspeed="700" data-delay="9400" data-link="{{URL::to('campaign')}}" data-target="_blank">
            <!-- THE MAIN IMAGE IN THE FIRST SLIDE -->
            <img src="{{$banner}}" alt="Category">
            
            <div class="caption lft slide_title_white slide_item_left"
              data-x="270"
              data-y="40"
              data-speed="400"
              data-start="1500"
              data-easing="easeOutExpo">
             <span class="myellow font-weight-500">INNOVATE</span>
            </div>
            <div class="caption lft slide_title_white slide_item_left"
              data-x="350"
              data-y="95"
              data-speed="400"
              data-start="2000"
              data-easing="easeOutExpo">
              YOUR <span class="myellow font-weight-500">CAREER</span> SPACE
            </div> 
          </li>   
         @endif   
        </ul>
      </div>
    </div>
  </div>
  <!-- END SLIDER -->
    
    <!--BEGIN Main Container -->
  <div class="main">
    <div class="container">
      <div class="row">
                <div class="col-md-4 col-sm-5 col-xs-12">
                @if(Request::is('catalog') || Request::is('catalog/*') || Request::is('/') || Request::is('catalog-search'))
                    <form action="{{ URL::to('catalog') }}" method="get" class="search-form" onsubmit="return submitCatSearch();">
                        <div class="form-group">
                            <div class="input-icon right">
                            <button name="action">
                              <img src="{{URL::to($custom_theme.'/img/icons/search-icon.png')}}" alt="Search icon">  
                            </button> 
                            <input style="display: inline;" type="text" class="form-control" placeholder="Search" name="cat_search" id="cat_search">
                            <input type="hidden" name="page_type" value="catalog">
                            </div>
                        </div>
                    </form>
                @endif
              
                </div>
        <div class="col-md-8 col-sm-7 col-xs-12">
            <div class="pull-right" style="margin-top:5px;">
                <a href="#" class="active" id="grid"><i class="sprite2 sprite-grid-icon"></i></a>&nbsp;&nbsp;
                <a href="#" id="list"><i class="sprite2 sprite-list-icon"></i></a>
            </div>
        </div>
      </div>
            <hr class="catalog-hr">
      <?php $categories = Category::getCategoryWithRelation();?>
            <div class="row">
               
                <div class="col-md-3 col-sm-4 col-xs-12 filter-div margin-bottom-30">
                    <div class="blue-bar">
                        <h4 class="white font-weight-500">Filter <img src="{{URL::to($custom_theme.'/img/icons/filter-icon.png')}}" alt="Filter icon" class="pull-right"></h4>
                    </div>
                    <div class="filter-options">
                        <!-- <div class="portlet box green">
                            <div class="portlet-title">
                                <div class="caption">COURSES</div>
                                <div class="tools">
                                    <a href="javascript:;" class="collapse">
                                    </a>
                                </div>
                            </div>
                            <div class="portlet-body">
                                <div class="form-group">
                                    <div class="checkbox-list">
                                        <label>
                                        <div class="checker"><span><input type="checkbox"></span></div> All Courses</label>
                                        <label>
                                        <div class="checker"><span><input type="checkbox"></span></div> Upcoming Courses</label>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                        @if(!empty($categories))
                        <div class="portlet box green margin-bottom-20">
                            <div class="portlet-title">
                                <div class="caption">
                                    BY CATEGORY
                                </div>
                                <div class="tools">
                                    <a href="javascript:;" class="collapse">
                                    </a>
                                </div>
                            </div>
                            <div class="portlet-body">
                                <div class="form-group">
                                   
                                    
                                    <div class="checkbox-list">
                                     
                                    <form method="GET" action="{{ URL::to('catalog/') }}">
                  
                 
                 

                                    <div class="checkbox-list">
                                    @foreach($categories as $eachcategories)
            <?php 
              $category_filter = Input::get('category_filter');
              // print_r($category_filter);echo "<br>";
              $program_filter = Input::get('program_filter');
              // print_r($program_filter);echo "<br>";
              if(isset($eachcategories['children'])) {
              $count=Category::getChildernProducts($eachcategories['children']);
              }
            ?>
            <!--  <input type="hidden" name="category_filter[]" value="{{$eachcategories['category_id']}}"> -->
                                        <label>
                                        <div class="checker">
                                        <span>
                                        <!-- <input type="checkbox">
                                        </span></div>{{$eachcategories['category_name']}}</label> -->
                                        <input type="checkbox" name="category_filter[]" class="selectcat" value="{{$eachcategories['category_id']}}" <?php if(isset($category_filter) && in_array($eachcategories['category_id'], $category_filter)){ ?>checked<?php } ?>>
                                        </span></div> {{$eachcategories['category_name']}}</label>
                                      @endforeach
                                      <input id="selectall" type="checkbox" name="category_filter[]" value="all" <?php if (isset($category_filter) && in_array("all", $category_filter)) { ?> checked
                                          
                                      <?php } ?>>&nbsp;&nbsp;All  
                                    </div>
                                    </br></br>
                                    <div class="center margin-bottom-20">
                  <input type="submit" class="btn btn-primary" value="SEARCH">
                  </div>
                  </form>
                  <!-- <h5 class="center"><b><a href="{{ URL::to('catalog/') }}">View All</a></b></h5> -->
                                    </div>
                                   
                                </div>
                            </div>
                        </div>
                        @endif
                        <!-- <div class="portlet box green">
                            <div class="portlet-title">
                                <div class="caption">
                                    BY TYPE
                                </div>
                                <div class="tools">
                                    <a href="javascript:;" class="collapse">
                                    </a>
                                </div>
                            </div>
                            <div class="portlet-body">
                                <div class="form-group">
                                    <div class="checkbox-list">
                                        <label>
                                        <div class="checker"><span><input type="checkbox"></span></div> Online</label>
                                        <label>
                                        <div class="checker"><span><input type="checkbox"></span></div> Self Paced</label>
                                        <label>
                                        <div class="checker"><span><input type="checkbox"></span></div> Face to Face</label>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                       <!--  <div class="center margin-bottom-20">
                            <a href="#" class="btn btn-primary">SEARCH</a>
                        </div> -->
                    </div>
                </div>
                <!-- sidebar filter -->
                <div id="grid_display" class="col-md-9 col-sm-8 col-xs-12 courses-div gridview">
                    
                        <?php
                        $cat_search = Input::get('cat_search');
                         if(isset($cat_search) && !empty($cat_search)){?>
                         <div class="col-md-12 col-sm-12 col-xs-12 margin-bottom-20 no-padding">
                        <div class="blue-bar">

                        
                            <h4 class="white font-weight-500">Search Result for:  <strong>{{Input::old('cat_search')}}</strong></h4>
                           
                             
                            
                        </div>
                        </div>
                          <?php } ?>
                    
                     <!-- @if(!empty($c_list))
                        @foreach ($c_list as $key => $value)
                   
                            @if(isset($value['programs']) && !empty($value['programs']))

                                @foreach ($value['programs'] as $key => $eachProgram)
                                
                                    <div class="col-md-3 col-sm-6 col-xs-12">
                                    
                                        <div class="course">
                                            <figure>
                                                <a href="{{ URL::to('catalog/course/'.$eachProgram['program_slug']) }}" title="{{$eachProgram['program_title']}}">
                                                        @if(isset($eachProgram['program_cover_media']) && !empty($eachProgram['program_cover_media']))
                                                            <img src="{{URL::to('media_image/'.$eachProgram['program_cover_media'])}}" alt="{{$eachProgram['program_title']}}" class="packet-img img-responsive">
                                                        @else
                                                            <img src="{{URL::to($custom_theme.'/img/default_channel.png')}}" alt="{{$eachProgram['program_title']}}" class="packet-img img-responsive">
                                                        @endif
                                                    </a>
                                            </figure>
                                            <div class="center course-title">
                                              <h4 class="font-weight-500 black">{{str_limit($eachProgram['program_title'], $limit = 54, $end = '...')}} </h4>
                                            </div>
                                            <div>
                                            <?php ?>
                                           @if(isset($eachProgram['vertical']['subscription'][0]['price'][0]['price']) && !empty($eachProgram['vertical']['subscription'][0]['price'][0]['price']))
                                                <div class="price">
                                                    <p>Price</p>
                                                    <p class="amt disc-line"><sup>&#8377;</sup>
                                                    <span>{{number_format($eachProgram['vertical']['subscription'][0]['price'][0]['price'])}}</span>
                                                    </p>
                                                </div>
                                                @if(isset($eachProgram['vertical']['subscription'][0]['price'][0]['markprice']) && !empty($eachProgram['vertical']['subscription'][0]['price'][0]['markprice']))
                                                    <div class="disc-price">
                                                        <p>Discount price</p>
                                                        <p class="amt"><sup>&#8377;</sup><span> {{number_format($eachProgram['vertical']['subscription'][0]['price'][0]['markprice'])}}</span></p>
                                                    </div>
                                                @endif
                                                @else
                                                    @if(isset($eachProgram['vertical']['subscription'][0]['price'][0]['price']) && !empty($eachProgram['vertical']['subscription'][0]['price'][0]['price']))
                                                        <span class="pull-left price">&#8377; {{number_format($eachProgram['vertical']['subscription'][0]['price'][0]['price'])}}</span>
                                                    @endif
                                                 <div class="center red free">FREE</div>
                                            @endif
                                            </div>
                                            <div class="center btn-divs">
                                                <a href="{{URL::to('catalog/course/'.$eachProgram['program_slug'])}}" class="btn btn-primary margin-bottom-5">VIEW DETAILS</a>
                                                <a href="{{URL::to('catalog/course/'.$eachProgram['program_slug'])}}" class="btn btn-success margin-bottom-5">BUY NOW</a>
                                            </div>
                                        </div>
                                    </div>
                    
                                @endforeach
                            @endif
                        @endforeach
                    @endif -->      
                     <?php 
                     // dd($c_list);
          if(!empty($c_list))
          {
         
            foreach ($c_list as $key => $value) 
            {
            if (isset($value['category_name'])) {
               $catname=$value['category_name'];
            }
              
             //  if($catname!='Miscellaneous') {
             //  $record=Category::getCategoyInfo($value['slug']);
             //  if($record[0]['parents'] > 0) {
             //  $parentname=Category::getcatname($record[0]['parents']);
             //  $catname=$parentname.' - '.$value['category_name'];
             // }
             // }
              if(isset($value['programs']) && !empty($value['programs']))
                { ?>
                      <div class="row"><!--start3-->
                      <div class="margin-bottom-5"></div>
                        <div class="col-md-12">
                            <h3 class="page-title-small" style="margin-top: -4px;">
                             @if(isset($catname))
                             <div class="blue-bar">
                                <h4 class="white font-weight-500">{{$catname}}</h4>
                              </div>
                               @endif
                            </h3>
                        </div>
                        <div class="facets-data"><!--start2-->
                     <?php
                    foreach ($value['programs'] as $key => $eachProgram)
                    { ?>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                              <div class="course">
                                <figure>

                                    <a href="{{ URL::to('catalog/course/'.$eachProgram['program_slug']) }}" title="{{$eachProgram['program_title']}}">
                                            @if(isset($eachProgram['program_cover_media']) && !empty($eachProgram['program_cover_media']))
                                                <img src="{{URL::to('media_image/'.$eachProgram['program_cover_media'])}}" alt="{{$eachProgram['program_title']}}" class="packet-img img-responsive">
                                            @else
                                                <img src="{{URL::to($custom_theme.'/img/default_channel.png')}}" alt="{{$eachProgram['program_title']}}" class="packet-img img-responsive">
                                            @endif
                                            @if(isset($eachProgram['program_sub_type']) && $eachProgram['program_sub_type'] == 'collection')
                                            <div class="label label-success">Pack</div>
                                            @endif
                                        </a>
                                </figure>
                                <div class="center course-title">
                                    <h4 class="font-weight-500 black">{{str_limit($eachProgram['program_title'], $limit = 40, $end = '...')}} </h4>
                                </div>
                                <div>
                                    @if(isset($eachProgram['vertical']['subscription'][0]['price'][0]['price']) && !empty($eachProgram['vertical']['subscription'][0]['price'][0]['price']))
                                        <div class="price">
                                            <p>Price</p>
                                            <p class="amt disc-line"><sup>&#8377;</sup>
                                            <span>{{number_format($eachProgram['vertical']['subscription'][0]['price'][0]['price'])}}</span>
                                            </p>
                                        </div>
                                    @if(isset($eachProgram['vertical']['subscription'][0]['price'][0]['markprice']) && !empty($eachProgram['vertical']['subscription'][0]['price'][0]['markprice']))
                                        <div class="disc-price">
                                            <p>Discount price</p>
                                            <p class="amt"><sup>&#8377;</sup><span> {{number_format($eachProgram['vertical']['subscription'][0]['price'][0]['markprice'])}}</span></p>
                                        </div>
                                    @endif
                                    @else
                                        @if(isset($eachProgram['vertical']['subscription'][0]['price'][0]['price']) && !empty($eachProgram['vertical']['subscription'][0]['price'][0]['price']))
                                            <span class="pull-left price">&#8377; {{number_format($eachProgram['vertical']['subscription'][0]['price'][0]['price'])}}</span>
                                        @endif
                                            <div class="center red free">FREE</div>
                                    @endif
                                </div>
                                <div class="center btn-divs">
                                    <a href="{{URL::to('catalog/course/'.$eachProgram['program_slug'])}}" class="btn btn-primary margin-bottom-5">VIEW DETAILS</a>
                                    <a href="{{URL::to('catalog/course/'.$eachProgram['program_slug'])}}" class="btn btn-success margin-bottom-5">ENROLL</a>
                                </div>
                              </div><!--packet-->
                          </div><!--start1-->
                   <?php
                    }
                    ?>
                        </div><!--start2-->
                  </div><!--start3-->
          <?php 
            }}} 
          ?>
                </div>
                <div id="list_display" class="col-md-9 col-sm-8 col-xs-12">
                    
                        <?php
                        $cat_search = Input::get('cat_search');
                         if(isset($cat_search) && !empty($cat_search)){?>
                         <div class="col-md-12 col-sm-12 col-xs-12 margin-bottom-20 no-padding">
                        <div class="blue-bar">
                            <h4 class="white font-weight-500">Search Result for:  <strong>{{Input::old('cat_search')}}</strong></h4>
                        </div>
                            
                        </div>
                          <?php } ?>
                    
                    @if(!empty($c_list))
                        @foreach ($c_list as $key => $value)
                        <?php 
                        if (isset($value['category_name'])) {
                         $catname=$value['category_name'];
                         }?>
                            @if(isset($value['programs']) && !empty($value['programs']))
                            <div class="row">
                                
                             <div class="margin-bottom-5"></div>
                            <div class="col-md-12">
                            <h3 class="page-title-small" style="margin-top: -4px;">
                               @if(isset($catname))
                               <div>
                             <div class="blue-bar">
                                <h4 class="white font-weight-500">{{$catname}}</h4>
                              </div>
                               @endif
                            </h3>
                        </div>                            </div>

                                @foreach ($value['programs'] as $key => $eachProgram)

                    <div class="listview">
                        <div class="course-list row">
                            <div class="col-md-3 col-sm-3 col-xs-3 no-padding">
                                <figure>
                                     <a href="{{ URL::to('catalog/course/'.$eachProgram['program_slug']) }}" title="{{$eachProgram['program_title']}}">
                                            @if(isset($eachProgram['program_cover_media']) && !empty($eachProgram['program_cover_media']))
                                                <img src="{{URL::to('media_image/'.$eachProgram['program_cover_media'])}}" alt="{{$eachProgram['program_title']}}" class="packet-img img-responsive">
                                            @else
                                                <img src="{{URL::to($custom_theme.'/img/default_channel.png')}}" alt="{{$eachProgram['program_title']}}" class="packet-img img-responsive">
                                            @endif
                                            @if(isset($eachProgram['program_sub_type']) && $eachProgram['program_sub_type'] == 'collection')
                                            <div class="label label-success">Pack</div>
                                            @endif
                                      </a>
                                </figure>
                            </div>
                            <div class="col-md-9 col-sm-9 col-xs-9">
                                <h4 class="font-weight-500 black">{{$eachProgram['program_title']}}</h4>
                                @if(strlen($eachProgram['program_description']) >= 280)
                                <p>{{str_limit($eachProgram['program_description'], 280)}}</p>
                                @else
                                <p>{{ $eachProgram['program_description'] }}</p>
                                @endif
                                <div>
                                    
                                    @if(isset($eachProgram['vertical']['subscription'][0]['price'][0]['price']) && !empty($eachProgram['vertical']['subscription'][0]['price'][0]['price']))
                                    <div class="price">
                                        <p>Price</p>
                                        <p class="amt disc-line"><sup>&#8377;</sup><span>{{number_format($eachProgram['vertical']['subscription'][0]['price'][0]['price'])}}</span></p>
                                    </div>
                                    @if(isset($eachProgram['vertical']['subscription'][0]['price'][0]['markprice']) && !empty($eachProgram['vertical']['subscription'][0]['price'][0]['markprice']))
                                    <div class="disc-price">
                                        <p>Discount price</p>
                                        <p class="amt"><sup>&#8377;</sup><span>{{number_format($eachProgram['vertical']['subscription'][0]['price'][0]['markprice'])}}</span></p>
                                    </div>
                                        @endif
                                        
                                        @endif
                                    
                                    <div class="btn-div">
                                        <a href="{{URL::to('catalog/course/'.$eachProgram['program_slug'])}}" class="btn btn-primary margin-bottom-5">VIEW DETAILS</a>
                                    </div>
                                    <div class="btn-div">
                                        <a href="{{URL::to('catalog/course/'.$eachProgram['program_slug'])}}" class="btn btn-success margin-bottom-5">ENROLL</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>

                                @endforeach
                               
                            @endif
                        @endforeach
                    @endif  
                </div>
            </div>
  </div>

<!-- BEGIN RevolutionSlider -->
<script src="{{ URL::asset($custom_theme.'/plugins/slider-revolution-slider/rs-plugin/js/jquery.themepunch.plugins.min.js')}}" type="text/javascript"></script> 
<script src="{{ URL::asset($custom_theme.'/plugins/slider-revolution-slider/rs-plugin/js/jquery.themepunch.revolution.min.js')}}" type="text/javascript"></script> 
<script src="{{ URL::asset($custom_theme.'/plugins/slider-revolution-slider/rs-plugin/js/jquery.themepunch.tools.min.js')}}" type="text/javascript"></script>
<script src="{{ URL::asset($custom_theme.'/js/revo-slider-init.js')}}" type="text/javascript"></script>
<!-- END RevolutionSlider -->

<script>
    jQuery(document).ready(function() {
        RevosliderInit.initRevoSlider();
    });
</script>


<!--Plug-in Initialisation-->
  <script type="text/javascript">
  function random(){
      $('.owl-carousel').each(function(pos,value){
        $(value).owlCarousel({
          items:4,
          navigation: true,
          navigationText: [
          "<i class='fa fa-caret-left'></i>",
          "<i class='fa fa-caret-right'></i>"
          ],
          beforeInit : function(elem){ 
          } 
        });
    });
  }
    $(document).ready(function() {
    //Sort random function
    $("#list_display").hide();
      random();

    $("#grid").click(function(){
       $("#grid_display").show();
       $("#list_display").hide();
       $( this ).parent().find( 'a.active' ).removeClass( 'active' );
       $( this ).addClass( 'active' );
   });
   $("#list").click(function(){
       $("#list_display").show();
       $("#grid_display").hide();
       $( this ).parent().find( 'a.active' ).removeClass( 'active' );
      $( this ).addClass( 'active' );

   });

   $("#selectall").click(function () 
 {
  $('.selectcat').attr('checked', this.checked)
 });
  
 $(".selectcat").click(function()
 {
  if($(".selectcat").length == $(".selectcat:checked").length) 
  {
   $("#selectall").attr("checked", "checked");
  } 
  else 
  {
   $("#selectall").removeAttr("checked");
  }
 });
    });
   

</script>
@stop