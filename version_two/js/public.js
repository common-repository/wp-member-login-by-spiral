(function ($) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

    // Function to run before form submission
    function beforeSubmit(event,formId) {
		event.preventDefault();
        var login_id = document.querySelector(".login_id").value
        var _nonce = document.querySelector("#_nonce").value

        jQuery.ajax({
            url: ajax.api,
            type: 'POST',
            data: {
                action: 'prefix_ajax_first',
                login_id: login_id,
                _nonce: _nonce
            },
			success: function(response) {
				// Submit the specified form programmatically
            	document.getElementById(formId).submit();
			}
        });
    }

    // Wait for the DOM to be fully loaded
	document.addEventListener("DOMContentLoaded", function () {
		// Get all form elements with class ".wpmls_login_form"
		var forms = document.querySelectorAll(".wpmls_login_form");

		// Attach event listener to each form
		forms.forEach(function(form) {
			form.addEventListener("submit", function(event) {
				// Get the id of the form being submitted
				var formId = form.id;
				// Call the function to run before form submission
				beforeSubmit(event, formId);
			});
		});
	});
})(jQuery);