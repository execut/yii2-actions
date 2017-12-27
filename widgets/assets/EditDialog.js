(function () {
    $.widget("execut.EditDialog", {
        formEl: null,
        alertEl: null,
        _create: function () {
            var t = this;
            t._initElements();
            t._initEvents();
        },
        _initElements: function () {
            var t = this;
            t.formEl = t.element.find('form');
            t.alertEl = $('#' + t.options.alertId);
            t.modalEl = t.element.find('div').first();
        },
        _initEvents: function () {
            var t = this;
            t.formEl.on('ajaxComplete', function (e, resp) {
                if (typeof resp.responseJSON.message !== 'undefined') {
                    t.message = resp.responseJSON.message;
                    t.alertEl.show().find('span').html(t.message);
                    setTimeout(function () {
                        t.alertEl.hide(1000);
                    }, 1000);
                    t.modalEl.modal('hide');
                }
            });
            t.formEl.on('beforeSubmit', function (event) {
                event.result = false;
                return false;
            });
        },
        values: function (attributes) {
            var t = this,
                el = t.element;
            for (var key in attributes) {
                $('#' + t.options.inputsPrefix + '-' + key).val(null).val(attributes[key]).trigger('change.select2');
            }
        },
        close: function () {
            var t = this,
                el = t.element;
            t.modalEl.modal('hide');
        },
        open: function () {
            var t = this,
                el = t.element;
            t.modalEl.modal('show');
        }
    })
})();