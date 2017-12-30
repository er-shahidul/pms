<?php

namespace Pms\CoreBundle\Services;


class DatabaseBackup
{

    private $databaseName;
    private $username;
    private $password;

    public function  __construct($username, $password,$databaseName)
    {

        $this->username = $username;
        $this->password = $password;
        $this->databaseName = $databaseName;
    }

     function databaseBackUp(){

        $backUpCommand = 'mysqldump -u'.' '.$this->username.' '.' -p'.$this->password.' '.$this->databaseName.'> /opt/pms.sql';
        shell_exec($backUpCommand);

    }
}