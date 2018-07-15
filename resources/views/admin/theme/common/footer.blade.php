@section('footer')
    <footer>
        <p><?=date('Y');?> &copy; {{ config('app.site_name', 'Ultron Admin') }}</p>
        <p class="pull-right">Linkstreet {{Config::get('app.application_version')}}</p>
    </footer>
@stop
