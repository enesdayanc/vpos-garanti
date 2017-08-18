<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 18/08/2017
 * Time: 11:04
 */

namespace PaymentGateway\VPosGaranti\Setting;


use PaymentGateway\VPosGaranti\Constant\RequestMode;
use PaymentGateway\VPosGaranti\Constant\StoreType;
use PaymentGateway\VPosGaranti\Exception\NotFoundException;

class GarantiBankasiTest extends Setting
{

    private $host = 'sanalposprovtest.garanti.com.tr';

    public function __construct($storeType)
    {
        $credential = new Credential();

        if ($storeType == StoreType::THREE_D) {
            $credential->setTerminalId('30691297');
        } else {
            throw new NotFoundException('Terminal id not found for store type: ' . $storeType, 'TERMINAL_ID_NOT_FOUND');
        }

        $credential->setMerchantId('7000679');
        $credential->setStoreKey('12345678');
        $credential->setProvisionPassword('123qweASD');
        $credential->setRefundProvisionPassword('123qweASD');


        parent::setCredential($credential);
    }

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