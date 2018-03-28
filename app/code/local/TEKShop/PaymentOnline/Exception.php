<?php

class TEKShop_PaymentOnline_Exception extends Exception
{
    protected $status;

    function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->status = sprintf('%02d', $code);
    }

    function getStatus()
    {
        return $this->status;
    }
}