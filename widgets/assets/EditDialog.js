$.fn.modal.Constructor.prototype.enforceFocus = function() {};
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
            // $.ajaxSetup({
            //     cache: false,
            //     contentType: false,
            //     processData: false
            // });
        },
        _initElements: function () {
            var t = this;
            t.formEl = t.element.find('form');
            t.alertEl = $('#' + t.options.alertId).hide();
            t.modalEl = t.element.find('div').first();

            var defaultAttributes = {};
            t.formEl.find(':input').each(function (key, el) {
                el = $(el);
                if (el.attr('id')) {
                    defaultAttributes[el.attr('id').replace(t.options.inputsPrefix + '-', '')] = el.val();
                }
            });

            t._defaultAttributes = defaultAttributes;
            t._sourceAction = t.formEl.attr('action');
            if (typeof t.options.editButtons !== 'undefined') {
                t.editButtons = $(t.options.editButtons);
            }

            t.addButton = $('#' + t.element.attr('id') + '-add-button');
        },
        _initEvents: function () {
            var t = this,
                onAjaxComplete = function (e, resp) {
                    if (typeof resp !== 'undefined' && typeof resp.responseJSON.message !== 'undefined') {
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
                };
            t.formEl.on('ajaxComplete', onAjaxComplete);

            t.addButton.click(function () {
                t.values(t._defaultAttributes);

                t.open();
                return false;
            });

            t.formEl.on('beforeSubmit', function (event) {
                event.result = false;
                return false;
            });

            if (typeof t.options.editButtons !== 'undefined') {
                t.editButtons.click(function (e) {
                    var curButton = $(this),
                        attributes;
                    if (typeof t.options.attributesElement === 'undefined') {
                        targetEl = $(e.target);
                        if (targetEl.parents('a').length || targetEl.is('a') || targetEl.is(':button')) {
                            return true;
                        }

                        attributes = curButton.attr('attributes');
                    } else {
                        attributes = curButton.parents(t.options.attributesElement).attr('attributes');
                    }

                    attributes = JSON.parse(attributes);
                    t.values(attributes).open();

                    return false;
                });
            }
        },
        setDefaultValue: function (key, value) {
            var t = this,
                el = t.element;
            t._defaultAttributes[key] = value;
            return t;
        },
        values: function (attributes) {
            var t = this,
                el = t.element;
            for (var key in attributes) {
                $('#' + t.options.inputsPrefix + '-' + key).val(null).val(attributes[key]).trigger('change.select2').change();
            }

            var realAction = t._sourceAction;
            if (typeof attributes.id !== 'undefined') {
                if (realAction.search('\\?') === -1) {
                    realAction += '?'
                } else {
                    realAction += '&'
                }

                realAction += 'id=' + attributes.id;
            }

            t._setAction(realAction);

            return t;
        },
        _setAction: function (realAction) {
            var t = this;
            t.formEl.attr('action', realAction)
                .data('yiiActiveForm').settings.validationUrl = realAction;
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