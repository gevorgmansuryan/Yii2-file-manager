<?php

namespace Gevman\FileManager;

use Gevman\FileManager\assets\FileManagerAsset;
use Yii;
use yii\web\AssetBundle;
use yii\caching\Cache;

class Module extends \yii\base\Module
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
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
        Yii::$app->urlManager->addRules([
            ['class' => 'yii\web\UrlRule', 'pattern' => $this->id, 'route' => $this->id . '/default/index'],
            ['class' => 'yii\web\UrlRule', 'pattern' => $this->id . '/<controller:[\w\-]+>/<action:[\w\-]+>', 'route' => $this->id . '/<controller>/<action>'],
        ]);
		$this->assetBundle = $this->assetBundle ? new $this->assetBundle : new FileManagerAsset;
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
