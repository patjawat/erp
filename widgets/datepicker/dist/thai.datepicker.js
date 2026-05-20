function thaiDatepicker(el) {
    var jq = window.jQuery

    if (!jq || !jq.datetimepicker || typeof jq.datetimepicker.setLocale !== 'function' || !jq.fn || typeof jq.fn.datetimepicker !== 'function') {
        return
    }

    var $el = jq(el)

    jq.datetimepicker.setLocale('th')
    $el.attr('readonly', true)
    $el.addClass('date-readonly')
    $el.datetimepicker({
        timepicker: false,
        format: 'd/m/Y',
        lang: 'th',
        yearOffset : 543,
        validateOnBlur: false,
    })
}
