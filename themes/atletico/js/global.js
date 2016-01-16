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
    $('.jersey-color').each(function(index, value) {
        $(this).css('background-color', $(this).attr('data-color'));
    });
})(jQuery);
