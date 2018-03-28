<?php

class Ved_SalesRule_Model_Observer
{
    protected $_isHandled = array();
    protected $_toAdd = array();

    protected $_itemsWithDiscount = array();
    protected $_calcHelper;

    protected $_rules = array();

    protected $_onCollectTotalAfterBusy = false;
    protected $_bundleProductsInCart = array();

    /**
     * Process sales rule form creation
     * @param   Varien_Event_Observer $observer
     */
    public function handleFormCreation($observer)
    {
        $actionsSelect = $observer->getForm()->getElement('simple_action');
        if ($actionsSelect){
            $vals = $actionsSelect->getValues();
            $vals[] = array(
                'value' => 'ved_buyxgety',
                'label' => 'Buy X get Y free',
            );
            
            $actionsSelect->setValues($vals);
            $actionsSelect->setOnchange('ved_hide_all();');
            
            $fldSet = $observer->getForm()->getElement('action_fieldset');

            $fldSet->addField('promo_sku', 'text', array(
                'name'     => 'promo_sku',
                'label' => 'Promo Items',
                'note'  => 'Comma separated list of the SKUs',
                ),
                'ampromo_type'
            );

        }
        
        return $this; 
    }
}

