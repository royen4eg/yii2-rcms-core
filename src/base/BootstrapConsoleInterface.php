<?php


namespace rcms\core\base;


use yii\console\Application;

interface BootstrapConsoleInterface
{
    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrapConsole($app);

}