<?php

namespace Gevman\FileManager;

use Gevman\FileManager\assets\FileManagerAsset;
use Yii;
use yii\web\Application as WebApplication;
use yii\web\AssetBundle;
use yii\caching\Cache;
use yii\base\BootstrapInterface;

class Module extends \yii\base\Module implements BootstrapInterface
{
    /**
     * @var string
     */
    public $uploadFolder;
    /**
     * @var boolean
     */
    public $returnFullPath;

    /**
     * @var array
     */
    public $allowedExtensions;

    /**
     * @var string
     */
    public $cacheComponent;

    /**
     * @var Cache
     */
    public $cache;

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'Gevman\FileManager\controllers';

    /**
     * @var AssetBundle
     */
    public $assetBundle;

    /**
     * @var callable
     */
    public $beforeAction;

    /**
     * @var int Listing Page Size
     */
    public $pageSize = 100;

    public function getControllerPath()
    {
        return realpath(__DIR__ . '/commands');
    }

    public function bootstrap($app)
    {
        if ($app instanceof \yii\web\Application) {
            Yii::$app->urlManager->addRules([
                ['class' => 'yii\web\UrlRule', 'pattern' => $this->id, 'route' => $this->id . '/default/index'],
                ['class' => 'yii\web\UrlRule', 'pattern' => $this->id . '/<controller:[\w\-]+>/<action:[\w\-]+>', 'route' => $this->id . '/<controller>/<action>'],
            ]);
        } elseif ($app instanceof \yii\console\Application) {
            $app->controllerMap[$this->id] = [
                'class' => 'Gevman\FileManager\commands\DefaultController',
                'module' => $this,
            ];
        }
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->uploadFolder = trim($this->uploadFolder, '/');

        if (Yii::$app instanceof WebApplication) {
            $this->assetBundle = $this->assetBundle ? new $this->assetBundle : new FileManagerAsset;
        }

        if ($this->cacheComponent) {
            $this->cache = Yii::$app->get($this->cacheComponent);
        }
    }

    public function beforeAction($action)
    {
        $valid =  parent::beforeAction($action);
        if (is_callable($this->beforeAction)) {
            call_user_func($this->beforeAction);
        }

        return $valid;
    }
}
