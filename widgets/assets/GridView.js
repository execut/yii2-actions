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
                el.find('tr[data-key]').click(function (e) {
                    var trEl = $(this),
                        targetEl = $(e.target);
                    if (!(targetEl.parents('a').length || targetEl.is('a'))) {
                        var id = trEl.attr('data-id'),
                            url = t.options.updateUrl;
                        location.href = url + '?id=' + id;
                    }
                });
            }
        },
        _initEvents: function () {
            var t = this;
        }
    })
})();