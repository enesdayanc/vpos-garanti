<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 21/08/2017
 * Time: 11:59
 */

namespace PaymentGateway\VPosGaranti\Request;


use PaymentGateway\ISO4217\Model\Currency;
use PaymentGateway\VPosGaranti\Constant\CardholderPresentCode;
use PaymentGateway\VPosGaranti\Constant\ProvUserID;
use PaymentGateway\VPosGaranti\Constant\RequestMode;
use PaymentGateway\VPosGaranti\Helper\Helper;
use PaymentGateway\VPosGaranti\Helper\Validator;
use PaymentGateway\VPosGaranti\Setting\Setting;

class ThreeDRequest implements RequestInterface
{
    private $type;
    private $version;
    private $userId;
    private $ip;
    private $email;
    private $installment;
    private $amount;
    private $orderId;
    /** @var  Currency $currency */
    private $currency;
    private $cavv;
    private $eci;
    private $xid;
    private $md;

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param mixed $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getInstallment()
    {
        return $this->installment;
    }

    /**
     * @param mixed $installment
     */
    public function setInstallment($installment)
    {
        $this->installment = $installment;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param mixed $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @return Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * @param Currency $currency
     */
    public function setCurrency(Currency $currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return mixed
     */
    public function getCavv()
    {
        return $this->cavv;
    }

    /**
     * @param mixed $cavv
     */
    public function setCavv($cavv)
    {
        $this->cavv = $cavv;
    }

    /**
     * @return mixed
     */
    public function getEci()
    {
        return $this->eci;
    }

    /**
     * @param mixed $eci
     */
    public function setEci($eci)
    {
        $this->eci = $eci;
    }

    /**
     * @return mixed
     */
    public function getXid()
    {
        return $this->xid;
    }

    /**
     * @param mixed $xid
     */
    public function setXid($xid)
    {
        $this->xid = $xid;
    }

    /**
     * @return mixed
     */
    public function getMd()
    {
        return $this->md;
    }

    /**
     * @param mixed $md
     */
    public function setMd($md)
    {
        $this->md = $md;
    }

    public function validate()
    {
        Validator::validateIp($this->getIp());
        Validator::validateEmail($this->getEmail());
        Validator::validateOrderId($this->getOrderId());
        Validator::validateRequestType($this->getType());
        Validator::validateAmount($this->getAmount());
        Validator::validateCurrency($this->getCurrency());
        Validator::validateInstallment($this->getInstallment());
        Validator::validateNotEmpty('XID', $this->getXid());
        Validator::validateNotEmpty('MD', $this->getMd());
    }

    public function toXmlString(Setting $setting)
    {
        $this->validate();

        $credential = $setting->getCredential();

        $elements = array(
            "Mode" => RequestMode::PROD,
            "Version" => $this->getVersion(),
            "Terminal" => array(
                "ProvUserID" => ProvUserID::PROVAUT,
                "HashData" => $this->getHashData($setting),
                "UserID" => $this->getUserId(),
                "ID" => $credential->getTerminalId(),
                "MerchantID" => $credential->getMerchantId(),
            ),
            "Customer" => array(
                "IPAddress" => $this->getIp(),
                "EmailAddress" => $this->getEmail(),
            ),
            "Order" => array(
                "OrderID" => $this->getOrderId(),
            ),
            "Transaction" => array(
                "Type" => $this->getType(),
                "Amount" => $this->getAmount(),
                "CurrencyCode" => $this->getCurrency()->getNumeric(),
                "CardholderPresentCode" => CardholderPresentCode::THREE_D,
                "Secure3D" => array(
                    "AuthenticationCode" => $this->getCavv(),
                    "SecurityLevel" => $this->getEci(),
                    "TxnID" => $this->getXid(),
                    "Md" => $this->getMd(),
                ),
            ),
        );

        if ($this->getInstallment() > 1) {
            $elements["Transaction"]["InstallmentCnt"] = $this->getInstallment();
        }

        return Helper::arrayToXmlString($elements);
    }

    public function getHashData(Setting $setting)
    {
        $this->validate();

        $credential = $setting->getCredential();

        return Helper::getHashData(
            array(
                $this->getOrderId(),
                $credential->getTerminalId(),
                $this->getAmount(),
            ),
            $credential->getProvisionPassword(),
            $credential->getTerminalId()
        );
    }
}