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
            var round_one_results = [];
            $.each(data, function (index, value) {
                teams.push([value.home, value.away]);
                round_one_results.push([value.home_score, value.away_score]);
            });

            for (var i = 0; i < round_one_results.length; i++) {
                for (var x = 0; x < round_one_results[i].length; x++) {
                    round_one_results[i][x] = parseInt(round_one_results[i][x]);
                }
            };
            var data = {
                teams : teams,
                results :
                    [
                        round_one_results,
                        [[], []],
                        [[]]
                    ]
            };
            $('#'.concat(value)).bracket({
                init: data
            });
        });

    });
})(jQuery);
