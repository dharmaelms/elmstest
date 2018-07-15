@section('content')
<style type="text/css">
    .page-content-wrapper{
        width: 100%;
        margin:0px;
    }
    .page-content-wrapper .page-content{
        margin-left: 0px !important;
        padding: 0px !important;
    }
    .page-content{
      background-color: #212121 !important;
    }
     .brand-img {
	    background-color: #ffffff;
	    margin-bottom: 20px;
	    padding: 0;
	    text-align: center;
	}
	.brand-img img {
	    height: 100% !important;
	    margin: 0 auto;
	}
</style>
	<div class="container sm-margin home-page">
        <div class="row">
            <div class="col-md-12">
               <div class="xs-margin"></div>
               <h3><center>Partners</center></h3>
                   </div>
                    <?php
                    foreach($partners as $partner)                   
                    {
	                    $logo_name = $partner['partner_diamension'];
	                    $logo =  config('app.partner_logo_path').$logo_name;
	                ?>
	                   	<div class="col-md-3 col-sm-4 col-xs-4 center">
	                    <div class="brand-img">
	                    <img alt="Corporate Partner" src="{{ URL::to($logo) }}" class="pro-img">
	                    </div>
	                    </div>
                  	<?php } ?>
    
          </div>
        </div>
          
@stop
