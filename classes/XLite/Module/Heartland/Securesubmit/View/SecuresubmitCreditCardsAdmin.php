<?php

namespace XLite\Module\Heartland\Securesubmit\View;

/**
 * SecureSubmit credit cards
 * @ListChild (list="admin.center", zone="admin")
 */
class SecuresubmitCreditCardsAdmin extends \XLite\View\Dialog
{
    public static function getAllowedTargets()
    {
        return array_merge(parent::getAllowedTargets(), array('securesubmit_credit_cards'));
    }

    protected function getDir()
    {
        return 'modules/Heartland/Securesubmit/account';
    }
}
