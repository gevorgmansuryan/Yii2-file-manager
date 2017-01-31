<?php

namespace Gevman\FileManager\assets;

use yii\web\AssetBundle;

class FileManagerAsset extends AssetBundle
{
	public $publishOptions = [
		'forceCopy' => YII_ENV_DEV,
	];

	public $sourcePath = __DIR__.'/files';

	public $css = [
		'css/app.css',
		'css/toastr.min.css'
	];

	public $js = [
		'js/app.min.js',
		'js/toastr.min.js'
	];

	public $depends = [
		'yii\web\YiiAsset',
		'yii\bootstrap\BootstrapAsset'
	];
}