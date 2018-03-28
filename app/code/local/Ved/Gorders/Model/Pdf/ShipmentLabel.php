<?php
/**
 * Sales Order Shipment PDF model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Ved_Gorders_Model_Pdf_ShipmentLabel extends Mage_Sales_Model_Order_Pdf_Abstract
{
    protected   $x = 0;
    /**
     * Draw table header for product items
     *
     * @param  Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(Zend_Pdf_Page $page)
    {
        /* Add table head */
        require_once 'Zend/Pdf/Color/Rgb.php';
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y-15);
        $this->y -= 10;
        $page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));

        //columns headers
        $lines[0][] = array(
            'text' => Mage::helper('sales')->__('ID'),
            'width' => 40,
            'feed'=>30,
            'align' => 'center'
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('OrderID'),
            'width'  => 50,
            'feed'=>70,
            'align' => 'center'
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Shipping Address'),
            //'width'  => 55,
            'feed'=>120,
            'align' => 'center'
        );
        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Products'),
           // 'width'  => 55,
            'feed'=>250,
            'align' => 'center'
        );
        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Quantity'),
            'width'  => 50,
            'feed'=>450,
            'align' => 'center'
        );
        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Checked'),
            'width'  => 50,
            'feed'=>500,
            'align' => 'center'
        );
        $lineBlock = array(
            'lines'  => $lines,
            'height' => 10
        );

        $this->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }
    protected function _setFontBold($object, $size = 7)
    {
        // $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf');
        //$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER);
        // $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir('skin') . '/frontend/default/gcafe/fonts/times.ttf');
        $object->setFont($font, $size);
        return $font;
    }
    public function printInvoicepdf($orderIds){
        $this->_beforeGetPdf();

        $this->_initRenderer('shipment');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $page  = $this->newPage();
        //Print header
        $this->_drawHeader($page);
        //Print content
        $count = 1;
        foreach($orderIds as $orderid){
            $this->_drawOrderItem($page, $count, $orderid);
            $count ++;
        }
        //Print footer

        //Call after event
        $this->_afterGetPdf();
        return $pdf;
    }




    public function printListProductspdf($orderIds){
        $this->_beforeGetPdf();

        $this->_initRenderer('shipment');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $page  = $this->newPage();
        //Print header
        $this->_drawHeader($page);
        //Print content
        $count = 1;
        foreach($orderIds as $orderid){
            $this->_drawOrderItem($page, $count, $orderid);
            $count ++;
        }
        //Print footer

        //Call after event
        $this->_afterGetPdf();
        return $pdf;
    }

    //Print Order
    public function _drawOrderItem($page, $count, $orderid){
        //Draw rectangle
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y-15);
        //Dra
    }

    public function printShipmentLabelpdf($orderIds){
        $this->_beforeGetPdf();

        $this->_initRenderer('shipment');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $page  = $this->newPage($settings = array(),$orientation='portrait');

        //Print Label
        foreach($orderIds as $orderid){
            $this->_drawLabelOrder($page, $orderid);
        }
        //Call after event
        $this->_afterGetPdf();
        return $pdf;
    }
    //Print Order
    public function _drawLabelOrder(&$page, $orderid){
        //Check Page
        if($this->y < 210){
            //Create new page
            $page  = $this->newPage($settings = array(),$orientation='portrait');
            $this->x = 0;
        }
        //Draw rectangle
        require_once 'Zend/Pdf/Color/Rgb.php';
        $page->setFillColor(new Zend_Pdf_Color_RGB(255, 255, 255));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        //$page->drawRectangle($this->x + 5, $this->y-15, $this->x + 290, $this->y-285);
        $page->setFillColor(new Zend_Pdf_Color_RGB(255, 255, 255));
        //Draw Logo
        $image = Mage::getBaseDir('skin').'/frontend/default/gcafe/images/tem-02.png' ;
        $current = $this->_drawLogo($page,$image,$this->y - 25,$this->x + 30);
        $page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));

        $order =  Mage::getModel('sales/order')->load($orderid);

        date_default_timezone_set('Asia/Ho_Chi_Minh');
        require_once Mage::getBaseDir('lib')."/Qrcode/qrlib.php";
        $filename = date("YmdHis",time()).".png";
        $qrimage = Mage::getBaseDir('media')."/qrcode/".$filename;

        $store = Mage::getModel('core/store')->load($order->getStoreId());
        $storename = $store->getName();
        $codeContents = "http://shop.gcafe.vn/index.php/".$storename."/sales/order/view/order_id/".$orderid."/";
        QRcode::png($codeContents,$qrimage , QR_ECLEVEL_L, 1);
        $x = 300;
        // if($this->x > 410){
            // $x = 760;
        // }
        //$this->_drawLogo($page,$qrimage,$this->y - 10,$x);


        $incrementId = $order->getIncrementId();
        $shipping_address = $order->getShippingAddress();
        $receiver = $shipping_address->getName();
        $shipping_address = $shipping_address->getStreetFull().", ".$shipping_address->getCity().", ".$shipping_address->getRegion();
        $receiver_phonenumber = $order->getBillingAddress()->getTelephone();
        $total_value = $order->getGrandTotal();
        $total_value = Mage::helper('core')->currency($total_value, true, false);
				$items = $order->getAllItems();




        //Draw all text
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir('skin') . '/frontend/default/gcafe/fonts/arial.ttf');
        //$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
