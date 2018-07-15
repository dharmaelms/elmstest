 <?php //$list = $batch_info;?>
 <table class="custom-table">
  <thead>
    <tr>
      <th>{{Lang::get('batch/course.batch_name')}}</th>
      <th>{{Lang::get('batch/course.batch_start_date')}}</th>
      <th>{{Lang::get('batch/course.batch_end_date')}}</th>
      <th>{{Lang::get('batch/course.batch_last_date_enrollment')}}</th>
      <th>{{Lang::get('batch/course.batch_seats_available')}}</th>
      <th>{{Lang::get('batch/course.mark_price')}}</th>
      <th><span class="red">{{Lang::get('batch/course.price')}}</span></th>
      <th width="100"></th>
    </tr>
  </thead>
  <tbody>
   @if(empty($list)) 
    <tr><td colspan="8"><br/><p class="text-center">{{Lang::get('batch/course.no_batch')}} <a href="{{URL::to('catalog')}}" class="text-danger">{{Lang::get('batch/course.href_text')}}</a>  {{Lang::get('batch/course.complete_message')}}</p></td></tr> 
   @else
   <?php 
        foreach($list as $eachBatch)
          {
            $pricestring = "";
            $offPrice = "";
            if(!empty($eachBatch['price']))
              {
                foreach ($eachBatch['price'] as $key => $eachPrice)
                 {
                   if($eachPrice['currency_code'] === $site_currency)
                   {
                      $pricestring .= $site_currency_symbol.number_format((float)$eachPrice['price']);
                      if(!empty($eachPrice['markprice']))
                      {
                        $offPrice .= $site_currency_symbol.number_format((float)$eachPrice['markprice']); 
                      }
                   }
                }
              }
             ?>
            <tr>
               <?php $seats_avialble = 0;?>
              <td>{{ $eachBatch['title'] }} </td>
              <td>{{ date_format(date_create($eachBatch['batch_start_date']), 'jS F Y') }}</td>
              <td>{{ date_format(date_create($eachBatch['batch_end_date']), 'jS F Y') }}</td>
              <td>{{ date_format(date_create($eachBatch['batch_last_enrollment_date']), 'jS F Y') }}</td>
              @if(strtotime($eachBatch['batch_last_enrollment_date']) >= strtotime(date('Y-m-d')))
              <td>
                {{ ($eachBatch['batch_maximum_enrollment'] == 0 || ($eachBatch['batch_maximum_enrollment'] - $eachBatch['batch_enrolled']) < 0) ? 'N/A' : ($eachBatch['batch_maximum_enrollment'] - $eachBatch['batch_enrolled']) }}
                </td>
              @else
                <td>
                  <?php $seats_avialble = 1;?>
                  N/A
                </td>
              @endif
              <td width="100">
                  <?php if($offPrice != "" && $pricestring != ""){?>
                    <span style="text-decoration: line-through;color:RED">
                      <?php echo $pricestring;?>
                    </span>
                  <?php }else if($offPrice === ""){ ?>
                      <?php echo $pricestring;?>
                  <?php }else{ ?>
                      <span>
                        N/A
                      </span>
                  <?php } ?>
              </td>
             <td width="100">
              <span>
                <?php if($offPrice === ""){?>
                  N/A
                <?php }else{
                  echo $offPrice;
                   } ?>
              </span>
             </td>
              <td width="100">
               @if(is_array($is_buyed) && in_array($eachBatch['course_id'], $is_buyed) )
                  <a href="{{URL::to('program/packets/course-'.$eachBatch['slug'].'-c'.$eachBatch['course_id'])}}" class="btn btn-danger btn-sm">{{Lang::get('batch/course.start_learning_batch')}}</a>
               @else
                     <?php 
                    $subUrl = "checkout/place-order/{$program_slug}/{$eachBatch['slug']}/course/{$eachBatch['course_id']}";?>
                    @if($eachBatch['batch_maximum_enrollment'] == 0 || ($eachBatch['batch_maximum_enrollment'] - $eachBatch['batch_enrolled']) > 0)
                        @if($seats_avialble === 1)
                           <a class="btn btn-danger btn-sm">
                           {{Lang::get('batch/course.sold_batch')}}
                           </a>
                           <!-- <div class="alert alert-danger">
                            Enrollment is closed. Please contact the admin if there are any available seats.
                          </div> -->
                           <span>
                        @else
                          <a class="btn btn-danger btn-sm  {{$is_loggedin}} catalog" data-baseurl="" data-productid="{{ $program_slug}}" data-catalog="{{$program_slug.'/'.$eachBatch['slug']}}" href="{{URL::to($subUrl)}}">{{Lang::get('batch/course.buy_batch')}}</a>
                        @endif
                      @else
                           <a class="btn btn-danger btn-sm  {{$is_loggedin}}">{{Lang::get('batch/course.sold_batch')}}</a>
                      @endif
               @endif
              </td>
            </tr>     
    <?php } ?>
    @endif
  </tbody>
  </table>