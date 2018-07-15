@section('content')
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <a href="{{URL::to('/')}}">
                {{ trans('search.home') }}
            </a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <a href="#">{{trans('search.search')}}</a>
        </li>        
    </ul>
</div>
<div class="row">
    <div class="col-md-12 col-sm-12 search-data">
        <div class="row">
            <span class="col-md-8 col-sm-8 font-16"> 
                {{ trans('search.search_result_for') }} 
                <strong>
                    {{$query}}
                </strong>
            </span>
            <div class="col-md-4 col-sm-4 font-16">
                @if($results['hits']['total'] > 0)
                    @if ($results['hits']['total'] <= $limit)
                            @if ($results['hits']['total'] > 1)
                                <span class="pull-right">
                                    Showing 1 - <span id="current">{{$results['hits']['total']}}</span>
                                    out of {{$results['hits']['total']}} results
                                </span>
                            @else
                                <span class="pull-right">
                                    Showing 1 out of {{$results['hits']['total']}} result
                                </span>
                            @endif
                    @else
                        <span class="pull-right">Showing 1 - <span id="current">{{$limit}}</span> out of {{$results['hits']['total']}} results</span>
                    @endif
                @endif
            </div>
        </div></br>
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <ul class="search-ul" id="dynamic_pager_content2">
                    @if(!empty($results['hits']['total'] > 0))
                        @include('portal.theme.default.search.listing', ['results' => $results['hits']['hits']])
                    @else
                        <b class="center">{{trans('search.no_results_found')}}</b>
                    @endif
                </ul>
            </div>
        </div>
        @if($results['hits']['total'] > 0 && $results['hits']['total'] <= $limit)
            <div class="center">{{trans('search.no_more_results_found')}}</div>
        @endif
        @if($results['hits']['total'] > 0 && $results['hits']['total'] > $limit)
            <div class="center"><a href="#" id="more">{{trans('search.more')}}</a></div>
        @endif
        <div id="end" class="center"></div> 
    </div><!--search data-->
</div><!--main row-->
    <!-- END CONTENT -->
<script>
    $.fn.ensureVisible = function () { $(this).each(function () { $(this)[0].scrollIntoView({behaviour: "smooth", block: "start", }); }); };
    $('#search', '.page-header').val("{{$query}}");
    var search = (function(){
        var total = {{$results['hits']['total']}},
            limit = {{$limit}},
            query = "{{$query}}",
            container = '.search-ul',
            more = '#more',
            page = 1,
            PROGRAM = 'programs',
            POST = 'posts',
            ITEM = 'items',
            MEDIA = 'media',
            ASSESSMENT = 'assessment',
            EVENT = 'event';
        function loadMore() {
            $(more).on('click', function(e){
                $('#end').html('loading');
                $(more).hide();
                current = page*limit;
                $.ajax({
                    'url' : "{{url('search')}}"+"?query="+query+"&from="+current+"&limit="+limit,
                    'method' : 'GET',
                    'dataType' : 'json'
                }).success(function(response) {
                    if (response.status) {
                        $('#end').html('');
                        $(container).append(response.data);
                        $('.search-ul li:eq('+(page*limit-1)+')').ensureVisible();
                        if (total == current) {
                            $(more).hide();
                        }
                        $('#current').html(response.count);
                        $(more).show();
                        page++;
                    } else {
                        $(more).hide();
                        $('#end').html("{{trans('search.no_more_results_found')}}");
                    }  
                });
                
                return false;
            });
        }
        loadMore();
        return {
            load : loadMore
        };        
    })();
</script>
@stop
