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

    // save new csv file
    public function saveCsvfile()
    {
        $csv_import = '';
        if (isset($_FILES['fileimport']['name']) && $_FILES['fileimport']['name'] != '') {
            try {
                if (!is_dir(Mage::getBaseDir('var') . DS ."inportcsv")) {
                    mkdir(Mage::getBaseDir('var') . DS ."inportcsv", 0777, true);
                }

                $uploader = new Varien_File_Uploader('fileimport');
                $uploader->setFilesDispersion(false);
                $path = Mage::getBaseDir('var') . DS ."inportcsv" . DS;
                $uploader->save($path, $_FILES['fileimport']['name'] );
                // Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__(' File Uploaded Sucessfully'));
            } catch (Exception $e) {
                die($e->getMessage());
            }
        }
    }

    // get id of product selection
    public function getSelectionId($selection_data)
    {
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $selection_data[3]);
        if ($product) {
            $product_id = $product->getData('entity_id');
        } else { //create new product width data of selection product
            $newProductData = array(
             // websites - Array of website ids to which you want to assign a new product
            'website_ids'        => explode(',',$selection_data[0]),
            'sku'				=> $selection_data[3],
            'name'              => $selection_data[4],
            'description'       => $selection_data[5],
            'short_description'       => $selection_data[6],
            'status'            => $selection_data[14],
            'weight'            => $selection_data[16],
            'tax_class_id'      => $selection_data[15],
            'price'             => $selection_data[11],
            'special_price'             => $selection_data[12],
            'created_at'		=> strtotime('now'),
            );

            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            $product = Mage::getModel('catalog/product');
            $product->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
                    ->setAttributeSetId($selection_data[1])
                    ->setTypeId($selection_data[2])
                    ->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID))
                    ->setCategoryIds(explode(',', $selection_data[7]));
            try{
                $product->addData($newProductData);
                $product->getResource()->save($product);
                $product->setMediaGallery(array('images' => array(), 'values' => array()));
                $product->save();

                $stockItem = Mage::getModel('cataloginventory/stock_item');
                $stockItem->loadByProduct($product->getId());
                $stockItem->assignProduct($product);
                $stockItem->setData('product_id',$product->getId());
                $stockItem->setData('is_in_stock', $selection_data[19]);
                $stockItem->setData('qty',$selection_data[20]);
                $stockItem->setData('manage_stock', 1);
                $stockItem->setData('stock_id', 1);
                $stockItem->setData('use_config_manage_stock', 0);
                $stockItem->save();
                $product_id = $product->getId();
            } catch (Exception $e) {
                Mage::log($e->getMessage());
                die("error: ".$e->getMessage());
            }
        }
        return $product_id;
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