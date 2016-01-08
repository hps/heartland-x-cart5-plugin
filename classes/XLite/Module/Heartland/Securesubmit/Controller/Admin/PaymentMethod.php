<?php

namespace XLite\Module\Heartland\Securesubmit\Controller\Admin;

class PaymentMethod extends \XLite\Controller\Admin\PaymentMethod implements \XLite\Base\IDecorator
{
    protected function isSecuresubmitPaymentMethod()
    {
        return $this->getPaymentMethod()
            && $this->getPaymentMethod()->getClass() == \XLite\Module\Heartland\Securesubmit\Core\Securesubmit::MODEL_PATH;
    }

    protected function doActionUpdate()
    {
        parent::doActionUpdate();

        if ($this->isSecuresubmitPaymentMethod()) {
            $request = \XLite\Core\Request::getInstance();

            // Return back to the SecureSubmit payment configurations page
            $this->setReturnURL(
                $this->buildURL(
                    'payment_method',
                    '',
                    array('method_id' => $request->method_id)
                )
            );
        }
    }
}
