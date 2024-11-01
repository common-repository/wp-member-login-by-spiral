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

    $(function () {
        const login_form = jQuery('form[name="loginform"]').length;
        if (login_form > 0) {
            var action_url = jQuery('form[name="loginform"]').attr('action').replace(/regist/ig, "area");
            let final_url = action_url;

            // Regexp that match regXXX url
            const regex = /\/\/reg+[0-9]{1,}/i;
            // Get regex word
            const regWord = action_url.match(regex);

            // If found word
            if (regWord) {
                // Replace reg by area
                const replacedArea = regWord.toString().replace(/reg/i, 'area');

                // Replace final regex by replacedArea
                final_url = action_url.replace(regex, replacedArea);
            }

            jQuery('form[name="loginform"]').attr('action', final_url);
        }
    });
})(jQuery);