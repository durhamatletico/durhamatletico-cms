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

  // Winter 2017 brackets.
  if (document.getElementsByClassName('page-title')[0].innerHTML.indexOf('Winter 2017') > -1) {
    $.ajax({
      url: "/api/tournaments/2199?_format=json",
      method: "get",
      headers: {
        "content-type": "application/json",
        "accept": "application/json"
      },
      success: function(data, status, xhr) {
        $('#winter-2017-division2').bracket({
          init: data
        })
      }
    });
    $.ajax({
      url: "/api/tournaments/2203?_format=json",
      method: "get",
      headers: {
        "content-type": "application/json",
        "accept": "application/json"
      },
      success: function(data, status, xhr) {
        $('#winter-2017-division1').bracket({
          init: data
        })
      }
    });
  }


  // Fall 2016 brackets.
  if (document.getElementsByClassName('page-title')[0].innerHTML.indexOf('Fall 2016') > -1) {
    $.ajax({
      url: "/api/tournaments/1924?_format=json",
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json"
      },
      success: function(data, status, xhr) {
        $('#fall-2016-division1').bracket({
          init: data
        })
      }
    });

    $.ajax({
      url: "/api/tournaments/1923?_format=json",
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json"
      },
      success: function(data, status, xhr) {
        $('#fall-2016-division2').bracket({
          init: data
        })
      }
    });
  }

    // Summer 2016 brackets.
  if (document.getElementsByClassName('page-title')[0].innerHTML.indexOf('Durham Summer Cup of Futsal') > -1) {
    $.ajax({
      url: "/api/tournaments/1655?_format=json",
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json"
      },
      success: function(data, status, xhr) {
        $('#summer-2016').bracket({
          init: data
        })
      }
    });
  }

    // TODO: This should not be global.
    // Spring 2016 brackets.
    var data = {
        teams: [
          ["Real Durham", "Invisible Feet"],
          ["Bull City", "Hustle Flow"],
          ["Strongest", "America"],
          ["Green Street", "Regulators"]
        ],
        results: [
            [
                [
                  [10, 6],
                  [6, 10],
                  [5, 0],
                  [11, 4],
                ],
                [
                  [4, 11],
                  [9, 5]
                ],
                [
                  [4, 5],
                  [9, 8],
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
          ["Stepside", "Red Wolves"],
          ["Grease Pigeons", "AU"],
          ["LDE", "Monday"]
        ],
        results: [
            [
                [
                  [11, 6],
                  [3, 4],
                  [8, 7],
                  [4, 8],
                ],
                [
                  [2, 4],
                  [0, 6]
                ],
                [
                  [5, 4],
                  [0, 3],
                ]
            ]
        ]
    };
    $('#spring2016-division2').bracket({
        init: data
    });


    // TODO: This should not be global.
    // Winter 2016 brackets.
  $.ajax({
    url: "/api/tournaments/1208?_format=json",
    method: "GET",
    headers: {
      "Content-Type": "application/json",
      "Accept": "application/json"
    },
    success: function(data, status, xhr) {
      $('#division1').bracket({
        init: data
      })
    }
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
