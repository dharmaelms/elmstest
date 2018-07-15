  function click_answer_hide_show(ids){ 
    var first = ids.substring(0,ids.indexOf(" ")); 
    var second  = ids.substr(ids.indexOf(" ") + 1); 
    if ($("#"+first).is(":visible")){ 
      $("#"+first).hide(); 
      $("#"+second).show(); 
    } else { 
      $("#"+first).show(); 
      $("#"+second).hide(); 
    } 
  }

  function question_hide_show(ids){ 
    var first = ids.substring(0,ids.indexOf(" ")); 
    var second  = ids.substr(ids.indexOf(" ") + 1); 
    if ($("#"+first).is(":visible")){ 
      $("#"+first).hide(); 
      $("#"+second).show(); 
    } else { 
      $("#"+first).show(); 
      $("#"+second).hide(); 
    } 
  }  

