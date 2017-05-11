<?php

require_once('../vendor/autoload.php');

use Sethorax\TYPO3TERWebHook\JsonResponse;
use Sethorax\TYPO3TERWebHook\SlackNotificationService;
use Sethorax\TYPO3TERWebHook\UploadService;
use Sethorax\TYPO3TERWebHook\WebhookListener;
use Symfony\Component\HttpFoundation\Request;

$data = ('cli' === php_sapi_name()) ? file_get_contents('php://stdin') : file_get_contents('php://input');
$request = Request::createFromGlobals();
$jsonResponse = new JsonResponse();
$slackNotificationService = new SlackNotificationService();
$uploadService = new UploadService();

$webhookListener = new WebhookListener($jsonResponse, $request, $uploadService, $slackNotificationService, $data);
$webhookListener->handleRequest();
