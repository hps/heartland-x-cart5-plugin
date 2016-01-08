<?php

namespace XLite\Module\Heartland\Securesubmit\View;

/**
 * ListChild (list="admin.center", zone="admin")
 */
class HeartlandSavedCardsAdmin extends \XLite\View\Dialog
{
    public static function getAllowedTargets()
    {
        $list = parent::getAllowedTargets();
        $list[] = 'heartland_saved_cards';

        return $list;
    }

    public function getDefaultTemplate()
    {
        return 'modules/Heartland/Securesubmit/account/heartland_saved_cards_admin.tpl';
    }

    public function getDir()
    {
        return 'modules/Heartland/Securesubmit/account';
    }
}