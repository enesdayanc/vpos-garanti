<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 21/08/2017
 * Time: 15:10
 */

namespace PaymentGateway\VPosGaranti\Setting;


class GarantiBankasi extends Setting
{
    private $host = 'sanalposprov.garanti.com.tr';

    public function getThreeDPostUrl()
    {
        return 'https://' . $this->host . '/servlet/gt3dengine';
    }

    public function getAuthorizeUrl()
    {
        return 'https://' . $this->host . '/VPServlet';
    }

    public function getCaptureUrl()
    {
        return 'https://' . $this->host . '/VPServlet';
    }

    public function getPurchaseUrl()
    {
        return 'https://' . $this->host . '/VPServlet';
    }

    public function getRefundUrl()
    {
        return 'https://' . $this->host . '/VPServlet';
    }

    public function getVoidUrl()
    {
        return 'https://' . $this->host . '/VPServlet';
    }
}