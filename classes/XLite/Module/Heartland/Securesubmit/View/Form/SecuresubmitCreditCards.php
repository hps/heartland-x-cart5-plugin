<?php

namespace XLite\Module\Heartland\Securesubmit\View\Form;

class SecuresubmitCreditCards extends \XLite\View\Form\AForm
{
    protected function getDefaultTarget()
    {
        return 'securesubmit_credit_cards';
    }

    protected function getDefaultAction()
    {
        return 'update';
    }

    protected function getDefaultParams()
    {
        $params = array();

        if (\XLite::isAdminZone()) {
            $params = array(
                'profile_id' => \XLite\Core\Request::getInstance()->profile_id
            );
        };

        return $params;
    }
}
