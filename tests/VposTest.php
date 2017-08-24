<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 18/08/2017
 * Time: 10:29
 */

namespace PaymentGateway\VPosGaranti;

use PaymentGateway\ISO4217\ISO4217;
use PaymentGateway\ISO4217\Model\Currency;
use PaymentGateway\VPosGaranti\Constant\StoreType;
use PaymentGateway\VPosGaranti\Exception\ValidationException;
use PaymentGateway\VPosGaranti\Model\Card;
use PaymentGateway\VPosGaranti\Model\ThreeDResponse;
use PaymentGateway\VPosGaranti\Request\AuthorizeRequest;
use PaymentGateway\VPosGaranti\Request\CaptureRequest;
use PaymentGateway\VPosGaranti\Request\PurchaseRequest;
use PaymentGateway\VPosGaranti\Request\RefundRequest;
use PaymentGateway\VPosGaranti\Request\VoidRequest;
use PaymentGateway\VPosGaranti\Response\Response;
use PaymentGateway\VPosGaranti\Setting\GarantiBankasiTest;
use PHPUnit\Framework\TestCase;

class VposTest extends TestCase
{
    /** @var  VPos $vPos */
    protected $vPos;
    /** @var  Card $card */
    protected $card;
    /** @var  Card $threeDCard */
    protected $threeDCard;
    /** @var  Currency $currency */
    protected $currency;

    protected $orderId;
    protected $authorizeOrderId;
    protected $amount;
    protected $userId;
    protected $installment;
    protected $userIp;

    public function setUp()
    {
        $settings = new GarantiBankasiTest(StoreType::THREE_D);

        $settings->setThreeDFailUrl('http://enesdayanc.com/fail');
        $settings->setThreeDSuccessUrl('http://enesdayanc.com/success');
        $settings->setStoreType(StoreType::THREE_D);

        $this->vPos = new VPos($settings);

        $card = new Card();
        $card->setCreditCardNumber("4282209027132016");
        $card->setExpiryMonth('05');
        $card->setExpiryYear('18');
        $card->setCvv('358');
        $card->setFirstName('Enes');
        $card->setLastName('Dayanç');

        $this->card = $card;

        $threeDCard = new Card();
        $threeDCard->setCreditCardNumber("4282209004348015");
        $threeDCard->setExpiryMonth('07');
        $threeDCard->setExpiryYear('19');
        $threeDCard->setCvv('123');
        $threeDCard->setFirstName('Enes');
        $threeDCard->setLastName('Dayanç');

        $this->threeDCard = $threeDCard;

        $iso4217 = new ISO4217();

        $this->currency = $iso4217->getByCode('TRY');

        $this->amount = rand(1, 100);
        $this->orderId = 'MO' . md5(microtime() . rand());
        $this->userId = md5(microtime() . rand());
        $this->installment = rand(1, 3);
        $this->userIp = '192.168.1.1';

    }

    public function testPurchase()
    {
        $purchaseRequest = new PurchaseRequest();

        $purchaseRequest->setCard($this->card);
        $purchaseRequest->setOrderId($this->orderId);
        $purchaseRequest->setAmount($this->amount);
        $purchaseRequest->setCurrency($this->currency);
        $purchaseRequest->setUserId($this->userId);
        $purchaseRequest->setInstallment($this->installment);
        $purchaseRequest->setIp('198.168.1.1');
        $purchaseRequest->setEmail('enes.dayanc@modanisa.com.tr');

        $response = $this->vPos->purchase($purchaseRequest);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());

