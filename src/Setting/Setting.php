<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 08/08/2017
 * Time: 14:29
 */

namespace PaymentGateway\VPosGaranti\Setting;

use PaymentGateway\VPosGaranti\Helper\Validator;

abstract class Setting
{
    /** @var  Credential $credential */
    private $credential;
    private $threeDSuccessUrl;
    private $threeDFailUrl;
    private $storeType;

    /**
     * @return Credential
     */
    public function getCredential()
    {
        return $this->credential;
    }

    /**
     * @param Credential $credential
     */
    public function setCredential(Credential $credential)
    {
        $this->credential = $credential;
    }

    /**
     * @return mixed
     */
    public function getThreeDSuccessUrl()
    {
        return $this->threeDSuccessUrl;
    }

    /**
     * @param mixed $threeDSuccessUrl
     */
    public function setThreeDSuccessUrl($threeDSuccessUrl)
    {
        $this->threeDSuccessUrl = $threeDSuccessUrl;
    }

    /**
     * @return mixed
     */
    public function getThreeDFailUrl()
    {
        return $this->threeDFailUrl;
    }

    /**
     * @param mixed $threeDFailUrl
     */
    public function setThreeDFailUrl($threeDFailUrl)
    {
        $this->threeDFailUrl = $threeDFailUrl;
    }

    /**
     * @return mixed
     */
    public function getStoreType()
    {
        return $this->storeType;
    }

    /**
     * @param mixed $storeType
     */
    public function setStoreType($storeType)
    {
        $this->storeType = $storeType;
    }

    public function validate()
    {
        Validator::validateNotEmpty('credential', $this->getCredential());
        $this->getCredential()->validate();
        Validator::validateNotEmpty('purchaseUrl', $this->getPurchaseUrl());
        Validator::validateNotEmpty('authorizeUrl', $this->getAuthorizeUrl());
        Validator::validateNotEmpty('captureUrl', $this->getCaptureUrl());
        Validator::validateNotEmpty('voidUrl', $this->getVoidUrl());
        Validator::validateNotEmpty('refundUrl', $this->getRefundUrl());

        if (!empty($this->getCredential()->getStoreKey())) {
            Validator::validateNotEmpty('threeDPostUrl', $this->getThreeDPostUrl());
            Validator::validateNotEmpty('threeDFailUrl', $this->getThreeDFailUrl());
            Validator::validateNotEmpty('threeDSuccessUrl', $this->getThreeDSuccessUrl());
            Validator::validateStoreType($this->getStoreType());
        }
    }

    public abstract function getThreeDPostUrl();

    public abstract function getAuthorizeUrl();

    public abstract function getCaptureUrl();

    public abstract function getPurchaseUrl();

    public abstract function getRefundUrl();

    public abstract function getVoidUrl();

    public abstract function getMode();
}