(function ($) {
  var menuToggle = $('#js-mobile-menu').unbind();
  $('#js-navigation-menu').removeClass("show");

  menuToggle.on('click', function(e) {
    e.preventDefault();
    $('#block-atletico-main-menu .menu').slideToggle(function(){
      if($('#block-atletico-main-menu .menu').is(':hidden')) {
        $('#block-atletico-main-menu .menu').removeAttr('style');
      }
    });
  });
})(jQuery);
