<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 18/08/2017
 * Time: 16:18
 */

namespace PaymentGateway\VPosGaranti\Request;


use PaymentGateway\VPosGaranti\Constant\RequestType;

class AuthorizeRequest extends PurchaseRequest implements RequestInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->type = RequestType::PRE_AUTH;
    }
}
