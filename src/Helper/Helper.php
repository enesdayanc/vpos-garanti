<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 18/08/2017
 * Time: 10:53
 */

namespace PaymentGateway\VPosGaranti\Helper;


use Exception;
use PaymentGateway\VPosGaranti\Constant\Success;
use PaymentGateway\VPosGaranti\Exception\ValidationException;
use PaymentGateway\VPosGaranti\Response\Response;
use PaymentGateway\VPosGaranti\Setting\Setting;
use ReflectionClass;
use SimpleXMLElement;
use Spatie\ArrayToXml\ArrayToXml;

class Helper
{
    public static function getConstants($class)
    {
        $oClass = new ReflectionClass ($class);
        return $oClass->getConstants();
    }

    public static function arrayToXmlString(array $array)
    {
        return ArrayToXml::convert($array, 'GVPSRequest', true, 'UTF-8');
    }

    public static function getHashData(array $params, $provisionPassword, $terminalId)
    {
        $securityData = self::getSecurityData($provisionPassword, $terminalId);

        $dataString = '';

        foreach ($params as $param) {
            $dataString .= $param;
        }

        $dataString .= $securityData;

        return strtoupper(sha1($dataString));
    }

    private static function getSecurityData($provisionPassword, $terminalId)
    {
        $terminalIdPad = str_pad($terminalId, 9, '0', STR_PAD_LEFT);

        $dataString = $provisionPassword . $terminalIdPad;

        return strtoupper(sha1($dataString));
    }

    public static function getResponseByXML($xml)
    {
        $response = new Response();

        $response->setRawData($xml);

        try {
            $data = new SimpleXMLElement($xml);
        } catch (Exception $exception) {
            throw new ValidationException('Invalid Xml Response', 'INVALID_XML_RESPONSE');
        }

        if (!empty($data->Transaction->Response->ReasonCode)
            && $data->Transaction->Response->ReasonCode == Success::RESPONSE_CODE) {
            $response->setSuccessful(true);
        }

        if (!empty($data->Transaction->AuthCode)) {
            $response->setCode((string)$data->Transaction->AuthCode);
        }

        if (!empty($data->Transaction->Response->ErrorMsg)
            && !empty($data->Transaction->Response->ReasonCode)) {
            $response->setErrorCode((string)$data->Transaction->Response->ReasonCode);
        }

        if (!empty($data->Transaction->Response->ErrorMsg)) {
            $response->setErrorMessage((string)$data->Transaction->Response->ErrorMsg);
        }

        if (!empty($data->Transaction->RetrefNum)) {
            $response->setTransactionReference((string)$data->Transaction->RetrefNum);
        }

        return $response;
    }

    public static function amountParser($amount)
    {
        return (int)number_format($amount, 2, '', '');
    }

    public static function get3DCryptedHash(Setting $setting, $orderId, $amount, $type, $installment)
    {
        $setting->validate();

        $credential = $setting->getCredential();

        return self::getHashData(
            array(
                $credential->getTerminalId(),
                $orderId,
                Helper::amountParser($amount),
                $setting->getThreeDSuccessUrl(),
                $setting->getThreeDFailUrl(),
                $type,
                $installment,
                $credential->getStoreKey(),
            ),
            $credential->getProvisionPassword(),
            $credential->getTerminalId()
        );
    }
}