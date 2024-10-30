( function() {
    $.widget("execut.loadingOverlay", {
        options: {
            loadingClass: 'loading',          // Class added to target while loading
            overlayClass: 'loading-overlay',  // Class added to overlay (style with CSS)
            spinnerClass: 'loading-spinner',  // Class added to loading overlay spinner
            iconClass: 'loading-icon',        // Class added to loading overlay spinner
            textClass: 'loading-text',        // Class added to loading overlay spinner
            loadingText: 'กำลังโหลด...',           // Text within loading overlay
            isCentralize: true,               // Is centralize icon while window scroll or resize
            offsetTop: 0,                     // Top window offset of static menu for icon position calculation
            offsetBottom: 0,                  // Bottom window offset of footer for icon position calculation
        },
        _icon: null,
        _centralizeHandler: null,
        _create: function () {
            var t = this;

            t._initElements();
            t._initEvents();
        },
        _initElements: function () {
            var t = this,
                opts = t.options,
                el = t.element.addClass(opts.loadingClass),
                overlay = $('<div class="' + opts.overlayClass + '">' +
                    '<p class="' + opts.spinnerClass + '">' +
                    '<span class="' + opts.iconClass + '"></span>' +
                    '<span class="' + opts.textClass + '">' + opts.loadingText + '</span>' +
                    '</p></div>');
            el.prepend(overlay).data('loading-overlay', true);
            if (opts.isCentralize && el.length) {
                t._icon = el.find('.' + opts.spinnerClass);

                t._centralize();
                t._centralizeHandler = function () {
                    t._centralize();
                };
            }
        },
        _initEvents: function () {
            var t = this;
            $(window).scroll(t._centralizeHandler);
            $(window).resize(t._centralizeHandler);
        },
        _centralize: function () {
            var t = this,
                opts = t.options,
                el = t.element,
                topCorner = 0,
                bottomCorner = 0,
                scroll = $(window).scrollTop() + opts.offsetTop,
                windowHeight = $(window).height() - opts.offsetBottom,
                top = $(el).offset().top,
                height = $(el).outerHeight();
            if (scroll < top) {
                topCorner = 0;
            } else {
                topCorner = scroll - top;
            }

            if (scroll + windowHeight > top + height) {
                bottomCorner = height;
            } else {
                bottomCorner = (scroll + windowHeight) - top;
            }

            var offsetTop = (topCorner + bottomCorner) / 2;
            t._icon.css('top', offsetTop + 'px');
        },
        remove: function () {
            var t = this;

            t.destroy();
        },
        _destroy: function () {
            var t = this,
                opts = t.options,
                el = t.element;
            $(window).unbind('scroll', t._centralizeHandler);
            $(window).unbind('resize', t._centralizeHandler);

            el.find('.' + opts.overlayClass).detach();
            if (el.hasClass(opts.loadingClass)) {
                el.removeClass(opts.loadingClass);
            } else {
                el.find('.' + opts.loadingClass).removeClass(opts.loadingClass);
            }
        }
    });
})();