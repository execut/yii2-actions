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
            t.formEl.on('ajaxBeforeSend', function (e, xhr, options) {
                t.element.loadingOverlay({
                    loadingText: 'Загрузка..'
                });
            });
            t.formEl.on('ajaxComplete', function (e, xhr, options) {
                t.element.loadingOverlay('remove');
            });
        }
    })
})();