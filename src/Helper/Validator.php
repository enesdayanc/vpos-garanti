<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 18/08/2017
 * Time: 10:48
 */

namespace PaymentGateway\VPosGaranti\Helper;


use PaymentGateway\VPosGaranti\Constant\BankType;
use PaymentGateway\VPosGaranti\Constant\Currency;
use PaymentGateway\VPosGaranti\Constant\RequestType;
use PaymentGateway\VPosGaranti\Constant\StoreType;
use PaymentGateway\VPosGaranti\Exception\ValidationException;

class Validator
{
    public static function validateNotEmpty($name, $value)
    {
        if (empty($value)) {
            throw new ValidationException("Invalid $name", "INVALID_$name");
        }
    }

    public static function validateStoreType($value)
    {
        if (!in_array($value, Helper::getConstants(StoreType::class))) {
            throw new ValidationException('Invalid Store Type', 'INVALID_STORE_TYPE');
        }
    }

    public static function validateRequestType($value)
    {
        if (!in_array($value, Helper::getConstants(RequestType::class))) {
            throw new ValidationException('Invalid Request Type', 'INVALID_REQUEST_TYPE');
        }
    }


    public static function validateExpiryMonth($value)
    {
        if (!is_string($value) || strlen($value) != 2) {
            throw new ValidationException('Invalid Expiry Month', 'INVALID_EXPIRY_MONTH');
        }
    }

    public static function validateExpiryYear($value)
    {
        if (!is_string($value) || strlen($value) != 2) {
            throw new ValidationException('Invalid Expiry Year', 'INVALID_EXPIRY_YEAR');
        }
    }

    public static function validateCardNumber($value)
    {
        $number = preg_replace('/\D/', '', $value);
        $number_length = strlen($number);
        $parity = $number_length % 2;

        $total = 0;
        for ($i = 0; $i < $number_length; $i++) {
            $digit = $number[$i];
            if ($i % 2 == $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $total += $digit;
        }

        if (!($total % 10 == 0)) {
            throw new ValidationException('Invalid Card Number', 'INVALID_CARD_NUMBER');
        }
    }

    public static function validateCvv($value)
    {
        if (!is_string($value) || !in_array(strlen($value), array(3, 4))) {
            throw new ValidationException('Invalid Cvv', 'INVALID_CVV');
        }
    }

    public static function validateIp($value)
    {
        if (!filter_var($value, FILTER_VALIDATE_IP)) {
            throw new ValidationException('Invalid Ip', 'INVALID_IP');
        }
    }

    public static function validateEmail($value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Invalid Email', 'INVALID_EMAIL');
        }
    }

    public static function validateAmount($value)
    {
        if (!is_numeric($value) || $value <= 0 || strpos(strval($value), ',') !== false) {
            throw new ValidationException('Invalid Amount', 'INVALID_AMOUNT');
        }
    }

    public static function validateInstallment($value)
    {
        if (empty($value) || !is_int($value)) {
            throw new ValidationException('Invalid Installment', 'INVALID_INSTALLMENT');
        }
    }

    public static function validateUserId($value)
    {
        if (empty($value)) {
            throw new ValidationException('Invalid User Id', 'INVALID_USER_ID');
        }
    }

    public static function validateOrderId($value)
    {
        if (empty($value)) {
            throw new ValidationException('Invalid Order Id', 'INVALID_ORDER_ID');
        }
    }

    public static function validateCurrency($value)
    {
        if (!$value instanceof \PaymentGateway\ISO4217\Model\Currency) {
            throw new ValidationException('Invalid Currency Type', 'INVALID_CURRENCY_TYPE');
        }

        $alpha3 = $value->getAlpha3();

        if (!in_array($alpha3, Helper::getConstants(Currency::class))) {
            throw new ValidationException('Invalid Currency', 'INVALID_CURRENCY');
        }
    }

    public static function validateBankType($value)
    {
        if (!in_array($value, Helper::getConstants(BankType::class))) {
            throw new ValidationException('Invalid Bank Type', 'INVALID_BANK_TYPE');
        }
    }
}