//        $this->gdrawText($page,Mage::helper('sales')->__("Company name"),$font,12,$this->x  + 70,$this->y - 40,'UTF-8',$page->getWidth()-20,40);
//        $this->gdrawText($page,Mage::helper('sales')->__("Shipment Title"),$font,11,$this->x  + 150,$this->y - 60,'UTF-8',$page->getWidth()-20,40);

        //Order
        $this->gdrawText($page,Mage::helper('sales')->__("Orderid"),$font,10,$this->x  + 20,$this->y - 120,'UTF-8',$page->getWidth()-20,40);
        $this->gdrawText($page,$incrementId,$font,11,$this->x  + 20,$this->y - 135,'UTF-8',$page->getWidth()-20,40);
        $this->gdrawText($page,"..........................................",$font,10,$this->x  + 20,$this->y - 140,'UTF-8',$page->getWidth()-20,40);

        //Shipment
		$this->gdrawText($page,Mage::helper('sales')->__("Shipment"),$font,10,$this->x  + 150,$this->y - 120,'UTF-8',$page->getWidth()-20,40);
        $this->gdrawText($page,"..........................................",$font,10,$this->x  + 150,$this->y - 140,'UTF-8',$page->getWidth()-20,40);

        //Receiver
        $this->gdrawText($page,Mage::helper('sales')->__("Receiver"),$font,10,$this->x  + 20,$this->y - 155,'UTF-8',$page->getWidth()-20,40);
        $this->gdrawText($page,$receiver,$font,11,$this->x  + 20,$this->y - 170,'UTF-8',$page->getWidth()-20,40);
        $this->gdrawText($page,"..........................................",$font,10,$this->x  + 20,$this->y - 175,'UTF-8',$page->getWidth()-20,40);

        //Phone
        $this->gdrawText($page,Mage::helper('sales')->__("Phonenumber"),$font,10,$this->x  + 150,$this->y - 155,'UTF-8',$page->getWidth()-20,40);
        $this->gdrawText($page,$receiver_phonenumber,$font,11,$this->x  + 150,$this->y - 170,'UTF-8',$page->getWidth()-20,40);
        $this->gdrawText($page,"..........................................",$font,10,$this->x  + 150,$this->y - 175,'UTF-8',$page->getWidth()-20,40);

        $this->gdrawText($page,Mage::helper('sales')->__("Total value"),$font,10,$this->x  + 20,$this->y - 190,'UTF-8',$page->getWidth()-20,40);
        $this->gdrawText($page,$total_value,$font,11,$this->x  + 20,$this->y - 205,'UTF-8',$page->getWidth()-20,40);
        $this->gdrawText($page,"..........................................",$font,10,$this->x  + 20,$this->y - 210,'UTF-8',$page->getWidth()-20,40);
