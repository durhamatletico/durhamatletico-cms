(function ($) {
  const $menuToggle = $('#js-mobile-menu-button').unbind();
  const $menuClose = $('#js-mobile-menu-close').unbind();
  const $mainNav = $('#js-mobile-menu');
  $mainNav.removeClass('show');

  $menuToggle.on('click', function (e) {
    toggleMenu(e);
  });

  $menuClose.on('click', function (e) {
    toggleMenu(e);
  });

  // Slide the main menu open or closed.
  function toggleMenu(e) {
    e.preventDefault();
    $mainNav.slideToggle(function (){
      if ($mainNav.is(':hidden')) {
        $mainNav.removeAttr('style');
      }
    });
    $menuToggle.toggleClass('open');
  }


})(jQuery);
