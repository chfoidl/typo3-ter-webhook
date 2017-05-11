<?php

namespace Sethorax\TYPO3TERWebHook;

use Symfony\Component\HttpFoundation\Response;

class JsonResponse
{
    protected $response;

    public function __construct(Response $response = null)
    {
        if (!isset($response)) {
            $response = new Response();
        }

        $this->response = $response;
    }

    public function sendError($data)
    {
        $this->response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);

        $this->sendResponse('error', $data);
    }

    public function sendSuccess($data)
    {
        $this->response->setStatusCode(Response::HTTP_OK);

        $this->sendResponse('success', $data);
    }

    protected function sendResponse(string $type, $data)
    {
        $this->response->headers->set('Content-Type', 'application/json;charset=utf-8');
        $this->response->setContent(json_encode([
            'status' => $type,
            'code' => http_response_code(),
            'message' => $data
        ]));

        $this->response->send();
    }
}