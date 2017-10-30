(function ($) {
  var menuToggle = $('#js-mobile-menu').unbind();
  $('#js-navigation-menu').removeClass("show");

  menuToggle.on('click', function(e) {
    e.preventDefault();
    $('#block-mainnavigation .menu').slideToggle(function(){
      if($('#block-mainnavigation .menu').is(':hidden')) {
        $('#block-mainnavigation .menu').removeAttr('style');
      }
    });
  });
})(jQuery);
