<?php
       
if (isset($announcement)) {
    $title=$announcement['announcement_title'];
    $announcement_content=$announcement['announcement_content'];
    $schedule=$announcement['schedule'];
    // $announcement_type=$announcement['announcement_type'];
    $slug =$announcement['announcement_id'];
    $for=$announcement['announcement_for'];
    // $target_device=$announcement['announcement_device'];
    $status=$announcement['status'];
} else {
    trans('admin/announcement.improper_selection_announce');
}

?>
<style type="text/css">
    .title{
        padding-left: 50px;
        max-width: 95% !important;
    }
    .content_area{
        padding-left: 50px;
        max-width: 95% !important;
        /*padding-right: 10px;*/
    }
   img{
     padding-right: 10px;
     max-width: 95% !important;
     max-height: 50% !important;
   }
   .media_attch{
        padding-left: 50px;
        width: 60% !important;
        height: 45% !important;
   }
   #modal .xs-margin{
    padding-left: 25px;
    padding-left: 50px;
   }
   #modal .xs-margin{
        padding-left: 45px;
    }

</style>

<div class="row custom-box">
    <div class="row">
        <div class="col-md-12">
            <div class="col-md-6">
            <table class="table table-bordered" style="table-layout: fixed;word-wrap: break-word;">
            <tr>
                <th>Announcement Title</th>
                <td>{!! ucfirst($title) !!}</td>
            </tr>
            <tr>
                <th>Content</th>
                <td>{!! html_entity_decode($announcement_content) !!}</td>
            </tr>
            <tr>
                <th>Type</th>
                <td>{!! ucfirst($announcement['announcement_type']) !!}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{!! ucfirst($announcement['status']) !!}</td>
            </tr>
            </table>
              <script type="text/javascript">
                $(document).ready(function(){
                    $('a').each(function() {
                        var a = new RegExp('/' + window.location.host + '/');
                        if(!a.test(this.href) && this.href != "javascript:;" && this.href != "#"  && this.href != "") {
                            $(this).click(function(event) {
                                event.preventDefault();
                                event.stopPropagation();
                                window.open(this.href, '_blank');
                            });
                        }
                    });
                });
                </script>
          </div>
          <div class="col-md-6">
              @if(!is_null($media))
              <div class="col-md-12 col-sm-12 col-xs-12 xs-margin"><br>
                  <h3>{{ trans('admin/announcement.attached_media') }}</h3>
                  <div align="center" class="media_attch" style="align:center">{!!$media!!}</div><br>
              </div>
              @endif
          </div>
        </div>
    </div>
</div>
