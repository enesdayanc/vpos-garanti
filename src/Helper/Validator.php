<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 18/08/2017
 * Time: 10:48
 */

namespace PaymentGateway\VPosGaranti\Helper;


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
}