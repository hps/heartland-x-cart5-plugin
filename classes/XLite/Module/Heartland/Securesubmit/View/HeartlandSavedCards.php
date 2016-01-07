<?php

namespace XLite\Module\Heartland\Securesubmit\View;

/**
 * @ListChild (list="center", zone="customer")
 */
class HeartlandSavedCards extends \XLite\View\Dialog
{
    public static function getAllowedTargets()
    {
        $list = parent::getAllowedTargets();
        $list[] = 'heartland_saved_cards';

        return $list;
    }

    public function getDir()
    {
        return 'modules/Heartland/Securesubmit/account';
    }

    public function getDefaultTemplate()
    {
        return 'modules/HeartlandSecuresubmit/account/heartland_saved_cards.tpl';
    }
}