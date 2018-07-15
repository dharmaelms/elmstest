@section('content')
<style>

ul.qusul {
    color:#000;
    list-style:none;
    margin:0;
    padding:0
}
.qusul li {
    border:1px solid #f5f5f5;
    margin:1em 0;
    padding:0
}
.qusul .question {
    cursor:pointer;
    display:block;
    font-size:1em;
    font-weight:bold;
    color: #000;
    padding:.75em 1.25em;
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
    padding-left:1.5em
}
.qusul .question:before {
    content:"+";
    font-weight:700;
    /*position:absolute;*/
    left:.5em
}
.qusul .active:before {
    padding-left:1.5em;
    content:"-";
    font-weight:700;
    /*position:absolute;*/
    left:.5em
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
            <div class="page-bar">
                <ul class="page-breadcrumb">
                    <li><a href="{{ URL::to('/') }}">Home</a><i class="fa fa-angle-right"></i></li>
                    <li><a href="#">{{$page_title}}</a></li>
                </ul>
            </div>
            <!-- <div class="md-margin"></div><!--space--> 
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
                <h3 class="page-title">FAQ</h3>
            </div><?php
            foreach ($faqs as $key => $value) 
            { ?> 
             <ul class="qusul">
                <li class="well">
                    <strong class="question">{{$value['question']}}?</strong>
                    <span class="answer" style="display: none; overflow: hidden;">
                        <p>{{$value['answer']}}.</p>
                    </span>
                </li>
            </ul>
<?php       }  
        }
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".qusul").click(function(){
            $(".qusul").find('strong').attr({class:'question'})
            $(".qusul").find('span').hide();
            $(this).find('strong').attr({class:'active'})
            $(this).find('span').toggle();     
        });
    });
</script>
@stop
