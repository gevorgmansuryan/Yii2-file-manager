<?php

namespace Gevman\FileManager\components;

use Imagine\Image\Box;
use Imagine\Image\ManipulatorInterface;
use Yii;
use Gevman\FileManager\Module;
use yii\base\Object;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\validators\FileValidator;
use yii\web\UploadedFile;
use DirectoryIterator;
use Exception;
use yii\imagine\Image;

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
			if ($file->isFile() && !$file->isDot()) {
				try {
					$size = Image::frame(Yii::getAlias(sprintf('@webroot/%s/%s', $this->module->uploadFolder, $file->getFilename())), 0)->getSize();
					$size = sprintf('%sx%s', $size->getHeight(), $size->getWidth());
				} catch (Exception $e) {
					$size = null;
				}
				$files[uniqid($file->getMTime())] = [
					'fileName' => $file->getFilename(),
					'fileSize' => round($file->getSize() / 1024, 1),
					'imageSize' => $size
				];
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
        $systemDefaultMaxSize = function() {
            $value = trim(ini_get('post_max_size'));
            $modifier = strtolower($value[strlen($value)-1]);
            switch($modifier) {
                case 'g':
                    $value *= 1024;
                case 'm':
                    $value *= 1024;
                case 'k':
                    $value *= 1024;
            }
            return $value;
        };

        $validator = new FileValidator();
        $validator->maxSize = $systemDefaultMaxSize();
        $validator->extensions = $this->getAllowedExtensions($token);
        $validator->uploadRequired = true;
        $validator->validate($file, $error);

        return $error;
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

	public function resize($data)
	{
		$original = Yii::getAlias(sprintf('@webroot/%s/%s', $this->module->uploadFolder, $data['image']));
		$extension = pathinfo($original, PATHINFO_EXTENSION);
		$tempName = tempnam(sys_get_temp_dir(), $this->module->id);
		$image = Image::thumbnail($original, $data['width'], $data['height'], ManipulatorInterface::THUMBNAIL_INSET);
		file_put_contents($tempName, $image->get($extension));

		$name = sprintf(
			'%s.%s',
			sha1_file($tempName),
			$extension
		);
		rename($tempName, Yii::getAlias(sprintf('@webroot/%s/%s', $this->module->uploadFolder, $name)));
	}

	public function getGallery($offset)
	{
		return array_slice($this->getFiles($offset == 0), $offset , 100);
	}
}