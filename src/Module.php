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
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		$this->assetBundle = $this->assetBundle ? new $this->assetBundle : new FileManagerAsset;
		if ($this->cacheComponent) {
			$this->cache = Yii::$app->get($this->cacheComponent);
		}
	}
}
