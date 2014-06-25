<?php
/**
 * Copyright (c) Steven Nance <steven@devtrw.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Steven Nance <steven@devtrw.com>
 */
namespace Devtrw\StateBridgeBundle\Controller;

use Devtrw\StateBridgeBundle\StateBridge;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

/**
 * Class StateController
 *
 * @author  Steven Nance <steven@devtrw.com>
 * @package Repx\TrackBundle\Controller
 */
class StateController
{
    private $jsonpCallbackFn;
    /**
     * @var \Devtrw\StateBridgeBundle\StateBridge
     */
    private $stateBridge;

    /**
     * @param StateBridge $stateBridge
     * @param string      $jsonpCallbackFn The function to set for JSONP responses.
     */
    public function __construct(StateBridge $stateBridge, $jsonpCallbackFn)
    {
        $this->stateBridge = $stateBridge;
        $this->jsonpCallbackFn = $jsonpCallbackFn;
    }

    public function indexAction($name, Request $request, $id = null)
    {
        $states = [
            $this->stateBridge->getBridgedState($name, $id)
        ];

        return $this->buildResponse($states, $request);
    }

    private function buildResponse(array $states, Request $request)
    {
        $responseArray = ['states' => $states];
        $requestedFormat = $request->getRequestFormat('json');

        switch ($requestedFormat) {
            case 'json':
                $response = new JsonResponse($responseArray);
                break;

            case 'jsonp':
                $response = new JsonResponse($responseArray);
                $response->setCallback($this->jsonpCallbackFn);
                break;

            default:
                $errorMessage = sprintf('The response type "%s" is not currently supported', $requestedFormat);
                throw new UnsupportedMediaTypeHttpException($errorMessage);
        }

        return $response;
    }
}
