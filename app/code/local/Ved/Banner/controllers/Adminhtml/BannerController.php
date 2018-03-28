<?php

class Ved_Banner_Adminhtml_BannerController extends Mage_Adminhtml_Controller_Action
{

    protected function __initBanner()
    {
        $bannerId = $this->getRequest()->getParam('id');
        $banner = Mage::getModel('ved_banner/banner');
        $banner->setData('_edit_mode', true);
        if ($bannerId) {
            try {
                $banner->load($bannerId);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        Mage::register('current_banner', $banner);
        return $banner;
    }

    /**
     * Product list page
     */
    public function indexAction()
    {
        $this->_title($this->__('Catalog'))->_title($this->__('Banner'));
        $this->loadLayout();
        $this->_setActiveMenu('catalog/catalog');
        $this->_addContent($this->getLayout()->createBlock('ved_banner/adminhtml_catalog_banner'));
        $this->renderLayout();
    }

    /**
     * Product grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ved_banner/adminhtml_catalog_Banner_grid')->toHtml()
        );
    }


    public function newAction(){
        $this->_forward('edit');
    }

    /**
     * Product edit form
     */
    public function editAction()
    {
        $bannerId = $this->getRequest()->getParam('id');
        $banner = $this->__initBanner();

        if ($bannerId && !$banner->getId()) {
            $this->_getSession()->addError(Mage::helper('catalog')->__('This banner no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        $this->_title($banner->getTitle());
        $this->loadLayout();
        $this->_setActiveMenu('catalog/catalog');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('ved_banner/adminhtml_catalog_banner_edit'));
        $this->renderLayout();
    }

        /**
     * Initialize banner before saving
     */
    protected function _initBannerSave()
    {
        $banner     = $this->__initBanner();
        $bannerData = $this->getRequest()->getPost();

        $banner->addData($bannerData);

        try {
            if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
                try {
                    //Xoa anh cu
                    $imgFilePath = Mage::getBaseDir('media') . '/banner/'  . $banner->getImage();
                    if (file_exists($imgFilePath)) unlink($imgFilePath);


                    //KHoi tao uploader
                    $uploader = new Varien_File_Uploader('image');

                    $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);

                    // Set media as the upload dir
                    $media_path  = Mage::getBaseDir('media') . '/banner/';
                    mkdir($media_path, 0777);
                    $fileName =  $_FILES['image']['name'];

                    // Check trung ten file
                    if(count(glob($media_path.$fileName))>0)
                    {
                        $file_ext = end(explode(".", $fileName));
                        $fileName = str_replace(('.'.$file_ext),"",$fileName);
                        $fileName = $fileName.'_'.count(glob($media_path."$fileName*.$file_ext")).'.'.$file_ext;
                    }

                    // Upload the image
                    $uploader->save($media_path, $fileName);

                    
                }
                catch (Exception $e) {
                    Mage::log($e);
                    $this->redirectError(502);
                }
            } 
            if(isset($fileName))
                $banner->setImage($fileName);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            return;
        }
        return $banner;
    }

    public function saveAction()
    {
        $isEdit = (int)($this->getRequest()->getParam('id') != null);

        $data = $this->getRequest()->getPost();
        if ($data) {

            try {
                $banner = $this->_initBannerSave();
                $banner->save();
                $bannerID = $banner->getId();
                //End update
                $this->_getSession()->addSuccess($this->__('The Banner has been saved.'));
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
                $redirectBack = true;
            }
        }

        if ($redirectBack) {
            $this->_redirect('*/*/edit', array(
                'id'    => $productId,
                '_current'=>true
            ));
        } else {
            $this->_redirect('*/*/');
        }
    }

    public function deleteAction()
    {
        $bannerId = $this->getRequest()->getParam('id');
        $banner = $this->__initBanner();

        if ($bannerId && !$banner->getId()) {
            $this->_getSession()->addError(Mage::helper('catalog')->__('This banner no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        // Xoa anh
        $imgFilePath = Mage::getBaseDir('media') . '/banner/'  . $banner->getImage();
        if (file_exists($imgFilePath)) unlink($imgFilePath);

        $banner->delete();

        $this->_redirect('*/*/');
    }
}