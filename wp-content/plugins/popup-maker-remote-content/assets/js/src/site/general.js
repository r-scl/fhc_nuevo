(function ($) {
    "use strict";

    $.fn.popmake.rc_user_args = {};

    $('.pum.remote-content')
        .on('pumInit', function () {
            var id = PUM.getSetting(this, 'id');

            $.fn.popmake.rc_user_args[id] = {};
        })
        /**
         * Shows loader and hides content area.
         */
        .on('pumBeforeOpen', function () {
            $('.pum-rc-box', this).each(function () {
                var $box = $(this),
                    $content = $('.pum-rc-content-area', $box).hide(0),
                    $loader = $('.pum-loader', $box).show(0),
                    settings = $box.data('settings') || {
                        min_height: "200"
                    };

                $box.css({minHeight: settings.min_height + 'px'});
                $loader.fadeIn(0).css({height: settings.min_height + 'px'});
                $content.fadeOut(0);
            });
        })
        .on('pumAfterOpen', function () {
            var $popup = PUM.getPopup(this),
                popupID = PUM.getSetting($popup, 'id'),
                $container = $popup.popmake('getContainer'),
                $trigger = false;

            try {
                $trigger = $($.fn.popmake.last_open_trigger);
            } catch (error) {
                $trigger = false;
            }

            /**
             * Gets a url from the trigger or its children.
             *
             * @returns {boolean}
             */
            function trigger_link() {
                var _$trigger = $trigger,
                    link = false;

                if ($trigger === false || !$trigger.length) {
                    return false;
                }

                if (!_$trigger.is('a[href]')) {
                    if (_$trigger.find('a[href]').length) {
                        _$trigger = _$trigger.find('a[href]');
                    } else {
                        _$trigger = false;
                    }
                }

                if (_$trigger) {
                    link = _$trigger.prop('href');
                }

                return link;
            }

            $('.pum-rc-box', this).each(function () {
                var $box = $(this),
                    $content = $('.pum-rc-content-area', $box),
                    $loader = $('.pum-loader', $box),
                    settings = $box.data('settings') || {},
                    showContent = function () {
                        $content.fadeIn('slow', function () {
                            $box.css({minHeight: 'auto'});
                        });
                        $loader.fadeOut('slow');
                    };

                switch (settings.method) {
                case 'iframe':
                    var $iframe = $('.pum-remote-content-frame iframe', $box),
                        link = trigger_link(),
                        src = link && link !== '' ? link : (settings.iframe_default_source || ''),
                        maxHeight = window.innerHeight - (($box.offset().top - $(window).scrollTop()) + parseInt($container.css('padding-bottom')) + parseInt($container.css('margin-bottom')) + parseInt($container.css('border-bottom-width')));

                    $iframe
                        .off('load.pum_rc')
                        .on('load.pum_rc', function () {
                            showContent();
                            if ( $iframe.outerHeight() > maxHeight ) {
                                $iframe.css({height: maxHeight});
                            } else if ( $iframe.outerHeight() < settings.min_height ) {
                                $iframe.css({height: settings.min_height});
                            } else {
                                $iframe.css({height: maxHeight});
                            }
                        })
                        // Backward compatibility.
                        .addClass('popmake-remote-content')
                        .prop('src', src)
                        .iFrameResize({
                            scrolling: 'auto',
                            minHeight: settings.min_height < maxHeight ? settings.min_height : maxHeight,
                            maxHeight: maxHeight,
                            heightCalculationMethod: 'lowestElement'
                        });
                    break;

                case 'load':
                    var link = trigger_link();
                    if (link && link !== '') {
                        $content.load(link + (settings.load_css_selector && settings.load_css_selector !== '' ? ' ' + settings.load_css_selector : ''), showContent);
                    }
                    break;

                case 'posts':
                    $popup.trigger('pumRcBeforePostsAjax');

                    var custom_args = $.extend({}, {
                        action: 'pum_rc',
                        method: 'posts',
                        popup_id: popupID,
                        url: trigger_link(),
                        postID: null
                    }, pum.hooks.applyFilters('pum.rc.posts_ajax_args', {}, $trigger, $box, $popup));

                    $.ajax(
                        {
                            method: "POST",
                            dataType: 'json',
                            url: pum_vars.ajaxurl,
                            data: custom_args
                        })
                        .done(function (response) {
                            var template = $box.find('script.pum-rc-content-template').html(),
                                postData = response.postdata || {},
                                search = new RegExp("\{" + Object.keys(postData).join('\}|\{') + "\}", 'gi');

                            if (response.success) {
                                template = template.replace(search, function (matched) {
                                    matched = matched.replace('{', '').replace('}', '');
                                    return postData[matched];
                                });

                                $content.html(template);
                            } else {
                                $content.html('<p>An error occurred or no data was returned by the server. Please try again.</p>');
                            }
                        })
                        .fail(function () {
                            $content.html('<p>An error occurred or no data was returned by the server. Please try again.</p>');
                        })
                        .always(showContent);
                    break;

                case 'ajax':
                    $popup.trigger('pumRcBeforeAjax');
                    // @deprecated 1.1.0
                    $container.trigger('popmakeRcBeforeAjax');

                    var custom_args = $.extend({}, {
                        action: 'pum_rc',
                        popup_id: popupID,
                        method: 'ajax',
                        function_name: settings.ajax_function_name || '',
                        url: trigger_link()
                    }, pum.hooks.applyFilters('pum.rc.ajax_args', $.fn.popmake.rc_user_args[popupID], $trigger, $box, $popup));

                    $.ajax(
                        {
                            method: "POST",
                            dataType: 'json',
                            url: pum_vars.ajaxurl,
                            data: custom_args
                        })
                        .done(function (response) {
                            $content.html(response.content);
                        })
                        .fail(function () {
                            $content.html('<p>An error occurred or no data was returned by the server. Please try again.</p>');
                        })
                        .always(showContent);
                    break;
                }
            });
        });

}(window.jQuery));