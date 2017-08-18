<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 18/08/2017
 * Time: 11:02
 */

namespace PaymentGateway\VPosGaranti\Constant;


class RequestType
{
    const PRE_AUTH = 'preauth';
    const POST_AUTH = 'postauth';
    const AUTH = 'sales';
    const VOID = 'void';
    const REFUND = 'refund';
}