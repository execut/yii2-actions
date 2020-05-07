(function () {
    $.widget("execut.DetailView", {
        isDebug: false,
        buttonsEl: null,
        topOffset: null,
        sizesEl: null,
        _create: function () {
            var t = this;
            t._initElements();
            t._initEvents();
            t._initReloadedAttributes();
        },
        _initElements: function () {
            var t = this;
            t.formEl = t.element.parent();
            t.buttonsEl = t.element.find('.buttons-container');
            if (t.isDebug) {
                t.sizesEl = $('<div style="display: inline-block; font-size: 6px"></div>');
                t.buttonsEl.prepend(t.sizesEl);
            }
        },
        _initialValueRefreshAttribute: null,
        _initReloadedAttributes: function () {
            var t = this;
            if (!t.options.reloadedAttributes.length) {
                return;
            }

            setTimeout(function () {
                t.reloadReloadedAttributes()
            }, 5000);
        },
        reloadReloadedAttributes: function () {
            var t =this;
            $.ajax({
                dataType: "json",
                url: location.href,
                data: {getReloadedAttributes: 1},
                success: function (data) {
                    for (var attributeKey in data.rows) {
                        $('.' + attributeKey).replaceWith(data.rows[attributeKey]);
                    }

                    if (t.options.initialValueRefreshAttribute && typeof data.refreshAttribute !== 'undefined') {
                        if (data.refreshAttribute !== t.options.initialValueRefreshAttribute) {
                            // console.debug('different');
                            // console.debug('current: ' + t.options.initialValueRefreshAttribute);
                            // console.debug('remove: ' + data.refreshAttribute);
                            location.reload();
                        }
                    }

                    setTimeout(function () {
                        t.reloadReloadedAttributes()
                    }, 5000);
                },
                headers: {
                    'X-Pjax': 1,
                }
            });
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
                }).on('resize orientationchange', function () {
                    t.initButtonsOffsetTop(true);
                    t.positionizeButtons();
                });
                $(document).on('touchmove', function () {
                    t.positionizeButtons();
                })
            }
        },
        initButtonsOffsetTop: function (isResetTopOffset) {
            var t = this;
            if (t.topOffset === null || isResetTopOffset) {
                t.topOffset = t.buttonsEl.css('bottom', false).removeClass('floated-buttons').offset().top + t.buttonsEl.outerHeight(true);
            }

            t.positionizeButtons();
        },
        positionizeButtons: function () {
            var t = this,
                diff = $(window).scrollTop() + $(window).outerHeight() - t.topOffset + 5,
                isFloat = diff < 0;
            if (t.isDebug) {
            //     var addressBarSize = getComputedStyle(document.documentElement).perspective;
                t.sizesEl.html('Time: ' + Date.now()
                    + '<br>t.buttonsEl.parent().width(): ' + Math.floor(t.buttonsEl.parent().width()));
            //         + '<br>addressBarSize: ' + addressBarSize
            //         + '<br>$(window).scrollTop(): ' + Math.floor($(window).scrollTop())
            //         + '<br>window.innerHeight: ' + Math.floor($(window).innerHeight)
            //         + '<br>window.height(): ' + Math.floor($(window).height())
            //         + '<br>$(window).innerHeight(): ' + Math.floor($(window).innerHeight())
            //         + '<br>window.outerHeight: ' + Math.floor(window.outerHeight)
            //         + '<br>document.documentElement.clientHeight: ' + document.documentElement.clientHeight
            //         + '<br>$(window).outerHeight(): ' + Math.floor($(window).outerHeight())
            //         + '<br>t.topOffset: ' + Math.floor(t.topOffset));
            }

            if (isFloat) {
                t.buttonsEl.addClass('floated-buttons');
                // var bottom = Math.floor(-diff - t.buttonsEl.outerHeight(true) + 5) + 'px';
            } else {
                t.buttonsEl.removeClass('floated-buttons');
            }

            t.buttonsEl.css('width', Math.floor(t.buttonsEl.parent().width()));
        }
    })
})();