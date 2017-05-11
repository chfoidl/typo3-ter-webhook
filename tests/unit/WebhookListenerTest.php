<?php

namespace Sethorax\TYPO3TERWebHook\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sethorax\TYPO3TERWebHook\JsonResponse;
use Sethorax\TYPO3TERWebHook\StatusMessage;
use Sethorax\TYPO3TERWebHook\UploadService;
use Sethorax\TYPO3TERWebHook\SlackNotificationService;
use Sethorax\TYPO3TERWebHook\WebhookListener;
use Symfony\Component\HttpFoundation\Request;


class WebhookListenerTest extends TestCase
{
    protected $configPath;

    protected $existingConfig = false;

    protected $mockedResponse;

    protected $mockedUploadService;

    protected $mockedSlackNotificationService;

    protected $payload;

    public function setUp()
    {
        $this->configPath = __DIR__ . '/../../config.yml';

        $bakFile = str_replace('config.yml', 'config.yml.bak', $this->configPath);
        rename($this->configPath, $bakFile);
        copy(__DIR__ . '/../fixtures/config.yml', $this->configPath);
        $this->configPath = $bakFile;

        $this->mockedResponse = $this->getMockBuilder(JsonResponse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockedUploadService = $this->getMockBuilder(UploadService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockedSlackNotificationService = $this->getMockBuilder(SlackNotificationService::class)
            ->disableOriginalConstructor()
            ->getMock();


        $this->payload = [
            'payload' => json_encode([
                'ref' => 'ref/tags/1.0.0',
                'repository' => [
                    'clone_url' => 'http://www.example.com',
                    'full_name' => 'Johndoe/Myextension'
                ],
                'head_commit' => [
                    'author' => [
                        'username' => 'johndoe'
                    ]
                ]
            ])
        ];
    }

    public function tearDown()
    {
        rename($this->configPath, str_replace('config.yml.bak', 'config.yml', $this->configPath));
    }

    public function testEmtpyRequest()
    {
        $this->mockedResponse->expects($this->once())
            ->method('sendError')
            ->with($this->equalTo(StatusMessage::NO_EXTKEY));

        $request = Request::create('/', 'GET');

        $listener = new WebhookListener($this->mockedResponse, $request, $this->mockedUploadService, $this->mockedSlackNotificationService, '');
        $listener->handleRequest();
    }

    public function testEmptyPayload()
    {
        $this->mockedResponse->expects($this->once())
            ->method('sendError')
            ->with($this->equalTo(StatusMessage::INVALID_PAYLOAD));

        $request = Request::create('/?ext_key=my_ext', 'POST');

        $listener = new WebhookListener($this->mockedResponse, $request, $this->mockedUploadService, $this->mockedSlackNotificationService, '');
        $listener->handleRequest();
    }

    public function testInvalidHash()
    {
        $this->mockedResponse->expects($this->once())
            ->method('sendError')
            ->with($this->equalTo(StatusMessage::INVALID_HASH));

        $request = Request::create('/?ext_key=my_ext', 'POST', $this->payload, [], [], ['HTTP_X_HUB_SIGNATURE' => 'sha1=abc123']);

        $listener = new WebhookListener($this->mockedResponse, $request, $this->mockedUploadService, $this->mockedSlackNotificationService, 'abc');
        $listener->handleRequest();
    }

    public function testUpload() {
        $this->mockedResponse->expects($this->once())
            ->method('sendSuccess')
            ->with($this->equalTo(StatusMessage::EXT_UPLOADED));

        $this->mockedUploadService->expects($this->once())
            ->method('upload');

        $request = Request::create('/?ext_key=my_ext', 'POST', $this->payload, [], [], ['HTTP_X_HUB_SIGNATURE' => 'sha1=1f027c6d9122af24cb2a37bc2b22ae45b74cca3d']);

        $listener = new WebhookListener($this->mockedResponse, $request, $this->mockedUploadService, $this->mockedSlackNotificationService, 'abc');
        $listener->handleRequest();
    }

    public function testNoTag() {
        $this->mockedResponse->expects($this->once())
            ->method('sendSuccess')
            ->with($this->equalTo(StatusMessage::NO_TAG));

        $this->payload['payload'] = json_encode([
            'ref' => 'ref/head/master'
        ]);
        $request = Request::create('/?ext_key=my_ext', 'POST', $this->payload, [], [], ['HTTP_X_HUB_SIGNATURE' => 'sha1=1f027c6d9122af24cb2a37bc2b22ae45b74cca3d']);

        $listener = new WebhookListener($this->mockedResponse, $request, $this->mockedUploadService, $this->mockedSlackNotificationService, 'abc');
        $listener->handleRequest();
    }
}
