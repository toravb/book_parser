<?php


namespace App\Parser\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as RXlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WXlsx;

class ExcelController extends BaseController
{
    public static function writeExcel($site_id){
        $path = storage_path('parser');
        $items = DB::table('parser_items')->where('site_id', $site_id)->where('Articul', '!=', '0')
            ->select('ID', 'Status', 'New', 'IsDeleted', 'Last_modified', 'Name', 'Articul', 'Url', 'Is_available', 'Price',
            'Price_action', 'Quantity', 'Series', 'Components', 'Accessories', 'Params')
            ->get();

        if (!is_dir($path)){
            mkdir($path, 0775, true);
        }
        if (file_exists($path.'/parser.xlsx')){
//            self::setUpdatedFalse();
            $reader = new RXlsx();
            $spreadsheet = $reader->load($path.'/parser.xlsx');
            $sheet = $spreadsheet->getActiveSheet();
        } else{
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'ID'); //num
            $sheet->setCellValue('B1', 'Status'); //bool
            $sheet->setCellValue('C1', 'New'); //bool
            $sheet->setCellValue('D1', 'IsDeleted'); //bool
            $sheet->setCellValue('E1', 'Last modified'); //timestamp
            $sheet->setCellValue('F1', 'Название товара'); //str
            $sheet->setCellValue('G1', 'Артикул донора'); //stl
            $sheet->setCellValue('H1', 'URL'); //str
            $sheet->setCellValue('I1', 'Наличие'); //bool
            $sheet->setCellValue('J1', 'Цена'); //num
            $sheet->setCellValue('K1', 'Акционная цена'); //num
            $sheet->setCellValue('L1', 'Остаток'); //str
            $sheet->setCellValue('M1', 'Товары серии'); //str
            $sheet->setCellValue('N1', 'Комплектующие'); //json
            $sheet->setCellValue('O1', 'Аксессуары'); //str
            $sheet->setCellValue('P1', 'Характеристики');//str
        }


        foreach ($items as $data) {
            $row = $spreadsheet->setActiveSheetIndex(0)->getHighestRow()+1;
            $column = 1;
            foreach ($data as $key => $value) {
                for ($i = 1; $i < $row; $i++) {
                    $cell = $sheet->getCell('A' . $i);
                    if ($cell->getValue() == $data->ID) {
                        $row = $cell->getRow();
                        break;
                    }
                }
//                $cell = $sheet->getCellByColumnAndRow(4, $row);
//                $cell->setValue(0);
//
//                $cell = $sheet->getCellByColumnAndRow(1, $row);
//                $cell->setValue($data->ID);
//                $cell = $sheet->getCellByColumnAndRow(2, $row);
//                $cell->setValue(1);
//
//                if ($key == 'New') {
//                    $cell = $sheet->getCellByColumnAndRow(3, $row);
//                    $cell->setValue($value);
////                break;
//                }
//
//                $cell = $sheet->getCellByColumnAndRow(5, $row);
//                $cell->setValue(now());

                if ($key == 'Params') {
                    $value = json_decode($value, true);
                    $text = '';
                    foreach ($value as $key => $element) {

                        $text .= "$key|||$element$$$";
                    }
                    $cell = $sheet->getCellByColumnAndRow($column, $row);
                    if ($cell->getValue() != $text || $cell->getValue() === null) {
                        $cell->setValue($text);
                        $spreadsheet->getActiveSheet()->getStyle($cell->getCoordinate())
                            ->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFFFFF00');
                    }else{
                        $spreadsheet->getActiveSheet()->getStyle($cell->getCoordinate())
                            ->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE);
                    }
                    $column++;
                } else {
                    $cell = $sheet->getCellByColumnAndRow($column, $row);
                    if ($cell->getValue() != $value || $cell->getValue() === null) {
                        $cell->setValue($value);

                        $spreadsheet->getActiveSheet()->getStyle($cell->getCoordinate())
                            ->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFFFFF00');
                    }else{
                        $spreadsheet->getActiveSheet()->getStyle($cell->getCoordinate())
                            ->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE);
                    }
                    $column++;
                }
            }
        }
//
        DB::table('sites')->where('id', $site_id)->update(['downloadedExcel' => 0]);
        $writer = new WXlsx($spreadsheet);
        $writer->save($path.'/parser.xlsx');
    }

    public static function setUpdatedFalse(){
//        DB::table('sites')->where('id', $site_id)->update(['downloadedExcel' => 0]);
        $path = storage_path('parser');
//        DB::table('parser_items')->update([/*'Updated' => 0,*/ 'New' => 0]);
        if (file_exists($path.'/parser.xlsx')) {
            $reader = new RXlsx();
            $spreadsheet = $reader->load($path . '/parser.xlsx');
            $row = $spreadsheet->setActiveSheetIndex(0)->getHighestRow();
            $sheet = $spreadsheet->getActiveSheet();
            $column = $spreadsheet->setActiveSheetIndex(0)->getHighestColumn();

            for ($i = $row; $i > 1; $i--){
                $cell = $sheet->getCellByColumnAndRow(3, $i);
                $cell->setValue(0);
            }
            $spreadsheet->getActiveSheet()->getStyle("A:$column")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE);

            $writer = new WXlsx($spreadsheet);
            $writer->save($path.'/parser.xlsx');
        }
    }
}
