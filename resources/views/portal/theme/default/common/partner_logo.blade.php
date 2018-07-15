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
      background-color: white !important;
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
  .row  {
        margin-right: -15px;
        margin-left: -15px;
        padding-left: 90px;
        position: relative;
        margin: 0px;
  }
</style>
	<div class="container sm-margin">
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-12 col-xs-12 col-sm-12 center">
                  <h2 class="font-weight-500 black myellow-border margin-bottom-30">{{ trans('partnerlogo.partners') }}</h2>
                </div>
               <!-- <h3><center>Partners</center></h3> -->
                   </div>
                    <?php
                    $i = 0;
                    foreach ($partners as $partner) {
                        $i++;
                        $logo_name = $partner['partner_diamension'];
                        $logo =  config('app.partner_logo_path').basename($logo_name);
                    ?>
                      <div class="col-md-3 col-sm-4 col-xs-4 center">
                        <div class="brand-img">
                            <a href="#" data-toggle="modal" data-target="#viewModal{{ $i }}"><img alt="Corporate Partner" src="{{ URL::to($logo) }}" class="pro-img"></a>
                        </div>
                        <p class="packet-title"><a href="#" data-toggle="modal" data-target="#viewModal{{ $i }}"><strong>{{ $partner['partner_name'] }}</strong></a></p>
                      </div>
                      <!-- View window -->
                            <div id="viewModal{{ $i }}" class="modal fade">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <!--header-->
                                        <div class="modal-header">
                                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                          <h1 class="margin-top-0"><i class="icon-file"></i>{{ $partner['partner_name'] }}</h1>        
                                        </div>
                                        <!--content-->
                                        <div class="modal-body padding-20 word-wrap">               
                                           @if(isset($partner['partner_description']) && !empty($partner['partner_description']))
                                           <p><h4>{{ trans('partnerlogo.partner_desc') }} :</h4>
                                           {{ $partner['partner_description'] }}</p>
                                           @else
                                           <p class="center gray">{{ trans('partnerlogo.no_description') }}</p>
                                           @endif
                                        </div>
                                        <!--footer-->
                                        <div class="modal-footer">
                                          <a class="btn btn-success" data-dismiss="modal">{{ trans('partnerlogo.close') }}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- View window ends -->
                <?php } ?>
    
          </div>
        </div>
          
@stop
