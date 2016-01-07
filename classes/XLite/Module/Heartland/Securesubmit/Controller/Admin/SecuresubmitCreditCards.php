<?php

namespace XLite\Module\Heartland\Securesubmit\Controller\Admin;

class SecuresubmitCreditCards extends \XLite\Controller\Admin\AAdmin
{
    const MODEL_PATH = 'XLite\Module\Heartland\Securesubmit\Model\SecuresubmitCreditCard';
    protected $entityManager = null;

    public function getTitle()
    {
        return static::t('SecureSubmit credit cards');
    }

    protected function getCustomerProfile()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Profile')->find(
            intval(\XLite\Core\Request::getInstance()->profile_id)
        );
    }

    public function doActionUpdate()
    {
        $request = \XLite\Core\Request::getInstance();
        $result = false;

        if ($request->delete_token) {
            try {
                $condition = array('id' => $request->delete_token);
                $repo = $this->getModelRepo();
                $token = $repo->findOneBy($condition);
                $this->getEM()->remove($token);
                $this->getEM()->flush();
                $result = true;
            } catch (Exception $e) {
            }
        }

        if ($result) {
            \XLite\Core\TopMessage::getInstance()->addInfo('Operation successfull');
        }
    }

    protected function getEM()
    {
        if ($this->entityManager == null) {
            $this->entityManager = \XLite\Core\Database::getEM();
        }
        if (!$this->entityManager->isOpen()) {
            $this->entityManager = $this->entityManager->create(
                $this->entityManager->getConnection(),
                $this->entityManager->getConfiguration()
            );
        }
        return $this->entityManager;
    }

    protected function getModelRepo($modelPath = self::MODEL_PATH)
    {
        return \XLite\Core\Database::getRepo($modelPath);
    }
}
