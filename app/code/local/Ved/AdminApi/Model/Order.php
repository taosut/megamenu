<?php

class Ved_AdminApi_Model_Order extends Mage_Sales_Model_Order
{
    const  DEPOSIT_CONFIRM_TRUE = 1;
    const  DEPOSIT_CONFIRM_FAIL = 2;
    const  DEPOSIT_CONFIRM_NO = 0;
}