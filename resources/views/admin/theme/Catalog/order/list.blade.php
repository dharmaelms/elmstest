@section('content')
    @if ( Session::get('success') )
        <div class="alert alert-success" id="alert-success">
          <button class="close" data-dismiss="alert">×</button>
          {{ Session::get('success') }}
        </div>
    <?php Session::forget('success'); ?>
    @endif
    @if ( Session::get('error'))
        <div class="alert alert-danger">
          <button class="close" data-dismiss="alert">×</button>
          {{ Session::get('error') }}
        </div>
    <?php Session::forget('error'); ?>
    @endif

    @if ( Session::get('warning'))
        <div class="alert alert-warning">
        <button class="close" data-dismiss="alert">×</button>
        {{ Session::get('warning') }}
        </div>
        <?php Session::forget('warning'); ?>
    @endif
<?php
use App\Libraries\Timezone;
use App\Model\Program;
?>
<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
<link rel="stylesheet" type="text/css" href="{{URL::to('admin/js/bootstrap-daterangepicker/daterangepicker-bs3.css')}}" />
<script type="text/javascript" src="{{URL::to('admin/js/bootstrap-daterangepicker/moment.min.js')}}"></script>
<script type="text/javascript" src="{{URL::to('admin/js/bootstrap-daterangepicker/daterangepicker.js')}}"></script>
  <link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
  <script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
  <link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
  <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
  <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
