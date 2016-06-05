/**
 * @file
 * Global JS.
 */

(function ($) {

    // Disable submit button on node form after click.
    $('.node-form').submit(function(){
        $('#edit-submit').attr('disabled', 'disabled');
    });

    // TODO: This should not be global.
    // Make entire row clickable.
    $('.game-list').each(function() {
      if ($(this).find('a').length) {
          $(this).click(function() {
              window.location = $(this).find('a').attr('href');
              return false;
          }).hover(function() {
              $(this).toggleClass('hover');
          });
      }
    });

    // TODO: This should not be global.
    // Set jersey color on game lists.
    $('.jersey-color').each(function(index, value) {
        $(this).css('background-color', $(this).attr('data-color'));
    });

    // TODO: This should not be global.
    // Spring 2016 brackets.
    var data = {
        teams: [
          ["Real Durham", "Invisible Feet"],
            ["Strongest", "America"],
            ["Green Street", "Regulators"],
            ["Bull City", "Hustle Flow"]
        ],
        results: [
            [
                [
                    [],
                    [],
                    [],
                    [],
                ],
                [
                    [],
                    []
                ],
                [
                    [],
                    [],
                ]
            ]
        ]
    };
    $('#spring2016-division1').bracket({
        init: data
    });

    data = {
        teams: [
            ["PLQS", "Thunder Cats"],
            ["Grease Pigeons", "AU"],
            ["LDE", "Monday"],
            ["Stepside", "Red Wolves"]
        ],
        results: [
            [
                [
                    [],
                    [],
                    [],
                    [],
                ],
                [
                    [],
                    []
                ],
                [
                    [],
                    [],
                ]
            ]
        ]
    };
    $('#spring2016-division2').bracket({
        init: data
    });


    // TODO: This should not be global.
    // Winter 2016 brackets.
    data = {
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
                    [12, 13],
                    [5, 8],
                ]
            ]
        ]
    };
    $('#division1').bracket({
        init: data
    });

    data = {
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
                    [5, 16],
                    [7, 12],
                ]
            ]
        ]
    };
    $('#division2').bracket({
        init: data
    });

})(jQuery);
