<?php

namespace Pms\CoreBundle\Services;

use Symfony\Component\Finder\Finder;

abstract class BaseStagedFileManager
{
    /** @var Finder */
    protected $finder;
    /**
     * @var \Docudex\Bundle\CoreBundle\Services\PathConfigurator
     */
    protected $configManager;
    protected $stagedDirectory;
    protected $allowedExtensions;
    protected $fileCollectionCache;

    public function __construct(PathConfigurator $configManager)
    {
        $this->configManager     = $configManager;
        $this->allowedExtensions = array('jpg','jpeg', 'png', 'tiff', 'tif', 'pdf');
    }

    public function findAll()
    {
        if ($this->fileCollectionCache === NULL) {
            $this->fileCollectionCache = array();
            foreach ($this->getFinder() as $file) {
                $this->fileCollectionCache[] = $file;
            }
        }

        return $this->fileCollectionCache;
    }

    protected function getFinder()
    {
        if (empty($this->finder)) {
            $this->finder = $this->createFinderObject();
        }

        return $this->finder;
    }

    public function getPaged($page = 1, $perPage = 10)
    {
        $start = $perPage * ($page - 1);

        try {
            return array_slice($this->findAll(), $start, $perPage, TRUE);
        } catch (\Exception $e) {
            return array();
        }
    }

    protected function createFinderObject()
    {
        if(!is_dir($this->stagedDirectory) || !is_readable($this->stagedDirectory)) {
            return array();
        }

        $finder = new Finder();

        $finder
            ->files()
            ->in($this->stagedDirectory)
            ->ignoreDotFiles(TRUE)
            ->ignoreVCS(TRUE)
            ->sort(function(\SplFileInfo $a, \SplFileInfo $b)
            {
                return strcmp($a->getFilename(), $b->getFilename());
            })
            ->filter(function (\SplFileInfo $file) {
                if (!in_array(strtolower($file->getExtension()), $this->allowedExtensions)) {
                    return FALSE;
                }
            });

        return $finder;
    }

    public function getBaseDirectory()
    {
        return $this->stagedDirectory;
    }

    /**
     * @return mixed|null
     */
    public function getStagedDirectory()
    {
        return $this->stagedDirectory;
    }
}