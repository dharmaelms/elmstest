$(document).ready(function(){

      $("#new-user-body, #new-post-body, #access-request-body, #active-feed-body, #active-users-body, #new-announcement-body, #new-feed-body, #new-event-body, #new-items-body").scroll(function() {
      if ($(this).scrollTop() > 50){ 
      $('#new-user-table-content  #datatable thead, #active-users-table-content #datatable thead').css({'position' : 'fixed','width' : '856px','top' : '59px','left' : '14px'});
      $('#new-post-table-content #datatable thead, #access-request-table-content #datatable thead, #active-feed-table-content #datatable thead, #new-announcement-table-content #datatable thead, #new-feed-table-content #datatable thead, #new-event-table-content #datatable thead, #new-items-table-content #datatable thead').css({'position' : 'fixed','width' : '556px','top' : '59px','left' : '14px'});

      $('#new-user-table-content  #datatable tr th, #active-users-table-content #datatable tr th').css({'width' : '285px'});
      $('#new-post-table-content #datatable tr th, #access-request-table-content #datatable tr th, #new-announcement-table-content #datatable tr th, #new-event-table-content #datatable tr th, #new-items-table-content #datatable tr th').css({'width' : '277px'});
      $('#new-feed-table-content #datatable tr th, #active-feed-table-content #datatable tr th').css({'width' : '556px'});

      $('#new-user-table-content #datatable, #active-users-table-content #datatable, #new-post-table-content #datatable, #access-request-table-content #datatable, #active-feed-table-content #datatable, #new-announcement-table-content #datatable, #new-feed-table-content #datatable, #new-event-table-content #datatable, #new-items-table-content #datatable').css({'margin-top' : '35px'});
      $('.modal-table-data').css({'margin-top' : '20px'});
    }
    else{
      $('.modal-table-data').css({'margin-top' : '0px'});
      $('#new-user-table-content #datatable thead, #active-users-table-content #datatable thead, #new-post-table-content #datatable thead, #access-request-table-content #datatable thead, #active-feed-table-content #datatable thead, #new-announcement-table-content #datatable thead, #new-feed-table-content #datatable thead, #new-event-table-content #datatable thead, #new-items-table-content #datatable thead').css({'position' : 'static'});
    }
  });
});