//        //var_dump($this->getTextWidth("Hoàng Hoa Thám Ba Đình Cầu Giấy Hoàn Kiếm Hà Nội Việt Nam",$font, 10));
        //$shipping_address = "444 Hoàng Hoa Thám, Ba Đình, Cầu Giấy, Hoàn Kiếm Hà Nội Việt Nam - Cạnh lăng bác Hồ";
        if($this->getTextWidth($shipping_address,$font, 11) > 300){
            $words = explode(" ",$shipping_address);
            $line1 = $words[0];
            $i = 1;
            while ($this->getTextWidth($line1,$font,11) < 300){
                $line1 .= " " . $words[$i];
                $i++;
            }
            $line2 = "";
            for($j = $i; $j < count($words); $j++){
                $line2.= " " . $words[$j];
            }
            $line3 = "";
            if($this->getTextWidth($line2,$font, 11) > 380){
                $words = explode(" ",$line2);
                $line2 = $words[0];
                $i = 1;
                while ($this->getTextWidth($line2,$font,11) < 380){
                    $line2 .= " " . $words[$i];
                    $i++;
                }
                $line3 = "";
                for($j = $i; $j < count($words); $j++){
                    $line3.= " " . $words[$j];
                }
            }
            $this->gdrawText($page,Mage::helper('sales')->__("Address") . ": .........................................................................",$font,10,$this->x  + 20,$this->y - 225,'UTF-8',$page->getWidth()-20,40);
            $this->gdrawText($page,$line1,$font,11,$this->x  + 60,$this->y - 222,'UTF-8',$page->getWidth()-20,40);
            $this->gdrawText($page,$line2,$font,11,$this->x  + 20,$this->y - 240,'UTF-8',$page->getWidth()-20,40);
            $this->gdrawText($page,".......................................................................................",$font,10,$this->x  + 20,$this->y - 245,'UTF-8',$page->getWidth()-20,40);
            $this->gdrawText($page,$line3,$font,11,$this->x  + 20,$this->y - 260,'UTF-8',$page->getWidth()-20,40);
            $this->gdrawText($page,".......................................................................................",$font,10,$this->x  + 20,$this->y - 265,'UTF-8',$page->getWidth()-20,40);
        }else{
            $this->gdrawText($page,Mage::helper('sales')->__("Address"),$font,10,$this->x  + 20,$this->y - 225,'UTF-8',$page->getWidth()-20,40);
            $this->gdrawText($page,$shipping_address,$font,11,$this->x  + 20,$this->y - 240,'UTF-8',$page->getWidth()-20,40);
            $this->gdrawText($page,".......................................................................................",$font,10,$this->x  + 20,$this->y - 245,'UTF-8',$page->getWidth()-20,40);
        }

