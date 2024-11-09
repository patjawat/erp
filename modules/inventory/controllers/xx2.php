        $sheet2->setTitle('แผ่นงานที่สอง');  // ตั้งชื่อแผ่นงานที่สอง
        $sheet2->setCellValue('A1', 'วดป.ที่รายงาน');  
        $sheet2->setCellValue('A2', 'ที่');  
        $sheet2->setCellValue('B2', 'คลัง');  
        $sheet2->setCellValue('C2', 'รหัส');  
        $sheet2->setCellValue('D2', 'รายการสินค้า');  
        $sheet2->setCellValue('E2', 'ประเภท');  
        $sheet2->setCellValue('F2', 'หน่วย');  
        $sheet2->setCellValue('G2', 'จำนวนคงเหลือ');  
        $sheet2->setCellValue('H2', 'มูลค่าคงเหลือ');  
        $sheet2->setCellValue('I2', 'จำนวนรับใหม่');  
        $sheet2->setCellValue('J2', 'มูลค่ารับใหม่');  
        $sheet2->setCellValue('K2', 'จำนวนจ่ายใหม่');  
        $sheet2->setCellValue('L2', 'มูลค่าจ่ายใหม่');  
        $sheet2->setCellValue('M2', 'จำนวนคงเหลือ');  
        $sheet2->setCellValue('N2', 'มูลค่าคงเหลือ');  

        $sheet2->getColumnDimension('A')->setWidth(12);
        $sheet2->getColumnDimension('B')->setWidth(20);
        $sheet2->getColumnDimension('C')->setWidth(10);
        $sheet2->getColumnDimension('D')->setWidth(40);
        $sheet2->getColumnDimension('E')->setWidth(25);
        $sheet2->getColumnDimension('F')->setWidth(9);
        $sheet2->getColumnDimension('G')->setWidth(13);
        $sheet2->getColumnDimension('H')->setWidth(13);
        $sheet2->getColumnDimension('I')->setWidth(13);
        $sheet2->getColumnDimension('J')->setWidth(13);
        $sheet2->getColumnDimension('K')->setWidth(13);
        $sheet2->getColumnDimension('L')->setWidth(13);
        $sheet2->getColumnDimension('M')->setWidth(13);
        $sheet2->getColumnDimension('N')->setWidth(13);

        
        $StartRow = 3;
        foreach ($datas as $key => $value) {
            $numRow = $StartRow++;
            // $a[] = ['B' => 'B'.$StartRow++];
            $sheet2->setCellValue('A' . $numRow, $numRow);
            
            $sheet2->setCellValue('B' . $numRow, $value['warehouse_name']);

            $sheet2->setCellValue('C' . $numRow, $value['asset_item']);

            $sheet2->setCellValue('D' . $numRow, $value['asset_name']);

            $sheet2->setCellValue('E' . $numRow, $value['asset_type']);
            $sheet2->setCellValue('F' . $numRow, $value['unit']);
            $sheet2->setCellValue('G' . $numRow, $value['qty_last']);
            $sheet2->setCellValue('H' . $numRow, ($value['sum_last']));
            $sheet2->setCellValue('I' . $numRow, $value['qty_po']);
            $sheet2->setCellValue('J' . $numRow, ($value['sum_po']));
            $sheet2->setCellValue('K' . $numRow, $value['qty_sub']);
            $sheet2->setCellValue('L' . $numRow, ($value['sum_sub']));
            $sheet2->setCellValue('M' . $numRow, (($value['qty_last'] - $value['qty_sub'])));
            $sheet2->setCellValue('N' . $numRow, (($value['sum_po'] - $value['sum_sub'])));
            
        }
        $setHeader = 'A1:Z1000';
        $sheet2->getStyle($setHeader)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
        $sheet2->getStyle($setHeader)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle($setHeader)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($setHeader)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($setHeader)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($setHeader)->getFill()->getStartColor()->setRGB('8DB4E2');
        
        
        $rowsheet2G = 'G2:G'.count($datas);
        $sheet2->getStyle($rowsheet2G)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle($rowsheet2G)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2G)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2G)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($rowsheet2G)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        
        $rowsheet2H = 'H2:H'.count($datas);
        $sheet2->getStyle($rowsheet2H)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet2->getStyle($rowsheet2H)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2H)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2H)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($rowsheet2H)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        
        
        $rowsheet2I = 'I2:I'.count($datas);
        $sheet2->getStyle($rowsheet2I)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle($rowsheet2I)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2I)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2I)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($rowsheet2I)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        
        $rowsheet2J = 'J2:J'.count($datas);
        $sheet2->getStyle($rowsheet2J)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet2->getStyle($rowsheet2J)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2J)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2J)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($rowsheet2J)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet2->getStyle($rowsheet2J)->getNumberFormat()->setFormatCode('0.00');
        
        $rowsheet2K = 'K2:K'.count($datas);
        $sheet2->getStyle($rowsheet2K)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle($rowsheet2K)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2K)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2K)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($rowsheet2K)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);


        $rowsheet2L = 'L2:L'.count($datas);
        $sheet2->getStyle($rowsheet2L)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet2->getStyle($rowsheet2L)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2L)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2L)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($rowsheet2L)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet2->getStyle($rowsheet2L)->getNumberFormat()->setFormatCode('0.00');
        
        $rowsheet2M = 'M2:M'.count($datas);
        $sheet2->getStyle($rowsheet2M)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle($rowsheet2M)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2M)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2M)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($rowsheet2M)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $rowsheet2N = 'N2:N'.count($datas);
        $sheet2->getStyle($rowsheet2N)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet2->getStyle($rowsheet2N)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet2->getStyle($rowsheet2N)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet2->getStyle($rowsheet2N)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet2->getStyle($rowsheet2N)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet2->getStyle('N3:N10')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);