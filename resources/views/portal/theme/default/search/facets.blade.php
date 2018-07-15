@section('leftsidebar')
<div class="row">
<form  name="category" action="search-results" id="cat_feed_facet_filter" method="get" >

<ul>
       @if(isset($facets) && count($facets['category']) > 0)

        <li class="facet">
            <div class="facet-tool">  
             <h3 class="sd_big">Category</h3>
              <a data-action="collapse" href="#" class="arrow1">
              <i class="icon-angle-up"></i></a>
            </div>
            <div class="facet-content">

            <!-- BEGIN Submenu -->
            <ul id="facet">
                <?php
                // $arrPriceFacets=Input::get('priceFacets', array());
                 // if(count($arrPriceFacets)<=0 && isset($facets['Price.PriceAmount'])) 
                 //     $arrPriceFacets=$facets['Price.PriceAmount'];
               
                foreach($facets['category'] as $key => $value)
                { ?>
                   <li><input type="checkbox" class="facet" value='{{$key}}' name="category[]" <?php if( isset($facets['category_facet']) && in_array($key,$facets['category_facet'])){?> checked="checked" <?php } ?>><label>{{$key}}</label></li>
                  
                 <?php
                }
                 ?>
             </ul>
        </div>
            <!-- END Submenu -->
        </li>
       @endif

       @if(isset($facets) && count($facets['format']) > 0)

        <li class="facet">
            <div class="facet-tool">  
             <h3 class="sd_big">Format</h3>
              <a data-action="collapse" href="#" class="arrow1">
              <i class="icon-angle-up"></i></a>
            </div>
            <div class="facet-content">

            <!-- BEGIN Submenu -->
            <ul id="facet">
                <?php
                // $arrPriceFacets=Input::get('priceFacets', array());
                 // if(count($arrPriceFacets)<=0 && isset($facets['Price.PriceAmount'])) 
                 //     $arrPriceFacets=$facets['Price.PriceAmount'];
                foreach($facets['format'] as $key => $value)
                { ?>
                   <li><input type="checkbox" class="facet" value='{{$key}}' name="format[]" <?php if( isset($facets['format_facet']) && in_array($key,$facets['format_facet'])){?> checked="checked" <?php } ?>><label>{{$key}}</label></li>
                  
                 <?php
                }
                 ?>
             </ul>
        </div>
            <!-- END Submenu -->
        </li>
       @endif

    </ul>
    <!-- END Navlist -->
</div>
<input type="hidden" name="search_type" value="facet_search">
<input type="hidden" name="search" value="{{Input::get('search')}}">

</form>
<?php 
// $time_end = microtime(true);
// $time = $time_end - $time_start;
// echo "Time taken facets $time seconds\n";
?>
<script type="text/javascript">
  $('.facet').click(function(){

    $('#cat_feed_facet_filter').submit();
  }); 
</script>
@stop 