//				$this->gdrawText($page,Mage::helper('sales')->__("Products:"),$font,10,$this->x  + 20,$this->y - 180,'UTF-8',$page->getWidth()-20,40);
//
//				$qtys = array(); //this will be used for processing the invoice
//				$i = 0;
//				foreach($items as $item){
//					$i+=15;
//					$this->gdrawText($page,Mage::helper('sales')->__("%s",(int)$item->getQtyOrdered()),$font,10,$this->x  + 40,$this->y - 180 - $i,'UTF-8',$page->getWidth()-20,40);
//					$this->gdrawText($page,Mage::helper('sales')->__("x"),$font,10,$this->x  + 60,$this->y - 180 - $i,'UTF-8',$page->getWidth()-20,40);
//					$this->gdrawText($page,Mage::helper('sales')->__("%s",$item->getName()),$font,10,$this->x  + 80,$this->y - 180 - $i,'UTF-8',$page->getWidth()-20,40);
//				}
				

        $this->x +=300;
        //Reduce Y
        if($this->x >590){
            //Return new line
            $this->y -= 270;
            $this->x = 0;
        }

    }
    public function gdrawText($page,$message,$font,$size = 11,$x,$y,$encode,$width,$height){
        $page->setFont($font, $size);
        $page->drawText($message,$x,$y,$encode);
    }
    protected function _drawLogo($page, $image,$top,$left)
    {
        if (is_file($image)) {
            $image = Zend_Pdf_Image::imageWithPath($image);
            $widthLimit = 250; //half of the page width
            $heightLimit = 70; //assuming the image is not a "skyscraper"
            $width = $image->getPixelWidth();
            $height = $image->getPixelHeight();

            //preserving aspect ratio (proportions)
            $ratio = $width / $height;
            if ($ratio > 1 && $width > $widthLimit) {
                $width = $widthLimit;
                $height = $width / $ratio;
            } elseif ($ratio < 1 && $height > $heightLimit) {
                $height = $heightLimit;
                $width = $height * $ratio;
            } elseif ($ratio == 1 && $height > $heightLimit) {
                $height = $heightLimit;
                $width = $widthLimit;
            }

            $y1 = $top - $height;
            $y2 = $top;
            $x1 = $left;
            $x2 = $x1 + $width;

            //coordinates after transformation are rounded by Zend
            $page->drawImage($image, $x1, $y1, $x2, $y2);
            return $top - $height;
        }
        return $top;
    }
    /**
     * Return PDF document
     *
     * @param  array $shipments
     * @return Zend_Pdf
     */
    public function getPdf($shipments = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('shipment');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
        foreach ($shipments as $shipment) {
            if ($shipment->getStoreId()) {
                Mage::app()->getLocale()->emulate($shipment->getStoreId());
                Mage::app()->setCurrentStore($shipment->getStoreId());
            }
            $page  = $this->newPage();
            $order = $shipment->getOrder();
            /* Add image */
            $this->insertLogo($page, $shipment->getStore());
            /* Add address */
            $this->insertAddress($page, $shipment->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $shipment,
                Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID,
                        $order->getStoreId())
            );
            /* Add document text and number */
            $this->insertDocumentNumber(
                $page,
                Mage::helper('sales')->__('Packingslip # ') . $shipment->getIncrementId()
            );
            /* Add table */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($shipment->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
        }
        $this->_afterGetPdf();
        if ($shipment->getStoreId()) {
            Mage::app()->getLocale()->revert();
        }
        return $pdf;
    }

    /**
     * Create new page and assign to PDF object
     *
     * @param  array $settings
     * @return Zend_Pdf_Page
     */
    public function newPage(array $settings = array(),$orientation='portrait')
    {
        /* Add new table head */
        if($orientation=='portrait'){
            $page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_A4);
            $this->y = 842;
            $this->x = 0;
        }else {
            $page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_A4_LANDSCAPE);
            $this->y = 594;
            $this->x = 0;
        }
      //  $pageHeight = $page->getHeight();
      //  $pageWidth = $page->getWidth();
//        echo 'Height = '.$pageHeight.'\n';
//        echo 'Width = '.$pageWidth.'\n';
//        die();
        $this->_getPdf()->pages[] = $page;

        if (!empty($settings['table_header'])) {
            $this->_drawHeader($page);
        }
        return $page;
    }

    private function getTextWidth($text, Zend_Pdf_Resource_Font $font, $font_size)
    {
        $drawing_text = iconv('UTF-8', 'UTF-8', $text);
        $characters    = array();
        for ($i = 0; $i < strlen($drawing_text); $i++) {
            $characters[] = $drawing_text[$i];
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $text_width   = (array_sum($widths) / $font->getUnitsPerEm()) * $font_size;
        return $text_width;
    }
}
