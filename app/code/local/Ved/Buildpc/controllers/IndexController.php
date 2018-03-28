<?php

/**
 * Created by PhpStorm.
 * User: Teko
 * Date: 7/12/2017
 * Time: 10:02 AM
 */
class Ved_Buildpc_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $old_params = $this->getRequest()->getParam('p');
        $parambases = base64_decode($this->getRequest()->getParam('pb'));
        $myPC = $this->getRequest()->getParam('mypc');
        $paramsProduct = [];
        $storeId = Mage::app()->getStore()->getId();

        if ($parambases) {
            $arr = explode("&", $parambases);
            foreach ($arr as $element) {
                if ($element !== "") {
                    $index = intval(substr($element, 2, strpos($element, ']') - 2));
                    $temp = strpos($element, '=') + 1;
                    $paramsProduct[$index] = substr($element, $temp, strlen($element) - $temp);
                }
            }
        } elseif ($myPC) {
            $productList = Mage::getModel('Ved_Buildpc/detail')->getCollection()
                ->addFieldToFilter('parent_id', intval($myPC));

            $catArr = [553, 552, 548, 636, 637, 554, 541, 549, 555, 540, 543, 542, 544, 781];

            foreach ($productList as $product) {
                $index = array_search($product->getCategoryId(), $catArr);
                $paramsProduct[$index] = $product->getProductId() . '_' . $product->getQuantity();
            }
            Mage::register('myPC', intval($myPC));
        }

        if ($parambases) {
            Mage::register('params', $paramsProduct);
            Mage::register('fromShare', true);
        } elseif ($old_params) {
            foreach ($old_params as $index => $old_param) {
                $old_params[$index] = $old_param . '_1';
            }
            Mage::register('params', $old_params);
        } elseif ($myPC) {
            Mage::register('params', $paramsProduct);
        } else {
            Mage::unregister('params');
            Mage::unregister('fromShare');
            Mage::unregister('myPC');
        }
        $this->loadLayout();
