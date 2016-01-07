<?php

namespace XLite\Module\Heartland\Securesubmit\Core;

class Securesubmit extends \XLite\Base\Singleton
{
    const MODEL_PATH = 'Module\Heartland\Securesubmit\Model\Payment\Securesubmit';
    protected $paymentMethod = null;

    public function getPaymentMethod()
    {
        if (!$this->paymentMethod) {

            $this->paymentMethod = \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
                ->findOneBy(array('class' => self::MODEL_PATH));
        }

        return $this->paymentMethod;
    }

    public function getSetting($setting)
    {
        return $this->getPaymentMethod()->getSetting($setting);
    }
}
