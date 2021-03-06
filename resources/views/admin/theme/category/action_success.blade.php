@section('content')
<div class="alert alert-success" id="alert-success">
<button class="close" data-dismiss="alert">×</button>
<!-- <strong>Success!</strong><br> -->
{!! Session::get('success') !!}
<?php Session::forget('success'); ?>
<?php use App\Model\Common; ?>
</div>
   <link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
        <script src='//cdn.datatables.net/1.10.5/js/jquery.dataTables.min.js'></script>
<!-- BEGIN Main Content -->
<div class="row custom-box">
  <div class="col-md-4">
   <div class="box box-lightgreen"> 
    <!-- <div class="box">  -->
    <div class="box-title">
      <h3>{{ trans('admin/category.category_action') }}</h3>
    </div>
    <div class="box-content">
        @if($par_slug !='' && $cat_slug=='')
            @if(has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::EDIT_CATEGORY))
                <a class="btn btn-blue" href="{{url::to('cp/categorymanagement/add-children/'.$par_slug)}}">
                    {{ trans('admin/category.add_a_sub_cat') }}
                </a>
            @endif
            @if(has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::ASSIGN_CHANNEL))
                <a href="{{url::to("/cp/contentfeedmanagement/list-feeds?filter=ACTIVE&subtype=single&view=iframe&from=category&relid={$cat_id}")}}" class='btn btn-blue triggermodal' data-info='feed'>{{ trans('admin/category.assign_channel_to_category') }}</a>
            @endif
        @else

            @if(has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::LIST_CATEGORY))
                <a class="btn btn-blue" href="{{url::to('cp/categorymanagement/categories')}}">
                    {{ trans('admin/category.view_all_category') }}
                </a>
            @endif

            @if(has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::EDIT_CATEGORY))
                <a class="btn btn-blue" href="{{{ URL::to('cp/categorymanagement/categories/'.$par_slug) }}}">
                    {{ trans('admin/category.view_all_sub_cat') }}
                </a>
            @endif

            @if(has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::ADD_CATEGORY))
                <a class="btn btn-blue" href="{{url::to('cp/categorymanagement/add-parent')}}">
                    {{ trans('admin/category.add_another_category') }}
                </a>
            @endif

            @if(has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::EDIT_CATEGORY))
                <a class="btn btn-blue" href="{{url::to('cp/categorymanagement/add-children/'.$par_slug)}}">
                    {{ trans('admin/category.add_another_sub_cat') }}
                </a>
            @endif
        @endif
    </div>
    <!-- </div> -->
      </div>
    </div>

    <div class="col-md-4">
      <div class="box box-lightgray">
        <div class="box-title">
          <h3>{{ trans('admin/category.additional_actions') }}</h3>
        </div>         
          <div class="box-content">     
           @if($par_slug != '' && $cat_slug=='')
              @if(has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::ADD_CATEGORY))
                  <a class="btn btn-lightblue" href="{{url::to('cp/categorymanagement/add-parent')}}">
                      {{ trans('admin/category.add_another_category') }}
                  </a>
              @endif

              @if(has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::LIST_CATEGORY))
                  <a class="btn btn-lightblue" href="{{url::to('cp/categorymanagement/categories')}}">
                      {{ trans('admin/category.view_all_category') }}
                  </a>
              @endif

            @else
              @if(has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::ASSIGN_CHANNEL))
                  <a  class='btn btn-blue triggermodal' data-info='feed'
                      href="{{url::to("/cp/contentfeedmanagement/list-feeds?filter=ACTIVE&subtype=single&view=iframe&from=category&relid={$cat_id}")}}">
                      {{ trans('admin/category.assign_channel_to_category') }}
                  </a>
              @endif
             @endif
          </div>      
      </div>
    </div>
  </div>
 <div class="modal fade" id="triggermodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row custom-box">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-title">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h3 class="modal-header-title">
                                    <i class="icon-file"></i>
                                        {{ trans('admin/category.assign_channel') }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                ...
            </div>
            <div class="modal-footer">
                <div style="float: left;" id="selectedcount"> 0 selected</div>
                <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{ trans('admin/category.assign') }}</a>
                <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{ trans('admin/category.close') }}</a>
            </div>
        </div>
    </div>
