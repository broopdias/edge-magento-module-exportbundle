<?php

class Edge_ExportBundle_Helper_Data extends Mage_Core_Helper_Abstract
{
    // Create new file csv
    public function createCsvfile($file,$data_title)
    {
        if (!is_dir(Mage::getBaseDir() . DS . "var" . DS . "exportcsv")) {
            mkdir(Mage::getBaseDir() . DS . "var" . DS . "exportcsv", 0777, true);
        }

        $csv_folder = Mage::getBaseDir() . DS . "var" . DS . "exportcsv";
        // $filename = str_replace('.csv','',$file).'_'.date("YmdHis");
        $filename = str_replace('.csv','',$file);
        $CSVFileName = $csv_folder. DS .$filename.'.csv';
        $FileHandle = fopen($CSVFileName, 'w') or die("can't open file");
        fclose($FileHandle);

        $fp = fopen($CSVFileName, 'a');
        fputcsv($fp, $data_title);

        Mage::getSingleton('core/session')->setCsvexport($CSVFileName);
    }

    // auto save file csv
    public function autoSave(){
        // full path to file csv need download
        $CSVFileName = Mage::getSingleton('core/session')->getCsvexport();
        // name of file auto save
        $file_csv = 'Productbundle-'.date("YmdHis").'.csv';
        if ( ! file_exists($CSVFileName)) {
            Mage::log('file missing');
            die("error: file missing");
        } else {
            header('HTTP/1.1 200 OK');
            header('Cache-Control: no-cache, must-revalidate');
            header("Pragma: no-cache");
            header("Expires: 0");
            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename=$file_csv");
            readfile($CSVFileName);
            exit;
        }
    }
}