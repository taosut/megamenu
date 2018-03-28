<?php

final class Teko
{
    public static function getSaleCenterVersion()
    {
        $version = file_get_contents('version');
        return $version;
    }

    /**
     * @return Mage_Core_Model_Session
     *
     */
    public static function getSession()
    {
        return Mage::getSingleton('core/session');
    }

    public static function getDir($path)
    {
        return Mage::getBaseDir() . DIRECTORY_SEPARATOR . $path;
    }
}