<!-- BEGIN Main Content -->
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-title">
                <div class="box-tool">
                    <a data-action="collapse" href="#"><i class="icon-chevron-up"></i></a>
                </div>
            </div>       
            <?php 
                $date_filter = Input::get('date_filter');
                if(is_null($date_filter))
                {
                  $date_filter = Session::get('date_filter');
                }
                $filter = Input::get('type_filter'); 

                $edit = has_admin_permission(ModuleEnum::E_COMMERCE, ECommercePermission::EDIT_ORDER);
                $export = has_admin_permission(ModuleEnum::E_COMMERCE, ECommercePermission::EXPORT_ORDER);
                $view = has_admin_permission(ModuleEnum::E_COMMERCE, ECommercePermission::VIEW_ORDER);
            ?>          
            <div class="box-content"> 
                <div class="col-md-6">
                    <form class="form-horizontal" action="{{ URL::to('cp/order/list-order') }}">
                        <div class="form-group">
                          <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b>{{trans('admin/catalog.showing')}}  :</b></label>
                          <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
                            <select class="form-control chosen" name="type_filter" data-placeholder="ALL" onchange="this.form.submit();" tabindex="1">
                                <option value="" <?php if ($filter == '') echo 'selected';?>>{{trans('admin/catalog.all')}} </option>
                                <option value="PayUMoney" <?php if ($filter == 'PayUMoney') echo 'selected';?>>{{trans('admin/catalog.pay_u_money')}} </option>
                                <option value="COD" <?php if ($filter == 'COD') echo 'selected';?>>{{trans('admin/catalog.cod')}} </option>
                                <option value="FREE" <?php if ($filter == 'FREE') echo 'selected';?>>{{trans('admin/catalog.free')}} </option>
                                <option value="BANK TRANSFER" <?php if ($filter == 'BANK TRANSFER') echo 'selected';?>>{{trans('admin/catalog.bank_transfer')}} </option>
                            </select>
                          </div>
                       </div>
                       <input type="hidden" class="form-control daterange" name="date_filter" id="range" value="{{ $date_filter }}"/>
                    </form>

                </div>
                <div class="col-md-6">
                    @if($export && count($data) > 0)
                      <a class="btn btn-circle show-tooltip pull-right" title="{{ trans('admin/order.export_order') }}" href="{{ URL::to('cp/order/export-orders') }}"><i class="fa fa-sign-out"></i></a>
                    @endif
                    <form class="form-horizontal" action="{{ URL::to('cp/order/list-order') }}">
                    <div class="form-group">
                        <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:right"><b>{{trans('admin/catalog.range')}}  : &nbsp;</b></label>
                        <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
                            <div class="input-group">
                               <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
                                <input type="text" class="form-control daterange" name="date_filter" id="range" value="{{ $date_filter }}"/>
                                <input type="hidden" name="type_filter"  value="{{ $filter }}"/>
                            </div>
                        </div>
                        <div class="col-sm-3 col-lg-3 controls" style="padding-left:0;">
                            <div class="input-group">
                                <input type="submit" class="form-control btn btn-success" value="Submit" />
                            </div>
                        </div>
                    </div>
                    </form>
                      
                </div>
                <div class="btn-toolbar clearfix">
                    <div class="table-responsive">
                        <table class="table table-advance" id="sample">
                            <thead>
                                <tr>
                                    <th>{{trans('admin/catalog.order_id')}} </th>
                                    <th>{{trans('admin/catalog.items_details')}} </th>
                                    <th>{{trans('admin/catalog.payment_type')}} </th>
                                    <th>{{trans('admin/catalog.payment_status')}} </th>
                                    <th>{{trans('admin/catalog.order_status')}} </th>
                                    <th>{{trans('admin/catalog.order_on')}} </th>
                                    <th>{{trans('admin/catalog.action')}} </th>
                                </tr>
                            </thead>
                            <tbody>
                             @foreach ($data as $eachOrder)
                             <tr>
                              <?php
                              $order = $eachOrder->toArray();
                              $program=Program::getProgram($order['items_details']['p_slug']);
                              ?>
                                              <td>{{$order['order_label']}}</td>
                                              <td>
                                              <?php if(isset($program[0]['program_sub_type']) && $program[0]['program_sub_type']=='collection') { ?>
                                                  <strong>{{trans('admin/catalog.product')}}</strong> : {{$order['items_details']['p_tite']}}
                                                   <sup><b class="show-tooltip badge badge-grey badge-info" style="color:white">{{trans('admin/catalog.pack')}} </b></sup>
                                                    <br/>
                                                  <?php } else {?>
                                                   <strong>{{trans('admin/catalog.product')}}</strong> : {{$order['items_details']['p_tite']}}<br/>
                                                  <?php }?>
                                                  <strong>{{trans('admin/catalog.type')}}</strong> : {{$order['items_details']['s_title']}}<br/>
                                                  <strong>{{trans('admin/catalog.price')}}</strong> : 
                                                  @foreach($suppoted_currency as $value)
                                                    @if(isset($order['currency_code']) && strtoupper($value['currency_code']) === $order['currency_code'])
                                                    {!! $value['currency_symbol'] !!}
                                                    @endif
                                                   @endforeach
                                                  {{number_format($order['items_details']['price'])}}
                                              </td>
                                               <td>{{$order['payment_type']}}</td>
                                              @if($order['payment_type'] != 'FREE')
                                                  <td>

                                                      {{$order['payment_status']}}

                                                  </td>
                                              @else
                                                  <td>

                                                      N/A

                                                  </td>
                                              @endif
                                              <td>{{$order['status']}}</td>
                                              <td>
                                                {{Timezone::convertFromUTC('@'.strtotime($order['created_at']), Auth::user()->timezone, Config('app.date_time_format'))}}

                                            </td>
                                              <td>
                                              @if($view)
                                                <a class="btn btn-circle show-tooltip" title="" href='{{URL::to("cp/order/view-order/".$order['order_id'])}}' data-original-title="View">
                                                  <i class="fa fa-eye"></i>
                                                </a>
                                              @endif
                                              @if($edit)
                                                <a class="btn btn-circle show-tooltip" title="" href='{{URL::to("cp/order/edit-order/".$order['order_id'])}}' data-original-title="Edit"
                              <?php
                              if($order['status'] === "COMPLETED" && $order['payment_status'] === "PAID") echo "disabled";
                              ?>
                                                >
                                                  <i class="fa fa-edit"></i>
                                                </a>
                                              @endif
                                                <!-- <a class="btn btn-circle show-tooltip" title="" data-original-title="Delete" href='{{URL::to("cp/order/view-order/".$order['order_id'])}}'
                              <?php
                              //if($order['status'] === "COMPLETED" && $order['payment_status'] === "PAID") echo "disabled";
                              ?>
                                                >  <i class="fa fa-trash-o"></i> 
                                                </a>-->
                              <?php
                              if($order['status'] === "COMPLETED" && $order['payment_status'] === "PAID" && isset($order['comment'])) echo "<p class='text-success'>".$order['comment']."<p>";
                              ?>
                                             </td>
                                            </tr>
                                          @endforeach
                            </tbody>
                        </table>
                        @if(count($data) > 0)
                          <span class="pull-right">
                          {!! $data->appends(['date_filter' => $date_filter,'type_filter' => $filter])->render() !!}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('.daterange').daterangepicker({
            format: 'DD-MM-YYYY',
            // dateLimit: { days: 60 },
            showDropdowns: true,
            showWeekNumbers: true,
            timePicker: false,
            timePickerIncrement: 1,
            timePicker12Hour: true,
            ranges: {
               'Yesterday': [moment().add(-1, 'days'), moment().add(-1, 'days')],
               'Today': [moment().startOf('day'), moment().endOf('day')],
               'This week': [moment().startOf('week'), moment().endOf('week')],
               'This Month': [moment().startOf('month'), moment().endOf('month')],
               'This Year': [moment().startOf('year'), moment()]
            },
            opens: 'right',
            drops: 'down',
            buttonClasses: ['btn', 'btn-sm'],
            applyClass: 'btn-primary',
            cancelClass: 'btn-default',
            separator: ' to ',
            locale: {
                applyLabel: 'Apply',
                cancelLabel: 'Cancel',
                fromLabel: 'From',
                toLabel: 'To',
                customRangeLabel: 'Custom',
                daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr','Sa'],
                monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                firstDay: 1
            }
        });

       $(document).ready(function(){
        $('#alert-success').delay(5000).fadeOut();
        $('#sample').DataTable({
            "aaSorting": [[ 0, "desc" ]],
            "paging":   true,
            "info":     true
        });
    })

</script>
@stop