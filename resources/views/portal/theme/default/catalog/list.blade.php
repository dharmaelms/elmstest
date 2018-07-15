@section('content')
<?php
  use App\Model\Category;
  use App\Model\Banners;
  use App\Model\Program;
 ?>
<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.css')}}" />
<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.theme.css')}}" />
<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/plugins/slider-revolution-slider/rs-plugin/css/settings.css')}}" />
<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/css/front-end/style-revolution-slider.css')}}" />
<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/css/front-end/style-responsive.css')}}" />
<script src="{{ URL::asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.js')}}"></script>
<script src="{{ URL::asset($theme.'/plugins/slider-revolution-slider/rs-plugin/js/jquery.themepunch.revolution.min.js')}}"></script>
<script src="{{ URL::asset($theme.'/plugins/slider-revolution-slider/rs-plugin/js/jquery.themepunch.tools.min.js')}}"></script>
<script src="{{ URL::asset($theme.'/js/front-end/revo-slider-init.js')}}"></script>
<style>
.filter-title {padding:1px 10px;border-bottom:1px solid #eeeeee;}
.filter-title li { padding:2px 10px; }
</style>

<!-- BEGIN SLIDER -->
<div class="page-slider">
  <div class="fullwidthbanner-container revolution-slider margin-btm-0">
    <div class="fullwidthbanner1">
      <ul id="revolutionul">

      <?php $banners=Banners::getAllBanners("ACTIVE","category"); ?>
        @if(isset($banners) && ($banners != '') && (!empty($banners)))
            @foreach($banners as $banner)
            <?php
                $banner_file_name=$banner['file_client_name'];
                $banner_file_path=config('app.site_banners_path').$banner_file_name; 
            ?>
            <li data-transition="fade" data-slotamount="8" data-masterspeed="700" data-delay="9400">

              <img src="{{URL::to($banner_file_path)}}" alt="Banner">
            </li>
            @endforeach
        @else
            <?php $banner = config('app.default_banner_path'); ?>
            <li data-transition="fade" data-slotamount="8" data-masterspeed="700" data-delay="9400">
                <img src="{{URL::to($banner)}}" alt="Banner">
            </li>
        @endif
      </ul>         
    </div>
  </div>
</div>
<!-- END SLIDER -->
<div class="catalog-view">
  <div class="container">
    <div class="lg-margin"></div><!--space-->
    <div class="row">
      <!-- category-list -->
      <?php
      $categories = Category::getCustomCategoryWithRelation();
      $totalcount = Category::getCustomContentCount();
      if(!empty($totalcount)) {
      ?>
      <div class="col-md-3 col-sm-4 col-xs-12 md-margin">

       <div class="cs-category-list">
            @foreach($categories as $eachcategories)
            <?php 
              $category_filter = Input::get('category_filter');
              $program_filter = Input::get('program_filter');
              if(isset($eachcategories['children'])) {
              $count=Category::getChildernProducts($eachcategories['children']);
              }
            ?>
            <div class="portlet blue box">
              
              <div class="portlet-title <?php if($category_filter == $eachcategories['category_id']) { ?>active <?php } ?>">
                <!-- <div class="caption">
                  <form method="GET" action="{{ URL::to('catalog/') }}">
                  <img src="{{URL::to('portal/theme/default/img/icons/category-icon.png')}}" width="30px">&nbsp;&nbsp;<a href="{{ URL::to('catalog/'.$eachcategories['category_id']) }}">{{ $eachcategories['category_name'] }}</a>
                  <input type="hidden" name="category_filter" value="{{$eachcategories['category_id']}}">
                  <input type="submit" value="{{$eachcategories['category_name']}}">
                  </form>
                </div> -->
                
                <div class="caption">
                  <form method="GET" action="{{ URL::to('catalog/') }}">
                    <div class="input-group left-addon">
                      <span class="input-group-addon">
                       <img src="{{URL::to('portal/theme/default/img/icons/category-icon.png')}}" alt="Category icon" width="24px">
                      </span>
                      <input type="hidden" name="category_filter" value="{{$eachcategories['category_id']}}" class="form-control">
                      <input type="submit" value="{{$eachcategories['category_name']}}" class="form-control">
                    </div>
                  </form>
                </div>


                  <?php if(isset($count)) {?>
                  <div class="tools">@if($count > 0 && isset($eachcategories['children']) && !empty($eachcategories['children']))<a href="javascript:;" class="expand"></a>@endif</div>
               
                  <?php }
                    else {?>
                    <div class="tools">@if(isset($eachcategories['children']) && !empty($eachcategories['children']))<a href="javascript:;" class="expand"></a>@endif</div>
             
                   <?php } ?>
                 </div>
             

              @if(isset($eachcategories['children']) && !empty($eachcategories['children']))
              <?php $parentid=Category::getParentId($category_filter);
          
              ?>
              <div class="portlet-body" <?php if($eachcategories['category_id'] == $parentid) { ?> style="display:block;" <?php }else { ?>  style="display:none;" <?php }  ?>>
                <ul>
                @foreach($eachcategories['children'] as $eachchildrens)
                  <?php $children_prog_rel = Category::getCustomChildrenProgramRelation($eachchildrens['category_id']);
                   ?>
                  @if(isset($children_prog_rel[0]['category_id']) && !empty($children_prog_rel[0]['category_id']))
                 
                  <li <?php if($category_filter == $children_prog_rel[0]['category_id']) { ?>class ="active" <?php } ?>>
                  <form method="GET" action="{{ URL::to('catalog/') }}">
                 
                  <input type="hidden" name="category_filter" value="{{$children_prog_rel[0]['category_id']}}">
                  <input type="submit" value="{{$children_prog_rel[0]['category_name']}}">
                  </form>
                  </li>
                  @endif
                @endforeach
                </ul>
              </div>
              @endif
              </div>
              
            @endforeach
            <?php
            $pd_list=Program::get_catalog_products();
			$ch_list=Program::get_catalog_channels();
            $cc_list=Program::get_catalog_courses();
            if(!empty($pd_list) || !empty($ch_list) || !empty($cc_list)) {
            ?>
<div class="portlet blue box">
  <div class="portlet-title <?php if($category_filter == -1) { ?>active <?php } ?>">
    <div class="caption">
      <form method="GET" action="{{ URL::to('catalog/') }}">
        <div class="input-group left-addon">
          <span class="input-group-addon">
            <img src="{{URL::to('portal/theme/default/img/icons/category-icon.png')}}" alt="Category icon" width="24px">
          </span>
          <input type="hidden" name="category_filter" value="-1"  class="form-control">
          <input type="submit" value="Miscellaneous"  class="form-control">
        </div>
      </form>
    </div>
  </div>
</div>
  <?php }?>

            <h5 class="center"><b><a href="{{ URL::to('catalog/') }}">{{Lang::get('catalog/template_two.view_all')}}</a></b></h5>
        </div>
      </div>
      <!-- category-list -->

      <div class="col-md-9 col-sm-8 col-xs-12 padding-lr-10">
        <div class="row">
          <div class="col-md-7 col-sm-7 col-xs-12"></div>
          <div class="col-md-5 col-sm-5 col-xs-12">
            <div class="btn-group pull-right">
               @if(isset($program_filter) && !empty($program_filter))  
                @if($program_filter == "content_feed")
                  <span class="font-12"><b>{{Lang::get('catalog/template_two.program_type')}}:</b>{{Lang::get('catalog/template_two.content_feed')}}</span>
                @elseif($program_filter == "product")
                  <span class="font-12"><b>{{Lang::get('catalog/template_two.program_type')}}:</b>{{Lang::get('catalog/template_two.Product')}}</span>
                @elseif($program_filter == "collection")
                  <span class="font-12"><b>{{Lang::get('catalog/template_two.program_type')}}:</b>{{Lang::get('catalog/template_two.package')}}</span>
                @elseif($program_filter == "course")
                  <span class="font-12"><b>{{Lang::get('catalog/template_two.program_type')}}:</b>{{Lang::get('catalog/template_two.course')}}</span>
                @endif
               @endif
               &nbsp;&nbsp;&nbsp;&nbsp;
              <a data-toggle="dropdown" aria-expanded="false"><img src="{{URL::to('portal/theme/default/img/icons/filter-icon.png')}}" width="20px" alt="Filter icon"></a>
              <ul class="dropdown-menu" role="menu"> 
                <li class="filter-title"><h4>{{Lang::get('catalog/template_two.program_type')}}</h4></li>
                <form method="GET" action="{{ URL::to('catalog/') }}" >
                <input type="hidden" value="{{ $category_filter }}" name="category_filter">
                <li><input type="checkbox" name="program_filter[]" value="all" onclick="this.form.submit()" <?php if(isset($program_filter) && is_array($program_filter) && in_array('all', $program_filter)){ ?>checked<?php }?>> {{Lang::get('catalog/template_two.all')}} </li>
                <li><input type="checkbox" name="program_filter[]" value="content_feed" onclick="this.form.submit()" <?php if(isset($program_filter) && is_array($program_filter) && in_array('content_feed', $program_filter)){ ?>checked<?php }?> > {{Lang::get('catalog/template_two.channel')}}</li>
                <li><input type="checkbox" name="program_filter[]" value="product" onclick="this.form.submit()" <?php if(isset($program_filter) && is_array($program_filter) && in_array('product', $program_filter)){ ?>checked<?php }?> > {{Lang::get('catalog/template_two.product')}} </li>
                <li><input type="checkbox" name="program_filter[]" value="collection" onclick="this.form.submit()" <?php if(isset($program_filter) && is_array($program_filter) && in_array('collection', $program_filter)){ ?>checked<?php }?> > {{Lang::get('catalog/template_two.package')}} </li>
                <li><input type="checkbox" name="program_filter[]" value="course" onclick="this.form.submit()" <?php if(isset($program_filter) && is_array($program_filter) && in_array('course', $program_filter)){ ?>checked<?php }?> >{{Lang::get('catalog/template_two.course')}} </li>
                </form>
              </ul>
            </div>
          </div>
        </div>
          <?php 
          if(!empty($c_list))
          {
         
            foreach ($c_list as $key => $value) 
            {
              $catname = html_entity_decode($value['category_name']);
              if($catname!='Miscellaneous') {
              $record=Category::getCategoyInfo($value['slug']);
              if($record[0]['parents'] > 0) {
              $parentname=Category::getcatname($record[0]['parents']);
              $catname = html_entity_decode($parentname).' - '.html_entity_decode($value['category_name']);
             }
             }
              if(isset($value['programs']) && !empty($value['programs']))
                { ?>
                      <div class="row"><!--start3-->
                        <div class="col-md-12">
                            <h3 class="page-title-small" style="margin-top: -4px;">
                              <a href="">
                                {{$catname}}
                              </a>
                            </h3>
                        </div>
                        <div class="facets-data"><!--start2-->
                     <?php
                    foreach ($value['programs'] as $key => $eachProgram)
                    { ?>
                        
                       <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 md-margin"><!--start1-->
                     <!--  @if(isset($eachProgram['program_sub_type']) && $eachProgram['program_sub_type']=='collection')
                      <sup><b style="color:white" class="show-tooltip badge badge-grey badge-info" data-original-title="" title="">Pack</b></sup>
                       @endif  -->
                              <div class="packet">
                              
                                <figure>
                                
                                  <a href="<?php echo URL::to("catalog/course/{$eachProgram['program_slug']}");?>" title="Video name">
                         
                            <?php
                            if(isset($eachProgram['program_cover_media']) && !empty($eachProgram['program_cover_media'])) {
                            ?>
                            
                          <img src="{{ URL::to('media_image/'.$eachProgram['program_cover_media'].'/?thumb=178x114') }}" alt="Program" class="packet-img img-responsive">
                            <?php
                            }
                            else
                            {
                            ?>
                          @if($eachProgram['program_type'] == "product")
                          <img alt="Channel Name" class="img-responsive margin-bottom-10 catalog-img" src="{{URL::to('portal/theme/default/img/default_product.png')}}">
                          @else
                          <img alt="Channel Name" class="img-responsive margin-bottom-10 catalog-img" src="{{URL::to('portal/theme/default/img/default_channel.png')}}">
                          @endif
                          <?php
                            }
                            ?>
                             @if(isset($eachProgram['program_sub_type']) && $eachProgram['program_sub_type']=='collection')
                            <div class="pack-type">
                            <span class="label label-success">{{Lang::get('program.pack')}}</span>
                            </div>
                            @endif 
                                  </a>
                                    
                                </figure>
                                <div>
                                  <p class="packet-title">
                                    <a href="<?php echo URL::to("catalog/course/{$eachProgram['program_slug']}");?>">
                                      <strong>
                                        {{str_limit($eachProgram['program_title'], $limit = 40, $end = '...')}}                             
                                      </strong>
                                    </a>
                                  </p>
                                 
                                  <p class="packet-data">
                                    <a href="<?php echo URL::to("catalog/course/{$eachProgram['program_slug']}");?>" class="btn btn-success btn-sm">{{Lang::get('catalog/template_two.learn_now')}}</a>
                                  </p>
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
          ?><!--ENd Packets div-->
      </div><!--facets data div-->
      <?php } ?>
    </div>
  </div>
</div>
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
      random();
    });
</script>
@stop