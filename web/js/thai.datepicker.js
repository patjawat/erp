function thaiDatepicker(el) {
    $.datetimepicker.setLocale('th')
    $(el).attr('readonly', false)
    $(el).addClass('date-readonly')
    $(el).datetimepicker({
        timepicker: false,
        format: 'd/m/Y',
        lang: 'th',
        yearOffset : 543,
        validateOnBlur: false,
        closeOnDateSelect: true,
    })
}

