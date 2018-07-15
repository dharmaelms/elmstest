@section('content')
	@if ( Session::get('success') )
		<div class="alert alert-success">
			<button class="close" data-dismiss="alert">×</button>
			<!-- <strong>Success!</strong> -->
			{{ Session::get('success') }}
		</div>
		<?php Session::forget('success'); ?>
	@endif
	@if ( Session::get('error'))
		<div class="alert alert-danger">
			<button class="close" data-dismiss="alert">×</button>
			<strong>Error!</strong>
			{{ Session::get('error') }}
		</div>
		<?php Session::forget('error'); ?>
	@endif
	<style>
		.messages-input-form .buttons .btn{
			margin-top: 1px;
		}
	</style>
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
    <script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
	<div class="row">
	    <div class="col-md-12">
	        <div class="box">
	            <div class="box-title">
	            </div>
	            <div class="box-content">
                    <div class="clearfix"></div>
                    <ul class="messages messages-chatty">
						<li>
							<img src="{{URL::to(Config::get('app.default_user'))}}" alt="">
							<div>
								<div>
									<h5>{{(isset($question['created_by_name']) ? $question['created_by_name'] : $question['username'])}}</h5>
									<span class="time"><i class="fa fa-clock-o"></i> {{ Timezone::convertFromUTC("@".$question['created_at'],Auth::user()->timezone,'Y-m-d H:i') }}</span>
								</div>
								<p>{{$question['question']}}</p>
							</div>
						</li>
					    <?php if(isset($answers) && is_array($answers)){ ?>
					    	<?php foreach($answers as $answer){ ?>
							    <li class="<?php echo (isset($question['user_id']) && $answer['user_id'] == $question['user_id']) ? "left" : "right"; ?>">
							        <img src="{{URL::to(Config::get('app.default_user'))}}" alt="">
							        <div>
							            <div>
							                <h5>{{(isset($answer['created_by_name']) ? $answer['created_by_name'] : $answer['username'])}}</h5>
						                	<a class="btn btn-circle show-tooltip answerdelete" style="background-color:darkgrey;color:white" title="Delete this answer" href="{{URL::to("/cp/contentfeedmanagement/delete-answer/{$post->packet_id}/{$question->id}/{$answer["id"]}")}}" ><i class="fa fa-trash-o"></i></a>
							                <span class="time"><i class="fa fa-clock-o"></i> {{ Timezone::convertFromUTC("@".$answer['created_at'],Auth::user()->timezone,'Y-m-d H:i') }}</span>
							            </div>
						            	<p>{{$answer['answer']}}</p>
							        </div>
							    </li>
					    	<?php } ?>
					    <?php } ?>
					    <div class="messages-input-form">
                            <form method="POST" action="{{URL::to("/cp/contentfeedmanagement/answer/{$post->packet_id}/{$question->id}")}}">
                                <div class="input">
                                    <input type="text" name="answer" placeholder="Write here..." class="form-control" value="">
                                </div>
                                <div class="buttons">
                                    <button type="submit" id="postanswer" class="btn btn-primary"><i class="fa fa-share"></i></button>
                                </div>
                            </form>
                            {!! $errors->first('answer', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                        </div>
					</ul>
                </div>
	        </div>
	    </div>

	    <div class="modal fade" id="answerdelete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="box">
                                    <div class="box-title">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                        <h3 class="modal-header-title" >
                                            <i class="icon-file"></i>
                                                {{trans('admin/program.delete_answer')}}
                                        </h3>                                                
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="padding-left: 20px;">
                        {{trans('admin/program.modal_delete_answer')}}
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-danger" id="actionbtn">{{trans('admin/program.yes')}}</a>
                        <a class="btn btn-success" data-dismiss="modal">{{trans('admin/program.close')}}</a>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function(){ 
                $('.alert-success').delay(5000).fadeOut();
                $('#postanswer').click(function(e){
                    e.preventDefault();
                    var $input = $(this).closest('form').find('input[type="text"]');
                    if($input.val() != ""){
                        $(this).closest('form').submit();
                    } else{
                        alert("{{trans('admin/program.err_msg')}}");
                    }
                });
                $('.answerdelete').click(function(e){
                    e.preventDefault();
                    var $modal = $('#answerdelete');
                    var $this = $(this);
                    $modal.find('.modal-footer a#actionbtn').attr('href',$this.attr('href'));
                    $modal.modal('show');
                });
            })
        </script>
    </div>
@stop