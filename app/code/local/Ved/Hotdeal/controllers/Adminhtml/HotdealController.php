<?php

class Ved_Hotdeal_Adminhtml_HotdealController extends Mage_Adminhtml_Controller_Action
{

    protected function __initHotdeal()
    {
        $hotdealId = $this->getRequest()->getParam('id');
        $hotdeal = Mage::getModel('ved_hotdeal/hotdeal');
        $hotdeal->setData('_edit_mode', true);
        if ($hotdealId) {
            try {
                $hotdeal->load($hotdealId);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        Mage::register('current_hotdeal', $hotdeal);
        return $hotdeal;
    }

    /**
     * Product list page
     */
    public function indexAction()
    {
        $this->_title($this->__('Catalog'))->_title($this->__('Hot Deal'));
        $this->loadLayout();
        $this->_setActiveMenu('catalog/catalog');
        $this->_addContent($this->getLayout()->createBlock('ved_hotdeal/adminhtml_catalog_hotdeal'));
        $this->renderLayout();
    }

    /**
     * Product grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ved_hotdeal/adminhtml_catalog_Hotdeal_grid')->toHtml()
        );
    }


    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Product edit form
     */
    public function editAction()
    {
        $hotdealId = $this->getRequest()->getParam('id');
        $hotdeal = $this->__initHotdeal();

        if ($hotdealId && !$hotdeal->getId()) {
            $this->_getSession()->addError(Mage::helper('catalog')->__('This hot deal no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        $this->_title($hotdeal->getTitle());
        $this->loadLayout();
        $this->_setActiveMenu('catalog/catalog');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('ved_hotdeal/adminhtml_catalog_hotdeal_edit'));
        $this->renderLayout();
    }

    /**
     * Initialize hotdeal before saving
     */
    protected function __initHotdealSave()
    {
        $hotdeal = $this->__initHotdeal();
        $hotdealData = $this->getRequest()->getPost();

        $hotdeal->addData($hotdealData);
        try {
            if (isset($_FILES['full_size']['name']) && $_FILES['full_size']['name'] != '') {
                try {
                    //Xoa anh cu
                    $imgFilePath = Mage::getBaseDir('media') . '/hotdeal/' . $hotdeal->getImage();
                    if (file_exists($imgFilePath)) unlink($imgFilePath);


                    //KHoi tao uploader
                    $uploader = new Varien_File_Uploader('full_size');

                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);

                    // Set media as the upload dir
                    $media_path = Mage::getBaseDir('media') . '/hotdeal/';
                    mkdir($media_path, 0777);
                    $fileName = $_FILES['full_size']['name'];

                    // Check trung ten file
                    if (count(glob($media_path . $fileName)) > 0) {
                        $file_ext = end(explode(".", $fileName));
                        $fileName = str_replace(('.' . $file_ext), "", $fileName);
                        $fileName = $fileName . '_' . count(glob($media_path . "$fileName*.$file_ext")) . '.' . $file_ext;
                    }

                    // Upload the image
                    $uploader->save($media_path, $fileName);

                    $hotdeal->setFull_size($fileName);
                } catch (Exception $e) {
                    Mage::log($e);
                    var_dump($e);
                    die();
                }
            }

            if (isset($_FILES['small_size']['name']) && $_FILES['small_size']['name'] != '') {
                try {
                    //Xoa anh cu
                    $imgFilePath = Mage::getBaseDir('media') . '/hotdeal/' . $hotdeal->getImage();
                    if (file_exists($imgFilePath)) unlink($imgFilePath);


                    //KHoi tao uploader
                    $uploader = new Varien_File_Uploader('small_size');

                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);

                    // Set media as the upload dir
                    $media_path = Mage::getBaseDir('media') . '/hotdeal/';
                    mkdir($media_path, 0777);
                    $fileName = $_FILES['small_size']['name'];

                    // Check trung ten file
                    if (count(glob($media_path . $fileName)) > 0) {
                        $file_ext = end(explode(".", $fileName));
                        $fileName = str_replace(('.' . $file_ext), "", $fileName);
                        $fileName = $fileName . '_' . count(glob($media_path . "$fileName*.$file_ext")) . '.' . $file_ext;
                    }

                    // Upload the image
                    $uploader->save($media_path, $fileName);

                    $hotdeal->setSmall_size($fileName);
                } catch (Exception $e) {
                    Mage::log($e);
                }
            }

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            return;
        }
        return $hotdeal;
    }

    public function saveAction()
    {
        $data = $this->getRequest()->getPost();
        if ($data) {
            try {
                $hotdeal = $this->__initHotdealSave();
                $hotdeal->save();
                $this->_getSession()->addSuccess($this->__('The hotdeal has been saved.'));
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $hotdealId = $this->getRequest()->getParam('id');
        $hotdeal = $this->__initHotdeal();

        if ($hotdealId && !$hotdeal->getId()) {
            $this->_getSession()->addError(Mage::helper('catalog')->__('This hotdeal no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }
        // Xoa anh
        $imgFilePath = Mage::getBaseDir('media') . '/hotdeal/' . $hotdeal->getImage();
        if (file_exists($imgFilePath)) unlink($imgFilePath);
        $hotdeal->delete();
        $this->_redirect('*/*/');
    }
}