(function ($) {
    $('.game-list').each( function() {
        if ($(this).find('a').length) {
            $(this).click(function() {
                window.location=$(this).find('a').attr('href');
                return false;
            }).hover(function() {
                $(this).toggleClass('hover');
            });
        }
    });
})(jQuery);
