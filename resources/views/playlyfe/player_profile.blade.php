<div class="profile">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-1 col-xs-6">
                <img src="{{ asset('portal/theme/default/img/avatar.png') }}" class="img-circle" alt="">
            </div>
            <div class="col-md-2 profile-name">
                {{ $playerProfile["player_info"]->player_alias }}
            </div>
              <div class="col-md-2 col-xs-4">
                <div class="dashboard-header-item-title">CURRENT LEVEL</div>
                <div class="dashboard-header-item-value">
                    <img src="/pl/image?size=small&metric_id={{$pl_score["metric"]["id"]}}&state={{$pl_score["value"]["name"]}}" />
                </div>

                <div class="dashboard-header-item-value">{{$pl_score["value"]["name"]}}</div>
                <div class="dashboard-header-item-score">({{$playerProfile['profile_info']['playlyfe_player_profile']['points']}} XP)</div>
              </div>
              <div class="col-md-1 center  col-xs-4">
                    <i class="fa fa-long-arrow-right" style="font-size:33px;line-height: 2.2;"></i>
              </div>
              <div class="col-md-2  col-xs-4">
                <div class="dashboard-header-item-title">NEXT LEVEL</div>
                <div class="dashboard-header-item-value">
                    <img src="/pl/image?size=small&metric_id={{$pl_score["metric"]["id"]}}&state={{$pl_score["value"]["name"]}}" >
                </div>
                <div class="dashboard-header-item-value">{{$pl_score["meta"]["next"]}}</div>
                <div class="dashboard-header-item-score">({{$pl_score["meta"]["high"]}} XP)</div>
              </div>
              <div class="col-md-2  col-xs-12">
                <div class="dashboard-header-item-title">LEVEL COMPLETION</div>
                <div class="dashboard-center">
                    <div class="dashboard-center-progress">
                        <div class="progress">
                          <div class="progress-bar" role="progressbar" style="width: {{$pl_score['percent']}}%;">
                            <span class="show">{{$pl_score['percent']}}%</span>
                          </div>
                        </div>
                    </div>
                </div>
              </div>
               <div class="col-md-2  col-xs-4">
                <?php $playlyfe_info = Lang::get('playlyfe.playlyfe_points_info'); ?>
                <a data-placement="left"  data-toggle="modal" href="#" data-target="#playlyfe">
                <!-- The class CANNOT be tooltip... -->
                <i class='glyphicon glyphicon-info-sign'></i>
            </a>
              </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6">
                 <div class="profile-h3">Your Rank</div>
                 <div class="profile-leaderboard-alltime">#{{$playerProfile['profile_info']['alltime']["rank"]}}</div>
                <h3> All Time </h3>
                @if($playerProfile['profile_info']['alltime']["next_score"] != -1)
                    <?php $remaining_points = $playerProfile['profile_info']['alltime']["next_score"] - $playerProfile['profile_info']['playlyfe_player_profile']['points']; ?>
                    @if($remaining_points > 1)<div class="profile-leaderboard-text"> Just <b>{{ $remaining_points }} XP</b> to get the next rank! </div>@endif
                @endif
                @if($playerProfile['profile_info']['lastweek']["rank"] != -1)
                <div class="col-md-2">
                    <div class="profile-leaderboard-thisweek">#{{$playerProfile['profile_info']['lastweek']}}</div>
                    <h3> This Week </h3>
                    @if($playerProfile['profile_info']['lastweek']["next_score"] != -1)
                        <div class="profile-leaderboard-text"> Just <b>{{$playerProfile['profile_info']['lastweek']["next_score"]}} XP</b> to get the next rank! </div>
                    @endif
                @endif


                <div class="profile-h3">Your Recent Achievements</div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="profile-badges-list">
                        @foreach($playerProfile["profile_info"]["playlyfe_player_profile"]["badges"] as $badge)
                            <div class="profile-sub-title-1">
                                <div class="profile-badges-list-item badge-tooltip" title="{{$badge["name"]}}&nbsp;&nbsp;{{$badge["description"]}}">
                                    <img class="{{ ($badge["count"] === 0) ?  "grayscale" : "" }}" src="/pl/image?size=small&metric_id={{$badge["type_info"]["id"]}}&item={{$badge["name"]}}"></img>
                                </div>
                            </div>
                        @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="profile-h3">Your Recent Activity</div>
                    <div id="profile_activity">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
        <div class="modal fade" id="playlyfe" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header" style="background-color: #297076;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel" style="color:white;"><b>Game Rules</b></h4>
              </div>
              <div class="modal-body" style="max-height: 400px;overflow-y: auto;">
              {!! trans($playlyfe_info) !!}
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
<script type="text/javascript">
    $(document).ready(function(){
        $.ajax({
            type: 'GET',
            url: '/pl/player-activity',
            dataType: 'html',
            contentType : 'application/x-www-form-urlencoded; charset=UTF-8',
        }).done(function(activity) {
            $("#profile_activity").append(activity);
            $('.badge-tooltip').tooltip();
        });
    });
</script>