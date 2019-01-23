(function () {
    $.widget("execut.DetailView", {
        _create: function () {
            var t = this;
            t._initElements();
            t._initEvents();
        },
        _initElements: function () {
            var t = this;
            t.formEl = t.element.parent();
        },
        isSaved: false,
        _initEvents: function () {
            var t = this;
            t.formEl.on('beforeValidate', function (e, xhr, options) {
                var handlerCallback = function () {
                    t.formEl.unbind('ajaxComplete', handlerCallback);
                    t.element.loadingOverlay('remove');
                };
                t.formEl.on('ajaxComplete', handlerCallback);

                t.element.loadingOverlay({
                    loadingText: 'Загрузка..'
                });
            });
            t.formEl.on('submit', function (e, xhr, options) {
                t.element.loadingOverlay({
                    loadingText: 'Загрузка..'
                });
            });
            t.formEl.on('afterValidate', function (e, xhr, options) {
                t.element.loadingOverlay('remove');
            });
        }
    })
})();