(function () {
    $.widget("execut.MassHandlerButton", {
        _linkEl: null,
        _gridEl: null,
        _create: function () {
            var t = this;
            t._initElements();
            t._initEvents();
        },
        _initElements: function () {
            var t = this,
                el = t.element;
            t._linkEl = el.find('a');
            t._gridEl = $(t.options.gridSelector);
        },
        _initEvents: function () {
            var t = this,
                el = t.element;
            el.click(function () {
                var selectedIds = t._gridEl.yiiGridView('getSelectedRows'),
                    url = t._linkEl.attr('href');

                if (url.search('\\?') == -1) {
                    url += '?';
                }

                for (var key = 0; key < selectedIds.length; key++) {
                    url += '&' + t.options.idAttribute + '[]=' + selectedIds[key];
                }

                location.href = url;
                return false;
            });
        },
    })
})();