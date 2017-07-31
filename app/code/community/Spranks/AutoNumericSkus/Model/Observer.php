<?php

class Spranks_AutoNumericSkus_Model_Observer
{

    public function coreBlockAbstractToHtmlAfter(Varien_Event_Observer $observer)
    {
        /** @var Mage_Core_Block_Abstract $block */
        $block = $observer->getEvent()->getBlock();
        if ($block->getNameInLayout() === 'product_edit') {
            // do nothing for existing products
            $sku = $block->getProduct()->getSku();
            if (!empty($sku)) {
                return;
            }
            $newSku = $this->calculateNewSku();
            $transport = $observer->getEvent()->getTransport();
            $html = $transport->getHtml();
            $html .= '<script type="text/javascript">';
            $html .= 'if (document.getElementById("sku").value == "") {';
            $html .= '    document.getElementById("sku").value = ' . $newSku;
            $html .= '}';
            $html .= '</script>';
            $transport->setHtml($html);
        }
    }

    private function calculateNewSku()
    {
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('sku')
            ->addFieldToFilter('sku', array('regexp' => '^[0-9]+$'))
            ->setPageSize(1)
            ->setCurPage(1);
        $collection->getSelect()->order('LENGTH(sku) DESC, sku DESC');
        $lastProduct = $collection->getFirstItem();
        if ( ! $lastProduct->getId()) {
            return 1;
        }
        $sku = intval($lastProduct->getSku());
        return $sku + 1;
    }

}
