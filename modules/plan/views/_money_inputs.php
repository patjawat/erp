<?php

use yii\web\View;

/** รูปแบบช่องจำนวนเงินร่วมของฟอร์มแผน: แสดง comma แต่ส่งค่าดิบให้ backend */
$this->registerJs(<<<'JS'
(function () {
    function numberValue(value) {
        var parsed = Number(String(value == null ? '' : value).replace(/,/g, ''));
        return Number.isFinite(parsed) ? parsed : 0;
    }

    function formatInput(input) {
        if (!input || input === document.activeElement || input.value === '') return;
        input.value = numberValue(input.value).toLocaleString('th-TH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function prepare(root) {
        var scope = root || document;
        var selector = '.money-input, input[id^="planorder-month_"], #planorder-order_price, input[name*="[unit_price]"]';
        if (scope.matches && scope.matches(selector)) scope.classList.add('money-input');
        scope.querySelectorAll(selector).forEach(function (input) {
            input.classList.add('money-input');
            input.type = 'text';
            input.setAttribute('inputmode', 'decimal');
        });
    }

    window.planMoneyValue = numberValue;
    window.planMoneyText = function (value) {
        return numberValue(value).toLocaleString('th-TH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };
    window.planFormatMoneyInputs = function (root) {
        prepare(root || document);
        (root || document).querySelectorAll('.money-input').forEach(formatInput);
    };

    document.addEventListener('focusin', function (event) {
        if (event.target.classList.contains('money-input')) {
            event.target.value = String(event.target.value).replace(/,/g, '');
        }
    });
    document.addEventListener('focusout', function (event) {
        if (event.target.classList.contains('money-input')) formatInput(event.target);
    });
    document.addEventListener('submit', function (event) {
        event.target.querySelectorAll('.money-input').forEach(function (input) {
            input.value = String(input.value).replace(/,/g, '');
        });
    }, true);

    new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(function (node) {
                if (node.nodeType === 1) window.planFormatMoneyInputs(node);
            });
        });
    }).observe(document.body, {childList: true, subtree: true});

    window.planFormatMoneyInputs(document);
})();
JS, View::POS_READY, 'plan-money-inputs');
