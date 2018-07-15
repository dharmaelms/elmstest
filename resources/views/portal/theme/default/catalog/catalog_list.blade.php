@section('content')
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
  .page-bar {
    margin-left: 0;
    margin-right: 0;
  }
  .name_indent
  {
    text-indent: 10px;
  }
  .search_btn {
    background: #eeeeee none repeat scroll 0 0;
    border: 0 none;
    color: #cccccc;
    cursor: pointer;
    display: inline-block;
    float: right;
    height: 34px;
    margin: 1px -4px 0 10px;
    padding: 2px 8px;
    position: absolute;
    right: 5px;
    text-align: center;
    width: 44px;
    z-index: 3
  }
</style>

@if(!is_null($category_list))
<!-- BEGIN SLIDER -->
<div class="page-slider ">
  <div class="fullwidthbanner-container revolution-slider margin-bottom-40">
    <div class="fullwidthbanner1">
      <ul id="revolutionul">
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
<!--BEGIN Main Container -->
<div class="catalog-view1">
  <div class="container">
    <div class="row">
        <div class="col-md-4 col-sm-5 col-xs-12">
            <div class="form-group search-form">
                <div class="input-icon right">
                    <a class="search_btn">
                        <img id="search_icon" src="../portal/theme/default/img/icons/search-icon.png" class="filter-items-in-catalog" alt="Search icon">
                    </a>
                    <input type="text" class="form-control inline-display" placeholder="Search Courses" name="search_item" id="catalog_search" value="{{Input::get('search_item')}}">
                    <input type="hidden" name="page_type" value="catalog">
                </div>
          </div>    
      </div>
        <div class="col-md-8 col-sm-7 col-xs-12">
            <div class="pull-right margin-top-10">
                <a href="#" class="active" id="grid"><i class="sprite2 sprite-grid-icon"></i></a>&nbsp;&nbsp;
                <a href="#" id="list"><i class="sprite2 sprite-list-icon"></i></a>
            </div>
        </div>
    </div>
    <hr class="catalog-hr">
    <div class="row">
        <div class="col-md-3 col-sm-4 col-xs-12 filter-div margin-bottom-30">
            <div class="blue-bar">
                <h4 class="white font-weight-500"> {{Lang::get('catalog/template_two.filter')}} <img src="../portal/theme/default/img/icons/filter-icon1.png" alt="Filter icon" class="pull-right"></h4>
            </div>
      <div class="filter-options">
          <div class="portlet box green margin-bottom-20" id="maincontent">
            <div class="portlet-title">
                <div class="caption"> {{Lang::get('catalog/template_two.category')}}</div>
                <div class="tools"><a href="javascript:;" class="collapse"></a></div>
            </div>
            <div class="portlet-body form">
                <div class="form-group form-md-checkboxes">
                    <div class="md-checkbox-list">
                        <?php $program_found = 0; ?>
                        <ul class="category-list">
                          <li>
                              <a href="{{URL::to('catalog')}}"><i class="fa fa-angle-right"></i> <b>{{Lang::get('catalog/template_two.all')}}</b></a>
                          </li>
                            @foreach($filter_data as $parent_each_category)
                                <?php
                                if ((empty($parent_each_category->program_list) && empty($parent_each_category->package_list)) && empty(Input::get('category_name'))) {
                                    continue;
                                }
                                    $program_found = 1;
                                    
                                ?>
                                @if($parent_each_category['slug'] == Input::get('category_name') || $parent_each_category['slug'] == Input::get('category_slug'))
                                  @foreach($parent_details as $each_category)  
                                    @if($each_category['slug'] == Input::get('category_name'))  
                                      <li class="name_indent">
                                          <a data-category-slug = "{{$each_category->slug}}" class="filter-items-in-catalog">
                                              <i class="fa fa-angle-right"></i>
                                              <b>{{html_entity_decode($each_category->category_name)}}</b> 
                                          </a>
                                      </li>
                                    @endif

                                    @if($each_category->sub_category)
                                      <ul class="category-list1">
                                        @foreach($each_category->sub_category as $subcategory)
                                          <?php
                                            $child_bold = 'fa fa-angle-down';
                                            if (isset($child_slug) && $child_slug == $subcategory->slug) {
                                              $child_bold = 'fa fa-angle-right';
                                            }
                                          ?>
                                          <li>
                                              <a data-category-slug = "{{$subcategory->slug}}" class="filter-items-in-catalog">
                                              <i class="{{$child_bold}}"></i>
                                              {{html_entity_decode($subcategory->category_name)}}
                                              </a>
                                          </li>
                                        @endforeach
                                      </ul>
                                    @endif
                                  @endforeach
                                @else                              
                                <li class="name_indent">
                                    <a data-category-slug = "{{$parent_each_category->slug}}" class="filter-items-in-catalog">
                                    {{html_entity_decode($parent_each_category->category_name)}}
                                    </a>
                                </li>
                              @endif
                            @endforeach
                        </ul>
                        @if($program_found === 0)
                            {{Lang::get('catalog/template_two.no_category')}}
                        @endif
                  </div>
                </div>
            </div>
          </div>
           <div class="portlet box green margin-bottom-20">
            <div class="portlet-title">
              <div class="caption"> {{Lang::get('catalog/template_two.type')}}</div>
              <div class="tools"><a href="javascript:;" class="collapse"></a></div>
            </div>
            <?php
              $program_type = Input::get('program_type');
            if (is_array($program_type) && in_array('all', $program_type)) {
                $program_type = array_merge($program_type, ['content_feed','product','course','collection']);
            } elseif (is_array($program_type)) {
                if (sizeof($program_type) === 4) {
                    $program_type = array_merge($program_type, ['all']);
                }
            } else {
                $program_type = ['content_feed','product','course','collection','all'];
            }
            ?>
            <div class="portlet-body form">
                <div class="form-group form-md-checkboxes">
                    <div class="md-checkbox-list product-type">
                        <input type="hidden" name="category_slug" id="category_slug" value="{{$category_slug}}">
                        <input type="checkbox" name="program_type[]" id="program_type[]" class="filter-items-in-catalog" value="all" @if(is_array($program_type) && in_array('all',$program_type)) checked @endif >{{Lang::get('catalog/template_two.all')}} <br/>
                        <input type="checkbox" name="program_type[]" id="program_type[]" class="filter-items-in-catalog" value="course"  @if(is_array($program_type) && in_array('course',$program_type)) checked @endif> {{Lang::get('catalog/template_two.course')}} <br/>
                        <input type="checkbox" name="program_type[]" id="program_type[]" class="filter-items-in-catalog" value="content_feed"  @if(is_array($program_type) && in_array('content_feed',$program_type)) checked @endif> {{Lang::get('catalog/template_two.content_feed')}} <br/>
                        <input type="checkbox" name="program_type[]" id="program_type[]" class="filter-items-in-catalog" value="collection"  @if(is_array($program_type) && in_array('collection',$program_type)) checked @endif> {{Lang::get('catalog/template_two.collection')}} 
                    </div>
                </div>
            </div>
          </div>
        </div>
      </div>
      <!-- sidebar filter -->

      <div id="grid_display" class="col-md-9 col-sm-8 col-xs-12 courses-div gridview">
            <?php $program_found = 0; ?>
        @foreach($category_list as $each_category)
            @if( (isset($each_category->program_list) && !empty($each_category->program_list) ) || (isset($each_category->package_list) &&  !empty($each_category->package_list)) )

              <div class="row">
                <div class="col-md-12 margin-bottom-5">
                  <div class="blue-bar">
                    <h4 class="white font-weight-500">{{html_entity_decode($each_category->category_name)}}</h4>
                  </div>

                  <div class="row">
                    @if(isset($each_category->program_list))
                        <?php $program_found = 1; ?>
                        <?php
                        foreach ($each_category->program_list as $key => $each_program) {
                            switch ($each_program['program_type']) {
                                case 'content_feed':
                                    ?>
                                    @include('portal.theme.default.catalog.__channel',['data'=>$each_program,'category_view_type'=>'list'])
                                    <?php
                                    break;

                                case 'course':
                                    ?>
                                    @include('portal.theme.default.catalog.__course',['data'=>$each_program,'category_view_type'=>'list'])
                                    <?php
                                    break;
                            }
                        }
                        ?>
                    @endif
                    @if(isset($each_category->package_list))
                        <?php $program_found = 1; ?>
                        @foreach($each_category->package_list as $key => $each_package)
                            @include('portal.theme.default.catalog.__package',['data'=>$each_package,'category_view_type'=>'list'])
                        @endforeach
                    @endif
                    </div>
                  </div>
                </div>
              @endif
          @endforeach
          @if($program_found === 0)
             <div class="note note-success">
              <p>
                {{Lang::get('catalog/template_two.no_program')}}
              </p>
            </div>
          @endif
        </div>
      <!-- grid view -->

      <div id="list_display" class="col-md-9 col-sm-8 col-xs-12">               
          @foreach($category_list as $each_category)
          @if( (isset($each_category->program_list) && !empty($each_category->program_list) ) || (isset($each_category->package_list) &&  !empty($each_category->package_list)) )
            <div class="row">
                <div class="col-md-12">                                          
                  <div class="blue-bar">
                      <h4 class="white font-weight-500">{{html_entity_decode($each_category->category_name)}}</h4>
                  </div>
                <br/>
                </div>
                @if(isset($each_category->program_list))
                    <?php
                    foreach ($each_category->program_list as $key => $each_program) {
                        switch ($each_program['program_type']) {
                            case 'content_feed':
                                ?>
                                @include('portal.theme.default.catalog.__channel',['data'=>$each_program,'category_view_type'=>'grid'])
                                <?php
                                break;

                            case 'course':
                                ?>
                                @include('portal.theme.default.catalog.__course',['data'=>$each_program,'category_view_type'=>'grid'])
                                <?php
                                break;
                        }
                    }
                    ?>
                @endif

                @if(isset($each_category->package_list))
                    <?php $program_found = 1; ?>
                        @foreach($each_category->package_list as $key => $each_package)
                        @include('portal.theme.default.catalog.__package',['data'=>$each_package,'category_view_type'=>'grid'])
                    @endforeach
                @endif
              </div>
            @endif
          @endforeach
            @if($program_found === 0)
              <div class="note note-success">
                <p>
                  {{Lang::get('catalog/template_two.no_program')}}
                </p>
              </div>
            @endif
        </div>
      <!-- list view -->
    </div>
  </div>
</div>
@else
  @include('portal.theme.default.catalog.__empty_catalog')
@endif
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
<!-- Catalog Filter Form -->
  @include('portal.theme.default.catalog.__filter_form')

        <!-- Catalog Filter Form End-->
@stop
