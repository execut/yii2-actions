(function () {
    $.widget("execut.EditDialog", {
        formEl: null,
        alertEl: null,
        editButtons: null,
        addButton: null,
        _sourceAction: null,
        _create: function () {
            var t = this;
            t._initElements();
            t._initEvents();
        },
        _initElements: function () {
            var t = this;
            t.formEl = t.element.find('form');
            t.alertEl = $('#' + t.options.alertId).hide();
            t.modalEl = t.element.find('div').first();
            t._sourceAction = t.formEl.attr('action');
            if (typeof t.options.editButtons !== 'undefined') {
                t.editButtons = $(t.options.editButtons);
            }

            t.addButton = $('#' + t.element.attr('id') + '-add-button');
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
                    if (typeof t.options.gridId !== 'undefined') {
                        $.pjax.reload('#' + t.options.gridId);
                    }
                }
            });

            t.addButton.click(function () {
                t.open();
            });

            t.formEl.on('beforeSubmit', function (event) {
                event.result = false;
                return false;
            });

            if (typeof t.options.editButtons !== 'undefined') {
                t.editButtons.click(function () {
                    var curButton = $(this),
                        attributes = JSON.parse(curButton.parents(t.options.attributesElement).attr('attributes'));
                    t.values(attributes).open();

                    return false;
                });
            }
        },
        values: function (attributes) {
            var t = this,
                el = t.element;
            for (var key in attributes) {
                $('#' + t.options.inputsPrefix + '-' + key).val(null).val(attributes[key]).trigger('change.select2');
            }

            var realAction = t._sourceAction;
            if (typeof attributes.id !== 'undefined') {
                realAction += '?id=' + attributes.id;
            }

            t.formEl.attr('action', realAction)
                .data('yiiActiveForm').settings.validationUrl = realAction;

            return t;
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
        },
    })
})();