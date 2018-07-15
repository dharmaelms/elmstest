var questionBankId, questionbankDiv, questionType;
$(document).ready(function(){
    $(document).on('change', '#questionbank-dropdown', function() {
        questionBankId = this.value,
        simpleloader.fadeIn();
        questionbankDiv = '#questionbank-' + questionBankId;
        if (questionBankId != '') {
            if (!questionbankDiv.length) {
                $(questionbankDiv).show();
            } else {
                var questionsList = $.get(chost + '/cp/flashcards/questions-list/' + questionBankId);
                questionsList.done(function(response) {
                    updateTable(response);
                });
                questionsList.fail(function(response){
                	console.log(response);
                });
            }
        }
        simpleloader.fadeOut();
    });
    $(document).on('click','.pagination li a', function(e){
    	var page = $(this).attr('href').split('page=')[1];
        simpleloader.fadeIn();
        questionType = $('#question_type').val();
        questionBankId = $('#questionbank-dropdown').val();
        itemsPerPage = $('#table_length').val();
        var pages = $.get(chost + '/cp/flashcards/questions-list/' + questionBankId+'?questionType='+questionType+'&page='+page+'&itemsPerPage='+itemsPerPage);
        pages.done(function(response){
            updateTable(response);            
        });
        pages.fail(function(response){
            console.log(response);
        });
        simpleloader.fadeOut();
        e.preventDefault();
    });
    $(document).on('change', '#table_length', function(e){
        if(typeof questionBankId == 'undefined' || questionBankId == ''){
            return false;
        }
        simpleloader.fadeIn();
        questionType = $('#question_type').val();
        questionBankId = $('#questionbank-dropdown').val();
        var length = $.get(chost + '/cp/flashcards/questions-list/' + questionBankId+'?questionType='+questionType+'&itemsPerPage='+this.value);
        length.done(function(response){
            updateTable(response);            
        });
        length.fail(function(response){
            console.log(response);
        });
        simpleloader.fadeOut();
        e.preventDefault();        
    })
    $(document).on('click', '.select-all', function(){
        var allChecked = $(this).prop('checked');
        if(allChecked){
            $('.questions-id').each(function(){
               $(this).prop('checked', true);
               // console.log(this.value)
               if($.inArray(this.value, questionIds) === -1){
                    questionIds.push(this.value);
                }
            });            
        }else {
            $('.questions-id').each(function(){
               $(this).prop('checked', false);
               // console.log(this.value);
               if($.inArray(this.value, questionIds) !== -1){
                    var index = questionIds.indexOf(this.value);
                    if (index >= 0) {
                      questionIds.splice( index, 1 );
                    }
                }
            });
        }
        // console.log(questionIds);
        updateCount();
    });  
    <!-- select specific questions -->
    $(document).on('click', '.questions-id', function(){
        var checked = $(this).prop('checked'),
            value = $(this).val();
        if(checked){
            if($.inArray(this.value, questionIds) === -1){
                questionIds.push(value);
            }
        }else {
            if($.inArray(this.value, questionIds) !== -1){
                var index = questionIds.indexOf(this.value);
                if (index >= 0) {
                  questionIds.splice( index, 1 );
                }
            }
        }
        // console.log(questionIds);
        updateCount();
    });
    $(document).on('change', '#question_type', function(){
        simpleloader.fadeIn();
        questionType = this.value;
        questionBankId = $('#questionbank-dropdown').val();
        length = $('#table_length').val();
        var questionsList = $.get(chost + '/cp/flashcards/questions-list/' + questionBankId+'?questionType='+questionType+'&itemsPerPage='+length);
        questionsList.done(function(response) {
            updateTable(response);
        });
        questionsList.fail(function(response){
            console.log('error');
        }); 
        simpleloader.fadeOut();        
    });
    $(document).on('click', '#question-create-cards', function(){
        var ids = questionIds;
        if(questionIds.length == 0){
            alert('Select at least one question');
            return false;
        }
         var questionCards = $.ajax({
            url: chost+'/cp/flashcards/question-create-cards',
            type: 'POST',
            data: {'questions': ids, 'count': count},
        });
        questionCards.done(function(response){
            var position = $('input[name="position"]:checked').val();
            $(response).insertBefore('div.slide:last');
            $('.card-column').each(function() { //removing empty cards
                if($(this).find('.front').val().trim().length == 0 && $(this).find('.back').val().trim().length == 0) {
                    $(this).remove();
                }
            });
            count = count+questionIds.length;
            $('#questions').modal('hide');
        });
        questionCards.fail(function(response){
            console.log(response);
        });
    });
    
    function updateSelected(){
        $('.questions-id').each(function(){
            if( $.inArray(this.value, questionIds) != -1){
                $(this).prop('checked', true);
            }
        });
        if($('#datatable tbody tr').length === $('.questions-id:checked').length){
            $('.select-all').prop('checked', true);
        }
        updateCount();        
    }
    function updateCount(){
        $('#selected span').html(questionIds.length);
    }
    function updateTable(response){
        $('#table').html(response);
        updateSelected();
    }
});