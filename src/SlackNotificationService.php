<?php

namespace Sethorax\TYPO3TERWebHook;


class SlackNotificationService
{
    protected $request;

    protected $config;

    public function __construct()
    {       
        $this->config = ConfigUtility::getConfig()['notification']['slack'];

        $this->request = curl_init($this->config['webhook-url']);
        curl_setopt($this->request, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($this->request, CURLOPT_RETURNTRANSFER, true);
    }

    public function send($message)
    {
        $data = 'payload=' . json_encode([
            'text' => urlencode($message),
            'link_names' => '1'
        ]);

        curl_setopt($this->request, CURLOPT_POSTFIELDS, $data);
        curl_exec($this->request);
        curl_close($this->request);
    }
}