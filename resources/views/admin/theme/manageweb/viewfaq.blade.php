
	<?php if(isset($faq)){
			$question=$faq->question;
			$answer=$faq->answer;
            $status=$faq->status;
		}
        else{
            echo "Improper selection Static Page";
        }
     
?>
	
    <div class="form-wrapper form-horizontal form-bordered form-row-stripped">
        <div class="box">
            <div class="box-title">
                <!-- <h3 style="color:black"><i class="fa fa-file"></i> View Faq</h3>   
                <span class="close" style="Color:black">|</span> -->
            </div>
            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label"> {{trans('admin/manageweb.title')}}</label>
                 <div class="col-sm-9 col-lg-10 controls">
                   <span style="color:black;font-size:15px">{{$question}}</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label"> {{trans('admin/manageweb.meta_key')}}</label>
                 <div class="col-sm-9 col-lg-10 controls">
                   <span style="color:black">{!! $answer !!}</span>
                </div>
            </div>
             <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label"> {{trans('admin/manageweb.status')}}</label>
                 <div class="col-sm-9 col-lg-10 controls">
                   <span style="color:black">{{$status}}</span>
                </div>
            </div>
        </div>     
</div>

