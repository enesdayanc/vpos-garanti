<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 18/08/2017
 * Time: 10:28
 */

namespace PaymentGateway\VPosGaranti;

use PaymentGateway\VPosGaranti\Model\ThreeDResponse;
use PaymentGateway\VPosGaranti\Request\AuthorizeRequest;
use PaymentGateway\VPosGaranti\Request\CaptureRequest;
use PaymentGateway\VPosGaranti\Request\PurchaseRequest;
use PaymentGateway\VPosGaranti\Request\RefundRequest;
use PaymentGateway\VPosGaranti\Request\RequestInterface;
use PaymentGateway\VPosGaranti\Request\VoidRequest;
use PaymentGateway\VPosGaranti\Response\Response;
use PaymentGateway\VPosGaranti\Setting\Setting;

class VPos
{
    /** @var  Setting $setting */
    private $setting;

    public function __construct(Setting $setting)
    {
        $this->setting = $setting;
        $this->setting->validate();
    }

    public function authorize(AuthorizeRequest $authorizeRequest)
    {
        return $this->send($authorizeRequest, $this->setting->getAuthorizeUrl());
    }

    public function capture(CaptureRequest $captureRequest)
    {
        return $this->send($captureRequest, $this->setting->getCaptureUrl());
    }

    public function purchase(PurchaseRequest $purchaseRequest)
    {
        return $this->send($purchaseRequest, $this->setting->getPurchaseUrl());
    }

    public function refund(RefundRequest $refundRequest)
    {
        return $this->send($refundRequest, $this->setting->getRefundUrl());
    }

    public function void(VoidRequest $voidRequest)
    {
        return $this->send($voidRequest, $this->setting->getVoidUrl());
    }

    public function authorize3D(AuthorizeRequest $authorizeRequest)
    {

        $redirectForm = $authorizeRequest->get3DRedirectForm($this->setting);

        $response = new Response();

        $response->setIsRedirect(true);
        $response->setRedirectMethod($redirectForm->getMethod());
        $response->setRedirectUrl($redirectForm->getAction());
        $response->setRedirectData($redirectForm->getParameters());

        return $response;
    }

    public function purchase3D(PurchaseRequest $purchaseRequest)
    {
        $redirectForm = $purchaseRequest->get3DRedirectForm($this->setting);

        $response = new Response();

        $response->setIsRedirect(true);
        $response->setRedirectMethod($redirectForm->getMethod());
        $response->setRedirectUrl($redirectForm->getAction());
        $response->setRedirectData($redirectForm->getParameters());

        return $response;
    }

    /**
     * @param RequestInterface $requestElements
     * @param $url
     * @return Response
     */
    private function send(RequestInterface $requestElements, $url)
    {
        $httpClient = new HttpClient($this->setting);

        return $httpClient->send($requestElements, $url);
    }

    public function handle3DResponse(ThreeDResponse $threeDResponse, $orderId)
    {
        return $threeDResponse->getResponseClass($this->setting, $orderId);
    }

    /**
     * @return Setting
     */
    public function getSetting(): Setting
    {
        return $this->setting;
    }
}
