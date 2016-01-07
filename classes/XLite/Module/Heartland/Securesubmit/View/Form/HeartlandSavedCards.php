<?php

namespace XLite\Module\Heartland\Securesubmit\View\Form;

class HeartlandSavedCards extends \XLite\View\Form\AForm
{
    protected function getDefaultTarget()
    {
        return 'heartland_saved_cards';
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
                'profile_id' => \XLite\Core\Request::getInstance()->profile_id,
            );
        }

        return $params;
    }
}