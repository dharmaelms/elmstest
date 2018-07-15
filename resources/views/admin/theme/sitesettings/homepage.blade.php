@if(isset($homepage_info))
<style type="text/css">
    #homepage .nav-tabs>li>a {    
        padding: 6px 8px;
        border-radius: 0;
        color: white;
    }
    #homepage .nav-tabs>li.active>a {     
        color: #444;
        text-shadow: none;
        border-color: transparent; 
    }
    #homepage .nav-tabs { padding-left: 20px; }
</style>

<?php 
$input = Input::old();
// echo '<pre>';print_r($input);die;
    $enabled = "upcomingcourses";

    if(Session::get('popularcourses'))
    {
        $enabled = "popularcourses";
    }

    if(Session::get('upcomingcourses'))
    {
        $enabled = "upcomingcourses";
    }
     if(Session::get('quotes'))
    {
        $enabled = "quotes";
    }
?>

<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs" style=" background: #b6d1f2;">
            <li class="<?php if($enabled === 'upcomingcourses'){ echo "active";} ?>"><a href="#upcomingcourses" data-toggle="tab">{{ trans('admin/sitesetting.upcoming_courses') }}</a></li>
            <li class="<?php if($enabled === 'popularcourses'){ echo "active";} ?>"><a href="#popularcourses" data-toggle="tab">{{ trans('admin/sitesetting.popular_courses') }}</a></li>
            <li class="<?php if($enabled === 'quotes'){ echo "active";} ?>"><a href="#quotes" data-toggle="tab">Quotes</a></li>
        </ul>
            
        <div class="tab-content">
            <div class="tab-pane <?php if($enabled && $enabled==='upcomingcourses'){ echo " active in";}?>" id="upcomingcourses">
                <form action="{{URL::to('cp/sitesetting/save-homepage/upcomingcourses')}}" method="post" accept-charset="utf-8" class="form-horizontal form-bordered form-row-stripped">
                    <?php
                        if($enabled == 'upcomingcourses' && Input::old('enabled')) 
                        {
                            $ischecked = Input::old('enabled');
                        } 
                        elseif($enabled == 'upcomingcourses' && $errors->first('enabled')) 
                        {
                            $ischecked = Input::old('enabled');
                        } 
                        elseif(isset($homepage_info['setting']['UpcomingCourses']['enabled']) && !empty($homepage_info['setting']['UpcomingCourses']['enabled'])) 
                        {
                            $ischecked = $homepage_info['setting']['UpcomingCourses']['enabled'];
                        }
                        else
                        {
                            $ischecked = "on";
                        }
                    ?>
                    <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">{{trans('admin/sitesetting.display_enabled')}}</label>
                        <div class="col-sm-6 col-lg-4 controls">                               
                             <input type="checkbox" value="{{$ischecked}}" name="enabled" <?php echo $ischecked=="on"?"checked":"";?> >
                        </div>
                    </div>
                    <div class="form-group">
                        <?php 
                            if($enabled == 'upcomingcourses' && Input::old('display_name')) 
                            {
                                $display_name = Input::old('display_name');
                            } 
                            elseif($enabled == 'upcomingcourses' && $errors->first('display_name')) 
                            {
                                $display_name = Input::old('display_name');
                            } 
                            elseif(isset($homepage_info['setting']['UpcomingCourses']['display_name']) && !empty($homepage_info['setting']['UpcomingCourses']['display_name'])) 
                            {
                                $display_name = $homepage_info['setting']['UpcomingCourses']['display_name'];
                            }
                            else
                            {
                                $display_name = "";
                            }
                        ?>
                        <label class="col-sm-6 col-lg-6 control-label">{{trans('admin/sitesetting.display_name')}}</label>
                        <div class="col-sm-6 col-lg-4 controls">
                            <input type="text" value="{{$display_name}}" name="display_name"> <br>
                            @if($enabled == 'upcomingcourses')
                                {!! $errors->first('display_name', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <?php 
                            if($enabled == 'upcomingcourses' && Input::old('records_per_course')) 
                            {
                                $records_per_course = Input::old('records_per_course');
                            } 
                            elseif($enabled == 'upcomingcourses' && $errors->first('records_per_course')) 
                            {
                                $records_per_course = Input::old('records_per_course');
                            } 
                            elseif(isset($homepage_info['setting']['UpcomingCourses']['records_per_course']) && !empty($homepage_info['setting']['UpcomingCourses']['records_per_course'])) 
                            {
                                $records_per_course = $homepage_info['setting']['UpcomingCourses']['records_per_course'];
                            }
                            else
                            {
                                $records_per_course = '';
                            }
                        ?>
                        <label class="col-sm-6 col-lg-6 control-label">{{trans('admin/sitesetting.records_per_course')}}</label>
                        <div class="col-sm-6 col-lg-4 controls">
                            <input type="text" value="{{$records_per_course}}" name="records_per_course"><br>
                            @if($enabled == 'upcomingcourses')
                                {!! $errors->first('records_per_course', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <?php 
                            if(Input::old('configuration')) 
                            {
                                $configuration = Input::old('configuration');
                            } 
                            elseif($errors->first('configuration')) 
                            {
                                $configuration = Input::old('configuration');
                            } 
                            elseif(isset($homepage_info['setting']['UpcomingCourses']['configuration']) && !empty($homepage_info['setting']['UpcomingCourses']['configuration'])) 
                            {
                                $configuration = $homepage_info['setting']['UpcomingCourses']['configuration'];
                            }
                            else
                            {
                                $configuration = "automated";
                            }
                        ?>
                        <label class="col-sm-6 col-lg-6 control-label">{{trans('admin/sitesetting.configuration')}}</label>
                        <div class="col-sm-6 col-lg-4 controls">
                            <label class="radio-inline">
                              <input type="radio" name="configuration" class="configuration" data-value="automated" value="automated" <?php echo $configuration=="automated"?"checked":"";?>/> {{trans('admin/sitesetting.configurationvalue1')}}
                            </label>
                            <label class="radio-inline">
                              <input type="radio" name="configuration" class="configuration" data-value="manual" value="manual" <?php echo $configuration=="manual"?"checked":"";?> /> {{trans('admin/sitesetting.configurationvalue2')}}
                            </label>  
                        </div>
                    </div>
                    <div id="config_div" @if($configuration == 'manual') class="hidden" @endif>
                        <div class="form-group">
                            <?php 
                                if(Input::old('duration_in_days')) 
                                {
                                    $duration_in_days = Input::old('duration_in_days');
                                } 
                                elseif($errors->first('duration_in_days')) 
                                {
                                    $duration_in_days = Input::old('duration_in_days');
                                } 
                                elseif(isset($homepage_info['setting']['UpcomingCourses']['duration_in_days']) && !empty($homepage_info['setting']['UpcomingCourses']['duration_in_days'])) 
                                {
                                    $duration_in_days = $homepage_info['setting']['UpcomingCourses']['duration_in_days'];
                                }
                                else
                                {
                                    $duration_in_days = 30;
                                }
                            ?>
                            <label class="col-sm-6 col-lg-6 control-label">{{trans('admin/sitesetting.duration_in_days')}}</label>
                            <div class="col-sm-6 col-lg-4 controls">
                                <input type="text" value="{{$duration_in_days}}" name="duration_in_days">  <br>
                                {!! $errors->first('duration_in_days', '<span class="help-inline" style="color:#f00">:message</span>') !!}                           
                            </div>
                        </div>
                        <div class="form-group">
                            <?php 
                                if(Input::old('type')) 
                                {
                                    $type = Input::old('type');
                                } 
                                elseif($errors->first('type')) 
                                {
                                    $type = Input::old('type');
                                } 
                                elseif(isset($homepage_info['setting']['UpcomingCourses']['type']) && !empty($homepage_info['setting']['UpcomingCourses']['type'])) 
                                {
                                    $type = $homepage_info['setting']['UpcomingCourses']['type'];
                                }
                                elseif($homepage_info['setting']['UpcomingCourses']['configuration'] == 'manual')
                                {
                                    $type = ['channels', 'products', 'packages', 'course'];
                                } else{
                                    $type = [];
                                }
                            ?>
                            <label class="col-sm-6 col-lg-6 control-label">{{trans('admin/sitesetting.type')}}</label>
                            <div class="col-sm-6 col-lg-5 controls">
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="type[]" value="channels" <?php echo $value = in_array('channels', $type) ? "checked" : "" ; ?> /> {{trans('admin/sitesetting.typevalue1')}}
                                </label>
                                <!-- <label class="checkbox-inline">
                                    <input type="checkbox" name="type[]" value="products" <?php echo $value = in_array('products', $type) ? "checked" : "" ; ?> /> {{trans('admin/sitesetting.typevalue2')}}
                                </label> -->
                                @if(config('app.ecommerce') === true)
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="type[]" value="packages" <?php echo $value = in_array('packages', $type) ? "checked" : "" ; ?> /> {{trans('admin/sitesetting.typevalue3')}}
                                </label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="type[]" value="course" <?php echo $value = in_array('course', $type) ? "checked" : "" ; ?> /> {{trans('admin/sitesetting.typevalue4')}}
                                </label>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-6">
                           <button type="submit" class="btn btn-info text-right">{{ trans('admin/sitesetting.update') }} </button>
                            <a href="{{URL::to('/cp/sitesetting/')}}" >
                                <button type="button" class="btn">{{ trans('admin/sitesetting.cancel') }}</button>
                            </a> 
                        </div>
                    </div>
                </form>
            </div>
            <div class="tab-pane <?php if($enabled && $enabled==='popularcourses'){ echo " active in";}?>" id="popularcourses">
                <form action="{{URL::to('cp/sitesetting/save-homepage/popularcourses')}}" method="post" accept-charset="utf-8" class="form-horizontal form-bordered form-row-stripped">
                    <?php
                        if($enabled == 'popularcourses' && Input::old('enabled')) 
                        {
                            $ischecked = Input::old('enabled');
                        } 
                        elseif($enabled == 'popularcourses' && $errors->first('enabled')) 
                        {
                            $ischecked = Input::old('enabled');
                        } 
                        elseif(isset($homepage_info['setting']['PopularCourses']['enabled']) && !empty($homepage_info['setting']['PopularCourses']['enabled'])) 
                        {
                            $ischecked = $homepage_info['setting']['PopularCourses']['enabled'];
                        }
                        else
                        {
                            $ischecked = "on";
                        }
                    ?>
                    <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">{{trans('admin/sitesetting.display_enabled')}}</label>
                        <div class="col-sm-6 col-lg-4 controls">                               
                             <input type="checkbox" value="{{$ischecked}}" name="enabled" <?php echo $ischecked=="on"?"checked":"";?> >
                        </div>
                    </div>
                    <div class="form-group">
                        <?php 
                            if($enabled == 'popularcourses' && Input::old('display_name')) 
                            {
                                $display_name = Input::old('display_name');
                            } 
                            elseif($enabled == 'popularcourses' && $errors->first('display_name')) 
                            {
                                $display_name = Input::old('display_name');
                            } 
                            elseif(isset($homepage_info['setting']['PopularCourses']['display_name']) && !empty($homepage_info['setting']['PopularCourses']['display_name'])) 
                            {
                                $display_name = $homepage_info['setting']['PopularCourses']['display_name'];
                            }
                            else
                            {
                                $display_name = "";
                            }
                        ?>
                        <label class="col-sm-6 col-lg-6 control-label">{{trans('admin/sitesetting.display_name')}}</label>
                        <div class="col-sm-6 col-lg-4 controls">
                            <input type="text" value="{{$display_name}}" name="display_name"> <br>
                            @if($enabled == 'popularcourses')
                                {!! $errors->first('display_name', '<span class="help-inline" style="color:#f00">:message</span>') !!}                            
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <?php 
                            if($enabled == 'popularcourses' && Input::old('records_per_course')) 
                            {
                                $records_per_course = Input::old('records_per_course');
                            } 
                            elseif($enabled == 'popularcourses' && $errors->first('records_per_course')) 
                            {
                                $records_per_course = Input::old('records_per_course');
                            } 
                            elseif(isset($homepage_info['setting']['PopularCourses']['records_per_course']) && !empty($homepage_info['setting']['PopularCourses']['records_per_course'])) 
                            {
                                $records_per_course = $homepage_info['setting']['PopularCourses']['records_per_course'];
                            }
                            else
                            {
                                $records_per_course = '';
                            }
                        ?>
                        <label class="col-sm-6 col-lg-6 control-label">{{trans('admin/sitesetting.records_per_course')}}</label>
                        <div class="col-sm-6 col-lg-4 controls">
                            <input type="text" value="{{$records_per_course}}" name="records_per_course"><br>
                            @if($enabled == 'popularcourses')
                                {!! $errors->first('records_per_course', '<span class="help-inline" style="color:#f00">:message</span>') !!}                           
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-6">
                           <button type="submit" class="btn btn-info text-right">{{ trans('admin/sitesetting.update') }} </button>
                            <a href="{{URL::to('/cp/sitesetting/')}}" >
                                <button type="button" class="btn">{{ trans('admin/sitesetting.cancel') }}</button>
                            </a> 
                        </div>
                    </div>
                </form>
            </div>
            <div class="tab-pane <?php if($enabled && $enabled==='quotes'){ echo " active in";}?>" id="quotes">
                <form action="{{URL::to('cp/sitesetting/save-homepage/quotes')}}" method="post" accept-charset="utf-8" class="form-horizontal form-bordered form-row-stripped">
                    <?php
                        if($enabled == 'quotes' && Input::old('quotes_enable'))
                        {
                          $quotes_enable = Input::old('quotes_enable');
                        }
                        elseif(isset($homepage_info['setting']['Quotes']['quotes_enable']))
                        {
                          $quotes_enable = $homepage_info['setting']['Quotes']['quotes_enable'];
                        }
                        else
                        {
                          $quotes_enable = "on";
                        }
                    ?>
                    <div class="form-group">
                            <label class="col-sm-6 col-lg-6 control-label">{{trans('admin/sitesetting.quotes_enable')}}</label>
                            <div class="col-sm-6 col-lg-4 controls">                               
                               <input type="checkbox" value="on" name="quotes_enable" <?php echo $quotes_enable=="on"?"checked":"";?> > 
                                {!! $errors->first('quotes_enable', '<span class="help-inline" style="color:#f00">:message</span>') !!}  
                            </div>
                    </div>
                    <?php
                        if ($enabled == 'quotes' && Input::old('quotes_label'))
                        {
                          $quotes_label = Input::old('quotes_label');
                        }
                        elseif ($enabled == 'quotes' && $errors->first('quotes_label'))
                        {
                          $quotes_label = Input::old('quotes_label');
                        }    
                        elseif (isset($homepage_info['setting']['Quotes']['label']))
                        {
                          $quotes_label = $homepage_info['setting']['Quotes']['label'];
                        }
                        else
                        {
                          $quotes_label = "";
                        }
                    ?>
                    <div class="form-group">
                            <label class="col-sm-6 col-lg-6 control-label">{{trans('admin/sitesetting.quotes_label')}}</label>
                            <div class="col-sm-6 col-lg-4 controls">                               
                                <input type="text" class="form-control" name="quotes_label" value="{{ $quotes_label }}"> 
                                {!! $errors->first('quotes_label', '<span class="help-inline" style="color:#f00">:message</span>') !!}  
                            </div>
                    </div>
                    <?php
                        if ($enabled == 'quotes' && Input::old('quotes_display_no'))
                        {
                          $quotes_display_no = Input::old('quotes_display_no');
                        }
                        elseif ($enabled == 'quotes' && $errors->first('quotes_display_no'))
                        {
                          $quotes_display_no = Input::old('quotes_display_no');
                        }
                        elseif (isset($homepage_info['setting']['Quotes']['number_of_quotes_display']))
                        {
                          $quotes_display_no = $homepage_info['setting']['Quotes']['number_of_quotes_display'];
                        }
                        else
                        {
                          $quotes_display_no = "";
                        }
                    ?>
                    <div class="form-group">
                            <label class="col-sm-6 col-lg-6 control-label">{{trans('admin/sitesetting.quotes_display_no')}}</label>
                            <div class="col-sm-6 col-lg-4 controls">                               
                                <input type="text" class="form-control" name="quotes_display_no" value="{{ $quotes_display_no }}"> 
                                {!! $errors->first('quotes_display_no', '<span class="help-inline" style="color:#f00">:message</span>') !!} 
                            </div>
                    </div>
                    <?php
                        if($enabled == 'quotes' && Input::old('description_chars'))
                        {
                          $description_chars = Input::old('description_chars');
                        }
                        elseif ($enabled == 'quotes' && $errors->first('description_chars'))
                        {
                          $description_chars = Input::old('description_chars');
                        }
                        elseif(isset($homepage_info['setting']['Quotes']['description_chars']))
                        {
                          $description_chars = $homepage_info['setting']['Quotes']['description_chars'];
                        }
                        else
                        {
                          $description_chars = "";
                        }
                    ?>
                    <div class="form-group">
                            <label class="col-sm-6 col-lg-6 control-label">{{trans('admin/sitesetting.description_chars')}}</label>
                            <div class="col-sm-6 col-lg-4 controls">                               
                                <input type="text" class="form-control" name="description_chars" value="{{ $description_chars }}"> 
                                {!! $errors->first('description_chars', '<span class="help-inline" style="color:#f00">:message</span>') !!} 
                            </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-6">
                           <button type="submit" class="btn btn-info text-right">{{ trans('admin/sitesetting.update') }} </button>
                            <a href="{{URL::to('/cp/sitesetting/')}}" >
                                <button type="button" class="btn">{{ trans('admin/sitesetting.cancel') }}</button>
                            </a> 
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@else
    <p>{{ trans('admin/sitesetting.please_update_your_seeder') }}</p>
@endif
<script type="text/javascript">
    $(document).on('change','.configuration',function(e){
        var $this = $(this);
        var val = $this.data('value');
        if(val == 'automated')
        {
            $("#config_div").removeClass("hidden").addClass("visible");
        }
        else
        {
            $("#config_div").removeClass("visible").addClass("hidden");
        }
    });
</script>
