<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 22.11.2017
 * Time: 14:31
 */

namespace PaymentGateway\VPosGaranti\Setting;


class MockBank extends Setting
{
    /** @var  string */
    private $host;

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host)
    {
        $this->host = $host;
    }

    public function getThreeDPostUrl()
    {
        return $this->host . "/three-d-post";
    }

    public function getAuthorizeUrl()
    {
        return $this->host . "/authorize";
    }

    public function getCaptureUrl()
    {
        return $this->host . "/capture";
    }

    public function getPurchaseUrl()
    {
        return $this->host . "/purchase";
    }

    public function getRefundUrl()
    {
        return $this->host . "/refund";
    }

    public function getVoidUrl()
    {
        return $this->host . "/void";
    }
}
