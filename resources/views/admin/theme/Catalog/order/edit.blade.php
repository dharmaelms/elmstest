@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box">
      <div class="box-title">
      </div>
      <div class="box-content">
        <form action="{{URL::to('cp/order/save-order')}}" class="form-horizontal form-bordered form-row-stripped" method="post">
            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="order_comment">{{trans('admin/catalog.order')}} </label>
              <div class="col-sm-6 col-lg-5 controls">
				  {{$o_data['order_label']}}              
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="order_comment">{{trans('admin/catalog.comment')}} </label>
              <div class="col-sm-6 col-lg-5 controls">
                <input type="text" class="form-control" name="order_comment" value="">                 
              </div>
            </div>
           <input type="hidden" name="order_id" value="{{$o_data['order_id']}}">
           <div class="form-group">
                <label class="col-sm-4 col-lg-3 control-label" for="order_status">{{trans('admin/catalog.order_status')}} </label>
                  <div class="col-sm-6 col-lg-5 controls">
                    <select name="order_status" class="chosen gallery-cat form-control" data-placeholder="None">
                    <option value="PENDING" selected="">{{trans('admin/catalog.pending')}}</option>
                      <option value="CANCELED">{{ trans('admin/catalog.cancel') }}</option>                 
                      <option value="COMPLETED">{{trans('admin/catalog.completed')}} </option>
                    </select>
                  </div>
                </div>
            <div class="form-group">
                            <label class="col-sm-4 col-lg-3 control-label" for="payment_status">{{trans('admin/catalog.payment_status')}} </label>
              <div class="col-sm-6 col-lg-5 controls">
                <select name="payment_status" class="chosen gallery-cat form-control" data-placeholder="None">
                  <option value="PAID">{{trans('admin/catalog.paid')}} </option>
                  <option value="NOT-PAID" selected="">{{trans('admin/catalog.not_paid')}} </option>
                </select>
                
              </div>
            </div>
            <div class="form-group last">
                <div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
                    <input type="submit" class="btn btn-info show-tooltip" value="Update Order" data-original-title="" title="">
                    <a class="btn" href="{{URL::to('/cp/order/list-order')}}">{{ trans('admin/catalog.cancel') }}</a>
                </div>
            </div>
          </form></div>
        
    </div>
  </div><!--Register-->
</div>
@stop