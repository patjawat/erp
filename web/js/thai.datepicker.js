function thaiDatepicker(el) {
    $.datetimepicker.setLocale('th');
    $(el).attr('readonly', false);
    $(el).addClass('date-readonly');

    $(el).datetimepicker({
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
