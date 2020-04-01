<?php

namespace XLite\Module\Heartland\Securesubmit\View\Tabs;

class Account extends \XLite\View\Tabs\Account implements \XLite\Base\IDecorator
{
    public static function getAllowedTargets()
    {
        $list = parent::getAllowedTargets();
        $list[] = 'securesubmit_credit_cards';

        return $list;
    }

    public function __construct()
    {
        if ($this->getProfile()) {
            $this->tabs['securesubmit_credit_cards'] = array(
                 'title'    => 'SecureSubmit credit cards',
                 'template' => 'modules/Heartland/Securesubmit/account/securesubmit_credit_cards.twig',
            );
        }

        parent::__construct();
    }
}
