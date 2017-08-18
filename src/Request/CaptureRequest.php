<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 18/08/2017
 * Time: 16:25
 */

namespace PaymentGateway\VPosGaranti\Request;


use PaymentGateway\ISO4217\Model\Currency;
use PaymentGateway\VPosGaranti\Constant\CardholderPresentCode;
use PaymentGateway\VPosGaranti\Constant\MotoInd;
use PaymentGateway\VPosGaranti\Constant\ProvUserID;
use PaymentGateway\VPosGaranti\Constant\RequestMode;
use PaymentGateway\VPosGaranti\Constant\RequestType;
use PaymentGateway\VPosGaranti\Constant\RequestVersion;
use PaymentGateway\VPosGaranti\Helper\Helper;
use PaymentGateway\VPosGaranti\Helper\Validator;
use PaymentGateway\VPosGaranti\Setting\Setting;

class CaptureRequest implements RequestInterface
{
    private $type;
    private $orderId;
    private $amount;
    /** @var  Currency $currency */
    private $currency;
    private $userId;
    private $ip;

    public function __construct()
    {
        $this->type = RequestType::POST_AUTH;
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

    public function getType()
    {
        return $this->type;
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

    public function validate()
    {
        Validator::validateCurrency($this->getCurrency());
        Validator::validateAmount($this->getAmount());
        Validator::validateOrderId($this->getOrderId());
        Validator::validateUserId($this->getUserId());
        Validator::validateIp($this->getIp());
    }

    public function toXmlString(Setting $setting)
    {
        $this->validate();

        $credential = $setting->getCredential();

        $elements = array(
            "Mode" => RequestMode::PROD,
            "Version" => RequestVersion::ZERO_ONE,
            "Terminal" => array(
                "ProvUserID" => ProvUserID::PROVAUT,
                "HashData" => $this->getHashData($setting),
                "UserID" => $this->getUserId(),
                "ID" => $credential->getTerminalId(),
                "MerchantID" => $credential->getMerchantId(),
            ),
            "Customer" => array(
                "IPAddress" => $this->getIp(),
            ),
            "Order" => array(
                "OrderID" => $this->getOrderId(),
            ),
            "Transaction" => array(
                "Type" => $this->getType(),
                "Amount" => $this->getAmount(),
                "CurrencyCode" => $this->getCurrency()->getNumeric(),
            ),
        );

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