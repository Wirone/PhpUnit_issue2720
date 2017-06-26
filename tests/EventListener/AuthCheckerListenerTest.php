<?php

namespace App\Tests\EventListener;

use App\EventListener\AuthCheckerListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use PHPUnit\Framework\TestCase;

/**
 * AuthCheckerListenerTest
 *
 * @author Grzegorz Korba <wirone@gmail.com>
 */
class AuthCheckerListenerTest extends TestCase
{
    /**
     * @param $isXmlHttpRequest
     * @param string $exceptionClass
     * @param array|null $responseBody
     * @param int|null $responseStatusCode
     *
     * @dataProvider onCoreExceptionData
     */
    public function testOnCoreExceptionHandler(
        $isXmlHttpRequest,
        $exceptionClass,
        $responseBody = null,
        $responseStatusCode = null
    ) {
        $httpKernel = $this->createMock(HttpKernelInterface::class);
        $authCheckerListener = new AuthCheckerListener();

        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['isXmlHttpRequest'])
            ->getMock();
        $request
            ->expects($this->once())
            ->method('isXmlHttpRequest')
            ->willReturn($isXmlHttpRequest);

        $exception = $this->createMock($exceptionClass);

        /** @var GetResponseForExceptionEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder(GetResponseForExceptionEvent::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([$httpKernel, $request, HttpKernelInterface::MASTER_REQUEST, $exception])
            ->enableProxyingToOriginalMethods()
            // Commenting this line will rise error described in https://github.com/sebastianbergmann/phpunit/issues/2720
            ->setMethods(['getException', 'getRequest', 'setResponse', 'getResponse'])
            ->getMock();

        $event->expects($this->once())->method('getException');
        $event->expects($this->once())->method('getRequest');

        $authCheckerListener->onCoreException($event);
        $response = $event->getResponse();

        if (!empty($responseBody)) {
            $this->assertEquals($responseBody, json_decode($response->getContent(), true));
            $this->assertEquals($responseStatusCode, $response->getStatusCode());
        } else {
            $this->assertEmpty($event->getResponse());
        }
    }

    /**
     * Defines behavior for {@see testOnCoreExceptionHandler}:
     * - is XmlHTTPRequest?
     * - expected exception class
     * - expected response body
     * - expected response status code
     * @return array
     */
    public function onCoreExceptionData()
    {
        return [
            [false, \Exception::class, null, null],
            [true, \Exception::class, null, null],
            [true, AuthenticationException::class, ['need_to_login' => true], 403],
            [true, AccessDeniedException::class, ['access_denied' => true], 403],
        ];
    }
}
