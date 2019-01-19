/* COOKIE */
(function () {
  var modal = $('.js-cookie--modal');
  var storageKey = 'showFz152Modal';
  var hideModal = window.localStorage.getItem(storageKey) || '';

  if (modal.length && hideModal !== '1') {
      modal.show();
      modal.find('.js-confirm').on('click', function (event) {
          event.preventDefault();
          window.localStorage.setItem(storageKey, '1');
          modal.hide();

          return false;
      });
  }
})();