//        $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('buildpc/buildpc'));
        $this->renderLayout();
    }

    public function openModalAction()
    {

        $catId = $this->getRequest()->getParam('catId');
//        var_dump($catId);
        $page = $this->getRequest()->getParam('p');
        $search = $this->getRequest()->getParam('search');
        if (!$page) {
            $page = 1;
        }
        if (!$search) {
            $search = "";
        }
//        var_dump($catId, $page, $search);
        Mage::register('catId', $catId);
        Mage::register('page', $page);
        Mage::register('search', $search);

        $bpmodalBlock = $this->getLayout()->createBlock('buildpc/bpmodal');

        $result = array('bp_modal' => $bpmodalBlock->toHtml());
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /** Export excel */
    public function exportExcelAction()
    {
        // create new folder in media to save excel file
        $today = getdate();
        $today_folder = $today['year'] . '-' . $today['mon'] . '-' . $today['mday'];
        if (!file_exists('media/export_buildpc/' . $today_folder)) {
            mkdir('media/export_buildpc/' . $today_folder, 0777, true);
        }

        // create file name
        $time = time();
        $position = rand(0, 10);
        $tmp = substr(hash('md5', $time), $position, $position + rand(1, 10));
        $fileName = date("H-i-s", $time + 7 * 60 * 60) . '_' . $tmp . "_build-pc.xlsx";

        // get data build PC
        $bpArray = $this->getRequest()->getParam('bpArray');

        // Load excel
        include Mage::getBaseDir("lib") . DS . "Excel" . DS . "PHPExcel.php";

        $objPHPExcel = PHPExcel_IOFactory::load("./template/tekshop/bao_gia_buildpc_template.xlsx");

        // Set title of sheet
        $objPHPExcel->getActiveSheet()->setTitle("Cấu hình build PC");
        $objPHPExcel->setActiveSheetIndex(0);

        // set date
        $objPHPExcel->getActiveSheet(0)
            ->setCellValue('G9', PHPExcel_Shared_Date::PHPToExcel($time));
        $objPHPExcel->setActiveSheetIndex(0)
            ->getStyle('G9')
            ->getNumberFormat()
            ->setFormatCode('dd-mm-yyyy');

        // set currency format
        $objPHPExcel->setActiveSheetIndex(0)
            ->getStyle('F12:H25')
            ->getNumberFormat()
            ->setFormatCode('#,##0');
        $objPHPExcel->setActiveSheetIndex(0)
            ->getStyle('H26:H28')
            ->getNumberFormat()
            ->setFormatCode('#,##0');

        $totalPrice = 0;
        $rowCount = 12;

        // append data to cell
        foreach ($bpArray as $index => $item) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->getRowDimension($rowCount)
                ->setRowHeight(22.5);
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('B' . $rowCount, $index + 1)
                ->setCellValue('C' . $rowCount, $item['productName'])
                ->setCellValue('F' . $rowCount, $item['productQty'])
                ->setCellValue('G' . $rowCount, $item['productPrice'])
                ->setCellValue('H' . $rowCount, $item['productPrice'] * $item['productQty']);
            $totalPrice += $item['productPrice'] * $item['productQty'];
            $rowCount++;
        }

        // Append total price
        $shippingFee = ($totalPrice < 500000) ? 11000 : 0;
        $totalFinalPrice = $totalPrice + $shippingFee;
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('H26', $shippingFee)
            ->setCellValue('H27', 0)
            ->setCellValue('H28', $totalFinalPrice);

        // save file and return response to client
        header('Content-Type: application/json');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('media/export_buildpc/' . $today_folder . '/' . $fileName);
        $this->getResponse()->setBody(json_encode(['url' => '/media/export_buildpc/' . $today_folder . '/' . $fileName]));
    }

    /** Export image **/
    public function exportImageAction()
    {
        $bpArray = json_decode($this->getRequest()->getParam('bpArray'), true);
        $screen = $this->getRequest()->getParam('screen');

        if (!file_exists('media/export_buildpc/images')) {
            mkdir('media/export_buildpc/images', 0777, true);
        }

        $fileName = "BuildPC_" . date("d-m-H-i-s", time() + 7 * 60 * 60) . '.png';

        header('Content-Description: File Transfer');
        header('Content-Type: image/png');
        header("Content-disposition: attachment; filename=$fileName");

        $newImage = $this->export($bpArray, $screen);

        imagepng($newImage);
        imagedestroy($newImage);
        die;
    }

    private function export($bpArray, $screen)
    {
        $webRowBaseSize = count($bpArray) <= 9 ? 120 : 70;
        $mobileRowBaseSize = count($bpArray) <= 9 ? 195 : 120;
        $headerSize = 200;
        $width = $screen == 'mobile' ? 993 : 1200;
        $height = $screen == 'mobile' ? ($mobileRowBaseSize * count($bpArray) + $headerSize * 3)
            : ($webRowBaseSize * count($bpArray) + $headerSize * 3);

        $newImage = @imagecreatetruecolor($width, $height) or die("Cannot Initialize new GD image stream");

        $backgroundColor = imagecolorallocate($newImage, 255, 255, 255);
        $logoTextColor = imagecolorallocate($newImage, 42, 51, 122);
        $titleColor = imagecolorallocate($newImage, 0, 0, 0);
        $headerColor = imagecolorallocate($newImage, 235, 236, 238);
        $priceColor = imagecolorallocate($newImage, 65, 64, 62);
        $finalPriceColor = imagecolorallocate($newImage, 212, 35, 51);
        imagefill($newImage, 0, 0, $backgroundColor);

        $totalPrice = 0;
        $rowSize = ($height - $headerSize * 2) / count($bpArray) + 1;
        $rowDraw = $headerSize;
        $columnDraw = 30;
        $padding = $screen == 'mobile' ? 20 : 30;
        $fontSize = $screen == 'mobile' ? 22 : 18;
        $priceFontSize = $screen == 'mobile' ? 24 : 20;
        $logoFontSize = 54;
        $finalFontSize = $screen == 'mobile' ? 32 : 28;
        $shippingFontSize = $screen == 'mobile' ? 26 : 20;
        $textPaddingTop = $screen == 'mobile' ? 30 : 30;
        $textPaddingBottom = $screen == 'mobile' ? 15 : -10;
        $titlePaddingRight = $screen == 'mobile' ? 120 : 30;
        if ($screen == 'web' && count($bpArray) <= 9) {
            $textPaddingBottom += 40;
        }
        if ($screen == 'mobile' && count($bpArray) <= 9) {
            $textPaddingTop += 20;
        }
        $font = Mage::getDesign()->getSkinBaseDir() . '/lib/fonts/Roboto-Medium.ttf';
        $imgSize = $rowSize - $padding;

        // Header
        imagefilledrectangle($newImage, 0, 0, $width, $headerSize - 10, $headerColor);
        $logoUrl = Mage::getDesign()->getSkinUrl('images/logoPV.png');
        $logoImage = imagecreatefrompng($logoUrl);
        $logoInfo = getimagesize($logoUrl);
        $box = imagettfbbox($logoFontSize, 0, $font, 'XÂY DỰNG CẤU HÌNH PC');
        imagecopy($newImage, $logoImage, ($width - $logoInfo[0]) / 2, 20, 0, 0, $logoInfo[0], $logoInfo[1]);
        imagettftext($newImage, $logoFontSize, 0, ($width - $box[2]) / 2, 100 + $logoInfo[1], $logoTextColor, $font, 'XÂY DỰNG CẤU HÌNH PC');

        // Loop each item
        foreach ($bpArray as $bpElement) {
            $product = Mage::getModel('catalog/product')->load($bpElement['productId']);
            $imageUrl = $product->getSmallImageUrl();
            $bpPrice = number_format($bpElement['productPrice'], 0, ',', '.');
            $bpSumPrice = number_format($bpElement['productPrice'] * $bpElement['productQty'], 0, ',', '.');

            $imageInfo = getimagesize($imageUrl);
            $originalWidth = $imageInfo[0];
            $originalHeight = $imageInfo[1];
            $mimeType = $imageInfo['mime'];

            if ($mimeType == 'image/jpeg') { // include .jpg and .jpeg extensions
                $originalImage = imagecreatefromjpeg($imageUrl);
            } elseif ($mimeType == 'image/png') { // .png extension
                $originalImage = imagecreatefrompng($imageUrl);
            } elseif ($mimeType == 'image/gif') { // .gif extension
                $originalImage = imagecreatefromgif($imageUrl);
            } else {
                continue;
            }

            imagecopyresampled($newImage, $originalImage, $columnDraw, $rowDraw + $padding / 2, 0, 0, $imgSize, $imgSize, $originalWidth, $originalHeight);

            // Explode text by words
            $textTmp = explode(' ', $bpElement['productName']);
            $textNew = '';
            foreach ($textTmp as $word) {
                // Create a new text, add the word, and calculate the parameters of the text
                $box = imagettfbbox($fontSize, 0, $font, $textNew . ' ' . $word);
                // If the line fits to the specified width, then add the word with a space, if not then add word with new line
                if ($box[2] > $width - $titlePaddingRight - $rowSize) {
                    $textNew .= "\n" . $word;
                } else {
                    $textNew .= " " . $word;
                }
            }
            // Trip spaces
            $textNew = trim($textNew);

            imagettftext($newImage, $fontSize, 0, $columnDraw + $rowSize + 20, $rowDraw + $textPaddingTop,
                $titleColor, $font, $textNew);

            $nameDimensions = imagettfbbox($fontSize, 0, $font, $textNew);
            $nameHeight = abs($nameDimensions[7] - $nameDimensions[1]) + 20;

            imagettftext($newImage, $priceFontSize, 0, $columnDraw + $rowSize + 20, $rowDraw + $textPaddingTop + $nameHeight,
                $priceColor, $font, $bpPrice . ' đ    x    ' . $bpElement['productQty']);

            // Print total price
            $totalText = '    =    ' . $bpSumPrice . ' đ';
            $dimensions = imagettfbbox($fontSize, 0, $font, $totalText);
            $textWidth = abs($dimensions[4] - $dimensions[0]);
            imagettftext($newImage, $priceFontSize, 0, $width - $padding - $textWidth - 80, $rowDraw + $textPaddingTop + $nameHeight,
                $finalPriceColor, $font, $totalText);
            $totalPrice += $bpElement['productPrice'] * $bpElement['productQty'];

            $rowDraw += $rowSize;
        }

        // Add shipping fee
        if ($totalPrice < 500000) {
            $shippingText = 'Phí vận chuyển: ' . number_format(11000, 0, ',', '.') . ' đ';
            $dimensions = imagettfbbox($shippingFontSize, 0, $font, $shippingText);
            $textWidth = abs($dimensions[4] - $dimensions[0]);
            imagettftext($newImage, $shippingFontSize, 0, ($width - $textWidth) / 2, $rowDraw + ($height - $rowDraw) / 2,
                $priceColor, $font, $shippingText);
            $totalPrice += 11000;
        }

        // Draw total price
        $finalText = 'Tổng chi phí: ' . number_format($totalPrice, 0, ',', '.') . ' đ';
        $dimensions = imagettfbbox($finalFontSize, 0, $font, $finalText);
        $textWidth = abs($dimensions[4] - $dimensions[0]);
        imagettftext($newImage, $finalFontSize, 0, ($width - $textWidth) / 2, $rowDraw + ($height - $rowDraw) / 2 + 40,
            $finalPriceColor, $font, $finalText);

        // Adding watermark
        $resizePercent = 0.7;
        $logoUrl = Mage::getDesign()->getSkinUrl('images/logo-export.png');
        $logoImage = imagecreatefrompng($logoUrl);
        $logoInfo = getimagesize($logoUrl);
        $stamp = imagecreatetruecolor($logoInfo[0] * $resizePercent, $logoInfo[1] * $resizePercent);
        imagefilledrectangle($stamp, 0, 0, imagesx($stamp), imagesy($stamp), $backgroundColor);
        imagecopyresampled($stamp, $logoImage, 0, 0, 0, 0, imagesx($stamp), imagesy($stamp), $logoInfo[0], $logoInfo[1]);
        imagecopymerge($newImage, $stamp, ($width - imagesx($stamp)) / 2, ($height - imagesy($stamp)) / 2, 0, 0, imagesx($stamp), imagesy($stamp), 7);

        return $newImage;
    }

    public function exportBeforeShareAction()
    {
        $fileName = $this->getRequest()->getParam('base_param') . '.png';
        $parambases = base64_decode($this->getRequest()->getParam('base_param'));
        $screen = $this->getRequest()->getParam('screen');
        $arr = explode("&", $parambases);
        $paramsProduct = array();
        foreach ($arr as $element) {
            if ($element !== "") {
                $index = intval(substr($element, 2, strpos($element, ']') - 2));
                $temp = strpos($element, '=') + 1;
                $paramsProduct[$index] = substr($element, $temp, strlen($element) - $temp);
            }
        }
        $bpArray = array();
        foreach ($paramsProduct as $index => $param) {
            $tempArr = explode("_", $param);
            $_product = Mage::getModel('catalog/product')->load(intval($tempArr[0])); // tempArr[0] = productId
            $productQty = intval($tempArr[1]); // tempArr[1] = productQty
            $bpArray[$index]['productId'] = $_product->getId();
            $bpArray[$index]['productPrice'] = $_product->getFinalPrice();
            $bpArray[$index]['productQty'] = $productQty;
        }
        if (!file_exists('media/export_buildpc/images')) {
            mkdir('media/export_buildpc/images', 0777, true);
        }
        $newImage = $this->export($bpArray, $screen, $fileName);
        imagepng($newImage, 'media/export_buildpc/images/' . $fileName);
        imagedestroy($newImage);
        return;
    }
}
