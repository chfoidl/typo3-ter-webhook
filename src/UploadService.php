<?php

namespace Sethorax\TYPO3TERWebHook;


class UploadService
{
    protected $cloneUrl;

    protected $extKey;

    protected $tempDir;


    public function upload($cloneUrl, $extKey)
    {
        $this->extKey = $extKey;
        $this->cloneUrl = $cloneUrl;

        $this->createTempDir();
        $this->cloneRepo();
        $this->uploadExtension();
        $this->cleanUp();
    }


    protected function cloneRepo()
    {
        $cmd = '/usr/bin/git clone ' . $this->cloneUrl;

        chdir($this->tempDir);
        exec($cmd);
    }

    protected function uploadExtension()
    {
        $extDir = array_values(array_diff(scandir($this->tempDir), ['.', '..']))[0];
        $username = ConfigUtility::getConfig()['authorization']['typo3']['username'];
        $password = ConfigUtility::getConfig()['authorization']['typo3']['password'];

        $uploader = new \NamelessCoder\TYPO3RepositoryClient\Uploader();
        $uploader->upload($extDir, $username, $password, 'Automatic release built from GitHub. See the CHANGELOG.md file that is shipped with this release for details.');
    }


    protected function createTempDir()
    {
        $this->tempDir = sys_get_temp_dir() . '/' . $this->extKey;

        if (file_exists($this->tempDir)) {
            $this->cleanUp();
        }

        mkdir($this->tempDir);
    }

    protected function cleanUp()
    {
        exec('rm -rf ' . $this->tempDir);
    }
}