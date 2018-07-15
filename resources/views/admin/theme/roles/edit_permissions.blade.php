@section('content')
@if ( Session::get('success') )
  <div class="alert alert-success">
  <button class="close" data-dismiss="alert">×</button>
  <!-- <strong>Success!</strong><br> -->
  {{ Session::get('success') }}
  </div>
  <?php Session::forget('success'); ?>
@endif
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-title">
                <h3><i class="icon-file"></i>{{ trans('admin/role.edit_permission') }}</h3>
            </div>
            <div class="box-content">
                <div class="col-md-2" style="text-align:center">
                  <h4>{{ trans('admin/role.modules') }}</h4>
                </div>
                <?php if(!empty($data['admin_capabilities'])){ ?>
                <div class="col-md-5">
                  <h4>{{ trans('admin/role.admin_activities') }}</h4>
                </div>
                <?php }
                if(!empty($data['portal_capabilities'])){ ?>
                <div class="col-md-5">
                  <h4>{{ trans('admin/role.portal_activities') }}</h4>
                </div>
                <?php } ?>
                <form action="{{URL::to('cp/rolemanagement/add-permissions/'.$parent_role.'/'.$id)}}" class="form-horizontal form-bordered form-row-stripped" method="post">

                <input name="action_flag" id="action_flag" type="hidden" value="edit">

                    <!-- @if(!empty($data[0]['admin_capabilities']))
                    <div class="form-group">
                        <label class="col-sm-4 col-lg-3 control-label" for="module" style="padding-bottom:10px"><b>Admin Module</b></label>                                                 
                        <label class="col-sm-6 col-lg-5 control-label" style="border-left:1px solid #eee; text-align:left;padding-bottom:10px"><b>Activities</b></label>                                          
                    </div>



                 <?php $i=0;?>
                    @foreach($data[0]['admin_capabilities'] as $modules)
                    <?php $i++; ?>
                        <div class="form-group">
                            <label class="col-sm-4 col-lg-3 control-label" for="admin_module"><input type="checkbox" name="admin_module[]" value="{{str_replace(" ","",$modules['slug'])}}|{{$modules['module']}}" <?php if(in_array($modules['module'], $current_permissions['admin_capabilities']['module_name'])) { echo "checked"; } ?> onclick="Admincheck(this);" checked/>&nbsp;{{$modules['module']}}</label>
                            <div class="col-sm-6 col-lg-5 controls">
                                @foreach($modules['action'] as $activities)                               
                                    <?php if($activities['is_default'] == true){ $admin_default[str_replace(" ","",$modules['module'])][]=str_replace(" ","",$activities['name']); } ?>
                                    <label class="checkbox">
                                      <input type="checkbox" class="{{str_replace(" ","",'admin_'.$modules['module'])}}" name="{{str_replace(" ","",'admin_'.$modules['module'])}}[]" id="{{str_replace(" ","",'admin_'.$activities['name'])}}" value="{{$activities['name']}}|{{$activities['slug']}}|{{$activities['is_default']}}" <?php if($activities['is_default'] == true) { echo "checked"; } ?> <?php if(in_array($activities['name'], $current_permissions['admin_capabilities']['activity_name'])) { echo "checked"; } ?>/> {{$activities['name']}}
                                    </label>
                                @endforeach
                            </div>                                        
                        </div>
                    @endforeach
                    @endif
                    
                    @if(!empty($data[0]['portal_capabilities']))
                    <div class="form-group">
                        <label class="col-sm-4 col-lg-3 control-label" for="portal_module" style="padding-bottom:10px"><b>Portal Module</b></label>                                                 
                        <label class="col-sm-6 col-lg-5 control-label" style="border-left:1px solid #eee; text-align:left;padding-bottom:10px"><b>Activities</b></label>                                          
                    </div>
                     <?php $i=0; ?>

                    @foreach($data[0]['portal_capabilities'] as $modules)
                    <?php $i++; ?>
                        <div class="form-group">
                            <label class="col-sm-4 col-lg-3 control-label" for="portal_module"><input type="checkbox" name="portal_module[]" value="{{str_replace(" ","",$modules['slug'])}}|{{$modules['module']}}" <?php if(in_array($modules['module'], $current_permissions['portal_capabilities']['module_name'])) { echo "checked"; } ?> onclick="Portalcheck(this);" checked/>&nbsp;{{$modules['module']}}</label>
                            <div class="col-sm-6 col-lg-5 controls">
                                @foreach($modules['action'] as $activities)                               
                                    <?php if($activities['is_default'] == true){ $portal_default[str_replace(" ","",$modules['module'])][]=str_replace(" ","",$activities['name']); } ?>
                                    <label class="checkbox">
                                      <input type="checkbox" class="{{str_replace(" ","",'portal_'.$modules['module'])}}" name="{{str_replace(" ","",'portal_'.$modules['module'])}}[]" id="{{str_replace(" ","",'portal_'.$activities['name'])}}" value="{{$activities['name']}}|{{$activities['slug']}}|{{$activities['is_default']}}" <?php if($activities['is_default'] == true) { echo "checked"; } ?>  <?php if(in_array($activities['name'], $current_permissions['portal_capabilities']['activity_name'])) { echo "checked"; } ?> /> {{$activities['name']}}
                                    </label>
                                @endforeach
                            </div>                                        
                        </div>
                    @endforeach
                    @endif -->

                    <div class="col-md-12">
                    <?php foreach($modules as $key=>$module)
                    { 
                      ?>
                      <label class="col-sm-2 col-md-2 col-lg-2 control-label cs-label" for="admin_module"><input type="checkbox" name="modules[]" onclick="Defaultcheck(this);" id='admin_{{$key}}' value="{{$key}}|{{$module}}" <?php if(isset($current_permissions['admin_capabilities'][$module]) || isset($current_permissions['portal_capabilities'][$module])) { echo "checked"; } ?> />&nbsp;{{$module}}</label>
                      <div class="form-group col-md-10">
                      <?php
                      if(!empty($data['admin_capabilities'])){ 
                        foreach($data['admin_capabilities'] as $each)
                        {
                             if($each['module']==$module)
                             {
                                ?>
                                <?php $i=0;?>
                                <?php $i++; ?>
                                        <div class="col-sm-6 col-md-6 controls">
                                            @foreach($each['action'] as $activities)                               
                                                <?php  if($activities['is_default'] == 1){ $admin_default[$each['module']][]=$activities['name']; } ?>
                                                <label class="checkbox">
                                                  <input type="checkbox" class="{{str_replace(" ","",'admin_'.$each['module'])}}" name="{{str_replace(" ","",'admin_'.$each['module'])}}[]" id="{{'admin_'.$activities['name']}}" onclick="CheckParent('admin_{{$key}}','{{str_replace(" ","",$module)}}');" value="{{$activities['name']}}|{{$activities['slug']}}|{{$activities['is_default']}}" <?php if(isset($current_permissions['admin_capabilities'][$module])) {if(in_array($activities['name'], $current_permissions['admin_capabilities'][$module])) { echo "checked"; } }?>/> <?php if($activities['is_default'] == true) { echo '<b>'.' '.$activities['name'].'</b>'; }else{echo ' '.$activities['name'];} ?> 
                                                </label>
                                            @endforeach
                                        </div>    
                                <?php
                             }
                        }
                      }
                      if(!empty($data['portal_capabilities'])){ 
                        foreach($data['portal_capabilities'] as $each)
                        {
                             if($each['module']==$module)
                             {
                                ?>
                                <?php $i=0; ?>
                            <?php $i++; ?>
                                    <div class="col-sm-6 col-md-6 controls b-left-0">
                                        @foreach($each['action'] as $activities)                               
                                            <?php if($activities['is_default'] == 1){ $portal_default[$each['module']][]=$activities['name']; } ?>
                                            <label class="checkbox">
                                              <input type="checkbox" class="{{str_replace(" ","",'portal_'.$each['module'])}}" name="{{str_replace(" ","",'portal_'.$each['module'])}}[]" id="{{'portal_'.$activities['name']}}"  onclick="CheckParent('admin_{{$key}}','{{str_replace(" ","",$module)}}');" value="{{$activities['name']}}|{{$activities['slug']}}|{{$activities['is_default']}}" <?php if(isset($current_permissions['portal_capabilities'][$module])) { if(in_array($activities['name'], $current_permissions['portal_capabilities'][$module])) { echo "checked"; } }?>/> <?php if($activities['is_default'] == true) { echo '<b>'.$activities['name'].'</b>'; }else{echo $activities['name'];} ?> 
                                            </label>
                                        @endforeach
                                    </div>                                        
                               

                                <?php
                             }
                        }
                      }?>

                         </div>
                  <?php   } 
                   
                      ?>

                    </div>
                     <div class="form-group last">
                        <div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
                          <input type="submit" class="btn btn-info" value="Save">
                          <a class="btn" href="{{ URL::to('cp/rolemanagement/user-roles') }}">{{ trans('admin/role.cancel') }}</a>
                        </div>
                    </div>
                </form>                    
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
function CheckParent(parent,child_class)
{
  
   document.getElementById(parent).checked = true;
   var admin_count= $('input:checkbox.admin_'+child_class+':checked').length;
   var portal_count= $('input:checkbox.portal_'+child_class+':checked').length;
    if(admin_count===0 && portal_count===0)
    {
       document.getElementById(parent).checked = false;
    }
      

}
function Defaultcheck(obj)
{
  var admin_value = obj.value;
  var admin_array = admin_value.split("|"); 

  var admin_module = <?php if(!empty($admin_default)){ echo json_encode($admin_default); }else{?> [] <?php } ?>;

  if(obj.checked)
  {
    var module=admin_array[1];
    if(admin_module[module])
    {
      var admin_count=<?php if(!empty($admin_default)){?> admin_module[admin_array[1]].length; <?php } else{?> 0 <?php }?> 
     
      for(var i=0; i<admin_count; i++)
      {
        document.getElementById('admin_'+admin_module[admin_array[1]][i]).checked = true;
      }
    }
  }
  else
  {
    var admin_activity=".admin_"+admin_array[1];
   
    $(admin_activity).attr('checked', false);
    $('input:checkbox.admin_'+admin_array[1].replace(" ", "")).removeAttr('checked');
  }

  var portal_value = obj.value;
  var portal_array = portal_value.split("|");  

  if(obj.checked)
  {
      var portal_module = <?php if(!empty($portal_default)){ echo json_encode($portal_default);}else{?> 0 <?php } ?>;
      var portal_count=portal_module[portal_array[1]].length;
     
      for(var i=0; i<portal_count; i++)
      {
        document.getElementById('portal_'+portal_module[portal_array[1]][i]).checked = true;
      }
    
  }
  else
  {
      var portal_activity=".portal_"+portal_array[1].replace(" ", "");
      $(portal_activity).attr('checked', false);
  }
}
</script>
@stop
    
    