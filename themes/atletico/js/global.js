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

    var data = {
        teams: [
            ["Pitbulls", "Green Street"],
            ["Real Durham", "Muchos Nachos"],
            ["Hustle & Flow", "Regulators"],
            ["MVFC", "Esemplastic Power"]
        ],
        results: [
            [
                [
                    [7, 6],
                    [17, 9],
                    [10, 5],
                    [3, 0],
                ],
                [
                    [8, 10],
                    [8, 10]
                ],
                [
                    [],
                    [],
                ]
            ]
        ]
    };
    $('#division1').bracket({
        init: data
    });

    var data = {
        teams: [
            ["Motorco", "Durham Thursday"],
            ["Minions", "DireWolfpack"],
            ["Stepside", "AU"],
            ["America", "Durham Monday"]
        ],
        results: [
            [
                [
                    [9, 6],
                    [4, 6],
                    [18, 6],
                    [10, 5],
                ],
                [
                    [7, 3],
                    [10, 7]
                ],
                [
                    [],
                    [],
                ]
            ]
        ]
    };
    $('#division2').bracket({
        init: data
    });

})(jQuery);
