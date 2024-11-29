<?php
/**
 * @see http://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\components\AppHelper;
use app\models\Categorise;
use app\modules\inventory\models\StockEvent;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\BaseConsole;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 *
 * @since 2.0
 * คลังวัสดุเทคนิคการแพทย์
 */
class ImportStock2Controller extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     *
     * @return int Exit code
     */
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";

        $this->M7();
        return ExitCode::OK;
    }


    // วัสดุวิทยาศาสตร์หรือการแพทย์
    public  function actionM7()
    {
        $warehouse_id = 1;
        $assettype = 'M7';
        $categoryName = 'วัสดุวิทยาศาสตร์หรือการแพทย์';
        $category_id = 1227;
        $code = 'IN-680013';

        $data = [
            ['code' => '07-00002','title' => '10% KOH 450 ml #41116141','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00003','title' => '3.2% Na citrate Tube (non vac.) #41116108','unit' => 'หลอด','qty' => '405','unit_price' => '1.35000'],
            ['code' => '07-00004','title' => '6.5 % NaCl (Semisolid) 50 tube/pack #41116129','unit' => 'กล่อง','qty' => '1','unit_price' => '750.00000'],
            ['code' => '07-00005','title' => 'ACCU CHEK SAFE-T-PRO UNO (เข็มเจาะปลายนิ้ว) #41116104','unit' => 'ชิ้น','qty' => '10800','unit_price' => '2.12175'],
            ['code' => '07-00006','title' => 'Glucose strip ACCU-CHEK INSTANT 100CT APAC #41116106','unit' => 'กล่อง','qty' => '14','unit_price' => '684.80000'],
            ['code' => '07-00008','title' => 'Glucose strip Control ACCU-CHEK INSTANTINTL II #41116107','unit' => 'กล่อง','qty' => '6','unit_price' => '1000.00000'],
            ['code' => '07-00016','title' => 'Amies Transport medium #41116129','unit' => 'ชิ้น','qty' => '12','unit_price' => '14.16667'],
            ['code' => '07-00017','title' => 'Amikacin disc 30 ug #41116131','unit' => 'หลอด','qty' => '5','unit_price' => '90.00000'],
            ['code' => '07-00018','title' => 'Amoxycycin/clavulanic (2:1) disc #41116131','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00019','title' => 'Ampicillin disc 10 ug #41116131','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00020','title' => 'Anti-A (10 ml) #41116102','unit' => 'ขวด','qty' => '5','unit_price' => '80.00000'],
            ['code' => '07-00021','title' => 'Anti-AB (10 ml) #41116102','unit' => 'ขวด','qty' => '3','unit_price' => '80.00000'],
            ['code' => '07-00022','title' => 'Anti-B (10 ml) #41116102','unit' => 'ขวด','qty' => '3','unit_price' => '80.00000'],
            ['code' => '07-00023','title' => 'Anti-D (IgG/IgM) #41116102','unit' => 'ขวด','qty' => '8','unit_price' => '225.00000'],
            ['code' => '07-00024','title' => 'Anti humanglobulin (10 ml) #41116102','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00025','title' => 'Bacitracin disc #41116131','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00026','title' => 'Bactec Ped Plus Vial (1-3 ml) #41104110','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00027','title' => 'Bactec Plus Aerobic (8-10ml) #41104110','unit' => 'ขวด','qty' => '109','unit_price' => '123.08266'],
            ['code' => '07-00028','title' => 'Bile aesculin Biochem 50 tube/pack #41116129','unit' => 'กล่อง','qty' => '2','unit_price' => '750.00000'],
            ['code' => '07-00031','title' => 'HIV Ab 40 test (Cassette) #41116126','unit' => 'กล่อง','qty' => '4','unit_price' => '2500.00000'],
            ['code' => '07-00032','title' => 'Blood lancet Terumo Medisafe 30 pcs./box #41116120','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00033','title' => 'Blue Pipette Tip 100-1000 ul (500/pack) #41116104','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00034','title' => 'Blue PipetteTip 100-1000 ul (1000 /pack) #41116104','unit' => 'ถุง','qty' => '3','unit_price' => '358.00000'],
            ['code' => '07-00035','title' => 'Yellow Pipette Tip 2-200ul (1000/pack) #41116126','unit' => 'ถุง','qty' => '2','unit_price' => '158.00000'],
            ['code' => '07-00037','title' => 'Cefotaxime disc 30 ug #41116131','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00038','title' => 'Ceftazidime disc 30 ug #41116131','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00039','title' => 'Ceftriaxone disc 30 ug #41116131','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00040','title' => 'Cellpack 20 L #41116008','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00041','title' => 'Cefazolin disc 30 ug #41116131','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00042','title' => 'Chocollate agar 10 pcs./pack #41116129','unit' => 'แพค','qty' => '3','unit_price' => '150.00000'],
            ['code' => '07-00044','title' => 'Ciprofloxacin disc 5 ug #41116131','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00045','title' => 'Clindamycin disc 2 ug #41116131','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00047','title' => 'CoaguChek PT strip 2*24 Test #41116108','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00048','title' => 'Container 40 ml.Gray with spoon #41116140','unit' => 'ชิ้น','qty' => '874','unit_price' => '1.39428'],
            ['code' => '07-00050','title' => 'Coombs Control cell #41116102','unit' => 'ขวด','qty' => '2','unit_price' => '100.00000'],
            ['code' => '07-00052','title' => 'Cover glass 22*22 mm 200 pcs/pack #41116140','unit' => 'แพค','qty' => '25','unit_price' => '77.00000'],
            ['code' => '07-00056','title' => 'CuSo 4 80% #41116102','unit' => 'ขวด','qty' => '0','unit_price' => '0'],ผ
            ['code' => '07-00057','title' => 'CuSo 4 90% #41116102','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00058','title' => 'Cuvette for ARES #41116108','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00059','title' => 'D-10 Hemoglobin A1C,400 test #41116004','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00063','title' => 'EDTA micro tube 0.5 ml #41116120','unit' => 'หลอด','qty' => '322','unit_price' => '2.30000'],
            ['code' => '07-00065','title' => 'Erythromycin disc 15 ug #41116131','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00067','title' => 'G-6PD 500 test with Control 2 level #41116121','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00068','title' => 'Gentamycin disc 10 ug #41116131','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00069','title' => 'Gentamycin disc 120 ug #41116131','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00073','title' => 'HBc Ab 25 test ( cassette ) #41116126','unit' => 'กล่อง','qty' => '1','unit_price' => '625.00000'],
            ['code' => '07-00076','title' => 'HBe Ag 25 test ( cassette ) #41116126','unit' => 'กล่อง','qty' => '2','unit_price' => '668.75000'],
            ['code' => '07-00078','title' => 'HBs Ab strip (50 test) #41116126','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00079','title' => 'HBs Ab Strip (100 test) #41116126','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00080','title' => 'HBs Ag Strip (50 Test) #41116126','unit' => 'กล่อง','qty' => '2','unit_price' => '300.00000'],
            ['code' => '07-00081','title' => 'HBs Ag strip (100 test) #41116126','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00083','title' => 'HCV Ab strip (50 test) #41116126','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00084','title' => 'HCV Ab strip (100 test) #41116126','unit' => 'กล่อง','qty' => '1','unit_price' => '1391.00000'],
            ['code' => '07-00086','title' => 'Hematocrit red tube 100 pcs./vial #41116120','unit' => 'หลอด','qty' => '28','unit_price' => '55.50000'],
            ['code' => '07-00087','title' => 'C-reactive protein ยี่ห้อHotgen 60 pcs./box #41116104','unit' => 'กล่อง','qty' => '1','unit_price' => '11235.00000'],
            ['code' => '07-00088','title' => 'C-reactive protein Control ยี่ห้อHotgen #41116107','unit' => 'กล่อง','qty' => '2','unit_price' => '200.00000'],
            ['code' => '07-00089','title' => 'hs-Troponin I ยี่ห้อHotgen 60 pcs./box #41116104','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00090','title' => 'hs-Troponin I control ยี่ห้อHotgen #41116107','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00091','title' => 'NT-ProBNP ยี่ห้อHotgen 60 pcs./box #41116104','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00092','title' => 'NT-proBNP control ยี่ห้อHotgen #41116107','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00093','title' => 'Immersion oil 500 ml #41116120','unit' => 'ขวด','qty' => '2','unit_price' => '3000.00000'],
            ['code' => '07-00095','title' => 'Iodine for Gram stain #41116130','unit' => '','qty' => '1','unit_price' => '350.00000'],
            ['code' => '07-00096','title' => 'K2 EDTA vacuum tube #41116120','unit' => 'หลอด','qty' => '2440','unit_price' => '1.90000'],
            ['code' => '07-00097','title' => 'DCIP 100 test #41116120','unit' => 'กล่อง','qty' => '1','unit_price' => '1740.00000'],
            ['code' => '07-00098','title' => 'Kovac reagent #41116129','unit' => 'ขวด','qty' => '1','unit_price' => '1200.00000'],
            ['code' => '07-00100','title' => 'LISS (กาชาด) #41116102','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00103','title' => 'Leptospira IgM antibody 30 Test #41116126','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00104','title' => 'Leukocyte Poor Packed Red Cells (LPRC) #41116102','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00106','title' => 'Levofloxacin disc 5 ug #41116131','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00107','title' => 'Lithium Battery CR2032 #41116104','unit' => 'ก้อน','qty' => '313','unit_price' => '15.00000'],
            ['code' => '07-00108','title' => 'Lithium Heparin Tube (non vac.) #41116104','unit' => 'หลอด','qty' => '980','unit_price' => '1.45000'],
            ['code' => '07-00109','title' => 'Loop for bacteria #41116129','unit' => 'ด้าม','qty' => '0','unit_price' => '0'],
            ['code' => '07-00110','title' => 'Loop 1 ul Sterile 50*10 pcs. #41116129','unit' => 'กล่อง','qty' => '1','unit_price' => '1500.00000'],
            ['code' => '07-00113','title' => 'MNP Biochem 50 tube/pack #41116129','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00114','title' => 'MacConkey agar 10 pcs./pack #41116129','unit' => 'แพค','qty' => '1','unit_price' => '100.00000'],
            ['code' => '07-00115','title' => 'Oxidase Test ยี่ห้อMast 25 strips #41116129','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00118','title' => 'McFARLAND STD 0.5 #41116143','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '07-00119','title' => 'Meropenem disc 10 ug #41116131','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00122','title' => 'Methamphetamine Cassette 40 test(Cut-off1000 ng/ml) #41116136','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00123','title' => 'Methanol 5 L. #41116121','unit' => 'ขวด','qty' => '1','unit_price' => '1500.00000'],
            ['code' => '07-00124','title' => 'Micro-tray 96 wells #41116126','unit' => 'ชิ้น','qty' => '32','unit_price' => '30.00000'],
            ['code' => '07-00125','title' => 'Microcentrifuge Tube 1.5 ml (500 pcs.) #41116104','unit' => 'ถุง','qty' => '1','unit_price' => '1500.00000'],
            ['code' => '07-00126','title' => 'Microscope slide ขอบฝ้า 72 pcs./box #41116140','unit' => 'กล่อง','qty' => '73','unit_price' => '38.00000'],
            ['code' => '07-00127','title' => 'Microscope slide ขอบใส 72 pcs./box #41116140','unit' => 'กล่อง','qty' => '82','unit_price' => '33.93902'],
            ['code' => '07-00128','title' => 'Modified Decolizer AFB #41116130','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00129','title' => 'Mueller Hinton agar 10 pcs./pack #41116129','unit' => 'แพค','qty' => '4','unit_price' => '120.00000'],
            ['code' => '07-00130','title' => 'Muller Hinton with 5% sheep blood agar 10 pcs./pack #41116129','unit' => 'แพค','qty' => '3','unit_price' => '150.00000'],
            ['code' => '07-00134','title' => 'NaF Blood tube (non vac.) #41116104','unit' => 'หลอด','qty' => '202','unit_price' => '1.21218'],
            ['code' => '07-00138','title' => 'Norfloxacin disc 10 ug #41116131','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00141','title' => 'Novobiocin disc #41116131','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00143','title' => 'OF-glucose biochem 50 tube/pack #41116129','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00144','title' => 'Ofloxacin disc 5 ug #41116131','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00145','title' => 'Optochin disc #41116131','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00146','title' => 'Oxacillin disc 1 ug #41116131','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00147','title' => 'PP container 40 ml for UA #41116136','unit' => 'ชิ้น','qty' => '2970','unit_price' => '1.50000'],
            ['code' => '07-00148','title' => 'Packed Red Cell (PRC) #41116102','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00150','title' => 'Penicillin disc G 10 #41116131','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00151','title' => 'Plasma Control Level 1 (Uniplastin) #41116110','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00152','title' => 'Plasma Control Level 2 (Uniplastin) #41116110','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00153','title' => 'Preg test (HCG Urine strip) 100 test #41116136','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '07-00154','title' => 'RPR 500 test #41116126','unit' => 'กล่อง','qty' => '1','unit_price' => '1130.00000'],
            ['code' => '07-00156','title' => 'Reflotron CK #41116104','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00158','title' => 'Reflotron P-Amylase #41116104','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00159','title' => 'Rh negative cells #41116102','unit' => 'ขวด','qty' => '1','unit_price' => '100.00000'],
            ['code' => '07-00160','title' => 'Rhumatoid Factor Latex 100 test #41116126','unit' => 'กล่อง','qty' => '1','unit_price' => '820.00000'],
            ['code' => '07-00161','title' => 'SS agar 10 pcs./pack #41116129','unit' => 'แพค','qty' => '0','unit_price' => '0'],
            ['code' => '07-00162','title' => 'SYM control muti level e-check (XS) #41116122','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00163','title' => 'Salmonella typhi IgG/IgM 25 cassette #41116126','unit' => 'กล่อง','qty' => '1','unit_price' => '2125.00000'],
            ['code' => '07-00165','title' => 'Scrub Typhus Ab 25 Test #41116126','unit' => 'กล่อง','qty' => '2','unit_price' => '1950.00000'],
            ['code' => '07-00166','title' => 'Serodia HIV1/2 mix 20*5 tests #41116126','unit' => 'กล่อง','qty' => '1','unit_price' => '6870.00000'],
            ['code' => '07-00167','title' => 'Clot activator Blood tube (non vac.) #41116104','unit' => 'หลอด','qty' => '470','unit_price' => '1.16957'],
            ['code' => '07-00168','title' => 'Sheep Blood Agar 10 pcs./pack #41116129','unit' => 'แพค','qty' => '1','unit_price' => '120.00000'],
            ['code' => '07-00169','title' => 'Simmon Citrate biochem 50 tube/pack #41116129','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00174','title' => 'Standard Cell A , Cell B #41116102','unit' => 'ชุด','qty' => '2','unit_price' => '60.00000'],
            ['code' => '07-00175','title' => 'Standard Cell O1 ,O2 (Screening Cell) #41116102','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00176','title' => 'Std. Micro bilirubin #41116107','unit' => 'ขวด','qty' => '2','unit_price' => '2100.00000'],
            ['code' => '07-00178','title' => 'Stromatolyser 4DL FFD-200A #41116008','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00179','title' => 'Stromatolyser 4DS FFS-800A #41116008','unit' => 'แพค','qty' => '0','unit_price' => '0'],
            ['code' => '07-00180','title' => 'Sulfolyser SLS-210A #41116008','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00181','title' => 'Sulpha/Trimetho disc 1.25*23.75 ug #41116131','unit' => 'หลอด','qty' => '5','unit_price' => '90.00000'],
            ['code' => '07-00182','title' => 'TCBS agar 10 pcs./pack #41116129','unit' => 'แพค','qty' => '0','unit_price' => '0'],
            ['code' => '07-00184','title' => 'TSI biochem 50 tube/pack #41116129','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00185','title' => 'Test tube 10*75 mm. (72 pcs./box) #41116101','unit' => 'กล่อง','qty' => '2','unit_price' => '900.00000'],
            ['code' => '07-00186','title' => 'Test tube 12*75 mm. (72 pcs./box) #41116104','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00189','title' => 'Thermal paper 57*25*1 cm #41116136','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '07-00190','title' => 'Tip for Hotgen MQ60 #41116104','unit' => 'กล่อง','qty' => '10','unit_price' => '100.00000'],
            ['code' => '07-00191','title' => 'Toluene 2.5 L #41116000','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00193','title' => 'Transfer Pipette 1 ml (500 pcs.) #41116126','unit' => 'กล่อง','qty' => '4','unit_price' => '320.00000'],
            ['code' => '07-00194','title' => 'Tribulb Pipette for UA (500 pcs.) #41116136','unit' => 'ถุง','qty' => '1','unit_price' => '1250.00000'],
            ['code' => '07-00196','title' => 'Trypton Soya Broth 50 tube/pack #41116129','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00197','title' => 'CPD Blood Tube 6 ml #41116101','unit' => 'หลอด','qty' => '100','unit_price' => '5.00000'],
            ['code' => '07-00198','title' => 'Clotted Blood Tube 6 ml #41116101','unit' => 'หลอด','qty' => '130','unit_price' => '5.00000'],
            ['code' => '07-00199','title' => 'EDTA Blood Tube 6 ml #41116101','unit' => 'หลอด','qty' => '1395','unit_price' => '2.95771'],
            ['code' => '07-00200','title' => 'Tube พลาสติก 12*75 mm (1000 pcs.) #41116126','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00201','title' => 'Tube พลาสติก 13*100 mm (500 pcs.) #41116104','unit' => 'ถุง','qty' => '1','unit_price' => '500.00000'],
            ['code' => '07-00202','title' => 'Prothrombin Time ยี่ห้อUniplastin ISI #41116005','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00203','title' => 'Urea biochem 50 tube/pack #41116129','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00206','title' => 'Urine Container Sterile 60 ml #41116129','unit' => 'ชิ้น','qty' => '467','unit_price' => '2.50150'],
            ['code' => '07-00207','title' => 'Urine strip 2 แถบ (100 test) #41116138','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00208','title' => 'Urine strip 11 แถบ (100 test) #41116138','unit' => 'กล่อง','qty' => '12','unit_price' => '1000.00000'],
            ['code' => '07-00210','title' => 'Vancomycin disc 30 ug. #41116131','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00211','title' => 'HIV Ab 25 Test (cassette) #41116126','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00213','title' => 'Cefoxitin disc 30 ug. #41116131','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00214','title' => 'กระดาษกรอง(Filter paper) #41116129','unit' => 'กล่อง','qty' => '2','unit_price' => '150.00000'],
            ['code' => '07-00218','title' => 'พาราฟิล์ม 4 IN. x 125 FT. #41116126','unit' => 'ม้วน','qty' => '3','unit_price' => '1000.00000'],
            ['code' => '07-00222','title' => 'Gram decolorizer #41116130','unit' => 'ขวด','qty' => '1','unit_price' => '250.00000'],
            ['code' => '07-00223','title' => 'ชุดสีย้อม AFB #41116130','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00224','title' => 'ชุดสีย้อม Gram stain #41116130','unit' => 'กล่อง','qty' => '2','unit_price' => '750.00000'],
            ['code' => '07-00225','title' => 'สีย้อม Reticulocyte 10 ml. #41116121','unit' => 'ขวด','qty' => '2','unit_price' => '680.00000'],
            ['code' => '07-00230','title' => 'Needle for bacteria #41116129','unit' => 'ด้าม','qty' => '0','unit_price' => '0'],
            ['code' => '07-00231','title' => 'กระดาษเช็ดเลนส์ #41116140','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '07-00232','title' => 'Wright Giemsa Stain A #41116121','unit' => 'กล่อง','qty' => '2','unit_price' => '5000.00000'],
            ['code' => '07-00233','title' => 'Wright Giemsa Stain B #41116121','unit' => 'กล่อง','qty' => '3','unit_price' => '2000.00000'],
            ['code' => '07-00236','title' => 'HBs Ab 40 test (cassette) #41116126','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00237','title' => 'Preg test (HCG Urine strip) 50 test #41116136','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '07-00239','title' => 'K3 EDTA tube (non vac.) #41116120','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00240','title' => '10% lactose 50 tube/pack #41116129','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00241','title' => 'OF-maltose 50 tube/pack #41116129','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00242','title' => 'Diabetes BI Lyph, 2*0.5 ml #41116107','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00248','title' => 'Blood collecting pack 350 ml double CPD-A1 #41104109','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00249','title' => 'Blood collecting pack 450 ml double CPD-A1 #41104109','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00258','title' => 'Volumatic Pipette 5 ml #41116100','unit' => 'ชิ้้น','qty' => '0','unit_price' => '0'],
            ['code' => '07-00259','title' => 'Control Urine MAS Multi Pack 6*15 ml #41116139','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00260','title' => 'MIO biochem 50 tube/pack #41116129','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00261','title' => 'Lysine biochem 50 tube/pack #41116129','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00262','title' => 'Fecal Occult Blood 25 test #41116126','unit' => 'กล่อง','qty' => '8','unit_price' => '325.00000'],
            ['code' => '07-00264','title' => 'HBsAb 25 test (cassette)#41116126','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00265','title' => 'DM Revised Glucose Gluc Flex (4 flex/pack) #41116004','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00266','title' => 'BUN-Urea nitrogen (4 flex/pack)#41116004','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00267','title' => 'Dimension EZCR/Enzymatic Creatinine Flex (8 flex/pack)#41116004','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00268','title' => 'DM URCA/Uric Acid (8 flex/pack)#41116004','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00269','title' => 'DM Chol/Cholesterol (8 flex/pack)#41116004','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00270','title' => 'DM Triglyceride Flex Revised (4 flex/pack)#41116004','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00271','title' => 'DM TP/Total Protein (4 flex/pack)#41116004','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00272','title' => 'DM ALB/Albumin (4 flex/pack)#41116004','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00273','title' => 'DM TBI Flex reagent (8 flex/pack)#41116004','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00274','title' => 'DM DBI Flex Reagent (8 flex/pack)#41116004','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00275','title' => 'AST (4 flex/pack)#41116004','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00276','title' => 'DM ALTI (4 flex/pack)#41116004','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00277','title' => 'ALPI (4 flex/pack)#41116004','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00278','title' => 'DM AHDL Flex Reagent (8 flex/pack)#41116004','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00279','title' => 'DM Automated LDL Flex (4 flex/pack)#41116004','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00280','title' => 'DM CFP/UP Flex (4 flex/pack)#41116004','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00281','title' => 'DM Enzymatic carbonate Flexr Rea. Cart (4 flex/pack)#41116004','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00282','title' => 'Electrolyte (Na/K/Cl)#41116004','unit' => 'รายการ','qty' => '0','unit_price' => '0'],
            ['code' => '07-00283','title' => 'Microcentrifuge Tube 1.5 ml (1,000 pcs.) #41116104','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00332','title' => 'PP container 40 ml (เก็บปัสสาวะ)','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '07-00351','title' => 'ชุดทดสอบโคลิฟอร์มขั้นต้น (SI-2)','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00352','title' => 'Blood bag 2 ถุง พร้อม safety ขนาด 450 ml#41104109','unit' => 'แพค','qty' => '0','unit_price' => '0'],
            ['code' => '07-00353','title' => 'Blood bag 2 ถุง พร้อม safety ขนาด 350 ml#41104109','unit' => 'แพค','qty' => '0','unit_price' => '0'],
            ['code' => '07-00354','title' => 'คลอรีนผง 65%','unit' => 'ถัง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00355','title' => 'Sperm Counting Fluid 450 ml#41116113','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00356','title' => 'Box for Blue Tip 100-100 ul#41116104','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00357','title' => 'Counting Chamber#41116111','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '07-00358','title' => 'COVID-19 Ag 25 Test#41116126','unit' => 'กล่อง','qty' => '40','unit_price' => '1000.00000'],
            ['code' => '07-00359','title' => 'Coag-Prothrombin Time Reagent kit (PT) 1*24 tests#41116108','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00360','title' => 'Coag-Coagulation control Plasma 1 (1*1 ml)#41116110','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00361','title' => 'Coag-Coagulation Control Plasma 2 (1*1ml)#41116110','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00362','title' => 'Thermal paper for coag 58 g 57 mm*40 mm. #41116120','unit' => 'ม้วน','qty' => '11','unit_price' => '85.00000'],
            ['code' => '07-00363','title' => 'Wash Bottle 250 ml#41116101','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00364','title' => 'Thromborel S 10*4 ml#41116005','unit' => 'กล่อง','qty' => '1','unit_price' => '12305.00000'],
            ['code' => '07-00365','title' => 'Reaction Tube SU40 1000 Pc./pack#41116108','unit' => 'ถุง','qty' => '1','unit_price' => '10700.00000'],
            ['code' => '07-00366','title' => 'CA Clean I 1*50 ml#41116108','unit' => 'ขวด','qty' => '2','unit_price' => '3210.00000'],
            ['code' => '07-00367','title' => 'CA Clean II 1*500 ml#41116108','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00368','title' => 'Control Plasma N 10*1 ml#41116110','unit' => 'กล่อง','qty' => '1','unit_price' => '10700.00000'],
            ['code' => '07-00369','title' => 'CiTrol 2 E 10*1 ml#41116110','unit' => 'กล่อง','qty' => '1','unit_price' => '10700.00000'],
            ['code' => '07-00372','title' => 'Rack 5*10 holes 18*18 mm. #41116104','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '07-00373','title' => 'COV-19 Antigen Rapid Test 20 test#41116126','unit' => 'กล่อง','qty' => '12','unit_price' => '800.00000'],
            ['code' => '07-00374','title' => 'PR Arabinose#41116129','unit' => 'กล่อง','qty' => '3','unit_price' => '750.00000'],
            ['code' => '07-00375','title' => 'Fecal Occult Blood Test (100ng)#41116126','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '07-00376','title' => 'Blood collecting pack 350 ml double CPD-A1 with diversion pouch#41104109','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00377','title' => 'Blood collecting pack 450 ml double CPD-A1 with diversion pouch#41104109','unit' => 'ถุง','qty' => '89','unit_price' => '170.00000'],
            ['code' => '07-00378','title' => 'THC Cassette 40 test#41116136','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00379','title' => 'Needle Container 5*7" #41116116','unit' => 'กระป๋อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00381','title' => 'ชุดทดสอบบอแร็กซ์(ผงกรอบ)ในอาหาร','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00382','title' => 'ชุดทดสอบสารกรดซาลิซิลิคในอาหาร(สารกันรา)','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00383','title' => 'ชุดทดสอบโซเตียม ไฮโดรซัลไฟด์(สารฟอกขาว)','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00384','title' => 'ชุดทดสอบฟอร์มาลิน(น้ำยาดองศพ)','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00385','title' => 'ชุดทดสอบโคลิฟอร์มแบคทีเรียตรวจน้ำบริโภค(อ.11)','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00386','title' => 'Piperacillin/Tazobactam 110#41116131','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00387','title' => 'Leptospira IgM/IgG 25 test#41116126','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00388','title' => 'Safety lancet lite 3 No.28G 100 pcs#41116120','unit' => 'กล่อง','qty' => '3','unit_price' => '230.00000'],
            ['code' => '07-00389','title' => 'Blood lactate test strip 25 test/box#41116104','unit' => 'กล่อง','qty' => '6','unit_price' => '1875.00000'],
            ['code' => '07-00390','title' => 'ketone test strip 25 test/box#41116104','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00392','title' => 'Dengue NS1 Ag 25 tests/box#41116126','unit' => 'กล่อง','qty' => '1','unit_price' => '1775.00000'],
            ['code' => '07-00394','title' => 'Glycohemoglobin test kit for A1C 25 test/box#41116104','unit' => 'กล่อง','qty' => '4','unit_price' => '2250.00000'],
            ['code' => '07-00395','title' => 'Glycohemoglobin control level 1&2#41116107','unit' => 'กล่อง','qty' => '1','unit_price' => '2000.00000'],
            ['code' => '07-00396','title' => 'Matrix AHG (Coombs) Test Card 24 cards#41116101','unit' => 'กล่อง','qty' => '4','unit_price' => '4320.00000'],
            ['code' => '07-00397','title' => 'Matrix Diluent-2 LISS 250 ml#41116101','unit' => 'ขวด','qty' => '2','unit_price' => '3600.00000'],
            ['code' => '07-00398','title' => 'lancet Device #41116120','unit' => 'ด้าม','qty' => '13','unit_price' => '120.00000'],
            ['code' => '07-00399','title' => 'Lancet Soft 28G 100 pcs#41116120','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00400','title' => 'Lancet Soft 23G 100 pcs#41116120','unit' => 'กล่อง','qty' => '23','unit_price' => '54.00000'],
            ['code' => '07-00401','title' => 'Plug Tite Cap 13mm 1000 pcs#41116104','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00402','title' => 'Thromborel S 10*10 ml#41116005','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00403','title' => 'Standard Cell O1 ,O2,O3 (Screening Cell) #41116102','unit' => 'ชุด','qty' => '2','unit_price' => '200.00000'],
            ['code' => '07-00405','title' => 'FOB Rapid Test 20 test/box#41116126','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00406','title' => 'ชุดทดสอบโคลิฟอร์มในอาหาร','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00407','title' => 'ชุดทดสอบโคลิฟอร์มในน้ำและน้ำแข็ง','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00408','title' => 'Tribulb Pipette for UA (1,000 pcs.) #41116136','unit' => 'ถุง','qty' => '1','unit_price' => '1800.00000'],
            ['code' => '07-00409','title' => 'Pipette stand#41116104','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '07-00410','title' => 'HCG/B-HCG 50 test (CLIA)#41116010','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00411','title' => 'HBsAg 100 test (CLIA)#41116010','unit' => 'กล่อง','qty' => '1','unit_price' => '8600.00000'],
            ['code' => '07-00412','title' => 'HIV Ag/Ab Combi 100 test (CLIA)#41116010','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00413','title' => 'Syphilis 100 test (CLIA)#41116010','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00414','title' => 'FT3 100 test (CLIA)#41116010','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00415','title' => 'FT4 100 test (CLIA)#41116010','unit' => 'กล่อง','qty' => '1','unit_price' => '10000.00000'],
            ['code' => '07-00416','title' => 'TSH 100 test (CLIA)#41116010','unit' => 'กล่อง','qty' => '1','unit_price' => '9300.00000'],
            ['code' => '07-00418','title' => 'Urine analysis by fully automate (strip and sediment) 2000 test/pack#41116014','unit' => 'แพค','qty' => '0','unit_price' => '0'],
            ['code' => '07-00419','title' => 'Syphilis strip 50 test#41116126','unit' => 'กล่อง','qty' => '2','unit_price' => '365.00000'],
            ['code' => '07-00420','title' => 'Leptospira IgG/IgM Rapid Test 20 Test#41116126','unit' => 'กล่อง','qty' => '1','unit_price' => '1150.00000'],
            ['code' => '07-00421','title' => 'Tsutsugamushi 30 test/box#41116126','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00422','title' => 'THC Rapid Test 25 test/kit#41116136','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00423','title' => 'Cryopreserve Beads Mix 50criotubes/box#41116129','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00424','title' => 'Dengue IgG/IgM 25 test#41116126','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00425','title' => 'Influenza A&B rapid test 25 test#41116126','unit' => 'กล่อง','qty' => '5','unit_price' => '1780.00000'],
            ['code' => '07-00426','title' => 'Actin FS 10*2 ml#41116005','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00427','title' => 'Calcium Chloride (25mM) 10*15 ml#41116005','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00429','title' => 'เครื่องวัดปริมาณคลอรีนอิสระ','unit' => 'เครื่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00430','title' => 'สารเคมีทดสอบคลอรีนอิสระ','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
        ];

        if (BaseConsole::confirm('Are you sure?')) {
            $total = 0;
            foreach ($data as $key => $value) {
               
                $asetItem = Categorise::findOne(['name' => 'asset_item', 'code' => $value['code'], 'title' => $value['title']]);
                $unit = Categorise::findOne(['name' => 'unit', 'title' => $value['unit']]);
                // ถ้าไม่มีหน่วยให้สร้างใหม่
                if (!$unit) {
                    $newUnit = new Categorise([
                        'name' => 'unit',
                        'title' => $value['unit'],
                        'active' => 1,
                    ]);
                    $newUnit->save(false);
                }
                // ถ้าไม่มีประวัสดุใฟ้สร้างมห่
                if (!$asetItem) {
                    $newItem = new Categorise([
                        'name' => 'asset_item',
                        'group_id' => 4,
                        'category_id' => $assettype,
                        'code' => $value['code'],
                        'title' => $value['title'],
                        'data_json' => [
                            'unit' => $value['unit'],
                            'sub_title' => '',
                            'price_name' => '',
                            'category_name' => $categoryName,
                            'asset_type_name' => '',
                        ],
                    ]);
                    $newItem->save(false);
                }

                $qty = (int) explode('.', $value['qty'])[0];
            
                $lot = \mdm\autonumber\AutoNumber::generate('LOT'.substr(AppHelper::YearBudget(), 2).'-?????');
                $ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);

                // $checkModel = StockEvent::findOne([])
                $model = new StockEvent([
                    'ref' => $ref,
                    'lot_number' => $lot,
                    'name' => 'order_item',
                    'code' => $code,
                    'category_id' => $category_id,
                    'transaction_type' => 'IN',
                    'asset_item' => $value['code'],
                    'warehouse_id' => $warehouse_id,
                    'qty' => $value['qty'],
                    'unit_price' => (float) $value['unit_price'],
                    'order_status' => 'pending',
                    'data_json' => [
                        'req_qty' => '0',
                        'exp_date' => '',
                        'mfg_date' => '',
                        'item_type' => 'ยอดยกมา',
                        'po_number' => '',
                        'pq_number' => '',
                        'asset_type' => '',
                        'receive_date' => '',
                        'asset_type_name' => '',
                        'employee_fullname' => 'Administrator Lastname',
                        'employee_position' => 'นักวิชาการคอมพิวเตอร์',
                        'employee_department' => 'งานซ่อมบำรุง',
                    ],
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
                // echo (DOUBLE) $value['unit_price'],"\n";
                if ($model->save(false)) {
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."สำเร็จ! \n";
                } else {
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."ผิดพลาด! \n";
                }
                $sum = $qty * (int) $value['unit_price'];
                $total += $sum;
            }
            echo $total;
        }
    }

        // วัสดุการแพทย์ทั่วไป
        public  function actionM22()
        {
            //คลังวัสดุกายภาพบำบัด
            $warehouse_id = 4;
            $assettype = 'M22';
            $categoryName = 'วัสดุการแพทย์ทั่วไป';
            $category_id = 1228;
            $code = 'IN-680014';
    
            $data = [
                ['code' => '22-00162','title' => 'เฝือกพยุงระดับบั้นเอว (Lumbosacral support) ขนาด S','unit' => 'อัน','qty' => '11.00','unit_price' => '374.5'],
                ['code' => '22-00163','title' => 'เฝือกพยุงระดับบั้นเอว (Lumbosacral support) ขนาด M','unit' => 'อัน','qty' => '5.00','unit_price' => '374.5'],
                ['code' => '22-00164','title' => 'เฝือกพยุงระดับบั้นเอว (Lumbosacral support) ขนาด L','unit' => 'อัน','qty' => '17.00','unit_price' => '425.1517647'],
                ['code' => '22-00165','title' => 'เฝือกพยุงระดับบั้นเอว (Lumbosacral support) ขนาด XL','unit' => 'อัน','qty' => '25.00','unit_price' => '401.12'],
                ['code' => '22-00166','title' => 'เฝือกพยุงระดับบั้นเอว (Lumbosacral support) ขนาด 2XL','unit' => 'อัน','qty' => '14.00','unit_price' => '428'],
                ['code' => '22-00167','title' => 'เฝือกพยุงคอ(collar) คออ่อนขนาด S','unit' => 'อัน','qty' => '12.00','unit_price' => '171'],
                ['code' => '22-00168','title' => 'ไม้ค้ำยัน ขนาด35','unit' => 'คู่','qty' => '0','unit_price' => '0'],
                ['code' => '22-00169','title' => 'ไม้ค้ำยัน ขนาด 44','unit' => 'คู่','qty' => '1.00','unit_price' => '290'],
                ['code' => '22-00170','title' => 'ไม้ค้ำยัน ขนาด 46','unit' => 'คู่','qty' => '5.00','unit_price' => '290'],
                ['code' => '22-00171','title' => 'ไม้ค้ำยัน ขนาด 48','unit' => 'คู่','qty' => '16.00','unit_price' => '290'],
                ['code' => '22-00172','title' => 'ไม้ค้ำยัน ขนาด 50','unit' => 'คู่','qty' => '6.00','unit_price' => '288.3333333'],
                ['code' => '22-00173','title' => 'ไม้ค้ำยัน ขนาด 52','unit' => 'คู่','qty' => '2.00','unit_price' => '280'],
                ['code' => '22-00174','title' => 'ไม้เท้า 1 ปุ่ม','unit' => 'อัน','qty' => '30.00','unit_price' => '107'],
                ['code' => '22-00175','title' => 'ไม้เท้าชนิด 3 หรือ 4 ปุ่ม','unit' => 'อัน','qty' => '21.00','unit_price' => '394.5238095'],
                ['code' => '22-00176','title' => 'สายคล้องแขน(arm sling) ขนาด L','unit' => 'อัน','qty' => '21.00','unit_price' => '49.99238095'],
                ['code' => '22-00177','title' => 'สายคล้องแขน(arm sling) ขนาด XL','unit' => 'อัน','qty' => '15.00','unit_price' => '28.19666667'],
                ['code' => '22-00179','title' => 'อุปกรณ์พยุงข้อเข่า(knee support) ไม่มีแกนด้านข้าง ขนาด M','unit' => 'อัน','qty' => '21.00','unit_price' => '200'],
                ['code' => '22-00180','title' => 'อุปกรณ์พยุงข้อเข่า(knee support) ไม่มีแกนด้านข้าง ขนาด L','unit' => 'อัน','qty' => '11.00','unit_price' => '200'],
                ['code' => '22-00181','title' => 'อุปกรณ์พยุงข้อเข่า(knee support) ไม่มีแกนด้านข้าง ขนาด XL','unit' => 'อัน','qty' => '4.00','unit_price' => '200'],
                ['code' => '22-00182','title' => 'อุปกรณ์พยุงข้อเข่า(knee support) ไม่มีแกนด้านข้าง ขนาด 2XL','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                ['code' => '22-00183','title' => 'อุปกรณ์พยุงข้อเข่า(knee support) ไม่มีแกนด้านข้าง ขนาด S','unit' => 'อัน','qty' => '31.00','unit_price' => '204.516129'],
                ['code' => '22-00184','title' => 'เฝือกพยุงไหปลาร้า ขนาด S','unit' => 'อัน','qty' => '24.00','unit_price' => '130'],
                ['code' => '22-00185','title' => 'เฝือกพยุงข้อเท้า(ankle support) ขนาด S','unit' => 'อัน','qty' => '5.00','unit_price' => '200'],
                ['code' => '22-00187','title' => 'เฝือกพยุงข้อเท้า(ankle support) ขนาด L','unit' => 'อัน','qty' => '12.00','unit_price' => '200'],
                ['code' => '22-00188','title' => 'เฝือกพยุงข้อมือ(wrist support) ขนาด S','unit' => 'อัน','qty' => '1.00','unit_price' => '320'],
                ['code' => '22-00189','title' => 'เฝือกพยุงข้อมือ(wrist support) ขนาด M','unit' => 'อัน','qty' => '12.00','unit_price' => '160'],
                ['code' => '22-00190','title' => 'เฝือกพยุงข้อมือ(wrist support) ขนาด L','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                ['code' => '22-00191','title' => 'เจลอัลตร้าซาวด์ 5 ลิตร','unit' => 'แกลลอน','qty' => '1.00','unit_price' => '928.33'],
                ['code' => '22-00192','title' => 'ผ้ารัดหน้าท้อง ขนาด M','unit' => 'อัน','qty' => '21.00','unit_price' => '280'],
                ['code' => '22-00193','title' => 'ผ้ารัดหน้าท้อง ขนาด L','unit' => 'อัน','qty' => '15.00','unit_price' => '264'],
                ['code' => '22-00194','title' => 'เฝือกพยุงนิ้วโป้ง(Thumb support) ขนาด S','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                ['code' => '22-00195','title' => 'เฝือกพยุงนิ้วโป้ง(Thumb support) ขนาด M','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                ['code' => '22-00196','title' => 'ลูกยางไม้ค้ำยัน สีดำ','unit' => 'ลูก','qty' => '0','unit_price' => '0'],
                ['code' => '22-00197','title' => 'เฝือกพยุงคอ(collar) คออ่อน ขนาด M','unit' => 'อัน','qty' => '9.00','unit_price' => '72'],
                ['code' => '22-00198','title' => 'เฝือกพยุงคอ(collar) คออ่อน ขนาด L','unit' => 'อัน','qty' => '11.00','unit_price' => '180'],
                ['code' => '22-00199','title' => 'สายคล้องแขนกันไหล่หลุด ขนาด L','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                ['code' => '22-00200','title' => 'สายคล้องแขนกันไหล่หลุด ขนาด XL','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                ['code' => '22-00201','title' => 'เครื่องช่วยเดินชนิด 4 ขา(pick up walker)','unit' => 'ตัว','qty' => '21.00','unit_price' => '374.5'],
                ['code' => '22-00202','title' => 'สายคล้องแขน(arm sling) ขนาด S','unit' => 'อัน','qty' => '21.00','unit_price' => '28.23666667'],
                ['code' => '22-00203','title' => 'สายคล้องแขน(arm sling) ขนาด M','unit' => 'อัน','qty' => '20.00','unit_price' => '31.61'],
                ['code' => '22-00204','title' => 'เฝือกพยุงไหปลาร้า ขนาด L','unit' => 'อัน','qty' => '6.00','unit_price' => '200'],
                ['code' => '22-00205','title' => 'เฝือกพยุงไหปลาร้า ขนาด M','unit' => 'อัน','qty' => '7.00','unit_price' => '164.7614286'],
                ['code' => '22-00206','title' => 'เฝือกพยุงไหปลาร้า ขนาด XL','unit' => 'อัน','qty' => '5.00','unit_price' => '130.002'],
                ['code' => '22-00207','title' => 'INDUSTRIAL BACK SUPPORT สีดำ No.XXL','unit' => 'อัน','qty' => '5.00','unit_price' => '900'],
                ['code' => '22-00208','title' => 'INDUSTRIAL BACK SUPPORT สีดำ No.M','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                ['code' => '22-00209','title' => 'INDUSTRIAL SACK SUPPORT สีดำNO.XL','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                ['code' => '22-00210','title' => 'ไม้ค้ำยัน ขนาด40','unit' => 'คู่','qty' => '0','unit_price' => '0'],
                ['code' => '22-00211','title' => 'ไม้ค้ำยัน ขนาด42','unit' => 'คู่','qty' => '0','unit_price' => '0'],
                ['code' => '22-00211','title' => 'ไม้ค้ำยัน ขนาด42','unit' => 'คู่','qty' => '4.00','unit_price' => '290'],
                ['code' => '22-00213','title' => 'อุปกรณ์พยุงข้อเข่า(knee support) มีแกนด้านข้าง (เจาะรู) ขนาด M','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                ['code' => '22-00215','title' => 'อุปกรณ์พยุงข้อเข่า(knee support) มีแกนด้านข้าง (เจาะรู) ขนาด XL','unit' => 'อัน','qty' => '2.00','unit_price' => '900'],
                ['code' => '22-00216','title' => 'เฝือกพยุงข้อมือ(wrist support) ขนาด XL','unit' => 'อัน','qty' => '12.00','unit_price' => '292.5'],
                ['code' => '22-00218','title' => 'ผ้ารัดหน้าท้อง ขนาด XXL','unit' => 'อัน','qty' => '22.00','unit_price' => '286.3636364'],
                ['code' => '22-00219','title' => 'ไม้ค้ำยัน ขนาด54','unit' => 'คู่','qty' => '12.00','unit_price' => '296.3016667'],
                ['code' => '22-00220','title' => 'SKIN TRACTION','unit' => 'อัน','qty' => '46.00','unit_price' => '246.1534783'],
                ['code' => '22-00221','title' => 'ผ้ารัดหน้าท้อง ขนาด XL','unit' => 'อัน','qty' => '24.00','unit_price' => '215'],
                ['code' => '22-00225','title' => 'รองเท้าหนังสำหรับผู้ป่วยเบาหวานเบอร์ 42','unit' => 'คู่','qty' => '0','unit_price' => '0'],
                ['code' => '22-00227','title' => 'อุปกรณ์พยุงข้อเข่า(knee support) มีแกนด้านข้าง (เจาะรู) ขนาด L','unit' => 'อัน','qty' => '2.00','unit_price' => '562.5'],
                ['code' => '22-00589','title' => 'ผ้าขนหนู ขนาด 27 x 54 นิ้ว','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
                ['code' => '22-00633','title' => 'รองเท้าหนังสำหรับผู้ป่วยเบาหวานเบอร์ 43','unit' => 'คู่','qty' => '0','unit_price' => '0'],
                ['code' => '22-00634','title' => 'รองเท้าหนังสำหรับผู้ป่วยเบาหวานเบอร์ 44','unit' => 'คู่','qty' => '0','unit_price' => '0'],
                ['code' => '22-00632','title' => 'กล่องโฟม','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
                ['code' => '22-00186','title' => 'เฝือกพยุงข้อเท้า(ankle support) ขนาด M','unit' => 'อัน','qty' => '12.00','unit_price' => '200'],
                ['code' => '22-00212','title' => 'เฝือกพยุงข้อเท้า(ankle support) ขนาด XL','unit' => 'อัน','qty' => '5.00','unit_price' => '200'],
                ['code' => '22-00778','title' => 'ซองกันน้ำลาย เบอร์ 2','unit' => '','qty' => '0','unit_price' => '0'],
                ['code' => '22-00799','title' => 'แบตเตอรี่เครื่องติดตามสัญญาณชีพ','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
                ['code' => '22-00810','title' => 'สาย Silicone แบบ Reusable ยาว 150 cm','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
                ['code' => '22-00811','title' => 'Silicone Bag 2.0 L','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                ['code' => '22-00812','title' => 'Durable Y-Connertor','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                ['code' => '22-00813','title' => 'Durable STRAIGHT Connector','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                ['code' => '22-00816','title' => 'ผ้าสี่เหลี่ยมสองชั้น เจาะกลาง 3X3 นิ้ว สีเขียว ขนาด 18X18 นิ้ว','unit' => '','qty' => '0','unit_price' => '0'],
                ['code' => '22-00817','title' => 'ผ้าสี่เหลี่ยมสองชั้น สีเขียว ขนาด 20X20 นิ้ว','unit' => '','qty' => '0','unit_price' => '0'],
                ['code' => '22-00818','title' => 'ผ้าสี่เหลี่ยมสองชั้น สีเขียว ขนาด 25X25 นิ้ว','unit' => '','qty' => '0','unit_price' => '0'],
                ['code' => '22-00828','title' => 'SPO2 Finger Sensor,2.5 m','unit' => '','qty' => '0','unit_price' => '0'],
                ['code' => '22-00829','title' => 'Rechargeable Li-Ion Battery','unit' => '','qty' => '0','unit_price' => '0'],
                ['code' => '22-00835','title' => 'หลอดไฟ HPX 3.5 V (03100)','unit' => '','qty' => '0','unit_price' => '0'],
                ['code' => '22-00836','title' => 'Rechargeable Internal Lithium Battery','unit' => '','qty' => '0','unit_price' => '0'],
                ['code' => '22-00837','title' => 'laryngeal mask airway (LMA) ,One way,Silicone,Size 1.0','unit' => '','qty' => '0','unit_price' => '0'],
                ['code' => '22-00842','title' => 'laryngeal mask airway (LMA) ,One way,Silicone,Size 4','unit' => '','qty' => '0','unit_price' => '0'],
                ['code' => '22-00843','title' => 'laryngeal mask airway (LMA) ,One way,Silicone,Size 5','unit' => '','qty' => '0','unit_price' => '0'],
                ['code' => '22-00851','title' => 'รถเข็นผู้ป่วย (สำหรับจำหน่าย)','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ];
    
            if (BaseConsole::confirm('Are you sure?')) {
                $total = 0;
                foreach ($data as $key => $value) {
                   
                    $asetItem = Categorise::findOne(['name' => 'asset_item', 'code' => $value['code'], 'title' => $value['title']]);
                    $unit = Categorise::findOne(['name' => 'unit', 'title' => $value['unit']]);
                    // ถ้าไม่มีหน่วยให้สร้างใหม่
                    if (!$unit) {
                        $newUnit = new Categorise([
                            'name' => 'unit',
                            'title' => $value['unit'],
                            'active' => 1,
                        ]);
                        $newUnit->save(false);
                    }
                    // ถ้าไม่มีประวัสดุใฟ้สร้างมห่
                    if (!$asetItem) {
                        $newItem = new Categorise([
                            'name' => 'asset_item',
                            'group_id' => 4,
                            'category_id' => $assettype,
                            'code' => $value['code'],
                            'title' => $value['title'],
                            'data_json' => [
                                'unit' => $value['unit'],
                                'sub_title' => '',
                                'price_name' => '',
                                'category_name' => $categoryName,
                                'asset_type_name' => '',
                            ],
                        ]);
                        $newItem->save(false);
                    }
    
                    $qty = (int) explode('.', $value['qty'])[0];
                
                    $lot = \mdm\autonumber\AutoNumber::generate('LOT'.substr(AppHelper::YearBudget(), 2).'-?????');
                    $ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
                    $model = new StockEvent([
                        'ref' => $ref,
                        'lot_number' => $lot,
                        'name' => 'order_item',
                        'code' => $code,
                        'category_id' => $category_id,
                        'transaction_type' => 'IN',
                        'asset_item' => $value['code'],
                        'warehouse_id' => $warehouse_id,
                        'qty' => $value['qty'],
                        'unit_price' => (float) $value['unit_price'],
                        'order_status' => 'pending',
                        'data_json' => [
                            'req_qty' => '0',
                            'exp_date' => '',
                            'mfg_date' => '',
                            'item_type' => 'ยอดยกมา',
                            'po_number' => '',
                            'pq_number' => '',
                            'asset_type' => '',
                            'receive_date' => '',
                            'asset_type_name' => '',
                            'employee_fullname' => 'Administrator Lastname',
                            'employee_position' => 'นักวิชาการคอมพิวเตอร์',
                            'employee_department' => 'งานซ่อมบำรุง',
                        ],
                        'created_by' => 1,
                        'updated_by' => 1,
                    ]);
                    // echo (DOUBLE) $value['unit_price'],"\n";
                    if ($model->save(false)) {
                        echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."สำเร็จ! \n";
                    } else {
                        echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."ผิดพลาด! \n";
                    }
                    $sum = $qty * (int) $value['unit_price'];
                    $total += $sum;
                }
                echo $total;
            }
        }


                // วัสดุทันตกรรม
                public  function actionM19()
                {
                    //คลังวัสดุทันตกรรม
                    $warehouse_id = 3;
                    $assettype = 'M19';
                    $categoryName = 'วัสดุทันตกรรม';
                    $category_id = 1229;
                    $code = 'IN-680015';
            
                    $data = [
                        ['code' => '19-00001','title' => 'Acrylic - ivory','unit' => 'ถุง','qty' => '1.00','unit_price' => '1500.00000'],
                        ['code' => '19-00003','title' => 'Alginate','unit' => 'ถุง','qty' => '5.00','unit_price' => '202.00000'],
                        ['code' => '19-00004','title' => 'Alvogyl','unit' => 'ขวด','qty' => '1.00','unit_price' => '1700.00000'],
                        ['code' => '19-00005','title' => 'Amalgam 1 spill (500 แคป/10ซองเล็ก)','unit' => 'กระปุก','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00006','title' => 'Amalgam 2 spills (500 แคป/10ซองเล็ก)','unit' => 'กระปุก','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00007','title' => 'Amalgam carrier','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00009','title' => 'Artery forceps','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00010','title' => 'Articulating paper','unit' => 'กล่อง','qty' => '6.00','unit_price' => '540.00000'],
                        ['code' => '19-00012','title' => 'Barbed broach - ดำ','unit' => 'ตัว','qty' => '10.00','unit_price' => '23.00000'],
                        ['code' => '19-00013','title' => 'Barbed broach - แดง','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00014','title' => 'Barbed broach - น้ำเงิน','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00015','title' => 'Barbed broach - เหลือง','unit' => 'ตัว','qty' => '10.00','unit_price' => '23.00000'],
                        ['code' => '19-00017','title' => 'Blade #12','unit' => 'กล่อง','qty' => '2.00','unit_price' => '980.00000'],
                        ['code' => '19-00018','title' => 'Blade #15','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00022','title' => 'Calcium hydroxide','unit' => 'ขวด','qty' => '1.00','unit_price' => '200.00000'],
                        ['code' => '19-00023','title' => 'Carbide cutter - เขียว','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00024','title' => 'Carbide cutter - ฟ้า','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00026','title' => 'Celluloid strip','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00030','title' => 'Clove oil','unit' => 'ขวด','qty' => '1.00','unit_price' => '130.00000'],
                        ['code' => '19-00032','title' => 'Composhape rugby - เหลือง','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00033','title' => 'Composhape thin taper - แดง','unit' => 'ตัว','qty' => '10.00','unit_price' => '45.00000'],
                        ['code' => '19-00034','title' => 'Composhape thin taper - เหลือง','unit' => 'ตัว','qty' => '10.00','unit_price' => '40.00000'],
                        ['code' => '19-00035','title' => 'Composite A2B (Filtek Z350 XT)','unit' => 'หลอด','qty' => '4.00','unit_price' => '856.00000'],
                        ['code' => '19-00038','title' => 'Composite A3.5B (Filtek Z350 XT)','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00039','title' => 'Composite A3.5 (Estelite)','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00040','title' => 'Composite A3B (Filtek Z350 XT)','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00042','title' => 'Composite A3E (Filtek Z350 XT)','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00043','title' => 'Composite A3 (Estelite)','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00044','title' => 'Composite A4B (Filtek Z350 XT)','unit' => 'หลอด','qty' => '6.00','unit_price' => '856.00000'],
                        ['code' => '19-00046','title' => 'Composite AT (Filtek Z350 XT)','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00048','title' => 'Adhesive bonding','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00052','title' => 'Compound','unit' => 'กล่อง','qty' => '6.00','unit_price' => '982.91667'],
                        ['code' => '19-00057','title' => 'Cylinder diamond bur','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00058','title' => 'Dental floss','unit' => 'กล่อง','qty' => '5.00','unit_price' => '240.00000'],
                        ['code' => '19-00060','title' => 'Dycal','unit' => 'กล่อง','qty' => '1.00','unit_price' => '909.50000'],
                        ['code' => '19-00063','title' => 'Endo stops','unit' => '','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00064','title' => 'Etchant - bottle','unit' => 'ขวด','qty' => '9.00','unit_price' => '666.61000'],
                        ['code' => '19-00065','title' => 'Etchant - syringe','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00067','title' => 'Explorer','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00068','title' => 'Face shield - แผ่นใส','unit' => 'แผ่น','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00069','title' => 'Face shield - หน้ากาก','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00070','title' => 'Fiber post #0.5','unit' => 'แพค','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00071','title' => 'Fiber post #1','unit' => 'แพค','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00072','title' => 'Fiber post #2','unit' => 'แพค','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00073','title' => 'Fiber post #3','unit' => 'แพค','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00074','title' => 'File 21mm #08','unit' => 'กล่อง','qty' => '4.00','unit_price' => '220.00000'],
                        ['code' => '19-00075','title' => 'File 21mm #10','unit' => 'กล่อง','qty' => '5.00','unit_price' => '138.60000'],
                        ['code' => '19-00076','title' => 'File 21mm #15','unit' => 'กล่อง','qty' => '10.00','unit_price' => '143.50000'],
                        ['code' => '19-00077','title' => 'File 21mm #20','unit' => 'กล่อง','qty' => '3.00','unit_price' => '220.00000'],
                        ['code' => '19-00078','title' => 'File 21mm #25','unit' => 'กล่อง','qty' => '1.00','unit_price' => '135.00000'],
                        ['code' => '19-00079','title' => 'File 21mm #30','unit' => 'กล่อง','qty' => '2.00','unit_price' => '220.00000'],
                        ['code' => '19-00080','title' => 'File 21mm #35','unit' => 'กล่อง','qty' => '3.00','unit_price' => '178.07333'],
                        ['code' => '19-00081','title' => 'File 21mm #40','unit' => 'กล่อง','qty' => '3.00','unit_price' => '206.66667'],
                        ['code' => '19-00082','title' => 'File 21mm #45','unit' => 'กล่อง','qty' => '2.00','unit_price' => '135.00000'],
                        ['code' => '19-00083','title' => 'File 21mm #50','unit' => 'กล่อง','qty' => '2.00','unit_price' => '235.63000'],
                        ['code' => '19-00084','title' => 'File 21mm #55','unit' => 'กล่อง','qty' => '2.00','unit_price' => '185.31500'],
                        ['code' => '19-00085','title' => 'File 21mm #60','unit' => 'กล่อง','qty' => '2.00','unit_price' => '157.50000'],
                        ['code' => '19-00086','title' => 'File 21mm #70','unit' => 'กล่อง','qty' => '2.00','unit_price' => '235.63000'],
                        ['code' => '19-00087','title' => 'File 21mm #80','unit' => 'กล่อง','qty' => '2.00','unit_price' => '217.08000'],
                        ['code' => '19-00088','title' => 'File 25mm #08','unit' => 'กล่อง','qty' => '3.00','unit_price' => '135.00000'],
                        ['code' => '19-00089','title' => 'File 25mm #10','unit' => 'กล่อง','qty' => '2.00','unit_price' => '220.00000'],
                        ['code' => '19-00090','title' => 'File 25mm #15','unit' => 'กล่อง','qty' => '7.00','unit_price' => '227.14286'],
                        ['code' => '19-00091','title' => 'File 25mm #20','unit' => 'กล่อง','qty' => '2.00','unit_price' => '220.00000'],
                        ['code' => '19-00092','title' => 'File 25mm #25','unit' => 'กล่อง','qty' => '5.00','unit_price' => '135.00000'],
                        ['code' => '19-00093','title' => 'File 25mm #30','unit' => 'กล่อง','qty' => '1.00','unit_price' => '135.00000'],
                        ['code' => '19-00094','title' => 'File 25mm #35','unit' => 'กล่อง','qty' => '2.00','unit_price' => '126.66500'],
                        ['code' => '19-00095','title' => 'File 25mm #40','unit' => 'กล่อง','qty' => '2.00','unit_price' => '135.00000'],
                        ['code' => '19-00096','title' => 'File 25mm #45','unit' => 'กล่อง','qty' => '2.00','unit_price' => '135.00000'],
                        ['code' => '19-00097','title' => 'File 25mm #50','unit' => 'กล่อง','qty' => '2.00','unit_price' => '122.50000'],
                        ['code' => '19-00098','title' => 'File 25mm #55','unit' => 'กล่อง','qty' => '2.00','unit_price' => '122.50000'],
                        ['code' => '19-00099','title' => 'File 25mm #60','unit' => 'กล่อง','qty' => '2.00','unit_price' => '110.00000'],
                        ['code' => '19-00100','title' => 'File 25mm #70','unit' => 'กล่อง','qty' => '2.00','unit_price' => '122.50000'],
                        ['code' => '19-00101','title' => 'File 25mm #80','unit' => 'กล่อง','qty' => '2.00','unit_price' => '122.50500'],
                        ['code' => '19-00105','title' => 'Film x-ray #0 - child','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00106','title' => 'Film x-ray #2 - adult','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00107','title' => 'Film x-ray #4 - occlusal','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00109','title' => 'Fissure carbide bur - impact','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00112','title' => 'Flowable composite A3.5 (Vertise Flow)','unit' => 'ถุง','qty' => '13.00','unit_price' => '1070.00000'],
                        ['code' => '19-00113','title' => 'Flowable composite A3 (Z350)','unit' => 'ถุง','qty' => '5.00','unit_price' => '428.00000'],
                        ['code' => '19-00115','title' => 'Flowable composite A3 (Vertise Flow)','unit' => 'ถุง','qty' => '2.00','unit_price' => '749.00000'],
                        ['code' => '19-00116','title' => 'Flowable composite A3.5 (Z350)','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00117','title' => 'Fluoride gel','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00119','title' => 'Fluoride tray M','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00121','title' => 'Fluoride varnish','unit' => 'กล่อง','qty' => '5.00','unit_price' => '2080.80000'],
                        ['code' => '19-00122','title' => 'Forceps','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00126','title' => 'Gates glidden drill #1','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00127','title' => 'Gates glidden drill #2','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00128','title' => 'Gates glidden drill #3','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00129','title' => 'Gelfoam','unit' => 'กล่อง','qty' => '1.00','unit_price' => '1150.00000'],
                        ['code' => '19-00130','title' => 'Dycal carrier','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00131','title' => 'ยาชา 2% lidocaine with epinephrine 1:100,000','unit' => 'กล่อง','qty' => '20.00','unit_price' => '970.12500'],
                        ['code' => '19-00132','title' => 'Lubricating cleaning agent (Hi-clean spray)','unit' => 'ขวด','qty' => '6.00','unit_price' => '590.00000'],
                        ['code' => '19-00133','title' => 'GI base (Vitrebond)','unit' => 'กล่อง','qty' => '9.00','unit_price' => '3531.00000'],
                        ['code' => '19-00134','title' => 'GI cement','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00135','title' => 'GI base ฉายแสง','unit' => 'กล่อง','qty' => '2.00','unit_price' => '2546.87500'],
                        ['code' => '19-00136','title' => 'Gutta percha #15','unit' => 'กล่อง','qty' => '1.00','unit_price' => '350.00000'],
                        ['code' => '19-00137','title' => 'Gutta percha #20','unit' => 'กล่อง','qty' => '1.00','unit_price' => '220.00000'],
                        ['code' => '19-00138','title' => 'Gutta percha #25','unit' => 'กล่อง','qty' => '3.00','unit_price' => '250.00000'],
                        ['code' => '19-00139','title' => 'Gutta percha #30','unit' => 'กล่อง','qty' => '1.00','unit_price' => '280.00000'],
                        ['code' => '19-00140','title' => 'Gutta percha #35','unit' => 'กล่อง','qty' => '2.00','unit_price' => '250.00000'],
                        ['code' => '19-00141','title' => 'Gutta percha #40','unit' => 'กล่อง','qty' => '2.00','unit_price' => '250.00000'],
                        ['code' => '19-00142','title' => 'Gutta percha #45','unit' => 'กล่อง','qty' => '1.00','unit_price' => '250.00000'],
                        ['code' => '19-00143','title' => 'Gutta percha #50','unit' => 'กล่อง','qty' => '1.00','unit_price' => '90.00000'],
                        ['code' => '19-00144','title' => 'Gutta percha #55','unit' => 'กล่อง','qty' => '1.00','unit_price' => '220.00000'],
                        ['code' => '19-00145','title' => 'Gutta percha #60','unit' => 'กล่อง','qty' => '1.00','unit_price' => '220.00000'],
                        ['code' => '19-00146','title' => 'Gutta percha #70','unit' => 'กล่อง','qty' => '2.00','unit_price' => '90.00000'],
                        ['code' => '19-00147','title' => 'Gutta percha #80','unit' => 'กล่อง','qty' => '2.00','unit_price' => '90.00000'],
                        ['code' => '19-00148','title' => 'Handpiece - airoter','unit' => 'ชิ้้น','qty' => '5.00','unit_price' => '12240.00000'],
                        ['code' => '19-00150','title' => 'Handpiece - straight','unit' => 'ชิ้น','qty' => '3.00','unit_price' => '6800.00000'],
                        ['code' => '19-00151','title' => 'High power suction','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00154','title' => 'Inverted diamond bur','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00155','title' => 'IRM','unit' => 'กล่อง','qty' => '2.00','unit_price' => '1450.00000'],
                        ['code' => '19-00156','title' => 'Ivory matrix band','unit' => 'ชิ้น','qty' => '1.00','unit_price' => '120.00000'],
                        ['code' => '19-00157','title' => 'Ivory matrix retainer','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00158','title' => 'Joint สำหรับ handpiece กรอช้า','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00159','title' => 'Lateral cone Fine','unit' => 'กล่อง','qty' => '2.00','unit_price' => '227.27000'],
                        ['code' => '19-00160','title' => 'Lateral cone Fine-Fine','unit' => 'กล่อง','qty' => '5.00','unit_price' => '250.00000'],
                        ['code' => '19-00161','title' => 'Lateral cone M-Fine','unit' => 'กล่อง','qty' => '3.00','unit_price' => '250.00000'],
                        ['code' => '19-00162','title' => 'Lateral cone X-Fine','unit' => 'กล่อง','qty' => '3.00','unit_price' => '250.00000'],
                        ['code' => '19-00163','title' => 'Lenturo spiral 21mm','unit' => 'แพค','qty' => '5.00','unit_price' => '435.00000'],
                        ['code' => '19-00164','title' => 'Lenturo spiral 25mm','unit' => 'แพค','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00165','title' => 'Light body silicone','unit' => 'กล่อง','qty' => '3.00','unit_price' => '1150.00000'],
                        ['code' => '19-00171','title' => 'Matrix band 6mm','unit' => 'กล่อง','qty' => '1.00','unit_price' => '280.00000'],
                        ['code' => '19-00174','title' => 'Microbrush','unit' => 'กล่อง','qty' => '1.00','unit_price' => '32.50000'],
                        ['code' => '19-00176','title' => 'Mixing pad','unit' => 'เล่ม','qty' => '11.00','unit_price' => '147.14273'],
                        ['code' => '19-00177','title' => 'Mixing plate','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00182','title' => 'Monophase silicone','unit' => 'กล่อง','qty' => '3.00','unit_price' => '1300.00000'],
                        ['code' => '19-00183','title' => 'Mouth gag - adult','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00185','title' => 'Mouth mirror','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00186','title' => 'Mouth mirror - front surface','unit' => 'กล่อง','qty' => '4.00','unit_price' => '555.00000'],
                        ['code' => '19-00187','title' => 'Mouth mirror - rear surface','unit' => 'กล่อง','qty' => '2.00','unit_price' => '380.00000'],
                        ['code' => '19-00188','title' => 'Mouth prop - adult','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00190','title' => 'Needle holder','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00192','title' => 'Paper point L','unit' => 'กล่อง','qty' => '2.00','unit_price' => '60.00000'],
                        ['code' => '19-00193','title' => 'Paper point M','unit' => 'กล่อง','qty' => '2.00','unit_price' => '60.00000'],
                        ['code' => '19-00194','title' => 'Paper point S','unit' => 'กล่อง','qty' => '1.00','unit_price' => '60.00000'],
                        ['code' => '19-00196','title' => 'Papoose board','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00200','title' => 'Peeso reamers #2','unit' => 'กล่อง','qty' => '1.00','unit_price' => '150.00000'],
                        ['code' => '19-00202','title' => 'Pink wax','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00203','title' => 'PIP','unit' => 'กระปุก','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00204','title' => 'Plaster spatula','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00207','title' => 'Pop-on เล็ก - coarse','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00208','title' => 'Pop-on เล็ก - medium','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00210','title' => 'Pop-on ใหญ่ - coarse','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00211','title' => 'Pop-on ใหญ่ - medium','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00213','title' => 'Prophy brush','unit' => 'แพค','qty' => '3.00','unit_price' => '320.00000'],
                        ['code' => '19-00215','title' => 'NiTi file - rotary (ProTaper)','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00216','title' => 'NiTi file - hand (ProTaper)','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00217','title' => 'Curette','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00218','title' => 'Pumice','unit' => 'กระปุก','qty' => '4.00','unit_price' => '130.00000'],
                        ['code' => '19-00219','title' => 'Putty silicone','unit' => 'กล่อง','qty' => '3.00','unit_price' => '2400.00000'],
                        ['code' => '19-00220','title' => 'RC Prep','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00221','title' => 'Resin cement','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00222','title' => 'Retraction cord','unit' => 'ขวด','qty' => '1.00','unit_price' => '420.00000'],
                        ['code' => '19-00223','title' => 'Root canal sealer','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00225','title' => 'Root canal spreader - D11T','unit' => 'ด้าม','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00226','title' => 'Root canal spreader - D11TS','unit' => 'ด้าม','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00227','title' => 'Root tip pick - double end','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00228','title' => 'หัวยางขัด acrylic - เขียว','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00230','title' => 'Round carbide bur - กรอช้า','unit' => 'ตัว','qty' => '20.00','unit_price' => '63.93900'],
                        ['code' => '19-00231','title' => 'Round carbide bur - กรอเร็ว','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00232','title' => 'Round carbide bur - impact','unit' => 'ตัว','qty' => '10.00','unit_price' => '80.00000'],
                        ['code' => '19-00233','title' => 'Round carbide bur - long shank','unit' => 'ตัว','qty' => '10.00','unit_price' => '107.50000'],
                        ['code' => '19-00234','title' => 'Round diamond bur','unit' => 'ตัว','qty' => '10.00','unit_price' => '60.00000'],
                        ['code' => '19-00235','title' => 'ถาดโลหะชุดตรวจ','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00236','title' => 'Round steel bur','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00237','title' => 'Rubber cup','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00238','title' => 'Rubber dam clamp #0','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00239','title' => 'Rubber dam clamp #14','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00240','title' => 'Rubber dam clamp #2','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00241','title' => 'Rubber dam forceps','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00244','title' => 'Rubber dam sheet - 5x5','unit' => 'กล่อง','qty' => '2.00','unit_price' => '240.00000'],
                        ['code' => '19-00245','title' => 'Rubber dam sheet - 6x6','unit' => 'กล่อง','qty' => '2.00','unit_price' => '335.62500'],
                        ['code' => '19-00246','title' => 'Rubber point','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00247','title' => 'Saliva ejector','unit' => 'แพค','qty' => '4.00','unit_price' => '85.00000'],
                        ['code' => '19-00248','title' => 'Sandpaper strip','unit' => 'กล่อง','qty' => '7.00','unit_price' => '480.00000'],
                        ['code' => '19-00249','title' => 'Sealant','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00250','title' => 'Sectional matrix band','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00258','title' => 'Stone type 2 - สีเขียว','unit' => 'กิโลกรัม','qty' => '4.00','unit_price' => '70.83250'],
                        ['code' => '19-00259','title' => 'Stone type 3 - Velmix','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00260','title' => 'Syringe สำหรับฉีดยาชา','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00262','title' => 'Talbot solution','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00263','title' => 'Taper diamond bur - ยาว','unit' => 'ตัว','qty' => '5.00','unit_price' => '260.00000'],
                        ['code' => '19-00264','title' => 'Taper diamond bur - สั้น','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00266','title' => 'T-band','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00267','title' => 'Temporary cement (Temp-Bond)','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00268','title' => 'Temporary filling material (Cavit)','unit' => 'ขวด','qty' => '1.00','unit_price' => '350.00000'],
                        ['code' => '19-00271','title' => 'Tofflemire matrix band','unit' => 'ชิ้น','qty' => '5.00','unit_price' => '280.00000'],
                        ['code' => '19-00272','title' => 'Tofflemire matrix retainer','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00273','title' => 'Tray adhesive','unit' => 'ขวด','qty' => '3.00','unit_price' => '480.00000'],
                        ['code' => '19-00274','title' => 'Vitapex','unit' => 'กล่อง','qty' => '4.00','unit_price' => '2292.00000'],
                        ['code' => '19-00276','title' => 'Wedge L','unit' => 'ห่อ','qty' => '4.00','unit_price' => '120.00000'],
                        ['code' => '19-00277','title' => 'Wedge M','unit' => 'ห่อ','qty' => '3.00','unit_price' => '120.00000'],
                        ['code' => '19-00278','title' => 'Wedge S','unit' => 'ห่อ','qty' => '2.00','unit_price' => '50.00000'],
                        ['code' => '19-00279','title' => 'White stone - flame','unit' => 'ตัว','qty' => '48.00','unit_price' => '62.50000'],
                        ['code' => '19-00280','title' => 'White stone - round','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00281','title' => 'Zinc phosphate cement','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00282','title' => 'Zinc oxide','unit' => 'กระปุก','qty' => '1.00','unit_price' => '200.00000'],
                        ['code' => '19-00283','title' => 'กรรไกรตัดไหม','unit' => 'เล่ม','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00284','title' => 'หัวขูดหินปูนไฟฟ้า P10','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00285','title' => 'กระจกถ่ายรูปในช่องปาก','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00286','title' => 'เข็มยาว 30 mm gauge 27','unit' => 'กล่อง','qty' => '8.00','unit_price' => '175.00000'],
                        ['code' => '19-00287','title' => 'เข็มเย็บ 3/8','unit' => 'ถุง','qty' => '3.00','unit_price' => '280.00000'],
                        ['code' => '19-00288','title' => 'เข็มสั้น 21 mm gauge 27','unit' => 'กล่อง','qty' => '10.00','unit_price' => '165.00000'],
                        ['code' => '19-00291','title' => 'ถาดโลหะ มีฝาปิด 12x8x2','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00292','title' => 'ถุงมือ M','unit' => 'กล่อง','qty' => '6.00','unit_price' => '185.00000'],
                        ['code' => '19-00293','title' => 'ถุงมือ XS','unit' => 'กล่อง','qty' => '1.00','unit_price' => '62.73000'],
                        ['code' => '19-00294','title' => 'น้ำยาฆ่าเชื้อเช็ดพื้นผิว','unit' => 'แกลลอน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00296','title' => 'น้ำยาฆ่าเชื้อวัสดุพิมพ์ปาก - ขวด','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00297','title' => 'น้ำยาล้างฟิล์ม - ชุด','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00300','title' => 'น้ำยาล้าง suction','unit' => 'ขวด','qty' => '2.00','unit_price' => '3100.00000'],
                        ['code' => '19-00301','title' => 'น้ำยาห้ามเลือด','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00302','title' => 'แปรงสีฟันเด็ก 0-3 ปี','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00303','title' => 'แปรงสีฟันเด็ก 3-6 ปี','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00304','title' => 'ผงแช่ทำความสะอาด tray พิมพ์ปาก','unit' => 'ขวด','qty' => '5.00','unit_price' => '1450.00000'],
                        ['code' => '19-00305','title' => 'ผ้าเช็ดทำความสะอาดพื้นผิว - สำเร็จรูป','unit' => 'กระปุก','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00308','title' => 'ยาชา 2% mepivacaine with epinephrine 1:100,000','unit' => 'กล่อง','qty' => '9.00','unit_price' => '950.00000'],
                        ['code' => '19-00309','title' => 'ยาชา 4% articaine with epinephrine 1:100,000','unit' => 'กล่อง','qty' => '4.00','unit_price' => '1605.25250'],
                        ['code' => '19-00310','title' => 'ยาชาเจล','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00313','title' => 'หลอดไฟสำหรับเครื่องฉายแสง','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00314','title' => 'หัวขัดฟันปลอมโลหะ','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00321','title' => 'Amalgam plugger','unit' => 'ด้าม','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00322','title' => 'Amalgam carver','unit' => 'ด้าม','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00323','title' => 'Ball burnisher','unit' => 'ด้าม','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00324','title' => 'Plastic instrument - amalgam','unit' => 'ด้าม','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00325','title' => 'Pop-on เล็ก - fine','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00327','title' => 'Elevator','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00332','title' => 'Triple syringe tip','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00333','title' => 'Snap A-Ray','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00335','title' => 'Fluocinolone 0.1% orabase 5 g','unit' => 'กระปุก','qty' => '10.00','unit_price' => '80.00000'],
                        ['code' => '19-00336','title' => 'Pop-on kit','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00337','title' => 'Flowable composite (Tetric n-flow)','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00338','title' => 'GI base/filling (Fuji II LC)','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00339','title' => 'MTA','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00341','title' => 'Fluocinolone 0.1% solution 15 ml','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00342','title' => 'Fluocinolone oral based','unit' => 'กระปุก','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00344','title' => 'ถุงมือ L','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00345','title' => 'Fresh Care Oil','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00346','title' => 'ชุดหัวกรอทำเดือยฟัน (D.T. Light Post Drills)','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00347','title' => 'Round diamond bur - long shank','unit' => 'ตัว','qty' => '10.00','unit_price' => '80.00000'],
                        ['code' => '19-00348','title' => 'Acrylic -liquid','unit' => 'กล่อง','qty' => '1.00','unit_price' => '900.00000'],
                        ['code' => '19-00349','title' => '17% EDTA','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00350','title' => 'Matrix band #1','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00351','title' => 'Sickle','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00352','title' => 'Sickle H6-7','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00353','title' => 'Sickle H6-H7','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00354','title' => 'Jacquette 30-33','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00355','title' => 'Jacquette J1-1S','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00356','title' => 'GI Capsule','unit' => 'แพค','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00357','title' => 'Applicator ปืนฉีด GI','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00358','title' => 'Steel cutter bur (หัวมะเฟือง)','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00359','title' => 'กระดาษเช็ดพื้นผิว สำหรับฆ่าเชื้อโรค (wipe)','unit' => 'แพค','qty' => '6.00','unit_price' => '317.46833'],
                        ['code' => '19-00360','title' => 'หัวกรอ NTI Carbide (มะเฟืองคาดเหลือง)','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00361','title' => 'Gates glidden drill #4','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00362','title' => 'Composite A1B (Filtek Z350 XT)','unit' => 'หลอด','qty' => '3.00','unit_price' => '856.00000'],
                        ['code' => '19-00363','title' => 'Luxator (ชุด kit)','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00364','title' => 'Luxator','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00365','title' => 'Luxator periotome','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00366','title' => 'Composite carver (IT Spatula)','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00368','title' => 'Root elevator - right','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00369','title' => 'Root elevator - left','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00371','title' => 'Stone 24 kg','unit' => 'ลัง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00372','title' => 'Root tip elevator','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00373','title' => 'Fissure Carbide bur - impact (box)','unit' => 'กล่อง','qty' => '3.00','unit_price' => '446.00000'],
                        ['code' => '19-00374','title' => 'Composite A3.5','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00375','title' => 'Composite สี light','unit' => 'แพค','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00376','title' => 'Composite สี dark','unit' => 'แพค','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00377','title' => 'Composite สี medium','unit' => 'แพค','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00378','title' => 'Amalgam 1 spill ชุดเล็ก (50 capsules)','unit' => 'แพค','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00379','title' => 'Carbide cutter - แดง','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00380','title' => 'Rubber dam clamp #8','unit' => 'อัน','qty' => '4.00','unit_price' => '380.00000'],
                        ['code' => '19-00384','title' => 'หัวขัด Enhance - point','unit' => 'กล่อง','qty' => '1.00','unit_price' => '1284.00000'],
                        ['code' => '19-00385','title' => 'หัวขัด Enhance - disc','unit' => 'กล่อง','qty' => '1.00','unit_price' => '1605.00000'],
                        ['code' => '19-00386','title' => 'ซองกันน้ำลายฟิล์มเบอร์ 2 (500 ชิ้น/กล่อง)','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00387','title' => 'Amalgam 2 spill ชุดเล็ก (50 capsules)','unit' => 'แพค','qty' => '7.00','unit_price' => '2950.00000'],
                        ['code' => '19-00388','title' => 'XCP set x-ray ฟัน','unit' => 'ชุด','qty' => '1.00','unit_price' => '5500.00000'],
                        ['code' => '19-00389','title' => 'ยาทาลดอาการเสียวฟัน (Gluma)','unit' => 'ขวด','qty' => '1.00','unit_price' => '1500.00000'],
                        ['code' => '19-00390','title' => 'Rubber dam clamp #9','unit' => 'อัน','qty' => '2.00','unit_price' => '380.00000'],
                        ['code' => '19-00391','title' => 'Green stone - flame','unit' => 'อัน','qty' => '15.00','unit_price' => '96.66667'],
                        ['code' => '19-00392','title' => 'CMCP camphophenol','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00393','title' => 'round white stone กรอเร็ว 1 โหล','unit' => 'กล่อง','qty' => '2.00','unit_price' => '700.00000'],
                        ['code' => '19-00394','title' => 'Flame white stone กรอเร็ว 1 โหล','unit' => 'กล่อง','qty' => '1.00','unit_price' => '700.00000'],
                        ['code' => '19-00395','title' => 'Ketac universal','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00397','title' => 'Diamond bur 008','unit' => 'ตัว','qty' => '30.00','unit_price' => '45.00000'],
                        ['code' => '19-00398','title' => 'Diamond bur 010','unit' => 'ตัว','qty' => '10.00','unit_price' => '45.00000'],
                        ['code' => '19-00400','title' => 'Proximal composite carver','unit' => 'ด้าม','qty' => '10.00','unit_price' => '250.00000'],
                        ['code' => '19-00401','title' => 'Prophy paste','unit' => 'กระปุก','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00404','title' => 'Grick No.1','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00405','title' => 'ข้อต่อ Suction','unit' => 'ชุด','qty' => '1.00','unit_price' => '2140.00000'],
                        ['code' => '19-00409','title' => 'Luxator 3mm','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00410','title' => 'Luxator 5mm','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00411','title' => 'Luxator 2mm','unit' => 'อัน','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00412','title' => 'ข้อต่อ air motor','unit' => 'อัน','qty' => '2.00','unit_price' => '10000.00000'],
                        ['code' => '19-00413','title' => 'opal dam','unit' => '','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00414','title' => 'Diamon bur 007','unit' => 'อัน','qty' => '30.00','unit_price' => '65.00000'],
                        ['code' => '19-00416','title' => 'Aquasil ultra smart','unit' => '','qty' => '1.00','unit_price' => '4066.00000'],
                        ['code' => '19-00418','title' => 'Protaper gold 21 mm','unit' => '','qty' => '4.00','unit_price' => '1754.80000'],
                        ['code' => '19-00419','title' => 'Protaper gold25mm','unit' => '','qty' => '3.00','unit_price' => '1826.13333'],
                        ['code' => '19-00423','title' => 'หินลับมีดสีดำ','unit' => '','qty' => '0','unit_price' => '0'],
                        ['code' => '19-00425','title' => 'เฟืองมอเตอร์เกียร์พนักพิงหลังยูนิต','unit' => '','qty' => '1.00','unit_price' => '3745.00000'],
                        ['code' => '19-00426','title' => 'safe tip19','unit' => '','qty' => '2.00','unit_price' => '45.00000'],
                        ['code' => '19-00427','title' => 'safe tip 19L','unit' => '','qty' => '2.00','unit_price' => '45.00000'],
                        ['code' => '19-00428','title' => 'safe tip 117A','unit' => '','qty' => '2.00','unit_price' => '45.00000'],
                        ['code' => '19-00429','title' => 'Eucalyptus oil','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
                    ];
            
                    if (BaseConsole::confirm('Are you sure?')) {
                        $total = 0;
                        foreach ($data as $key => $value) {
                           
                            $asetItem = Categorise::findOne(['name' => 'asset_item', 'code' => $value['code'], 'title' => $value['title']]);
                            $unit = Categorise::findOne(['name' => 'unit', 'title' => $value['unit']]);
                            // ถ้าไม่มีหน่วยให้สร้างใหม่
                            if (!$unit) {
                                $newUnit = new Categorise([
                                    'name' => 'unit',
                                    'title' => $value['unit'],
                                    'active' => 1,
                                ]);
                                $newUnit->save(false);
                            }
                            // ถ้าไม่มีประวัสดุใฟ้สร้างมห่
                            if (!$asetItem) {
                                $newItem = new Categorise([
                                    'name' => 'asset_item',
                                    'group_id' => 4,
                                    'category_id' => $assettype,
                                    'code' => $value['code'],
                                    'title' => $value['title'],
                                    'data_json' => [
                                        'unit' => $value['unit'],
                                        'sub_title' => '',
                                        'price_name' => '',
                                        'category_name' => $categoryName,
                                        'asset_type_name' => '',
                                    ],
                                ]);
                                $newItem->save(false);
                            }
            
                            $qty = (int) explode('.', $value['qty'])[0];
                        
                            $lot = \mdm\autonumber\AutoNumber::generate('LOT'.substr(AppHelper::YearBudget(), 2).'-?????');
                            $ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
                            $model = new StockEvent([
                                'ref' => $ref,
                                'lot_number' => $lot,
                                'name' => 'order_item',
                                'code' => $code,
                                'category_id' => $category_id,
                                'transaction_type' => 'IN',
                                'asset_item' => $value['code'],
                                'warehouse_id' => $warehouse_id,
                                'qty' => $value['qty'],
                                'unit_price' => (float) $value['unit_price'],
                                'order_status' => 'pending',
                                'data_json' => [
                                    'req_qty' => '0',
                                    'exp_date' => '',
                                    'mfg_date' => '',
                                    'item_type' => 'ยอดยกมา',
                                    'po_number' => '',
                                    'pq_number' => '',
                                    'asset_type' => '',
                                    'receive_date' => '',
                                    'asset_type_name' => '',
                                    'employee_fullname' => 'Administrator Lastname',
                                    'employee_position' => 'นักวิชาการคอมพิวเตอร์',
                                    'employee_department' => 'งานซ่อมบำรุง',
                                ],
                                'created_by' => 1,
                                'updated_by' => 1,
                            ]);
                            // echo (DOUBLE) $value['unit_price'],"\n";
                            if ($model->save(false)) {
                                echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."สำเร็จ! \n";
                            } else {
                                echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."ผิดพลาด! \n";
                            }
                            $sum = $qty * (int) $value['unit_price'];
                            $total += $sum;
                        }
                        echo $total;
                    }
                }



}
