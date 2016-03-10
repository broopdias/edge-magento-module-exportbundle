<?php

class Edge_ExportBundle_Adminhtml_ProductbundleController extends Mage_Adminhtml_Controller_action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->_title($this->__("Manage Bundle Products"));
        $this->renderLayout();
    }

    public function exportAction()
    {
        $products = Mage::getResourceModel('catalog/product_collection')->addAttributeToFilter('type_id', array('eq' => 'bundle'));
        if (count($products)) {
            try {
                $data_title = array(
                    'sku',
                    'website_ids',
                    'attribute_set_id',
                    'type_id',
                    'name',
                    'category_ids',
                    'has_options',
                    'sku_type',
                    'weight_type',
                    'shipment_type',
                    'status',
                    'price_type',
                    'price_view',
                    'special_price',
                    'is_in_stock',
                    'qty',
                    'simples_sku'
                );

                Mage::helper('productbundle')->createCsvfile('bundle', $data_title);
                $CSVFileName = Mage::getSingleton('core/session')->getCsvexport();

                foreach ($products as $product) {

                    $bundled_product = new Mage_Catalog_Model_Product();
                    $bundled_product->load($product->getId());

                    $b_website_ids      = implode(',',$bundled_product->getWebsiteIds());
                    $b_attribute_set_id = $bundled_product->getData('attribute_set_id');
                    $b_type_id          = $bundled_product->getData('type_id');
                    $b_sku              = $bundled_product->getData('sku');
                    $b_name             = $bundled_product->getData('name');
                    $b_category_ids     = implode(',',$bundled_product->getCategoryIds());
                    $b_has_options      = $bundled_product->getData('has_options');
                    $b_sku_type         = $bundled_product->getData('sku_type');
                    $b_weight_type      = $bundled_product->getData('weight_type');
                    $b_shipment_type    = $bundled_product->getData('shipment_type');
                    $b_status           = $bundled_product->getData('status');
                    $b_price_type       = $bundled_product->getData('price_type');
                    $b_price_view       = $bundled_product->getData('price_view');
                    $b_special_price    = $bundled_product->getData('special_price');
                    $stock              = Mage::getModel('cataloginventory/stock_item')->loadByProduct($bundled_product);
                    $b_is_in_stock      = $stock->getIsInStock();
                    $b_qty              = $stock->getQty();

                    $selectionCollection = $bundled_product->getTypeInstance(true)->getSelectionsCollection(
                        $bundled_product->getTypeInstance(true)->getOptionsIds($bundled_product), $bundled_product
                    );

                    if (count($selectionCollection)) {
                        $bundled_items = array();
                        foreach ($selectionCollection as $option) {
                            $bundled_items[] = $option->sku;
                        }
                    }
                    $bundle_sku_simples = implode(' - ',$bundled_items);
                    $data = array($b_sku, $b_website_ids, $b_attribute_set_id, $b_type_id, $b_name, $b_category_ids, $b_has_options, $b_sku_type, $b_weight_type, $b_shipment_type, $b_status, $b_price_type, $b_price_view, $b_special_price, $b_is_in_stock, $b_qty, $bundle_sku_simples);

                    $fp = fopen($CSVFileName, 'a');
                    fputcsv($fp, $data);
                }
                // auto save just file csv export
                Mage::helper('productbundle')->autoSave();

                Mage::getSingleton('adminhtml/session')->addSuccess('Export success');
            } catch (Exception $e) {
                Mage::log($e->getMessage());
                die("error: ".$e->getMessage());
            }
        }
    }
}