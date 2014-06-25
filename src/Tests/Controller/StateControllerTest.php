<?php
/**
 * Copyright (c) Steven Nance <steven@devtrw.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Steven Nance <steven@devtrw.com>
 */
namespace Devtrw\StateBridgeBundle\Tests\Controller;

use Devtrw\StateBridgeBundle\Controller\StateController;
use Devtrw\StateBridgeBundle\StateBridge;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

/**
 * Class StateControllerTest
 *
 * @author  Steven Nance <steven@devtrw.com>
 * @package Devtrw\StateBridgeBundle\Tests\Controller
 * @group   functional
 */
class StateControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $jsonpCallback = 'someTestCallbackFn';
    /**
     * @var StateBridge|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stateBridgeMock;
    /**
     * @var array
     */
    private $stateConfigMock;

    public function setUp()
    {
        $this->stateConfigMock = [
            'foo' => ['bar' => 'baz'],
            'baz' => ['bar' => 'foo']
        ];
        $this->stateBridgeMock = $this->getMockBuilder(StateBridge::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBridgedState'])
            ->getMock();
    }

    public function testExceptionOnUnsupportedMediaType()
    {
        $this->setExpectedException(UnsupportedMediaTypeHttpException::class);
        $this->assertReturnType('html');
    }

    public function testJsonFormatIsCorrectlyHandled()
    {
        $response = $this->assertReturnType('json', JsonResponse::class);
        $this->assertResponseJsonFormattedCorrectly('json', $response->getContent());
    }

    public function testJsonpFormatIsCorrectlyHandled()
    {
        $response         = $this->assertReturnType('jsonp', JsonResponse::class);
        $callbackFnLength = strlen($this->jsonpCallback);
        $responseContent  = $response->getContent();
        $callbackFn       = substr($responseContent, 0, $callbackFnLength);

        $this->assertEquals(
            $this->jsonpCallback,
            $callbackFn,
            'The JSONP response should set the callback to the value passed into the StateController constructor'
        );

        $responseJson = substr($responseContent, $callbackFnLength + 1, -2);
        $this->assertResponseJsonFormattedCorrectly('jsonp', $responseJson);
    }

    private function assertResponseJsonFormattedCorrectly($format, $jsonString)
    {
        $responseArray = json_decode($jsonString, true);
        $this->assertNotNull(
            $responseArray,
            sprintf('JSON response for the "%s" format should be valid JSON. Actual: %s', $format, $jsonString)
        );
        $this->assertEquals(
            ['states' => [$this->stateConfigMock]],
            $responseArray,
            sprintf(
                'The content of JSON responses for the "%s" format should contain the state returned from the ' .
                'StateBridge',
                $format
            )
        );
    }

    /**
     * @param string      $requestFormat
     * @param string|null $expectedResponseInstance
     *
     * @return Response|JsonResponse
     * @author Steven Nance <steven@devtrw.com>
     */
    private function assertReturnType($requestFormat, $expectedResponseInstance = null)
    {
        $request = new Request();
        $request->setRequestFormat($requestFormat);
        $response = $this->buildController('foo')->indexAction('foo', $request);
        $this->assertInstanceOf($expectedResponseInstance, $response);

        return $response;
    }

    /**
     * @param string   $stateName
     * @param int|null $stateId
     *
     * @return StateController
     * @author Steven Nance <steven@devtrw.com>
     */
    private function buildController($stateName, $stateId = null)
    {
        $this->stateBridgeMock
            ->expects($this->once())
            ->method('getBridgedState')
            ->with($stateName, $stateId)
            ->will($this->returnValue($this->stateConfigMock));

        return new StateController($this->stateBridgeMock, $this->jsonpCallback);
    }
}

