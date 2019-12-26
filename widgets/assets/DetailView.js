(function () {
    $.widget("execut.DetailView", {
        buttonsEl: null,
        topOffset: null,
        _create: function () {
            var t = this;
            t._initElements();
            t._initEvents();
        },
        _initElements: function () {
            var t = this;
            t.formEl = t.element.parent();
            t.buttonsEl = t.element.find('.buttons-container');
        },
        isSaved: false,
        _initEvents: function () {
            var t = this,
                loadingOverlayIsShowed = false;
            t.formEl.on('beforeValidate', function (e, xhr, options) {
                var handlerCallback = function () {
                    t.formEl.unbind('ajaxComplete', handlerCallback);
                    if (loadingOverlayIsShowed) {
                        t.element.loadingOverlay('remove');
                        loadingOverlayIsShowed = false;
                    }
                };
                t.formEl.on('ajaxComplete', handlerCallback);

                if (!loadingOverlayIsShowed) {
                    loadingOverlayIsShowed = true;
                    t.element.loadingOverlay({
                        loadingText: 'Загрузка..'
                    });
                }
            });
            t.formEl.on('submit', function (e, xhr, options) {
                if (!loadingOverlayIsShowed) {
                    loadingOverlayIsShowed = true;
                    t.element.loadingOverlay({
                        loadingText: 'Загрузка..'
                    });
                }
            });
            t.formEl.on('afterValidate', function (e, xhr, options) {
                if (loadingOverlayIsShowed) {
                    t.element.loadingOverlay('remove');
                    loadingOverlayIsShowed = false;
                }
            });

            if (t.options.isFloatedButtons) {
                setTimeout(function () {
                    t.topOffset = null;
                    t.initButtonsOffsetTop();
                }, 1000);
                t.initButtonsOffsetTop();
                $(window).on('scroll', function () {
                    t.positionizeButtons();
                }).on('resize', function () {
                    t.positionizeButtons();
                });
            }
        },
        initButtonsOffsetTop: function () {
            let t = this;
            if (t.topOffset === null) {
                t.topOffset = t.buttonsEl.removeClass('floated-buttons').offset().top + t.buttonsEl.outerHeight(true);
            }

            t.positionizeButtons();
        },
        positionizeButtons: function () {
            let t = this,
                diff = $(window).scrollTop() + $(window).height() - t.topOffset + 5,
                isFloat = diff < 0;
            console.debug('Diff{' + diff + '}=$(window).scrollTop(){' + $(window).scrollTop() + '} + $(window).height(){' + $(window).height() + '} - t.topOffset{' + t.topOffset + '}');
            if (isFloat) {
                t.buttonsEl.addClass('floated-buttons');
                let bottom = Math.floor(-diff - t.buttonsEl.outerHeight(true) + 5) + 'px';
                t.buttonsEl.css('bottom', bottom);
            } else {
                t.buttonsEl.removeClass('floated-buttons');
            }
        }
    })
})();