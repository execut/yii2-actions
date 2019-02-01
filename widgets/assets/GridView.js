(function () {
    $.widget("execut.GridView", {
        _create: function () {
            var t = this;
            t._initElements();
            t._initEvents();
        },
        _initElements: function () {
            var t = this,
                el = t.element;
            if (typeof t.options.updateUrl !== 'undefined') {
                el.find('tr[data-id]').mousedown(function (e) {
                    t._moveStarted = false;
                    if (e.originalEvent.button == 0) {
                    } else {
                        var trEl = $(this),
                            href = false;
                        if (href = t._getHrefFromTr(trEl, e)) {
                            var tdEl = $(e.target).css('position', 'relative'),
                                linkEl = $('<a href="' + href + '" style="width: 100%;left: 0; top: 0; height: 100%; display: block; position: absolute"></a>');

                            tdEl.append(linkEl);
                            setTimeout(function () {
                                linkEl.remove();
                            }, 10);
                        }

                        return false;
                    }
                });

                el.mousemove(function () {
                    t._moveStarted = true;
                });

                el.find('tr[data-id]').click(function (e) {
                    if (window.getSelection().toString().length && t._moveStarted) {
                        return false;
                    }

                    var trEl = $(this),
                        href = false;
                    if (href = t._getHrefFromTr(trEl, e)) {
                        if (typeof e.originalEvent !== 'undefined' && e.originalEvent.ctrlKey) {
                            window.open(href, '_blank');
                        } else {
                            location.href = href;
                        }

                        return;
                    }

                    return true;
                });
            }
        },
        _getHrefFromTr: function (trEl, e) {
            var t = this,
                targetEl = $(e.target);
            if (!(targetEl.parents('a,button,input').length || targetEl.is('a,button,input'))) {
                var dataId = trEl.attr('data-id'),
                    url = t.options.updateUrl,
                    delimiter = null;
                if (url.search('\\?') !== -1) {
                    delimiter = '&';
                } else {
                    delimiter = '?';
                }

                return url + delimiter + dataId;
            }
        },
        _initEvents: function () {
            var t = this;
        }
    })
})();