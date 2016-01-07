<?php

namespace XLite\Module\Heartland\Securesubmit\Controller\Customer;

class HeartlandSavedCards extends \XLite\Controller\Customer\ACustomer
{
    public function isSecure()
    {
        return \XLite\Core\Config::getInstance()->Security->customer_security;
    }

    public function getTitle()
    {
        return static::t('Heartland Saved Credit Cards');
    }

    public function checkAccess()
    {
        return parent::checkAccess() && \XLite\Core\Auth::getInstance()->isLogged();
    }

    public function getLocation()
    {
        return static::t('Heartland Saved Credit Cards');
    }

    public function addBaseLocation()
    {
        parent::addBaseLocation();
        $this->addLocationNode('My account');
    }

    protected function doActionUpdate()
    {
        $request = \XLite\Core\Request::getInstance();
        $result = false;

        if ($request->delete_card) {
            $result = true;
            print 'here';
        }

        if ($result) {
            \XLite\Core\TopMessage::getInstance()->addInfo('Operation successul');
        }
    }
}
