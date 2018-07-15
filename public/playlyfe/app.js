function get_image(size, metric_id, item, state) {
    var item_txt = '';
    if (item != null) {
        item_txt += '&item='+item;
    }
    if (state != null) {
        item_txt += '&state='+state;
    }
    return '<img src="/pl/image?size='+size+'&metric_id='+metric_id+item_txt+'"></img>';
}

function render_changes(changes, size) {
  var item = "";
  var scores = {};
  for (var i=0; i<changes.length;i++) {
    var change = changes[i];
    if (change.metric.type === "point" && change.metric.id === 'experience_points') {
      if (scores[change.metric.id] == null) {
        scores[change.metric.id] = {
        	type: change.metric.type,
          name: change.metric.name,
          value: 0
        };
      }
      scores[change.metric.id].value = scores[change.metric.id].value + (change.delta.new - change.delta.old);
    }
    if (change.metric.type === "set") {
    	for(var key in change.delta) {
    		scores[change.metric.id + "||" + key] = {
    			type: change.metric.type,
    			name: change.metric.name,
    			value: key
    		}
    	}
    }
    if (change.metric.type === "state") {
    	 scores[change.metric.id] = {
        	type: change.metric.type,
          name: change.metric.name,
          value: change.delta.new
        };
    }
  }
  for(var key in scores) {
  	var score = scores[key];
  	if (score.type === 'point') {
      item += '<div class="activity-tooltip" title="'+ score.name +'">';
  		item += "&nbsp;&nbsp;+&nbsp;"+ score.value;//+ get_image("small", key);
  	}
  	if (score.type === 'set') {
      item += '<div class="activity-tooltip" title="Badge: '+ score.value +'">';
  		var metric_id = key.split("||")[0];
  		var item_id = key.split("||")[1];
  		item += get_image("small", metric_id, item_id);
  	}
  	if (score.type === 'state') {
      item += '<div class="activity-tooltip" title="Level: '+ score.value +'">';
  		item += get_image("small", key, null, score.value);// + " " + score.value;
  	}
  	item += '</div>';
  }
  return item;
}

function set_activity_items(selector, activities, key) {
    var act_list = '';
    for (var i = 0; i < activities.length; i++) {
      var event = activities[i];
      if ((key === 'all' || key === 'general') && (event.action_id === 'login' || event.action_id === 'logout' || event.action_id === 'sign_up')) {
        act_list += '<div class="activity-list-item"><div class="activity-list-item-image">';
        act_list += '</div><div class="activity-list-item-text">';
        if (event.action_id === 'login') {
          act_list += 'You Gained on login';
        }
        if (event.action_id === 'logout') {
          act_list += 'You Gained on logout';
        }
      if (event.action_id === 'sign_up') {
        act_list += 'You Gained on Registration';
      }
        act_list += '<div class="activity-list-item-score">' + render_changes(event.action_result) + '</div></div></div></div>';
      }
      if ((key === 'all' || key === 'channel') && event.action_id === 'content_viewed') {
        act_list += '<div class="activity-list-item"><div class="activity-list-item-image">';
        act_list += '</div><div class="activity-list-item-text">';
        if (event.action_data.content_type === 'video') {
          act_list += 'Watched the video "' + event.action_data.content_name + '"';
        }
        if (event.action_data.content_type === 'document') {
          act_list += 'Reviewed the document "' + event.action_data.content_name + '"';
        }
        if (event.action_data.content_type === 'image') {
          act_list += 'Viewed the image "' + event.action_data.content_name + '"';
        }
        act_list += '<div class="activity-list-item-score">' + render_changes(event.action_result) + '</div></div></div></div>';
      }
      if ((key === 'all' || key === 'assessments') && event.action_id === 'quiz_completed') {
        act_list += '<div class="activity-list-item"><div class="activity-list-item-image">';
        act_list += '</div><div class="activity-list-item-text">';
        act_list += 'You completed the quiz '+ event.action_data.id + ' with score '+event.action_data.score;
        act_list += '<div class="activity-list-item-score">' + render_changes(event.action_result) + '</div></div></div></div>';
      }
      if ((key === 'all' || key === 'qas') && event.action_id === 'question_asked') {
        act_list += '<div class="activity-list-item"><div class="activity-list-item-image">';
        act_list += '</div><div class="activity-list-item-text">';
        act_list += 'You asked a question';
        act_list += '<div class="activity-list-item-score">' + render_changes(event.action_result) + '</div></div></div></div>';
      }
      if ((key === 'all' || key === 'qas') && event.action_id === 'question_marked_as_faq') {
        act_list += '<div class="activity-list-item"><div class="activity-list-item-image">';
        act_list += '</div><div class="activity-list-item-text">';
        act_list += 'Your question was marked as FAQ';
        act_list += '<div class="activity-list-item-score">' + render_changes(event.action_result) + '</div></div></div></div>';
      }
    }
    $(selector).html(act_list);
}
// <div style="width: 40px; height: 40px; line-height: 40px; background-image: url(&quot;/pl/image?size=small&amp;metric_id=3_day_login_streak&quot;); padding-left: 5px; font-size: 12px;">+50</div>

// function Leaderboard() {
//   this.leaderboard_page_index = 0;
//   this.leaderboard_type = "alltime";
//   this.leaderboard_total = 100;
//   this.players = '';
// }

// function show_leaderboard(id) {
//   leaderboard_total = response.total;
//   for(var i = 0; i<response.data.length; i++) {
//       var item = response.data[i];
//       if (item != null) {
//       }
//       var name_selected_class = "leaderboard-user-row-name";
//       if (item.is_primary != null) {
//         name_selected_class = "leaderboard-user-row-name-selected";
//       }
//       var score_selected_class = "leaderboard-user-row-score";
//       if (item.is_primary != null) {
//         score_selected_class = "leaderboard-user-row-score-selected";
//       }
//       players += '<div class="row leaderboard-user-row">'
//       + '<div class="col-md-1 leaderboard-user-row-rank">' + item.rank  + '</div>'
//       + '<div class="col-md-2 leaderboard-user-row-image">'
//       +   '<img height="50" src="http://ultron.dev/portal/theme/default/img/avatar.png" class="img-circle" alt="">'
//       + '</div>'
//       + '<div class="col-md-4 '+ name_selected_class+'"> ' + item.player.alias + '</div>'
//       + '<div class="col-md-4 '+score_selected_class+'">' + item.score + '</div>'
//       + '</div>';
//   }
//   $(id).html(players);
// }

$(document).ready(function(){
    loadPlayerActivity = function(filterOption, page, contentOnly){
      var xmlHTTPRequest = $.ajax({
        url : playerActivityURL + "?" + $.param({
            filter : filterOption,
            page : page,
            contentOnly : contentOnly
          }),
        type : "get",
        contentType : "application/x-www-form-urlencoded; charset=UTF-8",
        dataType : "html"
      });

      xmlHTTPRequest.done(function(response, textStatus, jqXHR){
        if(contentOnly)
          $("#player-activity-container").html(response);
        else
          $("#player-activity-main-container").html(response);
      });

      xmlHTTPRequest.fail(function(jqXHR, textStatus, errorThrown){
        alert(textStatus);
      });

      xmlHTTPRequest.always(function(){

      });
    };
});

