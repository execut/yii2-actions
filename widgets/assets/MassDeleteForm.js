(function () {
    $.widget("execut.MassDeleteForm", {
        _progressBarEl: null,
        _submitButtons: null,
        _create: function () {
            var t = this;
            t._initElements();
            t._initEvents();
        },
        _initElements: function () {
            var t = this,
                el = t.element;

            t._progressBarEl = el.find('.progress-bar');
            t._errorsEl = el.find('.delete-errors');
            // t._stopButtons = el.find('button[type=submit].stop');
            if (t._progressBarEl.length) {
                t._updateProgressBar();
            }
        },
        _initEvents: function () {
            var t = this,
                el = t.element;
        },
        _updateProgressBar: function () {
            var t = this;
            $.get(location.href + '&getProgress=1', function (r) {
                if (r.progress === 100) {
                    location.href = location.href;
                } else {
                    t._errorsEl.html(r.errors);
                    t._progressBarEl.css('width', r.progress + '%');
                    setTimeout(function () {
                        t._updateProgressBar();
                    }, 1000);
                }
            });
        }
    })
})();