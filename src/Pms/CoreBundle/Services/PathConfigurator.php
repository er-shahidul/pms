<?php

namespace Docudex\Bundle\CoreBundle\Services;

use Docudex\Bundle\CoreBundle\Helper\DateTime;
use Docudex\Bundle\CoreBundle\Repository\ConfigurationRepository;

class PathConfigurator
{
    protected $pathConstants;

    /* @var ConfigurationRepository $configRepository */
    protected $configRepository;

    public function __construct(ConfigurationRepository $configRepository, $root)
    {
        $this->configRepository = $configRepository;
        $this->pathConstants['root'] = $root;
        $this->pathConstants['web_root'] = realpath($root . "/../web");
        $this->parseConfigValues();
    }

    protected function parseConfigValues()
    {
        $configs = $this->configRepository->loadAllByGroup('system.paths');

        $this->pathConstants['default_repo'] = $configs['repository.default']->getValue();
        $this->pathConstants['upload_dir'] = '' ;
        $this->pathConstants['upload_dir'] = $this->replacePathConstant($configs['upload']->getValue());

    }

    public function getPathConstants($key = null)
    {
        if($key === null){
            return $this->pathConstants;
        }

        if(isset($this->pathConstants[$key])){
            return $this->pathConstants[$key];
        }

        return null;
    }

    public function getPathFromKey($pathKey, $replaceConstant = false)
    {
        $basePath = $this->configRepository->load($pathKey);

        if (!$basePath) {
            return;
        }

        if($replaceConstant){
            $basePath = $this->replacePathConstant($basePath);
        }

       return $basePath;
    }

    public function getPathSegmentByTime($dateTime)
    {
        $dateTime = DateTime::getDateTimeObject($dateTime);
        $format = implode(DIRECTORY_SEPARATOR, array('Ym', 'dH', 'i'));

        return $dateTime->format($format);
    }

    public function getTimedSuffixedPath($dateTime, $basePath = "")
    {
        if(!empty($dateTime) && $basePath !="" && substr($basePath, -1) != DIRECTORY_SEPARATOR) {
            $basePath .= DIRECTORY_SEPARATOR;
        }

        return $basePath . $this->getPathSegmentByTime($dateTime);
    }

    public function replacePathConstant($path)
    {
        $searchFor      = array('{ROOT_DIR}', '{WEB_ROOT}', '{UPLOAD_DIR}');

        $replaceWith = array($this->pathConstants['root'],
            $this->pathConstants['web_root'],
            $this->pathConstants['upload_dir']);

        return str_replace($searchFor, $replaceWith, $path);
    }
} 