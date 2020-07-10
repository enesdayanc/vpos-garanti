<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 18/08/2017
 * Time: 10:53
 */

namespace PaymentGateway\VPosGaranti\Helper;


use Exception;
use PaymentGateway\ISO4217\ISO4217;
use PaymentGateway\VPosGaranti\Constant\BankType;
use PaymentGateway\VPosGaranti\Constant\Success;
use PaymentGateway\VPosGaranti\Exception\NotFoundException;
use PaymentGateway\VPosGaranti\Exception\ValidationException;
use PaymentGateway\VPosGaranti\Model\ThreeDResponse;
use PaymentGateway\VPosGaranti\Response\Response;
use PaymentGateway\VPosGaranti\Setting\GarantiBankasi;
use PaymentGateway\VPosGaranti\Setting\GarantiBankasiTest;
use PaymentGateway\VPosGaranti\Setting\MockBank;
use PaymentGateway\VPosGaranti\Setting\Setting;
use ReflectionClass;
use SimpleXMLElement;
use Spatie\ArrayToXml\ArrayToXml;

class Helper
{
    public static function getConstants($class)
    {
        $oClass = new ReflectionClass($class);
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

    public static function getResponseByXML($xml, $requestRawData)
    {
        $response = new Response();

        $response->setRawData($xml);
        $response->setRequestRawData($requestRawData);

        $data = @simplexml_load_string($xml);

        if (empty($data)) {
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

    public static function getSettingClassByBankTypeAndStoreType($bankType, $storeType)
    {
        Validator::validateBankType($bankType);
        Validator::validateStoreType($storeType);

        switch ($bankType) {
            case BankType::GARANTI_BANKASI:
                $setting = new GarantiBankasi();
                break;
            case BankType::GARANTI_BANKASI_TEST:
                $setting = new GarantiBankasiTest($storeType);
                break;
            case BankType::MOCKBANK:
                $setting = new MockBank();
                break;
        }

        if (!isset($setting) || !$setting instanceof Setting) {
            $userMessage = $bankType . ' not found';
            $internalMessage = 'BANK_TYPE_NOT_FOUND';
            throw new NotFoundException($userMessage, $internalMessage);
        }

        return $setting;
    }

    public static function maskValue($value, $takeStart = 0, $takeStop = 0, $maskingCharacter = '*')
    {
        return substr($value, $takeStart, $takeStop) . str_repeat($maskingCharacter, strlen($value) - ($takeStop - $takeStart));
    }

    public static function getValueFromArray(array $array, $key, $default = null)
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        return $default;
    }

    /**
     * @param array $request
     * @return ThreeDResponse
     */
    public static function getThreeDResponseFromRequest(array $request)
    {
        $iso4217 = new ISO4217();

        $threeDResponse = new ThreeDResponse();

        $threeDResponse->setTransId(self::getValueFromArray($request, 'transid'));
        $threeDResponse->setClientId(self::getValueFromArray($request, 'clientid'));
        $threeDResponse->setOrderId(self::getValueFromArray($request, 'orderid'));
        $threeDResponse->setAuthCode(self::getValueFromArray($request, 'authcode'));
        $threeDResponse->setProcReturnCode(self::getValueFromArray($request, 'procreturncode'));
        $threeDResponse->setResponse(self::getValueFromArray($request, 'response'));
        $threeDResponse->setMdStatus(self::getValueFromArray($request, 'mdstatus'));
        $threeDResponse->setCavv(self::getValueFromArray($request, 'cavv'));
        $threeDResponse->setEci(self::getValueFromArray($request, 'eci'));
        $threeDResponse->setMd(self::getValueFromArray($request, 'md'));
        $threeDResponse->setRnd(self::getValueFromArray($request, 'rnd'));
        $threeDResponse->setHash(self::getValueFromArray($request, 'hash'));
        $threeDResponse->setHashParams(self::getValueFromArray($request, 'hashparams'));
        $threeDResponse->setHashParamsVal(self::getValueFromArray($request, 'hashparamsval'));
        $threeDResponse->setType(self::getValueFromArray($request, 'txntype'));
        $threeDResponse->setAmount(self::getValueFromArray($request, 'txnamount'));
        $threeDResponse->setInstallment((int)self::getValueFromArray($request, 'txninstallmentcount'));
        $threeDResponse->setCurrency($iso4217->getByCode(self::getValueFromArray($request, 'txncurrencycode')));
        $threeDResponse->setXid(self::getValueFromArray($request, 'xid'));
        $threeDResponse->setVersion(self::getValueFromArray($request, 'version'));
        $threeDResponse->setUserIp(self::getValueFromArray($request, 'userip'));
        $threeDResponse->setUserEmail(self::getValueFromArray($request, 'useremail'));
        $threeDResponse->setUserId(self::getValueFromArray($request, 'userid'));

        return $threeDResponse;
    }
}
