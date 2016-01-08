<?php

namespace XLite\Module\Heartland\Securesubmit\View\Tabs;

class AdminProfile extends \XLite\View\Tabs\AdminProfile implements \XLite\Base\IDecorator
{
    public function __construct(array $params = array())
    {
        if ($this->getProfile()) {
            $this->tabs['securesubmit_credit_cards'] = array(
                 'title'    => 'SecureSubmit credit cards',
                 'template' => 'modules/Heartland/Securesubmit/account/securesubmit_credit_cards.tpl',
            );
        }

        parent::__construct();
    }
}
