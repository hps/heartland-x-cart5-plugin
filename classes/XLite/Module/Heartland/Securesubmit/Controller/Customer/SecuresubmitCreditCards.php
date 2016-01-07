<?php

namespace XLite\Module\Heartland\Securesubmit\Controller\Customer;

class SecuresubmitCreditCards extends \XLite\Controller\Customer\ACustomer
{
    const MODEL_PATH = 'XLite\Module\Heartland\Securesubmit\Model\SecuresubmitCreditCard';
    protected $entityManager = null;

    public function isSecure()
    {
        return \XLite\Core\Config::getInstance()->Security->customer_security;
    }

    public function getTitle()
    {
        return static::t('Saved SecureSubmit credit cards');
    }

    public function isTitleVisible()
    {
        return \XLite\Core\Request::getInstance()->widget;
    }

    public function checkAccess()
    {
        return parent::checkAccess() && \XLite\Core\Auth::getInstance()->isLogged();
    }

    protected function getLocation()
    {
        return static::t('Saved SecureSubmit credit cards');
    }

    protected function addBaseLocation()
    {
        parent::addBaseLocation();
        $this->addLocationNode('My account');
    }

    public function isDisplayDefaultAction()
    {
        return count($this->getProfile()->getSecuresubmitCreditCardsHash()) > 1;
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
