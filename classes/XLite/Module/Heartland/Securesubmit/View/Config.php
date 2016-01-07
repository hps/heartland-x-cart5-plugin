<?php

namespace XLite\Module\Heartland\Securesubmit\View;

class Config extends \XLite\View\AView
{

    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        return $list;
    }

    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        return $list;
    }

    protected function getCommonFiles()
    {
        $list = parent::getCommonFiles();
        return $list;
    }

    protected function getDefaultTemplate()
    {
        return 'modules/Heartland/Securesubmit/config.tpl';
    }
}
