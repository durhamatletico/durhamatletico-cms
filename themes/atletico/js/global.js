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
            var round_two_results = [];
            var round_three_results = [[]];
            $.each(data, function (index, value) {
                if (parseInt(value.cup_round) == 1) {
                    // Just get the teams once, in round one.
                    teams.push([value.home, value.away]);
                    round_one_results.push([parseInt(value.home_score), parseInt(value.away_score)]);
                }
                else if ((parseInt(value.cup_round) == 2) && parseInt(value.grouping) == 1) {
                    // Only look at winning teams.
                    round_two_results.push([parseInt(value.home_score), parseInt(value.away_score)]);
                }
                else if (parseInt(value.cup_round) == 3 && parseInt(value.grouping) == 1) {
                    // Only look at finalists.
                    if (value.home_score) {
                        // Game has been played.
                        round_three_results.push([parseInt(value.home_score), parseInt(value.away_score)]);
                    }
                }
            });

            var data = {
                teams : teams,
                results :
                    [
                        round_one_results,
                        round_two_results,
                        round_three_results
                    ]
            };
            $('#'.concat(value)).bracket({
                init: data
            });
        });

    });
})(jQuery);
