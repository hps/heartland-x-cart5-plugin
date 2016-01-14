<?php

namespace XLite\Module\Heartland\Securesubmit\View;

class Payment extends \XLite\View\AView
{
    protected $paymentMethod = null;

    protected function getDefaultTemplate()
    {
        return 'modules/Heartland/Securesubmit/checkout.tpl';
    }

    protected function getDataAtttributes()
    {
        $data = array(
            'data-key' => $this->getPaymentMethod()->getSetting('publicKey' . $suffix),
        );

        return $data;
    }

    protected function useSavedCardsEnabled()
    {
        return $this->getPaymentMethod()->getSetting('useSavedCards') === 'yes';
    }

    protected function getPaymentMethod()
    {
        if ($this->paymentMethod == null) {
            $this->paymentMethod = \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
                                 ->findOneBy(array('class' => \XLite\Module\Heartland\Securesubmit\Core\Securesubmit::MODEL_PATH));
        }
        return $this->paymentMethod;
    }
}
