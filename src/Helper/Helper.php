<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 18/08/2017
 * Time: 10:53
 */

namespace PaymentGateway\VPosGaranti\Helper;


use ReflectionClass;

class Helper
{
    public static function getConstants($class)
    {
        $oClass = new ReflectionClass ($class);
        return $oClass->getConstants();
    }
}