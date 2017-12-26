<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 18/08/2017
 * Time: 16:46
 */

namespace PaymentGateway\VPosGaranti\Request;


use PaymentGateway\ISO4217\Model\Currency;
use PaymentGateway\VPosGaranti\Constant\ProvUserID;
use PaymentGateway\VPosGaranti\Constant\RequestMode;
use PaymentGateway\VPosGaranti\Constant\RequestType;
use PaymentGateway\VPosGaranti\Constant\RequestVersion;
use PaymentGateway\VPosGaranti\Helper\Helper;
use PaymentGateway\VPosGaranti\Helper\Validator;
use PaymentGateway\VPosGaranti\Setting\Setting;

class VoidRequest implements RequestInterface
{

    private $type;
    private $orderId;
    private $userId;
    private $ip;
    private $transactionReference;
    /** @var  Currency */
    private $currency;

    public function __construct()
    {
        $this->type = RequestType::VOID;
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
    public function getTransactionReference()
    {
        return $this->transactionReference;
    }

    /**
     * @param mixed $transactionReference
     */
    public function setTransactionReference($transactionReference)
    {
        $this->transactionReference = $transactionReference;
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

    public function validate()
    {
        Validator::validateIp($this->getIp());
        Validator::validateOrderId($this->getOrderId());
        Validator::validateUserId($this->getUserId());
        Validator::validateAmount($this->getAmount());
        Validator::validateNotEmpty('Transaction Reference', $this->getTransactionReference());
    }

    public function toXmlString(Setting $setting)
    {
        $this->validate();

        $credential = $setting->getCredential();

        $elements = array(
            "Mode" => RequestMode::PROD,
            "Version" => RequestVersion::ZERO_ZERO_ONE,
            "Terminal" => array(
                "ProvUserID" => ProvUserID::PROVRFN,
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
                "Amount" => Helper::amountParser($this->getAmount()),
                "OriginalRetrefNum" => $this->getTransactionReference()
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
                Helper::amountParser($this->getAmount()),
            ),
            $credential->getRefundProvisionPassword(),
            $credential->getTerminalId()
        );
    }
}