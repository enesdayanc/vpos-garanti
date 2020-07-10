<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 18/08/2017
 * Time: 14:40
 */

namespace PaymentGateway\VPosGaranti;


use Exception;
use GuzzleHttp\Client;
use PaymentGateway\VPosGaranti\Exception\CurlException;
use PaymentGateway\VPosGaranti\Helper\Helper;
use PaymentGateway\VPosGaranti\Request\RequestInterface;
use PaymentGateway\VPosGaranti\Setting\Setting;

class HttpClient
{
    private $setting;
    private $timeout = 20;

    /**
     * HttpClient constructor.
     * @param $setting
     */
    public function __construct(Setting $setting)
    {
        $this->setting = $setting;
    }

    /**
     * @param RequestInterface $requestElements
     * @param $url
     * @return Response\Response
     * @throws CurlException
     */
    public function send(RequestInterface $requestElements, $url)
    {
        $documentString = $requestElements->toXmlString($this->setting);

        $client = new Client();

        try {
            $clientResponse = $client->post($url, [
                'timeout' => $this->timeout,
                'form_params' => [
                    'data' => $documentString,
                ]
            ]);
        } catch (Exception $exception) {
            throw new CurlException('Connection Error', $exception->getMessage());
        }

        return Helper::getResponseByXML($clientResponse->getBody()->getContents(), $documentString);
    }
}
