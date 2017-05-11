<?php

namespace Sethorax\TYPO3TERWebHook;

final class StatusMessage
{
    const NO_EXTKEY = 'Extkey not set!';
    const INVALID_PAYLOAD = 'Could not convert payload to array!';
    const INVALID_HASH = 'Invalid hash! Correct secret?';
    const INVALID_CONFIG = 'Invalid config.yml';
    const NO_TAG = 'Nothing to do! No tag associated with this push.';
    const EXT_UPLOADED = 'Extension uploaded!';

    private function __construct()
    {
        throw new Exception('Cannot get instance of StatusMessage!');
    }
}
