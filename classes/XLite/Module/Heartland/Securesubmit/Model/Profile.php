<?php

namespace XLite\Module\Heartland\Securesubmit\Model;

class Profile extends \XLite\Model\Profile implements \XLite\Base\IDecorator
{
    const MODEL_PATH = 'XLite\Module\Heartland\Securesubmit\Model\SecuresubmitCreditCard';
    protected $securesubmitCreditCards = null;

    public function getSecuresubmitCreditCards()
    {
        if (is_null($this->securesubmitCreditCards)) {
            $condition = array('profileId' => $this->getProfileId());
            $repo = $this->getModelRepo();
            $this->securesubmitCreditCards = array();
            foreach ($repo->findBy($condition) as $card) {
                $this->securesubmitCreditCards[] = array(
                    'id' => $card->getId(),
                    'lastFour' => $card->getLastFour(),
                    'expMonth' => $card->getExpMonth(),
                    'expYear' => $card->getExpYear(),
                    'cardBrand' => $card->getCardBrand(),
                );
            }
        }
        return $this->securesubmitCreditCards;
    }

    public function hasSecuresubmitCreditCards()
    {
        return $this->getSecuresubmitCreditCards() !== array();
    }

    protected function getModelRepo($modelPath = self::MODEL_PATH)
    {
        return \XLite\Core\Database::getRepo($modelPath);
    }
}
