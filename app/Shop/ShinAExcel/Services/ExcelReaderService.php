<?php

namespace App\Shop\ShinA\Services;

//composer require phpoffice/phpspreadsheet

use App\Services\Barcode\BarcodeGeneratorHTML;
use App\Shop\Products\Product;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelReaderService
{
    public static $xlsFile;
    public static $typeFile;
    public static $fileName;
    // private static $folder_upload = 'excel_upload';
    public static $folder_process = 'excel_upload';
    public static $folder_backup = 'backup/excel/';

    private function __construct($xlsFile)
    {
        self::$xlsFile =  $xlsFile;
        $file_parts = pathinfo(self::$xlsFile);
        self::$typeFile = $file_parts['extension'];
        self::$fileName = $file_parts['filename'];
        if (strtoupper(self::$typeFile) == 'XLS')
            self::$typeFile = 'Xls';
        else if (strtoupper(self::$typeFile) == 'XLSX')
            self::$typeFile = 'Xlsx';
    }

    public static function run($xlsFile)
    {
        $service = new static($xlsFile);
        $template = config('excel_upload_file.convert_template');
        // $data = $service->convertResultArray($template);
        $date = now();
        $filename = $date->format('Ymd');
        $data = self::$folder_process . "\\0_" . $filename . ".json";

        $data = self::convertJsonToArray($data);

        $max_area = (self::getLimitAreaOnArrayJson(array_keys($data), [-2, -1, 3, 1]));
        $limit_area = (self::getLimitAreaOnArrayJson(array_values($template), [-2, -1, 3, 1]));
        $run = self::getAreaLimit($limit_area, $max_area, $template);

        $result_r = self::mergeTempAndData($data, $run);
        $time = Carbon::now();
        $backup = (self::$folder_backup . self::$fileName . '/' . $time->format('Ymd_his_si'));
        // Storage::disk('local')->move(self::$folder_process, $backup);
        return $result_r;
    }

    private function convertResultArray($template)
    {
        $temp = array();
        foreach ($template as $key => $value) {
            $temp[$value] = $key;
        }
        $date = now();
        $filename = $date->format('Ymd');
        $pathjson = self::$folder_process . "\\" . $filename . ".json";

        if (!Storage::disk('local')->exists($pathjson)) {

            $reader = IOFactory::createReader(self::$typeFile);
            $reloadedSpreadsheet = $reader->load(self::$xlsFile);
            $sheetCount = $reloadedSpreadsheet->getSheetCount();
            $result_m = [];

            for ($i = 0; $i < $sheetCount; $i++) {
                $pathjson = "\\" . self::$folder_process . "\\" . $i . '_' . $filename . ".json";
                $sheet = $reloadedSpreadsheet->getSheet($i);
                //[B2:AR21]
                $rows = $this->dataExcelToArrayByColumn($sheet, array());
                // GET IMAGE
                $rows = $this->imageExcelToArray($sheet, $rows, false, null, storage_path('app/excel_upload/image'), 'Image_');
                if (!empty($rows))
                    $result[] = $rows;

                Storage::disk('local')->put($pathjson . '_2', print_r($result, true));
                Storage::disk('local')->put($pathjson, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $result_m[] = $pathjson;
            }
            return $result_m;
        } else {
            return $pathjson;
        }
    }

    private static function convertJsonToArray($link)
    {
        $result = array();
        if (is_array($link)) {
            foreach ($link as $l) {
                $content = Storage::disk('local')->get($l);
                $result = $result_m  = array_merge($result, json_decode($content, true));
            }
        } else {
            $content = Storage::disk('local')->get($link);
            $result = json_decode($content, true);
        }
        $convert = array();

        foreach ($result as $rows) {
            foreach ($rows as $row => $column) {
                foreach ($column as $key => $value) {
                    $convert[$key] = $value;
                }
            }
        }
        return $convert;
    }
    /**
     * Create List Sheet Using
     * Exp: limit(A1:AR21)
     * @param string $limit_area Exp:A1:AR21
     * @param string $max_area Exp:A1:AR21
     * @param Array $template
     * @return Array Arreay List start $limit_area To $max_area
     */
    private static function getAreaLimit($limit_area, $max_area, $template)
    {
        $max = self::getRangeBoundaries($max_area, true);
        $area = self::getRangeBoundaries($limit_area, true);
        $run = array();
        $run_temp = array();
        $limit_column = ($max[1][2]);
        $limit_row = $max[1][1];

        $plus_column = $area[1][2] - $area[0][2];
        $plus_row = $area[1][1] - $area[0][1];

        $rows = range($area[1][1], $limit_row, $plus_row + 1);
        $columns = range($area[1][2], $limit_column, $plus_column);

        foreach ($rows as $i) {
            foreach ($columns as $j) {
                $start_column = ($j - $plus_column) - 1;
                $start_row = ($i - $plus_row) - 1;
                array_walk_recursive($template, function ($v, $k) use ($start_column, $start_row, $i, $j, &$run_temp) {
                    $v = self::getRangeBoundaries($v, true);
                    $run_temp[$i][$j][Coordinate::stringFromColumnIndex(($v[0][2] + $start_column)) . ($v[0][1] + $start_row)] = $k;
                });
                /** start_column, start_row, end_column, end_row **/
                $run[] = [
                    $start_column,
                    $start_row,
                    $j,
                    $i,
                    Coordinate::stringFromColumnIndex($j - $plus_column) . ($i - $plus_row) . ":" . Coordinate::stringFromColumnIndex($j) . $i
                ];
            }
        }
        return [$run, $run_temp];
    }

    /**
     * Embead Column data to Root Array
     * 
     */
    private function convertColumn($result, $column, $row, $key_check = array())
    {
        foreach ($column as $key => $val) {
            if ($key_check[0] == $key) {
                $result[$key_check[1]] = [$row, $key];
                return $result;
            }
        }
    }
    /**
     * Excel File to Array By Column
     * @param Worksheet $obj
     * @param Array $start [Row Index, Column Index]
     * @return Array
     */
    private function dataExcelToArrayByColumn($obj, $rows, $pRange = null)
    {
        $start = ["1", "A"];
        $end = ["1", "A"];
        if (!empty($pRange)) {
            $arrayIndex = $this->getRangeBoundaries($pRange);
            $start = [$arrayIndex[0][1], $arrayIndex[0][0]];
            $end =  [$arrayIndex[1][1], $arrayIndex[1][0]];
        } else {
            $highestRowAndColumn = $obj->getHighestRowAndColumn();
            $end = [$highestRowAndColumn['row'], $highestRowAndColumn['column']];
        }
        foreach ($obj->getRowIterator($start[0], $end[0]) as $row) {
            $cellIterator = $row->getCellIterator($start[1], $end[1]);
            $cellIterator->setIterateOnlyExistingCells(FALSE); // This loops through all cells,
            $cells = [];
            $index = $row->getRowIndex();
            foreach ($cellIterator as $cell) {
                $cellValue = $cell->getValue();
                if (!empty($cellValue)) {
                    $column = $cell->getColumn();
                    $_row = $cell->getRow();
                    if ($cellValue instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                        $cells[$column . $_row] = $cellValue->__toString();
                    } else {
                        $cells[$column . $_row] = $cellValue;
                    }
                }
            }
            if (!empty($cells))
                $rows[$index] = $cells;
        }

        return $rows;
    }
    /**
     * Excel File to Array By Row
     * @param Worksheet $obj
     * @param String $pRange Cell range (e.g. A1:A1)
     * @return Array
     */
    private function dataExcelToArrayByRow($obj, $rows, $pRange = null)
    {
        $start = ["1", "A"];
        $end = ["1", "A"];
        if (!empty($pRange)) {
            $arrayIndex = $this->getRangeBoundaries($pRange);
            $start = [$arrayIndex[0][1], $arrayIndex[0][0]];
            $end =  [$arrayIndex[1][1], $arrayIndex[1][0]];
        } else {
            $highestRowAndColumn = $obj->getHighestRowAndColumn();
            $end = [$highestRowAndColumn['row'], $highestRowAndColumn['column']];
        }
        foreach ($obj->getColumnIterator($start[1], $end[1]) as $row) {
            $cellIterator = $row->getCellIterator($start[0], $end[0]);
            $cellIterator->setIterateOnlyExistingCells(FALSE); // This loops through all cells,
            $cells = [];
            $column = $row->getColumnIndex(); //String name to index
            foreach ($cellIterator as $cell) {
                $value = $cell->getValue();
                if (!empty($value))
                    $cells[$cell->getRow()] = $cell->getValue();
            }
            if (!empty($cells))
                $rows[$column] = $cells;
        }

        return $rows;
    }

    private function imageExcelToArray($obj, $rows, $arrayKeyRow = true, $pRange = null, $path = '\\image\\', $filename = 'Image_')
    {
        $start_column = 0;
        $start_row = 0;
        $end_column = 0;
        $end_row = 0;
        if (!empty($pRange)) {
            $arrayIndex = $this->getRangeBoundaries($pRange, true);
            $start_column = $arrayIndex[0][0];
            $start_row = $arrayIndex[0][1];
            $end_column = $arrayIndex[1][0];
            $end_row = $arrayIndex[1][1];
        } else {
            $highestRowAndColumn = $obj->getHighestRowAndColumn();
            $end_column = $highestRowAndColumn['column'];
            $end_column = Coordinate::columnIndexFromString($end_column);
            $end_row = $highestRowAndColumn['row'];
        }
        $i = 0;
        foreach ($obj->getDrawingCollection() as $drawing) {

            $c = $drawing->getCoordinates();
            preg_match_all('!\d+!', $c, $matches);
            $row = $matches[0][0];
            $column = strstr($c, $row, true);
            $column_id = -1;
            if (!empty($column)) {
                $column_id = Coordinate::columnIndexFromString($column);
            }
            if ($start_row  <= $row && $row <= $end_row && $start_column <= $column_id && $column_id <= $end_column) {
                if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing) {
                    ob_start();
                    call_user_func(
                        $drawing->getRenderingFunction(),
                        $drawing->getImageResource()
                    );
                    $imageContents = ob_get_contents();
                    ob_end_clean();
                    switch ($drawing->getMimeType()) {
                        case \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_PNG:
                            $extension = 'png';
                            break;
                        case \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_GIF:
                            $extension = 'gif';
                            break;
                        case \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_JPEG:
                            $extension = 'jpg';
                            break;
                    }
                } else {
                    $zipReader = fopen($drawing->getPath(), 'r');
                    $imageContents = '';
                    while (!feof($zipReader)) {
                        $imageContents .= fread($zipReader, 2048);
                    }
                    fclose($zipReader);
                    $extension = $drawing->getExtension();
                }
                $myFileName = $path . $filename . ++$i . '.' . $extension;
                if ($arrayKeyRow) {
                    $rows[$column][$column . $row] = $myFileName;
                } else {
                    $rows[$row][$column . $row] = $myFileName;
                }
                file_put_contents($myFileName, $imageContents);
            }
        }
        return $rows;
    }

    private static function changeArray($array, $key)
    {
        if (array_key_exists($key, $array)) {
            return $array[$key][0];
        }
        return null;
    }
    /**
     * Calculate range boundaries.
     * 
     * @param string $pRange Cell range (e.g. A1:A1)
     * @param boolan $convert if true return arrays [Column Number, Row Number] or arrays [Column ID, Row Number]
     * @return array Range coordinates [Start Cell, End Cell] where Start Cell and End Cell are arrays [Column Number, Row Number]
     */
    private static function getRangeBoundaries(string $pRange, bool $convert = false)
    {
        try {
            $arrayIndex = Coordinate::getRangeBoundaries($pRange);
            if ($convert) {
                $arrayIndex[0][2] = Coordinate::columnIndexFromString($arrayIndex[0][0]);
                $arrayIndex[0][1] = (int) $arrayIndex[0][1];
                $arrayIndex[1][2] = Coordinate::columnIndexFromString($arrayIndex[1][0]);
                $arrayIndex[1][1] = (int) $arrayIndex[1][1];
            }
            return $arrayIndex;
        } catch (Exception $ex) {
            var_dump($pRange);
            var_dump($ex->getMessage());
        }
    }
    /**
     * get min area and max area on ArrayJson
     * @param Array $data
     * @param Array $space plus area [$min_column, $min_row, $max_column, $max_row]
     */
    private static function getLimitAreaOnArrayJson(array $data, $space = [0, 0, 0, 0])
    {
        if (!is_array($space)) {
            return null;
        }
        if (count($space) < 4) {
            for ($i = count($space); $i < 4; $i++) {
                array_push($space, 0);
            }
        }
        $min_r = $min_c = $max_r = $max_c = $count = 0;
        foreach ($data as $key) {
            // $check = ExcelReader::getRangeBoundaries($key, true)[0][1] . ExcelReader::getRangeBoundaries($key, true)[0][2];
            if ($count == 0) {
                $min_r = self::getRangeBoundaries($key, true)[0][1];
                $min_c = self::getRangeBoundaries($key, true)[0][2];
            } else {
                if ($min_r >= self::getRangeBoundaries($key, true)[0][1]) {
                    $min_r = self::getRangeBoundaries($key, true)[0][1];
                }
                if ($min_c >= self::getRangeBoundaries($key, true)[0][2]) {
                    $min_c =  self::getRangeBoundaries($key, true)[0][2];
                }
                if ($max_r <= self::getRangeBoundaries($key, true)[0][1]) {
                    $max_r =  self::getRangeBoundaries($key, true)[0][1];
                }
                if ($max_c <= self::getRangeBoundaries($key, true)[0][2]) {
                    $max_c =  self::getRangeBoundaries($key, true)[0][2];
                }
            }
            $count++;
        }
        return
            Coordinate::stringFromColumnIndex($min_c + $space[0]) . ($min_r + $space[1])
            . ":" .
            Coordinate::stringFromColumnIndex($max_c + $space[2]) . ($max_r + $space[3]);
    }
    /**
     * Merge Template Data
     * @param Array $data Data Array
     * @param Array $template (config)
     * @return Array
     */
    private static function mergeTempAndData($data, $template)
    {
        $result_r = [];
        $rows = $template[0][0];
        $min = Coordinate::stringFromColumnIndex(((int) $rows[0] + 1)) . ($rows[1] + 1);
        foreach ($template[0] as $k => $v) {
            foreach (Coordinate::extractAllCellReferencesInRange($v[4]) as $key_i) {
                if (array_key_exists($key_i, $data)) {
                    $value = $data[$key_i];
                    if (!is_null($value) && strpos($value, 'imageImage') !== false && strpos($value, '.jpeg') !== false) {
                        $result_r[$v[3] . $v[2]]['image'] = [$data[$key_i], 'image', $v[3] . $v[2]];
                    }
                }
            }
        }
        // $min = $template[]
        foreach ($template[1] as $key_p => $temp) {
            foreach ($temp as $row => $column) {
                foreach ($column as $key => $value) {
                    if (array_key_exists($key, $data)) {
                        $rows[$row][$value] = $data[$key];
                        $data[$key] = [$data[$key], $value, $key_p . $row];
                        $result_r[($key_p . $row)][$value] = $data[$key];
                    }
                }
            }
        }
        $pathjson = self::$folder_process . "\\" . self::$fileName . ".json";
        Storage::disk('local')->put($pathjson, json_encode($result_r, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $result_r;
    }
    public static function convertJsonToKeyTemplate()
    {
        $path = storage_path('app/excel_upload/test_list_1.json');
        $content = file_get_contents($path);
        $rows = json_decode($content, true);

        $key_constant = array();
        foreach ($rows as $row => $column) {
            switch ($row) {
                case 2:
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["R", "meika_name"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["X", "meika_value"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AF", "category_name"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AK", "category_value"]);
                    break;
                case 3:
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["R", "product_name"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["X", "product_value"]);
                    break;
                case 4:
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["C", "image"]);
                    break;
                case 5:
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["R", "standard"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["X", "weight"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AF", "quantity"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AI", "quantity_1"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AM", "quantity_x"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AO", "quantity_2"]);
                    break;
                case 6:
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["R", "price_label"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["X", "price_value"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AF", "limited_label"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AK", "limited_value"]);
                    break;
                case 7:
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["R", "code_jan_label"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["X", "code_jan_value"]);
                    break;
                case 8:
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["R", "code_tif_label"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["X", "code_tif_value"]);
                    break;
                case 9:
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["R", "cs_label"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["X", "cs_w_N"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["Z", "cs_w_V"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AD", "cs_h_N"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AG", "cs_h_V"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AK", "cs_d_N"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AN", "cs_d_V"]);
                    break;
                case 10:
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["R", "bl_label"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["X", "bl_w_N"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["Z", "bl_w_V"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AD", "bl_h_N"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AG", "bl_h_V"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AK", "bl_d_N"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AN", "bl_d_V"]);
                    break;
                case 11:
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["R", "ps_label"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["X", "ps_w_N"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["Z", "ps_w_V"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AD", "ps_h_N"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AG", "ps_h_V"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AK", "ps_d_N"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AN", "ps_d_V"]);
                    break;
                case 12:
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["X", "barcode"]);
                    break;
                case 15:
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["C", "feature_label"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["I", "feature_1"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["L", "feature_2"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["R", "feature_3"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AE", "feature_4"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AJ", "feature_5"]);
                    break;
                case 16:
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["C", "feature_6"]);
                    break;
                case 19:
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["C", "release_area_label"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["L", "release_area_1"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["P", "release_area_2"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["T", "release_area_3"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["X", "release_area_4"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AB", "release_area_5"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AF", "release_area_6"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AJ", "release_area_7"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AN", "release_area_8"]);
                    break;
                case 20:
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["C", "release_date_label"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["L", "release_date_1"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["P", "release_date_2"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["T", "release_date_3"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["X", "release_date_4"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AB", "release_date_5"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AF", "release_date_6"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AJ", "release_date_7"]);
                    $key_constant = self::convertColumn($key_constant, $column, $row, ["AN", "release_date_8"]);
                    break;
            }
        }
        return (json_encode($key_constant, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    public static function toProductModel($val)
    {
        try {
            $product = new Product();
            $product->image = self::changeArray($val, 'image');
            $product->meika_name = self::changeArray($val, 'meika_name');
            $product->meika_value = self::changeArray($val, 'meika_value');
            $product->category_name = self::changeArray($val, 'category_name');
            $product->category_value = self::changeArray($val, 'category_value');
            $product->product_name = self::changeArray($val, 'product_name');
            $product->product_value = self::changeArray($val, 'product_value');
            $product->standard = self::changeArray($val, 'standard');
            $product->weight = self::changeArray($val, 'weight');
            $product->quantity = self::changeArray($val, 'quantity');
            $product->quantity_1 = self::changeArray($val, 'quantity_1');
            $product->quantity_x = self::changeArray($val, 'quantity_x');
            $product->quantity_2 = self::changeArray($val, 'quantity_2');
            $product->price_label = self::changeArray($val, 'price_label');
            $product->price_value = self::changeArray($val, 'price_value');
            $product->limited_label = self::changeArray($val, 'limited_label');
            $product->limited_value = self::changeArray($val, 'limited_value');
            $product->code_jan_label = self::changeArray($val, 'code_jan_label');
            $product->code_jan_value = self::changeArray($val, 'code_jan_value');
            $product->code_tif_label = self::changeArray($val, 'code_tif_label');
            $product->code_tif_value = self::changeArray($val, 'code_tif_value');
            $product->cs_label = self::changeArray($val, 'cs_label');
            $product->cs_w_N = self::changeArray($val, 'cs_w_N');
            $product->cs_w_V = self::changeArray($val, 'cs_w_V');
            $product->cs_h_N = self::changeArray($val, 'cs_h_N');
            $product->cs_h_V = self::changeArray($val, 'cs_h_V');
            $product->cs_d_N = self::changeArray($val, 'cs_d_N');
            $product->cs_d_V = self::changeArray($val, 'cs_d_V');
            $product->bl_label = self::changeArray($val, 'bl_label');
            $product->bl_w_N = self::changeArray($val, 'bl_w_N');
            $product->bl_w_V = self::changeArray($val, 'bl_w_V');
            $product->bl_h_N = self::changeArray($val, 'bl_h_N');
            $product->bl_h_V = self::changeArray($val, 'bl_h_V');
            $product->bl_d_N = self::changeArray($val, 'bl_d_N');
            $product->bl_d_V = self::changeArray($val, 'bl_d_V');
            $product->ps_label = self::changeArray($val, 'ps_label');
            $product->ps_w_N = self::changeArray($val, 'ps_w_N');
            $product->ps_w_V = self::changeArray($val, 'ps_w_V');
            $product->ps_h_N = self::changeArray($val, 'ps_h_N');
            $product->ps_h_V = self::changeArray($val, 'ps_h_V');
            $product->ps_d_N = self::changeArray($val, 'ps_d_N');
            $product->ps_d_V = self::changeArray($val, 'ps_d_V');
            $product->code_jan_value = self::changeArray($val, 'code_jan_value');
            $product->feature_label = self::changeArray($val, 'feature_label');
            $product->feature_1 = self::changeArray($val, 'feature_1');
            $product->feature_2 = self::changeArray($val, 'feature_2');
            $product->feature_3 = self::changeArray($val, 'feature_3');
            $product->feature_4 = self::changeArray($val, 'feature_4');
            $product->feature_5 = self::changeArray($val, 'feature_5');
            $product->feature_6 = self::changeArray($val, 'feature_6');
            $product->release_area_label = self::changeArray($val, 'release_area_label');
            $product->release_area_1 = self::changeArray($val, 'release_area_1');
            $product->release_area_2 = self::changeArray($val, 'release_area_2');
            $product->release_area_3 = self::changeArray($val, 'release_area_3');
            $product->release_area_4 = self::changeArray($val, 'release_area_4');
            $product->release_area_5 = self::changeArray($val, 'release_area_5');
            $product->release_area_6 = self::changeArray($val, 'release_area_6');
            $product->release_area_7 = self::changeArray($val, 'release_area_7');
            $product->release_area_8 = self::changeArray($val, 'release_area_8');
            $product->release_date_label = self::changeArray($val, 'release_date_label');
            $product->release_date_1 = self::changeArray($val, 'release_date_1');
            $product->release_date_2 = self::changeArray($val, 'release_date_2');
            $product->release_date_3 = self::changeArray($val, 'release_date_3');
            $product->release_date_4 = self::changeArray($val, 'release_date_4');
            $product->release_date_5 = self::changeArray($val, 'release_date_5');
            $product->release_date_6 = self::changeArray($val, 'release_date_6');
            $product->release_date_7 = self::changeArray($val, 'release_date_7');
            $product->release_date_8 = self::changeArray($val, 'release_date_8');
            return $product;
        } catch (\Exception $ex) {
            var_dump($ex->getMessage());
            //var_dump($val);
            throw $ex;
            return null;
        }
    }
    public static function toObject($val)
    {
        try {
            $product = array();
            foreach(array_keys(config('excel_upload_file.convert_template')) as $key){
                $product[$key] = self::changeArray($val, $key);
            }
            return $product;
        } catch (\Exception $ex) {
            var_dump($ex->getMessage());
            //var_dump($val);
            throw $ex;
            return null;
        }
    }
    public static function htmlExample($val)
    {
        try {
            $generator = new BarcodeGeneratorHTML();
            $html = '<table style="width: 31px;">';
            $html .= '<tbody>';
            $html .= '<tr>';
            $html .= '<td style="width: 15px;">
                        ' . self::changeArray($val, 'image') . '
                       <img src="' . Storage::url(self::changeArray($val, 'image')) . '"/>
                    </td>';
            $html .= '<td style="width: 15px;">';
            $html .= '<table>';
            $html .= '<tbody>';
            $html .= '<tr>';
            $html .= '<td>' . self::changeArray($val, 'meika_name') . '</td>';
            $html .= '<td>' . self::changeArray($val, 'meika_value') . '</td>';
            $html .= '<td>' . self::changeArray($val, 'category_name') . '</td>';
            $html .= '<td>' . self::changeArray($val, 'category_value') . '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td>' . self::changeArray($val, 'product_name') . '</td>';
            $html .= '<td colspan="3">' . self::changeArray($val, 'product_value') . '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td>' . self::changeArray($val, 'standard') . '</td>';
            $html .= '<td>' . self::changeArray($val, 'weight') . '</td>';
            $html .= '<td>' . self::changeArray($val, 'quantity') . '</td>';
            $html .= '<td>' . self::changeArray($val, 'quantity_1') .
                self::changeArray($val, 'quantity_x') .
                self::changeArray($val, 'quantity_2') . '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td>' . self::changeArray($val, 'price_label') . '</td>';
            $html .= '<td>' . self::changeArray($val, 'price_value') . '</td>';
            $html .= '<td>' . self::changeArray($val, 'limited_label') . '</td>';
            $html .= '<td>' . self::changeArray($val, 'limited_value') . '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td>' . self::changeArray($val, 'code_jan_label') . '</td>';
            $html .= '<td colspan="3">' . self::changeArray($val, 'code_jan_value') . '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td>' . self::changeArray($val, 'code_tif_label') . '</td>';
            $html .= '<td colspan="3">' . self::changeArray($val, 'code_tif_value') . '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td>' . self::changeArray($val, 'cs_label') . '</td>';
            $html .= '<td>' . self::changeArray($val, 'cs_w_N') .
                self::changeArray($val, 'cs_w_V') . '</td>';
            $html .= '<td>' . self::changeArray($val, 'cs_h_N') .
                self::changeArray($val, 'cs_h_V') . '</td>';
            $html .= '<td>' . self::changeArray($val, 'cs_d_N') .
                self::changeArray($val, 'cs_d_V') . '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td>' . self::changeArray($val, 'bl_label') . '</td>';
            $html .= '<td>' . self::changeArray($val, 'bl_w_N') .
                self::changeArray($val, 'bl_w_V') . '</td>';
            $html .= '<td>' . self::changeArray($val, 'bl_h_N') .
                self::changeArray($val, 'bl_h_V') . '</td>';
            $html .= '<td>' . self::changeArray($val, 'bl_d_N') .
                self::changeArray($val, 'bl_d_V') . '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td>' . self::changeArray($val, 'ps_label') . '</td>';
            $html .= '<td>' . self::changeArray($val, 'ps_w_N') .
                self::changeArray($val, 'ps_w_V') . '</td>';
            $html .= '<td>' . self::changeArray($val, 'ps_h_N') .
                self::changeArray($val, 'ps_h_V') . '</td>';
            $html .= '<td>' . self::changeArray($val, 'ps_d_N') .
                self::changeArray($val, 'ps_d_V') . '</td>';
            $html .= '</tr>';
            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            // $html .= '<td style="width: 15px;">
            // ' . self::changeArray($val, 'barcode') . '
            //            <img src="' . Storage::url(self::changeArray($val, 'barcode')) . '"/>
            //         </td>';


            $html .= '<td style="width: 15px;">' . $generator->getBarcode(self::changeArray($val, 'code_jan_value'), $generator::TYPE_EAN_13) . '</td>';
            $html .= '<td style="width: 15px;">';
            $html .= '<table style="width: 195px;">';
            $html .= '<tbody>';
            $html .= '<tr>';
            $html .= '<td style="width: 25px;">' . self::changeArray($val, 'feature_label') . '</td>';
            $html .= '<td style="width: 33px;">' . self::changeArray($val, 'feature_1') . '</td>';
            $html .= '<td style="width: 25px;">' . self::changeArray($val, 'feature_2') . '</td>';
            $html .= '<td style="width: 25px;">' . self::changeArray($val, 'feature_3') . '</td>';
            $html .= '<td style="width: 26px;">' . self::changeArray($val, 'feature_4') . '</td>';
            $html .= '<td style="width: 15px;" colspan="3">' . self::changeArray($val, 'feature_5') . '</td>';
            $html .= '<td style="width: 15px;">　&times;</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            //$html .= '<td style="width: 25px;" colspan="9">' . self::changeArray($val, 'feature_6') . '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            // $html .= '<td style="width: 25px;">' . self::changeArray($val, 'release_area_label') . '</td>';
            $html .= '<td style="width: 33px;">' . self::changeArray($val, 'release_area_1') . '</td>';
            $html .= '<td style="width: 25px;">' . self::changeArray($val, 'release_area_2') . '</td>';
            $html .= '<td style="width: 25px;">' . self::changeArray($val, 'release_area_3') . '</td>';
            $html .= '<td style="width: 26px;">' . self::changeArray($val, 'release_area_4') . '</td>';
            $html .= '<td style="width: 15px;">' . self::changeArray($val, 'release_area_5') . '</td>';
            $html .= '<td style="width: 15px;">' . self::changeArray($val, 'release_area_6') . '</td>';
            $html .= '<td style="width: 15px;">' . self::changeArray($val, 'release_area_7') . '</td>';
            $html .= '<td style="width: 15px;">' . self::changeArray($val, 'release_area_8') . '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td style="width: 25px;">' . self::changeArray($val, 'release_date_label') . '</td>';
            $html .= '<td style="width: 33px;">' . self::changeArray($val, 'release_date_1') . '</td>';
            $html .= '<td style="width: 25px;">' . self::changeArray($val, 'release_date_2') . '</td>';
            $html .= '<td style="width: 25px;">' . self::changeArray($val, 'release_date_3') . '</td>';
            $html .= '<td style="width: 26px;">' . self::changeArray($val, 'release_date_4') . '</td>';
            $html .= '<td style="width: 15px;">' . self::changeArray($val, 'release_date_5') . '</td>';
            $html .= '<td style="width: 15px;">' . self::changeArray($val, 'release_date_6') . '</td>';
            $html .= '<td style="width: 15px;">' . self::changeArray($val, 'release_date_7') . '</td>';
            $html .= '<td style="width: 15px;">' . self::changeArray($val, 'release_date_8') . '</td>';
            $html .= '</tr>';
            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '</tbody>';
            $html .= '</table>';
            return $html;
        } catch (\Exception $ex) {
            var_dump($ex->getMessage());
            //var_dump($val);
            throw $ex;
            return null;
        }
    }
}
