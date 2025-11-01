// Minimal shim to avoid runtime errors if PerfectScrollbar is not available
// Keeps Soft UI dashboard script from failing while preserving app functionality
window.PerfectScrollbar = window.PerfectScrollbar || function () {
  return {
    update: function () {},
    destroy: function () {}
  };
};