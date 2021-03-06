<?php

namespace XLite\Module\Heartland\Securesubmit\Model\Payment;

class Securesubmit extends \XLite\Model\Payment\Base\Online
{
    protected $securesubmitLibIncluded = false;
    protected $chargeService;
    protected $eventId;
    protected $eventManager;

    public function getWebhookURL()
    {
        return '';
    }

    public function getReferralPageURL(\XLite\Model\Payment\Method $method)
    {
        return '';
    }

    public function isConfigured(\XLite\Model\Payment\Method $method)
    {
        return ($method->getSetting('secretKey') && $method->getSetting('publicKey'));
    }

    public function getAllowedTransactions()
    {
        return array(
            \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_CAPTURE,
            \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_CAPTURE_PART,
            \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_VOID,
            \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_REFUND,
            \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_REFUND_PART,
        );
    }

    public function getSettingsWidget()
    {
        return '\XLite\Module\Heartland\Securesubmit\View\Config';
    }

    public function getInputTemplate()
    {
        return 'modules/Heartland/Securesubmit/payment.tpl';
    }

    public function getInputErrors(array $data)
    {
        $errors = parent::getInputErrors($data);
        error_log(print_r($data, true));

        if ((!isset($data['securesubmit_use_stored_card']) && empty($data['securesubmit_token'])) ||
            (empty($data['securesubmit_token']) && $data['securesubmit_use_stored_card'] === 'new')
        ) {
            $errors[] = \XLite\Core\Translation::lbl(
                'Payment processed with errors. Please, try again or ask administrator'
            );
        }

        return $errors;
    }

    public function useDefaultSettingsFormButton()
    {
        return false;
    }

    public function getInitialTransactionType($method = null)
    {
        $type = $method ? $method->getSetting('type') : $this->getSetting('type');

        return 'sale' == $type
            ? \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_AUTH
            : \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_SALE;
    }

    public function getAdminIconURL(\XLite\Model\Payment\Method $method)
    {
        return true;
    }

    protected function doInitialPayment()
    {
        $this->includeSecuresubmitLibrary();

        $note = '';
        $requestMulti = false;

        try {
            $token = new \HpsTokenData();
            $token->tokenValue = $this->request['securesubmit_token'];

            $address = new \HpsAddress();
            $address->address = $this->getProfile()->getBillingAddress()->getStreet();
            $address->city = $this->getProfile()->getBillingAddress()->getCity();
            $address->state = $this->getProfile()->getBillingAddress()->getState()->getCode();
            $address->zip = preg_replace('/[^a-zA-Z0-9]/', '', $this->getProfile()->getBillingAddress()->getZipcode());
            $address->country = $this->getProfile()->getBillingAddress()->getCountry()->getCode();

            $cardHolder = new \HpsCardHolder();
            $cardHolder->firstName = $this->getProfile()->getBillingAddress()->getFirstname();
            $cardHolder->lastName = $this->getProfile()->getBillingAddress()->getLastname();
            $cardHolder->phone = preg_replace('/[^0-9]/', '', $this->getCustomerPhone());
            $cardHolder->emailAddress = $this->getProfile()->getLogin();
            $cardHolder->address = $address;

            $details = new \HpsTransactionDetails();
            $details->invoiceNumber = $this->getSetting('prefix') . $this->transaction->getPublicTxnId();

            if (isset($this->request['securesubmit_use_stored_card']) && $this->request['securesubmit_use_stored_card'] !== 'new') {
                $cardId = intval($this->request['securesubmit_use_stored_card']);
                $repo = \XLite\Core\Database::getRepo('\XLite\Module\Heartland\Securesubmit\Model\SecuresubmitCreditCard');
                $cards = $repo->findBy(array(
                    'profileId' => $this->getProfile()->getProfileId(),
                    'id'        => $cardId,
                ));
                if ($cards === array()) {
                    throw new Exception('Stored card cannot be found.');
                }
                $token->tokenValue = $cards[0]->getToken();
            } else {
                $requestMulti = isset($this->request['securesubmit_save_card']) && $this->request['securesubmit_save_card'] === 'save';
            }

            if ($this->isCapture()) {
                $payment = $this->chargeService->charge(
                    $this->transaction->getValue(),
                    'usd',
                    $token,
                    $cardholder,
                    $requestMulti,
                    $details
                );
            } else {
                $payment = $this->chargeService->authorize(
                    $this->transaction->getValue(),
                    'usd',
                    $token,
                    $cardholder,
                    $requestMulti,
                    $details
                );
            }

            if ($requestMulti && $payment->tokenData->responseCode === '0' && $payment->tokenData->tokenValue !== '') {
                $card = new \XLite\Module\Heartland\Securesubmit\Model\SecuresubmitCreditCard();
                $this->getEM()->persist($card);
                $card->setProfileId($this->getProfile()->getProfileId());
                $card->setToken($payment->tokenData->tokenValue);
                $card->setCardBrand(isset($this->request['securesubmit_card_type']) ? $this->request['securesubmit_card_type'] : '');
                $card->setExpMonth(isset($this->request['securesubmit_exp_month']) ? $this->request['securesubmit_exp_month'] : '');
                $card->setExpYear(isset($this->request['securesubmit_exp_year']) ? $this->request['securesubmit_exp_year'] : '');
                $card->setLastFour(isset($this->request['securesubmit_last_four']) ? $this->request['securesubmit_last_four'] : '');
                $this->getEM()->flush();
            }

            $result = static::COMPLETED;
            $backendTransactionStatus = \XLite\Model\Payment\BackendTransaction::STATUS_SUCCESS;

            $type = $this->isCapture()
                ? \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_SALE
                : \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_AUTH;

            $backendTransaction = $this->registerBackendTransaction($type);
            $backendTransaction->setDataCell('heartland_id', $payment->transactionId);
            $this->transaction->setType($type);

            $backendTransaction->setStatus($backendTransactionStatus);
            $backendTransaction->registerTransactionInOrderHistory('initial request');

            $this->setDetail('heartland_id', $payment->transactionId);
        } catch (\Exception $e) {
            $result = static::FAILED;
            \XLite\Core\TopMessage::addError($e->getMessage());
            $note = $e->getMessage();
        }

        $this->transaction->setNote($note);

        return $result;
    }