</div>
<!-- END Main Content -->
<script>
  var $key = '{{$cat_id}}';
  $(document).ready(function(){
    $('#alert-success').delay(5000).fadeOut();
    $('.triggermodal').click(function(e){
      e.preventDefault();
      simpleloader.fadeIn();
      var $this = $(this);
      var $triggermodal = $('#triggermodal');
      var $iframeobj = $('<iframe src="'+$this.attr('href')+'" width="100%" height="" frameBorder="0"></iframe>');
      
      $iframeobj.unbind('load').load(function(){
      $('#selectedcount').text('0 selected');

        if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
          $triggermodal.modal('show');
        simpleloader.fadeOut();
         var checkeditems = JSON.stringify($('.triggermodal').data('json'));                            
              if(checkeditems){
                checkeditems = JSON.parse(checkeditems);
                for(var x in checkeditems)
                  $iframeobj.get(0).contentWindow.checkedBoxes[x] = "";                    
              }
              
      $iframeobj.contents().click(function(){
         setTimeout(function(){
                 $('#selectedcount').text(Object.keys($iframeobj.get(0).contentWindow.checkedBoxes).length+ ' Entrie(s) selected');
         },10);
      });
      $('#selectedcount').text(Object.keys($iframeobj.get(0).contentWindow.checkedBoxes).length+ ' Entrie(s) selected');
      /* Code to refresh selected count ends here*/
      })
      $triggermodal.find('.modal-body').html($iframeobj);
      $triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.text());

      //code for top assign button click
                    $('.modal-assign .btn-success',$triggermodal).unbind('click').click(function(){
                        $(this).parents().find('.modal-footer .btn-success').click();
                    });
                    //code for top assign button ends here


      $('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
        var $checkedboxes = $iframeobj.get(0).contentWindow.checkedBoxes;        
                 
        if(!$.isEmptyObject($checkedboxes)){
          var $postdata = "";
          $.each($checkedboxes,function(index,value){
            if(!$postdata){
              $postdata += index;
            }
            else{
              $postdata += "," + index;
            }

          });

          // Post to server
          var action = $this.data('info');

          simpleloader.fadeIn();
          $.ajax({
          type: "POST",
          url: '{{URL::to('/cp/categorymanagement/assign-feed/')}}/'+action+'/'+$key,
          data: 'ids='+$postdata
        })
        .done(function( response ) {
          $('.alert-success').hide();
          if(response.flag == "success"){
            $this.data('json',$checkedboxes);
            $('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button>'+response.message+'</div>').insertAfter($('.page-title'));
            }
          else
            $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button>Error while fetching data from server. Please try again later.</div>').insertAfter($('.page-title'));
          $triggermodal.modal('hide');
          setTimeout(function(){
           $('.alert').alert('close');
          },5000);
          simpleloader.fadeOut(200);
        })
        .fail(function() {
          $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button>Error while fetching data from server. Please try again later.</div>').insertAfter($('.page-title'));
          simpleloader.fadeOut(200);
        })
        }
        else{
          alert('Please select atleast one entry');
        }
      })
    });



  window.onload = function(){
      (function(){var e={defaults:{id:"simpleloader",init:false,opacity:.5,autoopen:false,selector:false},init:function(e){if(typeof jQuery=="undefined"){this.log("jQuery is not found. Make sure you have jquery included");return false}else if(typeof $=="undefined"){$=jQuery}$.fn.extend({exists:function(){return this.length!==0}});if(!this.isEmpty(e)){this.updatedefaults(e)}if(!$("#"+this.defaults.id).exists()){var e=this.defaults.selector?this.defaults.selector:$("body");e.prepend('<div class="'+this.defaults.id+'" style="display:none;position:absolute;width:100%;top:0;background-color:black;height:100%;left:0;z-index:99999;opacity:'+this.defaults.opacity+';" ></div>');e.prepend('<div class="'+this.defaults.id+' progress progress-striped active" style="display:none; position: fixed !important; width: 16%; z-index: 100000; height: 20px;margin: auto; left: 0; top: 0; right: 0; bottom: 0; "> <div style="width: 100%;" class="progress-bar progress-bar-success">Loading...</div> </div>');this.defaults.init=true;if(this.autoopen){this.fadeIn()}}},show:function(e){if(this.defaults.init)$("."+this.defaults.id).show(e);else this.log("Please initialize the loader using simpleloader.init()")},hide:function(e){if(this.defaults.init)$("."+this.defaults.id).hide(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeIn:function(e){this.fadein(e)},fadeOut:function(e){this.fadeout(e)},fadein:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeIn(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeout:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeOut(e);else this.log("Please initialize the loader using simpleloader.init()")},remove:function(){$("."+this.defaults.id).remove();this.defaults.init=false},log:function(e){if(window.console)console.log(e)},updatedefaults:function(e){for(var t in e){this.defaults[t]=e[t]}},isEmpty:function(e){for(var t in e){if(e.hasOwnProperty(t))return false}return true}};window.simpleloader=e})();
      simpleloader.init();
    }
  })
</script>
@stop