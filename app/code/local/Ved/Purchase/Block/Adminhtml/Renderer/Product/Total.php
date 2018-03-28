<?php

/**
 * Class Ved_Purchase_Block_Adminhtml_Renderer_Product_Total
 */
class Ved_Purchase_Block_Adminhtml_Renderer_Product_Total extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        /**
         * @var Ved_Purchase_Model_Resource_Purchase_Collection $purchase
         */
        $purchase = Mage::getModel('ved_purchase/purchase')->load($row->getId());
        /**
         * @var Ved_Purchase_Model_Resource_Purchaseitem_Collection $purchaseItemCollection
         */
        $purchaseItemCollection = Mage::getModel('ved_purchase/purchaseitem')->getCollection();
        $totalProduct = $purchaseItemCollection->addFieldToFilter('purchase_id', $row->getId())
            ->addFieldToFilter(['product_sku', 'product_name'],
                array(
                    array('like' => '%'.$row->getFilterKey().'%'),
                    array('like' => '%'.$row->getFilterKey().'%')
                )
            )
            ->load();
        if ($purchase->getReceiveDate() == null) {
            $re = DateTime::createFromFormat('Y-m-d H:i:s',
                date('Y-m-d H:i:s', strtotime('+3 hours', time())),
                new DateTimeZone('UTC')
            )->getTimestamp();
        }
        else {
            $re = DateTime::createFromFormat('Y-m-d H:i:s',
                $purchase->getReceiveDate(),
                new DateTimeZone('UTC')
            )->getTimestamp();
        }
        $now = DateTime::createFromFormat('Y-m-d H:i:s',
            now(),
            new DateTimeZone('UTC')
        )->getTimestamp();
        $html = '';
        if ($re < $now && $purchase->getStatus() == 1) {
            $html = "<table><colgroup><col width='200px'><col width='200px'><col width='50px'><col width='50px'>
<col width='50px'><thead><tr><th>SKU</th><th>Tên sản phẩm</th><th>Loại hàng</th><th>Số lượng yêu cầu</th><th>Số lượng đã nhập</th></tr></thead><tbody>";
            foreach($totalProduct as $item){
                $html .= "<tr style='color: #f44141; font-weight: bold;'><td>".$item->getProductSku()."</td><td>".$item->getProductName()."</td><td>".($item->getType() == 0 ? 'Hàng hóa' : ($item->getType() == 1 ? 'Ký gửi' : ($item->getType() == 2 ? 'Hàng KM' : 'Hàng Demo')))."</td><td>". $item->getRequestQty()."</td><td>". $item->getImportQty()."</td></tr>";
            }
            $html.= "</tbody></table>";
        }
        else if ($re - $now <= 1800 && $purchase->getStatus() == 1) {
            $html = "<table><colgroup><col width='200px'><col width='200px'><col width='50px'><col width='50px'>
<col width='50px'><thead><tr><th>SKU</th><th>Tên sản phẩm</th><th>Loại hàng</th><th>Số lượng yêu cầu</th><th>Số lượng đã nhập</th></tr></thead><tbody>";
            foreach($totalProduct as $item){
                $html .= "<tr style='color: #d9b611; font-weight: bold;'><td>".$item->getProductSku()."</td><td>".$item->getProductName()."</td><td>".($item->getType() == 0 ? 'Hàng hóa' : ($item->getType() == 1 ? 'Ký gửi' : ($item->getType() == 2 ? 'Hàng KM' : 'Hàng Demo')))."</td><td>". $item->getRequestQty()."</td><td>". $item->getImportQty()."</td></tr>";
            }
            $html.= "</tbody></table>";
        }
        else {
            $html = "<table><colgroup><col width='200px'><col width='200px'><col width='50px'><col width='50px'>
<col width='50px'><thead><tr><th>SKU</th><th>Tên sản phẩm</th><th>Loại hàng</th><th>Số lượng yêu cầu</th><th>Số lượng đã nhập</th></tr></thead><tbody>";
            foreach($totalProduct as $item){
                $html .= "<tr><td>".$item->getProductSku()."</td><td>".$item->getProductName()."</td><td>".($item->getType() == 0 ? 'Hàng hóa' : ($item->getType() == 1 ? 'Ký gửi' : ($item->getType() == 2 ? 'Hàng KM' : 'Hàng Demo')))."</td><td>". $item->getRequestQty()."</td><td>". $item->getImportQty()."</td></tr>";
            }
            $html.= "</tbody></table>";
        }
        return $html;
    }
}