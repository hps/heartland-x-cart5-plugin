<?php
namespace XLite\Module\Heartland\Securesubmit\View\Checkout;

abstract class Payment extends \XLite\View\Checkout\Payment implements \XLite\Base\IDecorator
{
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $method = \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')->findOneBy(array('service_name' => 'Securesubmit'));

        if ($method && $method->isEnabled()) {
            $list[] = 'modules/Heartland/Securesubmit/payment.js';
            $list[] = array(
                'url' => 'https://api2.heartlandportico.com/SecureSubmit.v1/token/2.0/securesubmit.js',
            );
        }

        return $list;
    }

    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'modules/Heartland/Securesubmit/checkout.css';

        return $list;
    }

}
