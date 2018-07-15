
	<?php if(isset($staticpage)){
			$title=$staticpage->title;
			$metakey=$staticpage->metakey;
			$meta_description=$staticpage->meta_description;
			$slug =$staticpage->_id;
            $content=$staticpage->content;
		}
        else{
            echo "Improper selection Static Page";
        }
?>
	
    <div class="form-wrapper form-horizontal form-bordered form-row-stripped">
        <div class="box">
            <div class="box-title">
                <!-- <h3 style="color:black"><i class="fa fa-file"></i> View Static page</h3>    -->
                <!-- <a href="{{URL::to('cp/manageweb/static-pages')}}" title="go to manage page"><button class="close" >Back</button></a>           
                <span class="close" style="Color:black">|</span>
                <a href="{{URL::to('cp/manageweb/edit-static-page/'.$slug)}}" title="{{trans('admin/manageweb.edit')}}"><button class="close" >{{trans('admin/manageweb.edit')}}</button></a>            -->
            </div>
            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label"> {{trans('admin/manageweb.title')}}</label>
                 <div class="col-sm-9 col-lg-10 controls">
                   <span style="color:black;font-size:15px">{{$title}}</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label"> {{trans('admin/manageweb.meta_key')}}</label>
                 <div class="col-sm-9 col-lg-10 controls">
                   <span style="color:black">{{$metakey}}</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label"> {{trans('admin/manageweb.meta_description')}}</label>
                 <div class="col-sm-9 col-lg-10 controls">
                   <span style="color:black">{{$meta_description}}</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label"> {{trans('admin/manageweb.content')}}</label>
                 <div class="col-sm-9 col-lg-10 controls">
                   <span style="color:black"><?php print_r($content);?></span>
                </div>
            </div>
        </div>     
</div>

