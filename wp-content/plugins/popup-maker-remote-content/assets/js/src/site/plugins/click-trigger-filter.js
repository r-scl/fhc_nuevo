/*******************************************************************************
 * Copyright (c) 2018, WP Popup Maker
 ******************************************************************************/
(function ($) {
    pum.hooks.addFilter('pum.trigger.click_open.selectors', function (trigger_selectors, settings, $popup) {
        if (typeof settings.rc_post_type_regex !== 'undefined') {
            trigger_selectors.push('a:regex(href,' + settings.rc_post_type_regex + ')')
        }

        return trigger_selectors;
    });
}(window.jQuery));