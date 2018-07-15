@section('content')
<?php
  $pl = App::make("App\Services\Playlyfe\IPlaylyfeService");
  $actions = $pl->getActions();
  foreach($actions as $action) {
    switch($action["id"]) {
      case "sign_up":
        $sign_up = $pl->getPoints($action);
        break;
      case "login":
        $login = $pl->getPoints($action);
        break;
      case "logout":
        $logout = $pl->getPoints($action);
        break;
      case "quiz_completed":
        foreach($action['rules'] as $rule) {
          if ($rule['requires']['type'] == 'and') {
            switch($rule['requires']['expression'][0]['context']['rhs']) {
              case '60':
                $quiz_60 = $rule['rewards'][0]['value'];
                break;
              case '70':
                $quiz_70 = $rule['rewards'][0]['value'];
                break;
              case '80':
                $quiz_80 = $rule['rewards'][0]['value'];
                break;
              case '90':
                $quiz_90 = $rule['rewards'][0]['value'];
                break;
            }
          }
          if ($rule['requires']['type'] == 'var') {
            if ($rule['requires']['context']['rhs'] == '100') {
              $quiz_100 = $rule['rewards'][0]['value'];
            }
          }
        }
        break;
      case "question_asked":
        $question_asked = $pl->getPoints($action);
        break;
      case "question_marked_as_faq":
        $question_marked_as_faq = $pl->getPoints($action);
        break;
      case "content_viewed":
        $content_viewed = $pl->getPoints($action);
        break;
    }
  }
?>
<link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
<script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
<link rel="stylesheet" href="{{ URL::asset('playlyfe/app.css')}}">
<script src="{{ URL::asset('playlyfe/app.js')}}"></script>
<div class="modal fade" id="resetModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <div class="row custom-box">
          <div class="col-md-12">
              <div class="box">
                  <div class="box-title">
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                      <h3><i class="icon-file"></i>Reset All Users Scores!!</h3>                                                 
                  </div>
              </div>
          </div>
        </div>
      </div>
      <div class="modal-body">
        <div class="row custom-box">
          <div class="col-md-12">
            <div class="box">
              <div class="box-title" style="height:100px;background-color:white;">
                <h5>Are you sure you want to reset all user scores? This is an irreversible action!</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button id="reset_all" type="button" class="btn btn-primary">Reset</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="exportModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <div class="row custom-box">
          <div class="col-md-12">
              <div class="box">
                  <div class="box-title">
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                      <h3><i class="icon-file"></i>Export All Users to Playlyfe!!</h3>                                                 
                  </div>
              </div>
          </div>
        </div>
      </div>
      <div class="modal-body">
        <div class="row custom-box">
          <div class="col-md-12">
            <div class="box">
              <div class="box-title" style="height:100px;background-color:white;">
                <h5>If you have enabled Gamification then you have to export all the users?</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button id="export_all" type="button" class="btn btn-primary">Sync</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-title" style="padding:15px";>
                <h3></h3>
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#general" data-toggle="tab">General</a></li>
                    <li><a href="#contact_us" data-toggle="tab">Points</a></li>
                </ul>
            </div>

            <div class="box-content">
              <div class="tab-content">
                <div class="tab-pane active" id="general">
                  <form class="form-horizontal form-bordered form-row-stripped" id="filltersoptions">             
                      <div class="form-group">
                        <label class="col-sm-3 col-lg-2 control-label">Sync All Users to Playlyfe</label>
                        <div class="col-sm-9 col-lg-10 controls">
                          <div id="export_btn" class="btn btn-info">Sync</div><br />
                        </div>
                      </div>
                      <div class="form-group">
                          <label class="col-sm-3 col-lg-2 control-label">Reset all Users of the Game</label>
                          <div class="col-sm-9 col-lg-10 controls">
                            <div id="reset_btn" class="btn btn-info">Reset</div><br />
                            <span class="help-inline">Warning This Cannot be Undone</span><br />
                          </div>
                      </div>
                  </form>
                </div>
                <div class="tab-pane" id="contact_us">
                  <form action="{{URL::to('cp/reports/gamification-settings')}}" method="post" accept-charset="utf-8" class="form-horizontal form-bordered form-row-stripped" id="filltersoptions">   
                    <div class="form-group">
                      <label class="col-sm-3 col-lg-2 control-label">Points for Signing Up</label>
                      <div class="col-sm-9 col-lg-10 controls">
                        <input type="text" name="sign_up" class="form-control" value="{{$sign_up}}"/><br />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-3 col-lg-2 control-label">Points that a User Can Earn for logging in</label>
                      <div class="col-sm-9 col-lg-10 controls">
                        <input type="text" name="login" class="form-control" value="{{$login}}"/><br />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-3 col-lg-2 control-label">Points that a User Can Earn for logging out</label>
                      <div class="col-sm-9 col-lg-10 controls">
                        <input type="text" name="logout" class="form-control" value="{{$logout}}"/><br />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-3 col-lg-2 control-label">Points that a User Can Earn for Viewing Content</label>
                      <div class="col-sm-9 col-lg-10 controls">
                        <input type="text" name="content_viewed" class="form-control" value="{{$content_viewed}}"/><br />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-3 col-lg-2 control-label">Points that a User Can Earn for Asking Questions</label>
                      <div class="col-sm-9 col-lg-10 controls">
                        <input type="text" name="question_asked" class="form-control" value="{{$question_asked}}"/><br />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-3 col-lg-2 control-label">Points that a User Can Earn When his question is marked as FAQ</label>
                      <div class="col-sm-9 col-lg-10 controls">
                        <input type="text" name="question_marked_as_faq" class="form-control" value="{{$question_marked_as_faq}}"/><br /><br />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-3 col-lg-2 control-label">Points that a User Can Earn for Completing a Quiz</label>
                      <div class="col-sm-9 col-lg-10 controls">
                        <input type="text" name="quiz_100" class="form-control" value="{{$quiz_100}}"/>
                        <span class="help-inline">When the user score is 100% </span><br /><br />
                        <input type="text" name="quiz_90" class="form-control" value="{{$quiz_90}}"/>
                        <span class="help-inline">When the user score > 90% </span><br /><br />
                        <input type="text" name="quiz_80" class="form-control" value="{{$quiz_80}}"/>
                        <span class="help-inline">When the user score > 80% </span><br /><br />
                        <input type="text" name="quiz_70" class="form-control" value="{{$quiz_70}}"/>
                        <span class="help-inline">When the user score > 70% </span><br /><br />
                        <input type="text" name="quiz_60" class="form-control" value="{{$quiz_60}}"/>
                        <span class="help-inline">When the user score > 60% </span><br /><br />
                     </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-6">
                        <button type="submit" class="btn btn-info text-right">Update </button>
                        <a href="{{URL::to('/cp/reports/gamification-settings')}}" ><button type="button" class="btn">Cancel</button></a>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
  $('#export_btn').click(function(){
    $('#exportModal').modal();
  });
  $('#reset_btn').click(function(){
    $('#resetModal').modal();
  });
  $('#reset_all').click(function(){
    $.ajax({
      url: '/pl/reset',
      method: 'post'
    })
    $('#resetModal').modal('hide');
    alert("Resetting all users. This will take some time.");
  });
  $('#export_all').click(function(){
    $.ajax({
      url: '/pl/export',
      method: 'post'
    })
    .done(function(){
      alert("Exported All Users");
      $('#exportModal').modal('hide');
    })
  });
</script>
@stop