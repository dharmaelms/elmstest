@section('content')
<style>

ul.qusul {
    color:#000;
    list-style:none;
    margin:0;
    padding:0
}
.qusul li {
    /*border:1px solid #f5f5f5;*/
    margin:1em 0;
    padding:0
}
.qusul li div strong{ padding: 4px 10px;line-height: 30px; }


.qusul .question {
    cursor:pointer;
    display:block;
    font-size:1em;
    font-weight:bold;
    color: #000;
    /*padding:.75em 1.25em;*/ padding: 0;
    position:relative;
}
.qusul .answer {
    border-top:1px dashed #f5f5f5;
    display:block;
    padding:.50em 1.00em;
    font-size:1em;
    font-weight:200;
}
.qusul a {
    color:#ccc
}
.qusul .question {
    /*padding-left:1.5em*/
}
.qusul .question:before {
   /* content:"+";
    font-weight:700;*/
    /*position:absolute;*/
    /*left:.5em*/
}
.qusul .active{
     /*padding-left: 1.5em;*/ /*padding-left: 10px;*/
}
.qusul .active:before {
    /*padding-left:1.5em;
    content:"-";
    font-weight:700;*/
    /*position:absolute;*/
    /*left:.5em*/
}
.qusul span {
    background-color: #EBEBE6;
}
.print-section table td{
    background-color: #fff;
    margin: 50px;
    padding-left: 20px;
    padding-right: 20px;
    padding-top: 0px;
    font-size: 16px;
    line-height: 27px;
    border-radius: 4px !important;
}
</style>

	<?php if(isset($staticpage) && isset($page_title) ){  
            $content=$staticpage['content'];
            ?>
             <div class="md-margin"></div> 
            <div class="print-section">
                <h3 class="page-title">{{$page_title}}</h3>
                <hr>
                {!!$content!!}
                <script>
                    $(document).ready(function () {
                        $("a").each(function() {
                          var a = new RegExp("/" + window.location.host + "/");
                          if(!a.test(this.href) && this.href != "javascript:;" && this.href != "#" ) {
                             $(this).click(function(event) {
                                 event.preventDefault();
                                 event.stopPropagation();
                                 window.open(this.href, "_blank");
                             });
                          } 
                        });
                    });
                </script>
            </div>
            <?php
            // print_r();

		}
        elseif(isset($faqs)){?>
            <div class="page-title-bg">
                <h3 class="page-title"><?php echo Lang::get('manageweb.faq'); ?></h3>
            </div><?php
            foreach ($faqs as $key => $value) 
            { ?> 
             <ul class="qusul">
                <li class="well">
                    <div><strong>{{$value['question']}}?</strong></div>
                    <span class="answer" style="display: none; overflow: hidden;">
                     {!! html_entity_decode($value['answer']) !!}
                    </span>
                </li>
            </ul>
<?php       }  
        }
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".qusul").click(function(){
            $(".qusul").find('div').attr({class:'question'})
            $(".qusul").find('span').hide();
            $(this).find('div').attr({class:'active'})
            $(this).find('span').toggle();     
        });
    });
</script>
@stop
