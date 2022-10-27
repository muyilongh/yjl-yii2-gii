<?php
/**
 * @link http://www.diemeisterei.de/
 *
 * @copyright Copyright (c) 2014 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */
namespace Yjl\Gii;

use yii\base\Application;
use yii\base\BootstrapInterface;

/**
 * Class Bootstrap.
 *
 * @author Tobias Munk <tobias@diemeisterei.de>
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * Bootstrap method to be called during application bootstrap stage.
     *
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        if ($app->hasModule('gii')) {
            if (!isset($app->getModule('gii')->generators['giiant-model'])) {
                $app->getModule('gii')->generators['giiant-model'] = 'Yjl\Gii\generators\model\Generator';
            }

            if (!isset($app->getModule('gii')->generators['giiant-extension'])) {
                $app->getModule('gii')->generators['giiant-extension'] = 'Yjl\Gii\generators\extension\Generator';
            }

            if (!isset($app->getModule('gii')->generators['YjlGii-crud'])) {
                $app->getModule('gii')->generators['YjlGii'] = [
                    'class' => 'Yjl\Gii\generators\crud\Generator',
                    'templates' => [
                        'editable' => __DIR__.'/generators/crud/editable',
                    ],
                ];
            }

            if (!isset($app->getModule('gii')->generators['YjlGii-module'])) {
                $app->getModule('gii')->generators['giiant-module'] = 'Yjl\Gii\generators\module\Generator';
            }

            if (!isset($app->getModule('gii')->generators['YjlGii-test'])) {
                $app->getModule('gii')->generators['YjlGii-test'] = 'Yjl\Gii\generators\test\Generator';
            }

            if ($app instanceof \yii\console\Application) {
                $app->controllerMap['YjlGii-batch'] = 'Yjl\Gii\commands\BatchController';
            }
        }
    }
}