    protected function doCapture(\XLite\Model\Payment\BackendTransaction $transaction)
    {
        $this->includeSecuresubmitLibrary();

        $backendTransactionStatus = \XLite\Model\Payment\BackendTransaction::STATUS_FAILED;

        try {
            $payment = $this->chargeService->capture($transaction->getPaymentTransaction()->getDataCell('heartland_id')->getValue());
            $backendTransactionStatus = \XLite\Model\Payment\BackendTransaction::STATUS_SUCCESS;
        } catch (\Exception $e) {
            $transaction->setDataCell('errorMessage', $e->getMessage());
            \XLite\Logger::getInstance()->log($e->getMessage(), LOG_ERR);
            \XLite\Core\TopMessage::addError($e->getMessage());
        }

        $transaction->setStatus($backendTransactionStatus);

        return \XLite\Model\Payment\BackendTransaction::STATUS_SUCCESS == $backendTransactionStatus;
    }

    protected function doVoid(\XLite\Model\Payment\BackendTransaction $transaction)
    {
        return $this->doRefund($transaction, true);
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

    protected function doRefund(\XLite\Model\Payment\BackendTransaction $transaction, $isDoVoid = false)
    {
        $this->includeSecuresubmitLibrary();

        $backendTransactionStatus = \XLite\Model\Payment\BackendTransaction::STATUS_FAILED;
        $transactionId = $transaction->getPaymentTransaction()->getDataCell('heartland_id')->getValue();

        try {
            if ($isDoVoid) {
                $payment = $this->chargeService->void($transactionId);
            } else {
                $payment = $this->chargeService->refund(
                    $this->transaction->getValue(),
                    'usd',
                    $transactionId
                );
            }

            $backendTransactionStatus = \XLite\Model\Payment\BackendTransaction::STATUS_SUCCESS;
            $transaction->setDataCell('refund_txnid', $payment->transactionId);

        } catch (\Exception $e) {
            $transaction->setDataCell('errorMessage', $e->getMessage());
            \XLite\Logger::getInstance()->log($e->getMessage(), LOG_ERR);
            \XLite\Core\TopMessage::addError($e->getMessage());
        }

        $transaction->setStatus($backendTransactionStatus);

        return \XLite\Model\Payment\BackendTransaction::STATUS_SUCCESS == $backendTransactionStatus;
    }

    protected function getCustomerPhone()
    {
        $address = $this->getProfile()->getBillingAddress() ?: $this->getProfile()->getShippingAddress();
        return $address
            ? trim($address->getPhone())
            : static::t('000000');
    }

    protected function isCapture()
    {
        return 'sale' == $this->getSetting('type');
    }

    protected function registerBackendTransaction($type = null, \XLite\Model\Payment\Transaction $transaction = null)
    {
        if (!$transaction) {
            $transaction = $this->transaction;
        }

        if (!$type) {
            $type = $transaction->getType();
        }

        $backendTransaction = $transaction->createBackendTransaction($type);

        return $backendTransaction;
    }

    protected function includeSecuresubmitLibrary()
    {
        if (!$this->securesubmitLibIncluded) {
            require_once LC_DIR_MODULES . 'Heartland' . LC_DS . 'Securesubmit' . LC_DS . 'Library' . LC_DS . 'Securesubmit' . LC_DS . 'Hps.php';

            if ($this->transaction) {
                $method = $this->transaction->getPaymentMethod();
                $key = $method->getSetting('secretKey' . $suffix);

            } else {
                $method = \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
                    ->findOneBy(array('service_name' => 'Securesubmit'));
                $key = $method->getSetting('secretKey' . $suffix);
            }

            $heartlandConfig = new \HpsServicesConfig();
            $heartlandConfig->secretApiKey = $key;

            $heartlandConfig->versionNumber = '1514';
            $heartlandConfig->developerId = '002914';

            $this->chargeService = new \HpsCreditService($heartlandConfig);

            $this->securesubmitLibIncluded = true;
        }
    }
}
