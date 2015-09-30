<?php

namespace XLite\Module\Heartland\Securesubmit\View;

class Payment extends \XLite\View\AView
{
    protected function getDefaultTemplate()
    {
        return 'modules/Heartland/Securesubmit/checkout.tpl';
    }

    protected function getDataAtttributes()
    {
        $data = array(
            'data-key' => $this->getCart()->getPaymentMethod()->getSetting('publicKey' . $suffix),
        );

        return $data;
    }
}

