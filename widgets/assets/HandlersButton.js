(function () {
    $.widget("execut.HandlersButton", {
        _gridEl: null,
        _create: function () {
            var t = this;
            t._initElements();
            t._initEvents();
        },
        _initElements: function () {
            var t = this,
                el = t.element;
            t._gridEl = $(t.options.gridSelector);
        },
        _initEvents: function () {
            var t = this,
                el = t.element;
            el.click(function () {
                var selectedIds = t._gridEl.yiiGridView('getSelectedRows'),
                    message = t._getMessage(selectedIds.length || t.options.totalCount);

                if (confirm(message)) {
                    var url = t.options.url;
                    if (url.search('\\?') == -1) {
                        url += '?';
                    }

                    for (var key = 0; key < selectedIds.length; key++) {
                        url += '&' + t.options.idAttribute + '[]=' + selectedIds[key];
                    }

                    $.get(url);
                }
            });
        },
        _getMessage: function (totalCount) {
            var t = this,
                el = t.element;

            return t.options.confirmMessage.replace('#', totalCount);
        }
    })
})();