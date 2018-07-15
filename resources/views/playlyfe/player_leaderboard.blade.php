<div class="row">

  <div class="row leaderboard-table-header">
    <div class="col-md-1 leaderboard-header-rank">Rank</div>
    <div class="col-md-2 leaderboard-header-image">Image</div>
    <div class="col-md-4 leaderboard-header-user">Username</div>
    <div class="col-md-4 leaderboard-header-score">Score</div>
  </div>
  <div id="player-leaderboard-container-{{$cycle}}">
  </div>
  <div class="row">
    <div class="col-sm-offset-5 col-md-offset-5 col-sm-7 col-md-7">
      <ul id="player-leaderboard-paginator-{{$cycle}}" class="pagination-sm">
      </ul>
    </div>
  </div>
</div>
<script type="text/javascript">
  $(document).ready(function(){
    base_url = "<?php echo config('app.url'); ?>";
    total_pages = "<?php echo $total; ?>";
    playerLeaderboardURL = "{{ URL::to("pl/leaderboard") }}";
    $("#player-leaderboard-paginator-{{$cycle}}").twbsPagination({
      totalPages : total_pages,
      visiblePages : 1,
      initiateStartPageClick : true,
      startPage : 1,
      onPageClick: function(event, page) {
        $.ajax({
          type : "get",
          url: playerLeaderboardURL + '?' + $.param({
            length: 10,
            start: (page-1)*10,
            type: "{{$cycle}}",
            contentOnly : true
          }),
          contentType : "application/json",
          dataType : "json",
        })
         .done(function(response, textStatus, jqXHR){
          console.log(response);
          var players = "";
          for(var i = 0; i<response.data.length; i++) {
              var item = response.data[i];
              if (item != null) {
              }
              var leaderboard_row_selected = "";
              var name_selected_text = "";
              var name_selected_class = "leaderboard-user-row-name";
              var score_selected_class = "leaderboard-user-row-score";
              if (item.is_primary != null) {
                name_selected_class = "leaderboard-user-row-name-selected";
                name_selected_text = " (YOU)";
                score_selected_class = "leaderboard-user-row-score-selected";
                leaderboard_row_selected = "leaderboard-user-row-selected";
              }

              players += '<div class="row leaderboard-user-row '+ leaderboard_row_selected +'">'
              + '<div class="col-md-1 leaderboard-user-row-rank">' + item.rank  + '</div>'
              + '<div class="col-md-2 leaderboard-user-row-image">'
              +   '<img height="50" src="'+base_url+'/portal/theme/default/img/avatar.png" class="img-circle" alt="">'
              + '</div>'
              + '<div class="col-md-4 '+ name_selected_class+'"> ' + item.player.alias + name_selected_text + '</div>'
              + '<div class="col-md-4 '+score_selected_class+'">' + item.score + '</div>'
              + '</div>';
          }
          $("#player-leaderboard-container-{{$cycle}}").html(players);
        }).fail(function(jqXHR, textStatus, errorThrown){
          alert(textStatus);
        });
      },
    });
  });
</script>
