<?php

namespace XLite\Module\Heartland\Securesubmit\Controller\Admin;

class HeartlandSavedCards extends \XLite\Controller\Admin\AAdmin
{
    public function getTitle()
    {
        return static::t('Heartland Saved Credit Cards');
    }

    protected function getCustomerProfile()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Profile')->find(
            intval(\XLite\Core\Request::getInstance()->profile_id)
        );
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