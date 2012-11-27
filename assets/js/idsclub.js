// Wrap this shit.
window.yoozi = window.yoozi || {};
yoozi.app = yoozi.app || {};

(function () {

  "use strict"; // jshint ;_;

  /**
   * Bootstrap for all pages
   *
   * @dependency bootstrap-tooltip.js
   */
  $(document).ready(function () {
	// Tooltip on navbar
	$('.nav-pills li').tooltip({
		selector: 'a[rel="tooltip"]',
		placement: 'bottom'
	});
	// Tooltip on footer links
	$('#footer').tooltip({
		selector: 'a[rel="tooltip"]',
		placement: 'top'
	});
  });
})();