<?php
namespace XLite\Module\Heartland\Securesubmit\Model\Payment;

class Securesubmit extends \XLite\Model\Payment\Base\Online
{
    protected $securesubmitLibIncluded = false;
    protected $chargeService;
    protected $eventId;

    public function getWebhookURL()
    {
        return \XLite::getInstance()->getShopURL(
            \XLite\Core\Converter::buildURL('callback', null, array(), \XLite::CART_SELF),
            \XLite\Core\Config::getInstance()->Security->customer_security
        );
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

        if (empty($data['securesubmit_token'])) {
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

        try {
            $suToken = new \HpsTokenData();
            $suToken->tokenValue = $this->request['securesubmit_token'];

            $address = new \HpsAddress();
            $address->address = $this->getProfile()->getBillingAddress()->getStreet();
            $address->city = $this->getProfile()->getBillingAddress()->getCity();
            $address->state = $this->getProfile()->getBillingAddress()->getState()->getCode();
            $address->zip = preg_replace('/[^a-zA-Z0-9]/', '', $this->getProfile()->getBillingAddress()->getZipcode());
            $address->country = $this->getProfile()->getBillingAddress()->getCountry()->getCode();

            $cardHolder = new \HpsCardHolder();
            $cardHolder->firstName = $this->getProfile()->getBillingAddress()->getFirstname();
            $cardHolder->lastName = $this->getProfile()->getBillingAddress()->getLastname();
            $cardHolder->phone = preg_replace('/[^0-9]/', '',  $this->getCustomerPhone());
            $cardHolder->emailAddress = $this->getProfile()->getLogin();
            $cardHolder->address = $address;

            $details = new \HpsTransactionDetails();
            $details->invoiceNumber = $this->getSetting('prefix') . $this->transaction->getPublicTxnId();

            if ($this->isCapture())
                $payment = $this->chargeService->charge($this->transaction->getValue(), 'usd', $suToken, $cardHolder, $details);
            else
                $payment = $this->chargeService->authorize($this->transaction->getValue(), 'usd', $suToken, $cardHolder, $details);

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

    protected function doRefund(\XLite\Model\Payment\BackendTransaction $transaction, $isDoVoid = false)
    {
        $this->includeSecuresubmitLibrary();

        $backendTransactionStatus = \XLite\Model\Payment\BackendTransaction::STATUS_FAILED;

        try {
            if ($isDoVoid)
                $payment = $this->chargeService->void($transaction->getPaymentTransaction()->getDataCell('heartland_id')->getValue());
            else
                $payment = $this->chargeService->refund($this->transaction->getValue(), 'usd', $transaction->getPaymentTransaction()->getDataCell('heartland_id')->getValue());

            $backendTransactionStatus = \XLite\Model\Payment\BackendTransaction::STATUS_SUCCESS;
            $transaction->setDataCell('heartland_date', getdate()->format('Y-m-d H:i:s'));

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

    protected function detectEventId()
    {
        $body = @file_get_contents('php://input');
        $event = @json_decode($body);
        $id = $event ? $event->id : null;

        return ($id && preg_match('/^evt_/Ss', $id)) ? $id : null;
    }

    protected function checkResponse($payment, &$note)
    {
        $result = $this->checkTotal($this->transaction->getCurrency()->convertIntegerToFloat($payment->amount))
            && $this->checkCurrency(strtoupper($payment->currency));

        if ($result && $payment->captured != $this->isCapture()) {
            $result = false;
            $note .= static::t(
                'Requested transaction type: X; real transaction type: Y',
                array(
                    'actual' => $this->isCapture() ? 'capture' : 'authorization',
                    'real'   => $payment->captured ? 'capture' : 'authorization',
                )
            );
        }

        return $result;
    }
}

