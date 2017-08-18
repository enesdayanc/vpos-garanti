<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 18/08/2017
 * Time: 11:22
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
use PaymentGateway\VPosGaranti\Model\Card;
use PaymentGateway\VPosGaranti\Setting\Credential;
use PaymentGateway\VPosGaranti\Setting\Setting;

class PurchaseRequest implements RequestInterface
{
    protected $type;

    private $email;
    private $ip;
    /** @var  Card $card */
    private $card;
    private $orderId;
    /** @var  Currency $currency */
    private $currency;
    private $installment;
    private $amount;
    /** @var  bool */
    private $mailOrder;
    private $userId;

    /**
     * PurchaseRequest constructor.
     */
    public function __construct()
    {
        $this->type = RequestType::AUTH;
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
     * @return Card
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * @param Card $card
     */
    public function setCard(Card $card)
    {
        $this->card = $card;
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
    public function getCurrency()
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
     * @return bool
     */
    public function isMailOrder()
    {
        return $this->mailOrder;
    }

    /**
     * @param bool $mailOrder
     */
    public function setMailOrder(bool $mailOrder)
    {
        $this->mailOrder = $mailOrder;
    }

    public function getType()
    {
        return $this->type;
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

    public function validate()
    {
        Validator::validateNotEmpty('card', $this->getCard());
        $this->getCard()->validate();
        Validator::validateIp($this->getIp());
        Validator::validateEmail($this->getEmail());
        Validator::validateUserId($this->getUserId());
        Validator::validateAmount($this->getAmount());
        Validator::validateInstallment($this->getInstallment());
        Validator::validateCurrency($this->getCurrency());
        Validator::validateOrderId($this->getOrderId());
    }

    public function toXmlString(Setting $setting)
    {
        $this->validate();

        $credential = $setting->getCredential();

        $card = $this->getCard();

        $elements = array(
            "Mode" => RequestMode::PROD,
            "Version" => RequestVersion::ZERO_ZERO_ONE,
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
            "Card" => array(
                "Number" => $card->getCreditCardNumber(),
                "ExpireDate" => $card->getExpireDate(),
                "CVV2" => $card->getCvv(),
            ),
            "Order" => array(
                "OrderID" => $this->getOrderId(),
            ),
            "Transaction" => array(
                "Type" => $this->getType(),
                "Amount" => $this->getAmount(),
                "InstallmentCnt" => $this->getInstallment(),
                "CurrencyCode" => $this->getCurrency()->getNumeric(),
                "CardholderPresentCode" => CardholderPresentCode::NORMAL,
                "MotoInd" => $this->isMailOrder() ? MotoInd::MAIL_ORDER : MotoInd::E_COMMERCE,
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
                $this->getCard()->getCreditCardNumber(),
                $this->getAmount(),
            ),
            $credential->getProvisionPassword(),
            $credential->getTerminalId()
        );
    }
}