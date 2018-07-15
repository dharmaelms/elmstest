jQuery.fn.extend({
    autoHide : function () {
        $(this).delay(5000).slideUp({
            duration : 400,
            complete : function () {
                $(this).remove();
            }
        });
    }
});
