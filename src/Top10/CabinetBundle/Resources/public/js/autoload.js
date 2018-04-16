(function($) {

    $(function() {
        $('#continue-shopping').click(function() {
            console.info(window.history.length);
            if( window.history.length > 1 ) {
                window.history.back();
                return false;
            }

            return true;
        });
    })

})(jQuery);