        return array(
            'orderId' => $this->orderId,
            'amount' => $this->amount,
            'userId' => $this->userId,
            'refNumber' => $response->getTransactionReference(),
        );
    }

    public function testPurchaseForVoid()
    {
        $purchaseRequest = new PurchaseRequest();

        $purchaseRequest->setCard($this->card);
        $purchaseRequest->setOrderId($this->orderId);
        $purchaseRequest->setAmount($this->amount);
        $purchaseRequest->setCurrency($this->currency);
        $purchaseRequest->setUserId($this->userId);
        $purchaseRequest->setInstallment($this->installment);
        $purchaseRequest->setIp('198.168.1.1');
        $purchaseRequest->setEmail('enes.dayanc@modanisa.com.tr');

        $response = $this->vPos->purchase($purchaseRequest);


        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());

        return array(
            'orderId' => $this->orderId,
            'amount' => $this->amount,
            'userId' => $this->userId,
            'refNumber' => $response->getTransactionReference(),
        );
    }

    public function testPurchaseFailAmount()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Amount');

        $purchaseRequest = new PurchaseRequest();

        $purchaseRequest->setCard($this->card);
        $purchaseRequest->setOrderId($this->orderId);
        $purchaseRequest->setAmount(0);
        $purchaseRequest->setCurrency($this->currency);
        $purchaseRequest->setUserId($this->userId);
        $purchaseRequest->setInstallment($this->installment);
        $purchaseRequest->setIp('198.168.1.1');
        $purchaseRequest->setEmail('enes.dayanc@modanisa.com.tr');

        $this->vPos->purchase($purchaseRequest);
    }

    public function testPurchaseFailInstallment()
    {
        $purchaseRequest = new PurchaseRequest();

        $purchaseRequest->setCard($this->card);
        $purchaseRequest->setOrderId($this->orderId);
        $purchaseRequest->setAmount($this->amount);
        $purchaseRequest->setCurrency($this->currency);
        $purchaseRequest->setUserId($this->userId);
        $purchaseRequest->setInstallment(50);
        $purchaseRequest->setIp('198.168.1.1');
        $purchaseRequest->setEmail('enes.dayanc@modanisa.com.tr');

        $response = $this->vPos->purchase($purchaseRequest);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('58', $response->getErrorCode());
    }


    public function testAuthorize()
    {
        $authorizeRequest = new AuthorizeRequest();

        $authorizeRequest->setCard($this->card);
        $authorizeRequest->setOrderId($this->orderId);
        $authorizeRequest->setAmount($this->amount);
        $authorizeRequest->setCurrency($this->currency);
        $authorizeRequest->setUserId($this->userId);
        $authorizeRequest->setInstallment($this->installment);
        $authorizeRequest->setIp('198.168.1.1');
        $authorizeRequest->setEmail('enes.dayanc@modanisa.com.tr');

        $response = $this->vPos->authorize($authorizeRequest);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());

        return array(
            'orderId' => $this->orderId,
            'amount' => $this->amount,
            'userId' => $this->userId,
        );
    }

    public function testAuthorizeFail()
    {
        $authorizeRequest = new AuthorizeRequest();

        $authorizeRequest->setCard($this->card);
        $authorizeRequest->setOrderId(1);
        $authorizeRequest->setAmount($this->amount);
        $authorizeRequest->setCurrency($this->currency);
        $authorizeRequest->setUserId($this->userId);
        $authorizeRequest->setInstallment($this->installment);
        $authorizeRequest->setIp('198.168.1.1');
        $authorizeRequest->setEmail('enes.dayanc@modanisa.com.tr');

        $response = $this->vPos->authorize($authorizeRequest);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('0104', $response->getErrorCode());
    }


    /**
     * @depends testAuthorize
     * @param $params
     */
    public function testCapture($params)
    {
        $captureRequest = new CaptureRequest();

        $captureRequest->setOrderId($params['orderId']);
        $captureRequest->setAmount($params['amount']);
        $captureRequest->setCurrency($this->currency);
        $captureRequest->setUserId($params['userId']);
        $captureRequest->setIp($this->userIp);

        $response = $this->vPos->capture($captureRequest);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
    }


    public function testCaptureFail()
    {
        $captureRequest = new CaptureRequest();

        $captureRequest->setOrderId(1);
        $captureRequest->setAmount($this->amount);
        $captureRequest->setCurrency($this->currency);
        $captureRequest->setUserId(1);
        $captureRequest->setIp($this->userIp);

        $response = $this->vPos->capture($captureRequest);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('0205', $response->getErrorCode());
    }


    /**
     * @depends testPurchase
     * @param $params
     */
    public function testRefund($params)
    {
        $refundRequest = new RefundRequest();
        $refundRequest->setCurrency($this->currency);
        $refundRequest->setAmount($params['amount'] / 2);
        $refundRequest->setOrderId($params['orderId']);
        $refundRequest->setIp($this->userIp);
        $refundRequest->setUserId($params['userId']);
        $refundRequest->setTransactionReference($params['refNumber']);

        $response = $this->vPos->refund($refundRequest);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());

        return $params;
    }


    /**
     * @depends testPurchaseForVoid
     * @param $params
     */
    public function testVoid($params)
    {
        $voidRequest = new VoidRequest();
        $voidRequest->setAmount($params['amount']);
        $voidRequest->setOrderId($params['orderId']);
        $voidRequest->setIp($this->userIp);
        $voidRequest->setUserId($params['userId']);
        $voidRequest->setTransactionReference($params['refNumber']);

        $response = $this->vPos->void($voidRequest);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
    }

    /**
     * @depends testPurchaseForVoid
     * @param $params
     */
    public function testVoidFail($params)
    {
        $voidRequest = new VoidRequest();
        $voidRequest->setAmount($params['amount']);
        $voidRequest->setOrderId($params['orderId']);
        $voidRequest->setIp($this->userIp);
        $voidRequest->setUserId($params['userId']);
        $voidRequest->setTransactionReference($params['refNumber']);

        $response = $this->vPos->void($voidRequest);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('0210', $response->getErrorCode());
    }

    public function test3DAuthorizeFormCreate()
    {
        $authorizeRequest = new AuthorizeRequest();

        $authorizeRequest->setCard($this->threeDCard);
        $authorizeRequest->setOrderId($this->orderId);
        $authorizeRequest->setAmount($this->amount);
        $authorizeRequest->setCurrency($this->currency);
        $authorizeRequest->setUserId($this->userId);
        $authorizeRequest->setInstallment($this->installment);
        $authorizeRequest->setIp('198.168.1.1');
        $authorizeRequest->setEmail('enes.dayanc@modanisa.com.tr');

        $response = $this->vPos->authorize3D($authorizeRequest);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertInternalType('array', $response->getRedirectData());
    }

    public function test3DPurchaseFormCreate()
    {
        $purchaseRequest = new PurchaseRequest();

        $purchaseRequest->setCard($this->threeDCard);
        $purchaseRequest->setOrderId($this->orderId);
        $purchaseRequest->setAmount($this->amount);
        $purchaseRequest->setCurrency($this->currency);
        $purchaseRequest->setUserId($this->userId);
        $purchaseRequest->setInstallment($this->installment);
        $purchaseRequest->setIp('198.168.1.1');
        $purchaseRequest->setEmail('enes.dayanc@modanisa.com.tr');

        $response = $this->vPos->purchase3D($purchaseRequest);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertInternalType('array', $response->getRedirectData());
    }

    public function test3DResponseHandleSignatureFail()
    {

        $iso4217 = new ISO4217();

        $threeDResponse = new ThreeDResponse();

        $threeDResponse->setTransId('MOd9edcf9207c65a1f587748445060e774');
        $threeDResponse->setClientId('30691297');
        $threeDResponse->setOrderId('MOd9edcf9207c65a1f587748445060e774');
        $threeDResponse->setAuthCode('');
        $threeDResponse->setProcReturnCode('');
        $threeDResponse->setResponse('');
        $threeDResponse->setMdStatus('1');
        $threeDResponse->setCavv('jCm0m+u/0hUfAREHBAMBcfN+pSo=');
        $threeDResponse->setEci('02');
        $threeDResponse->setMd('1i9hORO4ozU+3RJv2fyoP00zslosUN1hMF4tHWxYIS1O3f6PtLZOGLfbVuEDZIvKOUVzLURVSo96QEG1hUsj+3U+kCUuh8sUcEfrQznQ+sl8BnNjSYRaSkqY4x63+GrryJqVgta2xwFhKJUQQG7+mIYayYL/7sbRK3qkfZYt8ekhUkSDBD+VjQ==');
        $threeDResponse->setRnd('zLxb4t9zCzbh1hFFC2cq');
        $threeDResponse->setHash('DIRTY_HASH');
        $threeDResponse->setHashParams('clientid:oid:authcode:procreturncode:response:mdstatus:cavv:eci:md:rnd:');
        $threeDResponse->setHashParamsVal('30691297MOd9edcf9207c65a1f587748445060e7741jCm0m+u/0hUfAREHBAMBcfN+pSo=021i9hORO4ozU+3RJv2fyoP00zslosUN1hMF4tHWxYIS1O3f6PtLZOGLfbVuEDZIvKOUVzLURVSo96QEG1hUsj+3U+kCUuh8sUcEfrQznQ+sl8BnNjSYRaSkqY4x63+GrryJqVgta2xwFhKJUQQG7+mIYayYL/7sbRK3qkfZYt8ekhUkSDBD+VjQ==zLxb4t9zCzbh1hFFC2cq');
        $threeDResponse->setUserIp('198.168.1.1');
        $threeDResponse->setUserEmail('enes.dayanc@modanisa.com.tr');
        $threeDResponse->setType('sales');
        $threeDResponse->setAmount(9300);
        $threeDResponse->setInstallment(1);
        $threeDResponse->setCurrency($iso4217->getByCode('949'));
        $threeDResponse->setXid('RszfrwEYe/8xb7rnrPuh6C9pZSQ=');
        $threeDResponse->setVersion('2.0');
        $threeDResponse->setUserId('0a5bfc66175d43b15e6c8a81bc0ca89d');

        $response = $this->vPos->handle3DResponse($threeDResponse);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('Invalid Signature', $response->getErrorMessage());
    }
}