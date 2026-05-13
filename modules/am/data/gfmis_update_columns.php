<?php
/**
 * Template/config for updating GFMIS values by asset code.
 */

return [
    'sheet_name' => 'GFMIS Update',
    'headers' => [
        'รหัสครุภัณฑ์',
        'GFMIS',
    ],
    'sample' => [
        [
            '7910-003-0003',
            'GFMIS-001',
        ],
        [
            '7420-001-0001',
            'GFMIS-002',
        ],
    ],
    'aliases' => [
        'code' => [
            'รหัสครุภัณฑ์',
            'หมายเลขครุภัณฑ์',
            'หมายเลขครุภัณฑ์ (code)',
            'หมายเลข FSN',
            'code',
            'Code',
        ],
        'gfmis' => [
            'GFMIS',
            'gfmis',
            'รหัสโครงสร้างงบประมาณ',
            'รหัสโครงสร้างงบประมาณ(GFMIS)',
        ],
    ],
];
