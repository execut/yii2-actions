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
                el.find('tr[data-key]').mousemove(function (e) {
                    t._moveStarted = true;
                });

                el.find('tr[data-key]').mousedown(function (e) {
                    t._moveStarted = false;
                });
                el.find('tr[data-key]').click(function (e) {
                    var trEl = $(this),
                        targetEl = $(e.target);
                    if (t._moveStarted) {
                        return false;
                    }
                    if (!(targetEl.parents('a').length || targetEl.is('a'))) {
                        var id = trEl.attr('data-id'),
                            url = t.options.updateUrl,
                            delimiter = null;
                        if (url.search('\\?') !== -1) {
                            delimiter = '&';
                        } else {
                            delimiter = '?';
                        }

                        location.href = url + delimiter + 'id=' + id;
                    }
                });
            }
        },
        _initEvents: function () {
            var t = this;
        }
    })
})();