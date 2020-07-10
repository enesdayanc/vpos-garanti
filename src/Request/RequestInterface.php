<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 08/08/2017
 * Time: 13:56
 */

namespace PaymentGateway\VPosGaranti\Request;


use PaymentGateway\VPosGaranti\Setting\Credential;
use PaymentGateway\VPosGaranti\Setting\Setting;

interface RequestInterface
{
    public function getType();

    public function validate();

    public function toXmlString(Setting $setting);

    public function getHashData(Setting $setting);
}
