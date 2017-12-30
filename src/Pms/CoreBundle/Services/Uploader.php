<?php

namespace Pms\CoreBundle\Services;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class Uploader
{

    /* @var PathConfigurator $configManager */
    protected $configManager;
    protected $uploadPath;
    protected $repositoryKey;
    protected $defaultBasePath;

    /** @var  \Exception */
    protected $error = null;

    protected $acceptTypesRegex = '/.+$/i';

    private static $maximumAllowedFileSize = null;

    public function __construct(PathConfigurator $configManager, $extensions = array())
    {
        $this->setAllowedExtensions($extensions);
        $this->configManager = $configManager;
        $this->setDefaultRepository();
        $this->defaultBasePath = $this->getUploadPath();
    }

    public function setAllowedExtensions($extensions = array())
    {
        $this->acceptTypesRegex = '/(' . implode('|', array_map(function ($extension) {
                return '\.' . $extension;
            }, $extensions)) . ')$/i';
    }

    public function getUploadPath()
    {
        return $this->uploadPath;
    }

    public function setUploadPathKey($pathKey)
    {
        $basePath = $this->configManager->getPathFromKey($pathKey);

        if (!$basePath) {
            $this->setUploadPath($this->defaultBasePath);
            return $this;
        }

        $this->repositoryKey = $pathKey;
        $this->setUploadPath($basePath);

        return $this;
    }

    public function setUploadPath($basePath)
    {
        $this->uploadPath = $this->configManager->replacePathConstant($basePath);

        return $this;
    }

    public function upload(UploadedFile $file = null, $partitionDirectory = false)
    {
        $this->error = null;

        if (!$this->isValid($file)) {
            return;
        }

        $fileData['filename'] = $this->getRandomFileName($file);
        $fileData['extension'] = $file->getClientOriginalExtension();
        $fileData['mimeType'] = $file->getMimeType();
        $fileData['userFileName'] = $file->getClientOriginalName();
        $fileData['size'] = $file->getClientSize();
        $fileData['createTime'] = $file->getCTime();
        $fileData['repositoryKey'] = $this->repositoryKey;

        $path = $this->uploadPath;

        if($partitionDirectory) {
            $path = $this->configManager->getTimedSuffixedPath($fileData['createTime'], $path);
        }

        if(!is_dir($path)) {
            @mkdir($path, 0777, TRUE);
        }

        $file->move(
            $path,
            $fileData['filename']
        );

        $file = null;

        return $fileData;
    }

    protected function setError($msg, $code = 400)
    {
        $this->error = new \Exception($msg, $code);
    }

    protected function isValid(UploadedFile $file = null)
    {
        if(null === $file){
            $this->setError('No file selected');
        }

        if (!preg_match($this->acceptTypesRegex, $file->getClientOriginalName())) {
            $this->setError('File type is not allowed');
        }

        return $this->error === null;
    }

    public function getErrorCode()
    {
        if($this->error) {
            return $this->error->getCode();
        }
    }

    public function getErrorMessage()
    {
        if($this->error) {
            return $this->error->getMessage();
        }
    }

    protected function getRandomFileName(UploadedFile $file)
    {
        return md5(microtime()).".".$file->getClientOriginalExtension();
    }

    public static function getMaximumAllowedUploadSize()
    {
        if(empty(self::$maximumAllowedFileSize))
        {
            $maxUpload = self::getConfigInBytes('upload_max_filesize');
            $maxPost = self::getConfigInBytes('post_max_size');
            $memoryLimit = self::getConfigInBytes('memory_limit');

            self::$maximumAllowedFileSize = min($maxUpload, $maxPost, $memoryLimit);
        }

        return self::$maximumAllowedFileSize;
    }

    public static function getConfigInBytes($key)
    {
        $val = ini_get($key);
        $last = strtolower($val[strlen($val) - 1]);

        switch ($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k':$val *= 1024;
        }

        return self::fixIntegerOverflow($val);
    }

    protected static function fixIntegerOverflow($size)
    {
        if ($size < 0) {
            $size += 2.0 * (PHP_INT_MAX + 1);
        }

        return $size;
    }

    public function setDefaultRepository()
    {
        $this->repositoryKey = $this->configManager->getPathConstants('default_repo');
        $this->setUploadPathKey($this->repositoryKey);

        return $this;
    }
}