(function($) {
    $(document).ready(function() {
        $('.opt-mecom-stripe-ajax-link').on('click', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            $.ajax({
                type: 'GET',
                url: url,
                success: function(response) {
                    window.location.reload(true);
                }
            });
        });
    });
})(jQuery);