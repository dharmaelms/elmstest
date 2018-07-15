@section('content')
  <style type="text/css">
    .disclosure .heading{
        text-align: center !important;
        font-weight: 700!important;
        color:darkblue;
        display: inline-block;
        border-bottom: 2px solid darkblue;
        padding-bottom:15px;
    }
    .disclosure h6{
        text-align: justify !important;
        font-size: 14px!important;
        line-height: 1.8!important;
    }
    .disclosure h5{
        text-align: center !important;
        font-size: 16px!important;
        line-height: 1.8 !important;
        font-weight: bold;
        margin-bottom: 0px !important;
    }
    .disclosure p{ color:darkgrey; }
    .disclosure .subheading { text-align: left !important; }
    .disclosure ul{ text-align: left; padding: 0px;}
    .disclosure li{ display:inline;} 
    .disclosure li .btn-default{ background-color: #d0d0d0; margin-right: 20px !important;font-weight: bold;}
    .tooltip-inner{
      text-align:left !important;
      background: #fff !important;
      color: red !important;
    }
  </style>

  @if($redirect_url)
    <?php $redirect_url = urldecode($redirect_url); ?>
  @else
    <?php $redirect_url = '/dashboard'; ?>
  @endif
  <form action="{{URL::to('auth/user-nda/?redirect_url='.$redirect_url )}}" class="form-horizontal form-bordered form-row-stripped" method="post" >   
    <div class="row disclosure">
      <div class="col-lg-12 col-md-12 col-sm-12 text-center">
        <div class="container">
          <h4 class="heading">{{ trans('user.nda_title') }}</h4><br>
          <h6 class="content">&quot;This Agreement sets forth the terms and conditions for the use content and information used to deliver the courses/training and conduct assessments through this website.
          The information contained in this website, including all attachments is confidential and is subject to these terms of use. By clicking &quot;Accept&quot; below, you acknowledge that you have read these terms, and you agree on your own behalf and on behalf of your organization (which includes, without limitation, all of its other members, employees, officers, directors and agents) to be bound by these terms. You shall not use such information for any other purpose, or disclose such information to any third party, except as required by law, court order, or a national securities exchange rule.  
          You are hereby authorized to disclose confidential information to your attorneys, accountants, and similar advisers bound by a duty of confidentiality and limited use at least as substantial as those set forth in these terms.  
          You shall hold and maintain the confidential information in strictest confidence. You shall not, without prior written approval publish, copy, or otherwise disclose to others, or permit the use by others for their benefit any confidential information.
          Access to the portal will be denied, if you decline these terms of use. &quot;</h6><br>
          <ul>
            <li><input type="submit" class="btn btn-default"  role="button" value="{{ trans('user.nda_accept') }}"></li>
            <li><a href="{{ URL::to('/auth/logout?nda_logout=yes') }}" class="btn btn-default red-tooltip" role="button" data-toggle="tooltip" title="{{ trans('user.nda_tooltip') }}" data-placement="right">{{ trans('user.nda_decline') }}</a></li>        
          </ul> 
        </div>
      </div>
    </div>
  </form>
  <script>
    $(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
  });
  </script>
@endsection
