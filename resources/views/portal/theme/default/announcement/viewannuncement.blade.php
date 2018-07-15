@section('content')@section('content')
	
<div class="page-content-wrapper">
        <div class="page-content">      
            <!-- BEGIN PAGE HEADER-->
            <div class="page-bar">
                <ul class="page-breadcrumb">
                    <li><a href="{{URL::to('/')}}"><?php echo Lang::get('announcement.home') ?></a><i class="fa fa-angle-right"></i></li>
                    <li><a href="{{URL::to('announcements')}}"><?php echo Lang::get('announcement.announcement') ?></a><i class="fa fa-angle-right"></i></li>
                   <li><?php
                    if(isset($announce_title) && !empty($announce_title) && $announce_title !=""){
                        ?>
                        {!! $announce_title !!}
                        <?php
                    }
                    ?>
                    </li>
                </ul>
            </div>
            
            <div class="row">
                <div class="col-md-offset-1 col-md-10 col-md-0ffset-1 col-sm-12 col-xs-12">
                    <?php
                    if(isset($announcement) && !empty($announcement)){
                        ?>
                        {!! $announcement !!}
                        <?php
                    }
                    ?>
                </div>
            </div>
            
        </div>
    </div>

        
            <div class="col-md-3 col-sm-3 col-xs-4">
                <div class="pull-right margin-top-10">
                    <a class="font-13" href="{{URL::to('announcements')}}"><?php echo Lang::get('announcement.view_all') ?></a>
                </div>
            </div>
@stop
