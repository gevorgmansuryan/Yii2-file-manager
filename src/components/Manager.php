<?php

namespace Gevman\FileManager\components;

use Yii;
use Gevman\FileManager\Module;
use yii\base\Object;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\web\UploadedFile;
use DirectoryIterator;

class Manager extends Object
{
	/**
	 * @var Module
	 */
	public $module;

	public function getAllowedExtensions($token)
	{
		return Yii::$app->session->get($token);
	}

	private function getFiles($noCache = false)
	{
		if ($this->module->cache && $this->module->cache->exists($this->module->id) && !$noCache) {
			return $this->module->cache->get($this->module->id);
		}
		$files = [];
		$iterator = new DirectoryIterator(Yii::getAlias(sprintf('@webroot/%s', $this->module->uploadFolder)));
		foreach ($iterator as $file) {
			if ($file->isFile() && $file->getBasename()[0] != '.') {
				$files[uniqid($file->getMTime())] = $file->getFilename();
			}
		}
		krsort($files);
		$files = array_values($files);
		if ($this->module->cache) {
			$this->module->cache->set($this->module->id, $files);
		}
		return $files;
	}

	public function generateToken($type)
	{
		$allowedExtensions = [];
		if ($type) {
			$allowedExtensions = $this->module->allowedExtensions[$type];
		} elseif (isset($this->module->allowedExtensions[0])) {
			$allowedExtensions = $this->module->allowedExtensions;
		} else {
			foreach ($this->module->allowedExtensions as $group) {
				$allowedExtensions = ArrayHelper::merge($allowedExtensions, $group);
			}
		}
		$token = md5(Json::encode($allowedExtensions));
		if (!Yii::$app->session->has($token)) {
			Yii::$app->session->set($token, $allowedExtensions);
		}
		return $token;
	}

	public function hasErrors(UploadedFile $file, $token)
	{
		$fileExtensions = FileHelper::getExtensionsByMimeType(FileHelper::getMimeType($file->tempName));
		$matchedExtensions = array_intersect($this->getAllowedExtensions($token), $fileExtensions);
		return empty($matchedExtensions);
	}

	public function save(UploadedFile $file)
	{
		$name = sprintf(
			'%s.%s',
			sha1_file($file->tempName),
			$file->extension
		);
		$file->saveAs(Yii::getAlias(sprintf('@webroot/%s/%s', $this->module->uploadFolder, $name)));
		return Yii::getAlias(sprintf('@web/%s/%s', $this->module->uploadFolder, $name));
	}

	public function getGallery($offset)
	{
		return array_slice($this->getFiles($offset == 0), $offset , 100);
	}
}