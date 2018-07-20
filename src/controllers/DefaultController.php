<?php

namespace Gevman\FileManager\controllers;

use Gevman\FileManager\components\Manager;
use Yii;
use Gevman\FileManager\assets\FileManagerAsset;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\UploadedFile;
use yii\web\View;
use yii\helpers\Json;
use yii\web\Controller;

/**
 * Default controller for the `file-manager` module
 *
 * Class DefaultController
 * @package Gevman\FileManager\controllers
 *
 * @property \Gevman\FileManager\Module $module
 */
class DefaultController extends Controller
{
	/**
	 * @var Manager
	 */
	private $manager;

	public function beforeAction($action)
	{
		$this->manager = new Manager(['module' => $this->module]);
		$this->module->assetBundle->register($this->view);
		return parent::beforeAction($action);
	}

	public function actionIndex($type = null)
	{
		$token = $this->manager->generateToken($type);
		$this->layout = 'main';
		$this->view->registerJs(sprintf('var context = \'%s\';', Json::encode([
			'id' => $this->module->id,
			'ajaxUrl' => Url::to([sprintf('/%s/default', $this->module->id)]),
			'uploadsUrl' => Url::to(["/{$this->module->uploadFolder}"], $this->module->returnFullPath),
			'allowedExtensions' => $this->manager->getAllowedExtensions($token),
			'token' => $token
		])), View::POS_BEGIN);
		return $this->renderContent('<div id="app"></div>');
	}

	public function actionUpload($token)
	{
		$file = UploadedFile::getInstanceByName('file');
		if ($error = $this->manager->hasErrors($file, $token)) {
			Yii::$app->getResponse()->statusCode = 400;
			return $error;
		}
		return $this->manager->save($file);
	}

	public function actionResize()
	{
		$this->manager->resize(Yii::$app->request->post());
	}

    public function actionPage($offset = 0)
    {
        return Json::encode($this->manager->getFiles($offset, $this->module->pageSize));
    }
}
