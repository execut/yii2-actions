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
        }
    })
})();