(function () {
    $.widget("execut.DetailView", {
        isDebug: false,
        buttonsEl: null,
        topOffset: null,
        sizesEl: null,
        initialHtml: {},
        timeout: 1000,
        _initInitialHtml: function () {
            var t = this,
                attribute = null,
                refreshParams = null;
            for (attribute in t.options.refreshedRows) {
                refreshParams = t.options.refreshedRows[attribute];
                var el = $(t.options.fields[refreshParams.name].rowSelector);
                t.initialHtml[attribute] = t._replaceNewStrings($("<div />").append(el.clone()).html());
            }
        },
        _replaceNewStrings: function (s) {
            return s.replace(/(?:\r\n|\r|\n)/g, "");
        },
        _create: function () {
            var t = this;
            t._initElements();
            t._initEvents();
            t._initRefreshedRows();
            t._initInitialHtml();
        },
        _initRefreshedRows: function () {
            var t = this,
                attribute = null,
                periodycalyUpdated = [],
                dependentUpdated = {};
            t.options.refreshedRows.forEach(function (refreshedRow) {
                switch (refreshedRow.refreshType) {
                    case 'periodically':
                        periodycalyUpdated[periodycalyUpdated.length] = refreshedRow.name;
                    break;
                    case 'dependent':
                        for (var key in refreshedRow.watchedInputs) {
                            var watchedInput = refreshedRow.watchedInputs[key];
                            if (typeof dependentUpdated[watchedInput.name] === 'undefined') {
                                dependentUpdated[watchedInput.name] = [];
                            }

                            dependentUpdated[watchedInput.name].push(refreshedRow.name);
                        }
                    break;
                }
            });

            t.dependentUpdated = dependentUpdated;

            for (var name in t.dependentUpdated) {
                t.initChangeEventForDependentElement(name);
            }

            if (periodycalyUpdated.length) {
                var callback = function () {
                    setTimeout(function () {
                        t.refreshPeriodicalyUpdatedAttributes(periodycalyUpdated, callback);
                    }, t.timeout);
                };
                callback();
            }
        },
        findAttributeRefreshParams: function (attribute, type) {
            var result = [],
                t = this;
            t.options.refreshedRows.forEach(function (refreshedRow) {
                if (refreshedRow.refreshType == type && refreshedRow.name === attribute) {
                    result.push(refreshedRow);
                }
            });

            return result;
        },
        refreshPeriodicalyUpdatedAttributes: function (attributes, callback) {
            var reloadedAttributes = [],
                t = this;
            attributes.forEach(function (attribute) {
                var refreshParams = t.findAttributeRefreshParams(attribute, 'periodically'),
                    isRefresh = false,
                    reason;
                refreshParams.forEach(function (refreshParam) {
                    if (refreshParam.watchedInputs.length === 0) {
                        isRefresh = true;
                    } else {
                        refreshParam.watchedInputs.forEach(function (watchedInput) {
                            var val = t.findInputEl(watchedInput.name).val();
                            if (typeof watchedInput.values !== 'undefined' && watchedInput.values.length) {
                                for (var valueKey in watchedInput.values) {
                                    if (val == watchedInput.values[valueKey]) {
                                        isRefresh = true;
                                        reason = 'setted values';
                                        break;
                                    }
                                }
                            } else if (typeof watchedInput.whenIsEmpty !== 'undefined') {
                                if (watchedInput.whenIsEmpty && val.length === 0) {
                                    isRefresh = true;
                                    reason = 'empty'
                                } else if (!watchedInput.whenIsEmpty && val.length !== 0) {
                                    isRefresh = true;
                                    reason = 'not empty'
                                }
                            } else {
                                isRefresh = true;
                                reason = 'change'
                            }
                        });
                    }
                });

                if (isRefresh && !reloadedAttributes.includes(attribute)) {
                    reloadedAttributes.push(attribute);
                }
            });

            t.refreshAttributes(reloadedAttributes, callback);
        },
        findInputEl: function (name) {
            var t = this,
                el = $(t.options.fields[name].fieldSelector);
            if (el.is('div')) {
                el = el.find('input:checked');
            }

            return el;
        },
        initChangeEventForDependentElement: function (name) {
            var t = this,
                selector = t.options.fields[name].fieldSelector;
            $(selector).change(function () {
                var refreshedAttributes = [],
                    val = $(this).val();
                for (var refreshKey in t.dependentUpdated[name]) {
                    var isRefresh = false,
                        reason = 'not',
                        refreshedAttributeName = t.dependentUpdated[name][refreshKey];
                    t.options.refreshedRows.forEach(function (refreshedRow) {
                        if (refreshedRow.name === refreshedAttributeName) {
                            var watchedInputs = refreshedRow.watchedInputs;
                            for (var key in watchedInputs) {
                                var watchedInput = watchedInputs[key];
                                if (typeof watchedInput.values !== 'undefined' && watchedInput.values.length) {
                                    for (var valueKey in watchedInput.values) {
                                        if (val == watchedInput.values[valueKey]) {
                                            isRefresh = true;
                                            reason = 'setted values';
                                            break;
                                        }
                                    }
                                } else if (typeof watchedInput.whenIsEmpty !== 'undefined') {
                                    if (watchedInput.whenIsEmpty && val.length === 0) {
                                        isRefresh = true;
                                        reason = 'empty'
                                    } else if (!watchedInput.whenIsEmpty && val.length !== 0) {
                                        isRefresh = true;
                                        reason = 'not empty'
                                    }
                                } else {
                                    isRefresh = true;
                                    reason = 'change'
                                }

                                if (isRefresh) {
                                    return false;
                                }
                            }
                        }
                    });

                    if (isRefresh && !refreshedAttributes.includes(refreshedAttributeName)) {
                        refreshedAttributes.push(refreshedAttributeName);
                    }
                }

                t.refreshAttributes(refreshedAttributes, null);
            });
        },
        refreshAttributes: function (refreshedAttributes, onComplete) {
            var t = this,
                $form = t.element.parent('form');
            if (!refreshedAttributes.length) {
                console.debug('skip');
                if (onComplete) {
                    onComplete();
                }

                return;
            }

            var oldVals = {};
            refreshedAttributes.forEach(function (refreshedAttribute) {
                if (typeof t.options.fields[refreshedAttribute] !== 'undefined') {
                    var el = $(t.options.fields[refreshedAttribute].fieldSelector),
                        disabledEls = false;
                    if (el.is('div')) {
                        disabledEls = el.parent().find('input');
                        el = el.find('input:checked');
                    }

                    oldVals[refreshedAttribute] = el.val();
                    if (disabledEls) {
                        disabledEls.prop('disabled', true);
                    }
                }
            });
            var data = $form.serialize();
            refreshedAttributes.forEach(function (refreshedAttribute) {
                if (typeof t.options.fields[refreshedAttribute] !== 'undefined') {
                    var el = $(t.options.fields[refreshedAttribute].fieldSelector),
                        disabledEls = false;
                    if (el.is('div')) {
                        disabledEls = el.parent().find('input');
                        el = el.find('input:checked');
                    }

                    if (disabledEls) {
                        disabledEls.prop('disabled', false);
                    }
                    el.val(oldVals[refreshedAttribute]);
                }
            });
            var delimiter = '';
            if (location.href.match(/\?/)) {
                delimiter = '&';
            } else {
                delimiter = '?';
            }

            $.ajax({
                dataType: "json",
                url: location.href + delimiter + jQuery.param({
                    refreshedAttributes: refreshedAttributes,
                }),
                method: 'post',
                data: data,
                success: function (data) {
                    for (var fieldName in data.rows) {
                        var row = data.rows[fieldName];
                        row.html = t._replaceNewStrings($("<div />").append(row.html).html());
                        // if (row.html === t.initialHtml[fieldName]) {
                        //     console.debug('skip unchanged ' + fieldName);
                        // } else if (typeof t.options.fields[fieldName] === 'undefined') {
                        //     console.debug('skip undefined field ' + fieldName);
                        // }

                        if (row.html !== t.initialHtml[fieldName] && typeof t.options.fields[fieldName] !== 'undefined') {
                            console.debug('update ' + fieldName);
                            console.debug(row.js);
                            console.debug('old html:', t.initialHtml[fieldName]);
                            console.debug('new html:', row.html);
                            // console.debug('replace ' + fieldName + ' ' + t.options.fields[fieldName].rowSelector + ' to ', row.html);
                            $(t.options.fields[fieldName].rowSelector).replaceWith(row.html);
                            t.initialHtml[fieldName] = row.html;
                            if (typeof row.js !== 'undefined') {
                                $(document.body).append('<script>' + row.js + '</script>');
                            }

                            if (typeof  t.dependentUpdated[fieldName] !== 'undefined') {
                                t.findInputEl(fieldName).change();
                                t.initChangeEventForDependentElement(fieldName);
                            }
                        }
                    }

                    if (onComplete) {
                        onComplete();
                    }
                },
                headers: {
                    'X-Pjax': 1,
                }
            });
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