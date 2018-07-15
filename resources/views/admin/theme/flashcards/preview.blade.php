<link rel="stylesheet" type="text/css" href="{{ URL::asset('admin/assets/flashcards/PageTransitions/css/component.css')}}" />
<link rel="stylesheet" type="text/css" href="{{ URL::asset('admin/assets/flashcards/PageTransitions/css/animations.css')}}" />
<script src="{{ URL::asset('admin/assets/flashcards/PageTransitions/js/modernizr.custom.js')}}"></script>
<script src="{{ URL::asset('admin/assets/ckeditor/plugins/ckeditor_wiris/integration/WIRISplugins.js?viewer=image') }}"></script>
<div id="pt-main" class="pt-perspective" <?php if(isset($height)){ echo 'style="height:'.$height.'"';} ?>>
    @foreach($flashcards as $key => $card)
    <div class="pt-page">
        <span class="label label-info label-small pk-item-blue pull-left card-label"><strong>{{ trans('admin/flashcards.front')}}</strong></span>
        <div class="centered">
            {!! $card['front'] !!}
        </div>
    </div>
    <div class="pt-page">
        <span class="label label-info label-small pk-item-blue pull-left card-label"><strong>{{ trans('admin/flashcards.back')}}</strong></span>
        <div class="centered">
            {!! $card['back'] !!}
        </div>
    </div>
    @endforeach    
</div>
<div id="progressBar" class="pt-triggers">
    <div></div>
</div>
<div class="pt-triggers" style="width:87px;">
    <button onclick="window.PageTransitions.showQuestion(35);" class="pt-touch-button" id="question" style="background-color:#89c4f4; color:#ffffff;">
    FLIP
    </button>
    <button onclick="window.PageTransitions.showAnswer(34);" class="pt-touch-button answer" id="answer" style="background-color:#89c4f4; color:#ffffff;">
    FLIP
    </button>
</div>
<div class="pt-triggers">
    @if($key > 1)
    <button onclick="window.PageTransitions.prevPage(33);" class="pt-touch-button">
        <span class="glyphicon glyphicon-chevron-left"></span>
    </button>
    @endif
    <button class="pt-touch-button">
        <span id="count">1</span>/{{ (count($flashcards)) }}
    </button>
    
    @if($key > 1)
    <button onclick="window.PageTransitions.nextPage(32);" class="pt-touch-button">
        <span class="glyphicon glyphicon-chevron-right"></span>
    </button> 
    @endif       
</div>
<!-- /triggers -->
<div class="pt-message">
    <p>{{ trans('admin/flashcards.browser_msg_for_unsupported_animations') }}</p>
</div>
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="{{ URL::asset('admin/assets/flashcards/PageTransitions/js/jquery.dlmenu.js')}}"></script>
<script src="{{ URL::asset('admin/assets/flashcards/PageTransitions/js/pagetransitions.js')}}"></script>
