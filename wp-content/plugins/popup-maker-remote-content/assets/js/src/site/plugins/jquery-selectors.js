/*******************************************************************************
 * Copyright (c) 2018, WP Popup Maker
 ******************************************************************************/
(function ($) {
    if ($.expr[':'].external === undefined) {
        $.expr[':'].external = function (obj) {
            return obj && obj.href && !obj.href.match(/^mailto:/)
                && (obj.hostname != document.location.hostname);
        };
    }

    if ($.expr[':'].internal === undefined && $.expr[':'].external !== undefined) {
        $.expr[':'].internal = function (obj) {
            return $(obj).is(':not(:external)');
        };
    }

    if ($.expr[':'].regex === undefined) {
        $.expr[':'].regex = function (elem, index, match) {
            var param = match[3].split(',', 1)[0],
                pattern = match[3].split(',').slice(1).join(','),
                validLabels = /^(data|css):/,
                attr = {
                    method: param.match(validLabels) ? param.split(':')[0] : 'attr',
                    property: param.replace(validLabels, '')
                },
                regexFlags = 'ig',
                regex = new RegExp(pattern.replace(/^\s+|\s+$/g, ''), regexFlags);


            return regex.test(jQuery(elem)[attr.method](attr.property));
        };
    }
}(window.jQuery));