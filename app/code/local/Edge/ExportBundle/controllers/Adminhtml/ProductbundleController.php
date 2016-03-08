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
                $csv_export = '';
                if ($csv_export == '') {
                    $file = 'bundle';
                    $data_title = array('sku', 'website_ids', 'attribute_set_id', 'type_id', 'name', 'description', 'short_description', 'category_ids', 'has_options', 'sku_type', 'weight_type', 'shipment_type', 'status', 'price_type', 'price_view', 'special_price', 'is_in_stock', 'qty', 'bundle_options_selections');

                    Mage::helper('productbundle')->createCsvfile($file,$data_title);
                    $CSVFileName = Mage::getSingleton('core/session')->getCsvexport();
                }

                foreach($products as $product) {
                    $bundled_product = new Mage_Catalog_Model_Product();
                    $bundled_product->load($product->getId());

                    $b_website_ids = implode(',',$bundled_product->getWebsiteIds());
                    $b_attribute_set_id = $bundled_product->getData('attribute_set_id');
                    $b_type_id = $bundled_product->getData('type_id');
                    $b_sku = $bundled_product->getData('sku');
                    $b_name = $bundled_product->getData('name');
                    $b_description = $bundled_product->getData('description');
                    $b_short_description = $bundled_product->getData('short_description');
                    $b_category_ids = implode(',',$bundled_product->getCategoryIds());
                    $b_has_options = $bundled_product->getData('has_options');
                    $b_sku_type = $bundled_product->getData('sku_type');
                    $b_weight_type = $bundled_product->getData('weight_type');
                    $b_shipment_type = $bundled_product->getData('shipment_type');
                    $b_status = $bundled_product->getData('status');
                    $b_price_type = $bundled_product->getData('price_type');
                    $b_price_view = $bundled_product->getData('price_view');
                    $b_special_price = $bundled_product->getData('special_price');

                    $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($bundled_product);
                    $b_is_in_stock = $stock->getIsInStock();
                    $b_qty = $stock->getQty();

                    $optionCollection = $bundled_product->getTypeInstance()->getOptionsCollection();
                    $selectionCollection = $bundled_product->getTypeInstance()->getSelectionsCollection($bundled_product->getTypeInstance()->getOptionsIds());
                    $options = $optionCollection->appendSelections($selectionCollection); // get all options
                    $options_arr = array();
                    if (count($options)) {
                        foreach( $options as $option ) {
                            $o_required = $option->getData('required');
                            $o_position = $option->getData('position');
                            $o_type = $option->getData('type');
                            $o_title = $option->getData('default_title');

                            $_selections = $option->getSelections(); // get all items of each option
                            $selections_arr = array();
                            if(count($_selections)){
                                foreach( $_selections as $selection )
                                {
                                    // data of product selection
                                    $selection_price_value = $selection->getData('selection_price_value');
                                    $selection_price_type = $selection->getData('selection_price_type');
                                    $selection_qty = $selection->getData('selection_qty');
                                    $selection_can_change_qty = $selection->getData('selection_can_change_qty');
                                    $position = $selection->getData('position');
                                    $is_default = $selection->getData('is_default');

                                    // data of product to import new product
                                    $selection = Mage::getModel('catalog/product')->loadByAttribute('sku', $selection->getData('sku'));
                                    $website_ids = implode(',',$selection->getWebsiteIds());
                                    $attribute_set_id = $selection->getData('attribute_set_id');
                                    $type_id = $selection->getData('type_id');
                                    $sku = $selection->getData('sku');
                                    $name = $selection->getData('name');
                                    $description = $selection->getData('description');
                                    $short_description = $selection->getData('short_description');
                                    $category_ids = implode(',',$selection->getCategoryIds());
                                    $has_options = $selection->getData('has_options');
                                    $msrp_enabled = $selection->getData('msrp_enabled');
                                    $msrp_display_actual_price_type = $selection->getData('msrp_display_actual_price_type');
                                    $price = $selection->getData('price');
                                    $special_price = $selection->getData('special_price');
                                    $msrp = $selection->getData('msrp');
                                    $status = $selection->getData('status');
                                    $tax_class_id = $selection->getData('tax_class_id');
                                    $weight = $selection->getData('weight');
                                    // $stock_item = $selection->getData('stock_item');
                                    $stock_item = '';

                                    $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($selection);
                                    $is_in_stock = $stock->getIsInStock();
                                    $qty = $stock->getQty();

                                    $selections_arr[] = implode('#sa#', array($website_ids, $attribute_set_id, $type_id, $sku, $name, $description, $short_description, $category_ids, $has_options, $msrp_enabled, $msrp_display_actual_price_type, $price, $special_price, $msrp, $status, $tax_class_id, $weight, $stock_item, $is_in_stock, $qty, $selection_price_value, $selection_price_type, $selection_qty, $selection_can_change_qty, $position, $is_default));
                                }
                            }
                            $options_arr[] = implode('#oa#',array($o_required, $o_position, $o_type, $o_title, implode('#s#',$selections_arr)));
                        }
                    }

                    $bundle_options_selections = implode('#o#', $options_arr);

                    $data = array($b_sku, $b_website_ids, $b_attribute_set_id, $b_type_id, $b_name, $b_description, $b_short_description, $b_category_ids, $b_has_options, $b_sku_type, $b_weight_type, $b_shipment_type, $b_status, $b_price_type, $b_price_view, $b_special_price, $b_is_in_stock, $b_qty, $bundle_options_selections);

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