$.ajaxSetup({
    headers: {
        'X-CSRF-Token': $('meta[name=_token]').attr('content')
    }
});
var questionIds = [];
$('#add-flashcards').on('submit', function(event) {
    event.preventDefault();
    var $form = $(this),
        data = $($form).getFormValues(),
        url = $form.attr('action');
    var posting = $.ajax({
        url: url,
        type: 'post',
        data: data,
        dataType: 'json'
    });
    posting.done(function(data) {
        if (data.fail) {
            $('.error').hide().parent().removeClass('has-error');
            $.each(data.errors, function(key, value) {
                if (key.indexOf(".") != -1) {
                    card = key.split('.');
                    key = card.join('_');
                    var errorDiv = '#' + key + '_error';
                    $(errorDiv).addClass('required').show();
                    $(errorDiv).parent().addClass('has-error');
                    $(errorDiv).empty().append('Please fill the '+card[2]);
                }
                else{
                    var errorDiv = '#' + key + '_error';
                    $(errorDiv).addClass('required').show();
                    $(errorDiv).parent().addClass('has-error');
                    $(errorDiv).empty().append(value);    
                }
                              
            });
            var errorDiv = $('.has-error:visible').first();
            var scrollPos = errorDiv.offset().top;
            $('html, body').animate({ scrollTop: scrollPos }, 'slow');
        } else {
            $('.error').html('').parent().removeClass('has-error');
            window.location = chost+data.url;
        }
    });
});
jQuery.fn.getFormValues = function() {
    var formvals = {};
    jQuery.each(jQuery(':input', this).serializeArray(), function(i, obj) {
        if (formvals[obj.name] == undefined) formvals[obj.name] = obj.value;
        else if (typeof formvals[obj.name] == Array) formvals[obj.name].push(obj.value);
        else formvals[obj.name] = [formvals[obj.name], obj.value];
    });
    return formvals;
}
$('#add-new-card').on('click', function(event) {
    event.preventDefault();
    var self = this;
    var front = $(self).parent().parent().find('div.slide:last').find('textarea').eq(0);
    var back = $(self).parent().parent().find('div.slide:last').find('textarea').eq(1);
    if ( front.val().length == 0 && back.val().length == 0 ) {
        front.parent().addClass('has-error').find('span.error').html("Please fill the front").show();
        back.parent().addClass('has-error').find('span.error').html("Please fill the back").show();
    }
    else if(front.val().length == 0){
        back.parent().removeClass('has-error').find('span.error').hide();
        front.parent().addClass('has-error').find('span.error').html("Please fill the front").show();
    }
    else if(back.val().length == 0){
        front.parent().removeClass('has-error').find('span.error').hide();
        back.parent().addClass('has-error').find('span.error').html("Please fill the back").show();
    }
     else {
        var card = $.ajax({
            url: chost + '/cp/flashcards/new-card',
            data: {
                'count': count++
            },
            type: 'get'
        });
        card.done(function(data) {
            front.parent().find('span.error').hide().parent().removeClass('has-error');
            back.parent().find('span.error').hide().parent().removeClass('has-error');
            $(data).insertAfter('div.slide:last');
        });
        card.fail(function(data) {
            console.log('Error occured');
        });
    }
    return false;
});
$(document).on('click', '.delete-card', function() {
    simpleloader.fadeIn();
    var parent = $(this).parent().parent();
    if ($('div.slide').length > 1) {
        parent.remove();
    } else {
        alert("Minimum 1 set is required");
    }
    simpleloader.fadeOut();
    return false;
});
$(document).on('click', '#select-questions', function(e) {
    e.preventDefault();
    questionIds = [];
    var questionbanks = $.ajax({
        url: chost + '/cp/flashcards/questions',
        method: 'get',
        dataTye: 'json'
    });
    questionbanks.done(function(data) {
        $('#questions').find('#questionbanks-dropdown').html(data).end().modal('show');
    });
});
$(document).on('change', '#qb-select', function() {
    simpleloader.fadeIn();
    var questions = $.ajax({
        url: chost + '/cp/flashcards/questions',
        data: {
            'qbank': this.value
        },
        type: 'post'
    });
    questions.done(function(data) {
        front.parent().find('span.error').hide().parent().removeClass('has-error');
        $(data).insertAfter('div.slide:last');
    });
    questions.fail(function(data) {
        console.log(response);
    });
    simpleloader.fadeOut();
});

// $(document).on('click', '.editor', function(event){
//     var $this = $(this),
//         id = $this.attr('id');
//     $this.attr('contenteditable', true);
//     var editor = CKEDITOR.instances[id];
//     if (editor) { editor.destroy(true); }
//     CKEDITOR.inline( id );
//     CKEDITOR.instances[id].on('change', function(){
//         // $(this).prev().html(this.getData());
//         $("#"+id).parent().parent().find('textarea').html(this.getData());
//     });
// });