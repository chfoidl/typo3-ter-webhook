<?php

namespace Sethorax\TYPO3TERWebHook;

use Symfony\Component\HttpFoundation\Request;

class WebhookListener
{
    protected $extKey;

    protected $payload;

    protected $data;

    protected $request;

    protected $jsonResponse;

    protected $uploadService;

    protected $slackNotificationService;


    public function __construct(JsonResponse $jsonResponse, Request $request, UploadService $uploadService, SlackNotificationService $slackNotificationService, string $data)
    {
        $this->data = $data;
        $this->request = $request;
        $this->jsonResponse = $jsonResponse;
        $this->uploadService = $uploadService;
        $this->slackNotificationService = $slackNotificationService;
    }

    public function handleRequest()
    {
        $this->extKey = $this->request->query->get('ext_key');
        $this->payload = json_decode($this->request->request->get('payload'), true);

        if (ConfigUtility::validateConfig()) {
            if ($this->isValidRequest() && $this->pushHasTag()) {
                $this->slackNotificationService->send('Extension *' . $this->extKey . '* from repository *' . $this->payload['repository']['full_name'] . '@' . explode('/', $this->payload['ref'])[2] . '* \nsucessfully uploaded to the <https://typo3.org/extensions/repository/view/' . $this->extKey . '|TYPO3 TER> by ' . $this->payload['head_commit']['author']['username'] . '.');
                if ($this->uploadExtension()) {
                    $this->slackNotificationService->send('Extension *' . $this->extKey . '* from repository *' . $this->payload['repository']['full_name'] . '@' . explode('/', $this->payload['ref'])[2] . '* \nsucessfully uploaded to the <https://typo3.org/extensions/repository/view/' . $this->extKey . '|TYPO3 TER> by ' . $this->payload['head_commit']['author']['username'] . '.');
                    $this->jsonResponse->sendSuccess(StatusMessage::EXT_UPLOADED);
                }
            }
        } else {
            $this->jsonResponse->sendError(StatusMessage::INVALID_CONFIG);
        }
    }

    protected function uploadExtension()
    {
        try {
            $this->uploadService->upload($this->payload['repository']['clone_url'], $this->extKey);
        } catch (\Exception $e) {
            $this->jsonResponse->sendError($e->getMessage());

            return false;
        }

        return true;
    }

    protected function pushHasTag()
    {
        if (!strpos($this->payload['ref'], 'tags')) {
            $this->jsonResponse->sendSuccess(StatusMessage::NO_TAG);

            return false;
        }

        return true;
    }

    protected function isValidRequest()
    {
        if (!isset($this->extKey)) {
            $this->jsonResponse->sendError(StatusMessage::NO_EXTKEY);

            return false;
        }

        if (!is_array($this->payload)) {
            $this->jsonResponse->sendError(StatusMessage::INVALID_PAYLOAD);

            return false;
        }

        if (!$this->isValidHash()) {
            $this->jsonResponse->sendError(StatusMessage::INVALID_HASH);

            return false;
        }

        return true;
    }

    protected function isValidHash()
    {
        list($algorithm, $hash) = explode('=', $this->request->server->get('HTTP_X_HUB_SIGNATURE'));
        $calculatedHash = hash_hmac($algorithm, $this->data, ConfigUtility::getConfig()['authorization']['github']['secret']);

        return $calculatedHash === $hash;
    }
}
