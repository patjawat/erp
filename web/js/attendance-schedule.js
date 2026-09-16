(function () {
    'use strict';
    const form = document.getElementById('assignment-form');
    if (!form) return;
    const department = form.dataset.scope === 'department';
    const modes = document.getElementById('assignment-modes');
    const schedule = document.getElementById('assignment-schedule');
    const submit = document.getElementById('assignment-submit');
    const checkboxes = Array.from(form.querySelectorAll('input[name="members[]"]'));
    function isBulk() {
        return department && form.querySelector('input[name="Assignment[apply_to]"]:checked')?.value === 'members';
    }
    function sync() {
        const bulk = isBulk();
        const personal = !department || bulk;
        modes.hidden = !personal;
        modes.querySelectorAll('input').forEach(input => { input.disabled = !personal; });
        const mode = personal ? form.querySelector('input[name="Assignment[mode]"]:checked')?.value : 'normal';
        schedule.disabled = mode !== 'normal';
        schedule.required = mode === 'normal';
        document.getElementById('schedule-choice').hidden = mode !== 'normal';
        if (department) {
            document.getElementById('department-hint').hidden = bulk;
            document.getElementById('members-hint').hidden = !bulk;
            form.querySelectorAll('.member-select, .member-tools').forEach(el => { el.hidden = !bulk; });
            checkboxes.forEach(input => { input.disabled = !bulk || input.dataset.eligible !== '1'; });
            const count = checkboxes.filter(input => input.checked && !input.disabled).length;
            document.getElementById('member-expected-count').value = String(count);
            document.getElementById('member-count').textContent = 'เลือก ' + count + ' คน';
            submit.textContent = bulk ? 'บันทึกให้บุคลากรที่เลือก ' + count + ' คน' : 'บันทึกเวลาปกติของหน่วยงาน';
            submit.disabled = bulk && count === 0;
        }
    }
    form.addEventListener('change', sync);
    form.querySelectorAll('[data-select-members]').forEach(button => {
        button.addEventListener('click', function () {
            checkboxes.forEach(input => { if (!input.disabled) input.checked = button.dataset.selectMembers === 'all'; });
            sync();
        });
    });
    const search = document.getElementById('member-search');
    if (search) search.addEventListener('input', function () {
        const term = search.value.trim().toLocaleLowerCase();
        const rows = Array.from(form.querySelectorAll('[data-member-name]'));
        rows.forEach(row => { row.hidden = !row.dataset.memberName.toLocaleLowerCase().includes(term); });
        document.getElementById('member-no-results').hidden = !rows.length || rows.some(row => !row.hidden);
    });
    form.addEventListener('submit', function (event) {
        if (form.dataset.submitting === '1') { event.preventDefault(); return; }
        if (isBulk() && !checkboxes.some(input => input.checked && !input.disabled)) { event.preventDefault(); return; }
        form.dataset.submitting = '1';
        submit.disabled = true;
        submit.textContent = 'กำลังบันทึก…';
    });
    window.addEventListener('pageshow', function () { delete form.dataset.submitting; sync(); });
    sync();
    document.getElementById('assignment-error')?.focus();
}());
