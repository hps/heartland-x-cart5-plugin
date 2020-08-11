<?php
namespace XLite\Module\Heartland\Securesubmit;

abstract class Main extends \XLite\Module\AModule
{
    public static function getAuthorName()
    {
        return 'Heartland';
    }

    public static function getModuleName()
    {
        return 'Securesubmit';
    }

    public static function getDescription()
    {
        return 'Use Heartland for PCI-friendly credit card payments';
    }

    public static function getMajorVersion()
    {
        return '5.4';
    }

    public static function getMinorVersion()
    {
        return '0';
    }

    public static function getModuleType()
    {
        return static::MODULE_TYPE_PAYMENT;
    }

}
