<?php

namespace XLite\Module\Heartland\Securesubmit\Model;

/**
 * @Entity
 * @Table (name="heartland_securesubmit_credit_cards")
 */
class SecuresubmitCreditCard extends \XLite\Model\AEntity
{
    /**
    * @Id
    * @GeneratedValue (strategy="AUTO")
    * @Column         (type="integer", name="id")
    */
    protected $id;

    /**
     * @Column (type="integer", name="profile_id")
     */
    protected $profileId;

    /**
    * @Column (type="string", length=255, name="last_four")
    */
    protected $lastFour;

    /**
    * @Column (type="string", length=255, name="exp_month")
    */
    protected $expMonth;

    /**
    * @Column (type="string", length=255, name="exp_year")
    */
    protected $expYear;

    /**
    * @Column (type="string", length=255, name="card_brand")
    */
    protected $cardBrand;

    /**
    * @Column (type="string", length=255, name="token")
    */
    protected $token;
}
