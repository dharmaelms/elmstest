@section('content')
<link rel="stylesheet" href="{{ URL::asset("portal/theme/".config("app.portal_theme_name")."/css/postlogin.css") }}">
<link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/css/calendar.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/css/custom_2.css') }}" />
<style>
  .page-content-wrapper .page-content { margin-left: 0 !important; }
  .page-sidebar.navbar-collapse { display: none !important; max-height: none !important; }
  .page-header.navbar .menu-toggler.sidebar-toggler { display: none; }
</style>
<div>
  @foreach($channels as $channel)
    {{ $channel->program_title }}
  @endforeach
</div>
@stop