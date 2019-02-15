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
            t._modalEl = $('#' + el.attr('id') + '-modal');
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

                    $.get(url, function (r) {
                        var resultError = [];
                        for (var modelName in r) {
                            for (var attribute in r[modelName]) {
                                for (var errorKey in r[modelName][attribute]) {
                                    var error = r[modelName][attribute][errorKey];
                                    resultError[resultError.length] = modelName + ': ' + error;
                                }
                            }
                        }

                        t._modalEl.find('.modal-body').html(resultError.join('<br>'));
                        t._modalEl.modal('show');
                    });
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