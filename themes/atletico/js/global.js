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

    var cups = ['division1', 'division2'];
    $.each(cups, function (index, value) {

        $.ajax({
            url: "/api/tournaments/winter2016/".concat(value)
        }).then(function(data) {
            var teams = [];
            var results = [];
            $.each(data, function (index, value) {
                teams.push([value.home, value.away]);
                results.push([value.home_score, value.away_score]);
            });
            var data = {
                teams : teams,
                results :
                    [
                        [
                        ],
                        [
                            [
                            ]
                        ]
                    ]
            };
            $('#'.concat(value)).bracket({
                init: data,
            });
        });

    });
})(jQuery);
