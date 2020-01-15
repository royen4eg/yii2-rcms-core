<?php


namespace rcms\core\base;


use yii\console\Application;

/**
 * Interface BootstrapConsoleInterface
 * @package rcms\core\base
 * @author Andrii Borodin
 * @since 0.1
 */
interface BootstrapConsoleInterface
{
    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrapConsole($app);

}