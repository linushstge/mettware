/**
 * This plugin shows the off-canvas cart and refresh the Cart Widget in the header
 */
$.plugin('dateSave', {

    init: function () {
        var me = this;

        $.subscribe('plugin/swDatePicker/onPickerClose', function(event) {
            var date = $('.datepicker.flatpickr-input[name=shippingDate]');

            $.ajax({
                url: date.data('url'),
                method: 'POST',
                data: {
                    mettwochDate: date.val()
                }
            })
        });
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});

$(document).dateSave();
