$.ajaxSetup({
    headers: {
        'X-CSRF-Token': $('meta[name=_token]').attr('content')
    }
});
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
                    key = key.split('.').join('_');
                }
                var errorDiv = '#' + key + '_error';
                $(errorDiv).addClass('required').show();
                $(errorDiv).parent().addClass('has-error');
                $(errorDiv).empty().append('Required');
                var errorDiv = $('.has-error:visible').first();
                var scrollPos = errorDiv.offset().top;
                $(window).scrollTop(scrollPos);
            });
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
    if (front.val().length == 0) {
        front.parent().addClass('has-error').find('span.error').html("Required").show();
    } else {
        var card = $.ajax({
            url: chost + '/cp/flashcards/new-card',
            data: {
                'count': count++
            },
            type: 'get'
        });
        card.done(function(data) {
            front.parent().find('span.error').hide().parent().removeClass('has-error');
            $(data).insertAfter('div.slide:last');
        });
        card.fail(function(data) {
            console.log('Error occured');
        });
    }
    return false;
});
$(document).on('click', '.delete-card', function() {
    var parent = $(this).parent().parent();
    if ($('div.slide').length > 1) {
        parent.remove();
    } else {
        alert("Minimum 1 set is required");
    }
});
$(document).on('click', '#select-questions', function(e) {
    e.preventDefault();
    var questionbanks = $.ajax({
        url: chost + '/cp/flashcards/question-banks',
        method: 'post',
        dataTye: 'json'
    });
    questionbanks.done(function(data) {
        $('#questions').find('#questionbanks-dropdown').html(data).end().modal('show');
    });
})
$(document).on('change', '#qb-select', function() {
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
});
CKEDITOR.disableAutoInline = true;
$(document).on('click', '.editor', function() {
    var editor = CKEDITOR.instances[$(this).attr('id')];
    if (editor) {
        editor.destroy(true);
    }
    CKEDITOR.inline($(this).attr('id'), {
        on: {
            change: function(event) {
                $('#' + this.element.$.id + '').parent().parent().find('textarea.dynamic').text(event.editor.getData());
            },
            instanceReady: function(event) {
                $('#' + this.element.$.id + '').parent().parent().find('textarea.dynamic').text(event.editor.getData());
            },
        },
        toolbar: [{
            name: 'clipboard',
            items: ['Undo', 'Redo']
        }, {
            name: 'basicstyles',
            items: ['Bold', 'Italic']
        }, {
            name: 'paragraph',
            items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyCenter']
        }, {
            name: 'links',
            items: ['Link', 'Unlink']
        }, {
            name: 'insert',
            items: ['Image', 'Table']
        }, {
            name: 'document',
            items: ['Source']
        }, {
            name: 'format',
            items: ['RemoveFormat']
        }]
    });
});