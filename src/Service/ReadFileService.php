<?php
namespace App\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ReadFileService
{
    public function read($fileName)
    {
        $inputFileType = IOFactory::identify($fileName);

        $reader = IOFactory::createReader($inputFileType);

        $spreadsheet = $reader->load($fileName);

        $activeSheet = $spreadsheet->getActiveSheet();
        $data = $activeSheet->toArray();
        if (count($data)) {
            $header = $data[0];
            unset($data[0]);
            // foreach ($data as $item) {
            //     dd($item);
            //     // this is where you do your database stuff
            // }
            return ['header' => $header, 'rows' => $data];
        }
    }
}