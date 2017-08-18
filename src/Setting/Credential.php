<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 18/08/2017
 * Time: 10:36
 */

namespace PaymentGateway\VPosGaranti\Setting;


use PaymentGateway\VPosGaranti\Helper\Validator;

class Credential
{
    private $terminalId;
    private $merchantId;
    private $storeKey;
    private $provisionPassword;
    private $refundProvisionPassword;

    /**
     * @return mixed
     */
    public function getTerminalId()
    {
        return $this->terminalId;
    }

    /**
     * @param mixed $terminalId
     */
    public function setTerminalId($terminalId)
    {
        $this->terminalId = $terminalId;
    }

    /**
     * @return mixed
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @param mixed $merchantId
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @return mixed
     */
    public function getStoreKey()
    {
        return $this->storeKey;
    }

    /**
     * @param mixed $storeKey
     */
    public function setStoreKey($storeKey)
    {
        $this->storeKey = $storeKey;
    }

    /**
     * @return mixed
     */
    public function getProvisionPassword()
    {
        return $this->provisionPassword;
    }

    /**
     * @param mixed $provisionPassword
     */
    public function setProvisionPassword($provisionPassword)
    {
        $this->provisionPassword = $provisionPassword;
    }

    /**
     * @return mixed
     */
    public function getRefundProvisionPassword()
    {
        return $this->refundProvisionPassword;
    }

    /**
     * @param mixed $refundProvisionPassword
     */
    public function setRefundProvisionPassword($refundProvisionPassword)
    {
        $this->refundProvisionPassword = $refundProvisionPassword;
    }

    public function validate()
    {
        Validator::validateNotEmpty('terminalId', $this->getTerminalId());
        Validator::validateNotEmpty('merchantId', $this->getMerchantId());
        Validator::validateNotEmpty('storeKey', $this->getStoreKey());
        Validator::validateNotEmpty('provisionPassword', $this->getProvisionPassword());
        Validator::validateNotEmpty('refundProvisionPassword', $this->getRefundProvisionPassword());
    }
}