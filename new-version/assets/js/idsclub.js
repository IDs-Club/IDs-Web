// Wrap this shit.
window.yoozi = window.yoozi || {};
yoozi.app = yoozi.app || {};

(function () {

  "use strict"; // jshint ;_;

  yoozi.app.qs = function(key){
	key = key.replace(/[*+?^$.\[\]{}()|\\\/]/g, "\\$&"); // Escape RegEx control chars
    var match = location.search.match(new RegExp("[?&]" + key + "=([^&]+)(&|$)"));
    return match && decodeURIComponent(match[1].replace(/\+/g, " "));
  };

  /**
   * Bootstrap for all pages
   *
   * @dependency bootstrap-tooltip.js
   */
  $(document).ready(function () {
	// Tooltip on navbar
	$('.nav li').tooltip({
		selector: 'a[rel="tooltip"]',
		placement: 'bottom'
	});
	// Tooltip on footer links
	$('#footer').tooltip({
		selector: 'a[rel="tooltip"]',
		placement: 'top'
	});
	// Tooltip on sponsers
	$('#thanks, .qq-group').tooltip({
		selector: 'a[rel="tooltip"]',
		placement: 'bottom'
	});
  });
})();