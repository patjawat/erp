function thaiDatepicker(el) {
    var jq = window.jQuery;

    if (!jq || !jq.datetimepicker || typeof jq.datetimepicker.setLocale !== 'function' || !jq.fn || typeof jq.fn.datetimepicker !== 'function') {
        return;
    }

    var $el = jq(el);

    jq.datetimepicker.setLocale('th');
    $el.attr('readonly', false);
    $el.addClass('date-readonly');

    $el.datetimepicker({
        timepicker: false,
        format: 'd/m/Y',
        lang: 'th',
        yearOffset: 543,
        validateOnBlur: false,
        closeOnDateSelect: true,

        // 👇 ปิด scroll ที่เปลี่ยนค่าใน input ขณะ focus
        scrollInput: false,

        // 👇 ปิด scroll ใน popup calendar
        onShow: function(ct) {
            $('.xdsoft_datetimepicker').on('mousewheel DOMMouseScroll', function(e) {
                e.preventDefault();
                e.stopPropagation();
            });
        },
        onClose: function(ct) {
            $('.xdsoft_datetimepicker').off('mousewheel DOMMouseScroll');
        }
    });
}
