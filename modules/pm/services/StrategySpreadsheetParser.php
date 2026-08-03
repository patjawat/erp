<?php

namespace app\modules\pm\services;

use PhpOffice\PhpSpreadsheet\IOFactory;

class StrategySpreadsheetParser
{
    private const YEARS = [2568=>['F','G','H'],2569=>['I','J','K'],2570=>['L','M','N'],2571=>['O','P','Q'],2572=>['R','S','T']];
    private const VALUES = [2568=>['V','W'],2569=>['X','Y'],2570=>['Z','AA'],2571=>['AB','AC'],2572=>['AD','AE']];

    public function parse(string $path): array
    {
        $book=IOFactory::load($path); $rows=[]; $summary=['sheets'=>0,'rows'=>0,'warnings'=>0];
        foreach($book->getWorksheetIterator() as $sheet){
            if($sheet->getSheetState() !== 'visible' || !preg_match('/^M\d+\.S\d+/u',$sheet->getTitle())) continue;
            $summary['sheets']++; $mission=$this->splitCode($sheet->getCell('B2')->getFormattedValue()); $issue=$this->splitCode($sheet->getCell('B3')->getFormattedValue());
            $currentGoal='';
            for($r=7;$r<=$sheet->getHighestDataRow();$r++){
                $goal=trim((string)$sheet->getCell("A$r")->getFormattedValue()); if($goal!=='')$currentGoal=$goal;
                $code=trim((string)$sheet->getCell("B$r")->getFormattedValue()); $name=trim((string)$sheet->getCell("C$r")->getFormattedValue());
                $rca=trim((string)$sheet->getCell("D$r")->getFormattedValue()); $secondary=trim((string)$sheet->getCell("E$r")->getFormattedValue());
                $annual=[]; foreach(self::YEARS as $year=>$cols){$annual[$year]=['measure'=>$this->cell($sheet,$cols[0],$r),'program'=>$this->cell($sheet,$cols[1],$r),'owner'=>$this->cell($sheet,$cols[2],$r)];}
                $values=['baseline'=>$this->number($sheet->getCell("U$r")->getCalculatedValue())]; foreach(self::VALUES as $year=>$cols)$values[$year]=['target'=>$this->number($sheet->getCell($cols[0].$r)->getCalculatedValue()),'actual'=>$this->number($sheet->getCell($cols[1].$r)->getCalculatedValue())];
                if($code===''&&$name===''&&$rca===''&&$secondary===''&&!$this->hasAnnual($annual))continue;
                $errors=[]; if($currentGoal==='')$errors[]='ไม่พบเป้าประสงค์'; if($code===''&&$name!=='')$errors[]='มีชื่อตัวชี้วัดแต่ไม่มีรหัส';
                $rows[]=['sheet_name'=>$sheet->getTitle(),'row_no'=>$r,'status'=>$errors?'warning':'valid','errors'=>$errors,'payload'=>[
                    'mission'=>$mission,'issue'=>$issue,'goal'=>$this->splitCode($currentGoal),'indicator'=>['code'=>$code,'name'=>$name],
                    'rca'=>$rca,'secondary'=>$secondary,'annual'=>$annual,'values'=>$values,
                ]]; if($errors)$summary['warnings']++;
            }
        }
        $summary['rows']=count($rows); return ['rows'=>$rows,'summary'=>$summary];
    }
    private function cell($sheet,string $col,int $row):string{return trim((string)$sheet->getCell($col.$row)->getFormattedValue());}
    private function number($value):?float{return is_numeric($value)?(float)$value:null;}
    private function hasAnnual(array $annual):bool{foreach($annual as $v)if(implode('',$v)!=='')return true;return false;}
    private function splitCode(string $value):array{$value=trim($value);if(preg_match('/^([A-Z0-9.]+)\s*[-–]\s*(.+)$/us',$value,$m))return ['code'=>rtrim($m[1],'.'),'name'=>trim($m[2])];return ['code'=>'','name'=>$value];}
}
