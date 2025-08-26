<?php

namespace Custom\Template;

use Neos\Flow\Package\Package as BasePackage;
use Neos\Flow\Core\Bootstrap;
use Dotenv\Dotenv;

class Package extends BasePackage
{
    public function boot(Bootstrap $bootstrap): void
    {

        $rootPath = dirname(__DIR__, 3);
        if (file_exists($rootPath . '/.env')) {
            $dotenv = Dotenv::createImmutable($rootPath);
            $dotenv->safeLoad();
        }

        // $bootstrap->getSignalSlotDispatcher()->connect(
        //     Bootstrap::class,
        //     'configurationManagerReady',
        //     function() {
        //         $rootPath = dirname(__DIR__, 3);
        //         if (file_exists($rootPath . '/.env')) {
        //             $dotenv = Dotenv::createImmutable($rootPath);
        //             $dotenv->safeLoad();
        //         }
        //     }
        // );
    }
}