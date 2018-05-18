(function () {
    $.widget("execut.GridView", {
        _moveStarted: false,
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
                    if (e.originalEvent.button == 0) {
                        t._moveStarted = false;
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

                el.find('tr[data-id]').click(function (e) {
                    if (window.getSelection().toString().length) {
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
                    }
                });
            }
        },
        _getHrefFromTr: function (trEl, e) {
            var t = this,
                targetEl = $(e.target);
            if (!(targetEl.parents('a,button,input').length || targetEl.is('a,button,input'))) {
                var id = trEl.attr('data-id'),
                    url = t.options.updateUrl,
                    delimiter = null;
                if (url.search('\\?') !== -1) {
                    delimiter = '&';
                } else {
                    delimiter = '?';
                }

                return url + delimiter + 'id=' + id;
            }
        },
        _initEvents: function () {
            var t = this;
        }
    })
})();