<?php

namespace Gevman\FileManager\components;

use Imagine\Image\Box;
use Imagine\Image\ManipulatorInterface;
use Yii;
use Gevman\FileManager\Module;
use yii\base\BaseObject;
use yii\console\Application;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\validators\FileValidator;
use yii\web\UploadedFile;
use DirectoryIterator;
use Exception;
use yii\imagine\Image;
use yii\helpers\Console;

class Manager extends BaseObject
{
    /**
     * @var Module
     */
    public $module;

    public function getAllowedExtensions($token)
    {
        return Yii::$app->session->get($token);
    }

    private function getListing()
    {
        $listing = [];
        $iterator = new DirectoryIterator(Yii::getAlias(sprintf('@webroot/%s', $this->module->uploadFolder)));

        foreach ($iterator as $key => $file) {
            if ($file->isFile() && !$file->isDot() && $file->getFilename() != '.gitignore') {
                $listing[uniqid($file->getMTime())] = $file->getPathname();
            }
        }
        krsort($listing);

        return array_values($listing);
    }

    private function getFileInfo($filename)
    {
        if ($size = @getimagesize($filename)) {
            $size = sprintf('%sx%s', $size[1], $size[0]);
        } else {
            $size = null;
        }

        return [
            'fileName' => basename($filename),
            'fileSize' => round(filesize($filename) / 1024, 1),
            'imageSize' => $size
        ];
    }

    public function indexAll()
    {
        $listing = $this->getListing();
        $total = count($listing);

        Console::startProgress(0, $total, 'Indexing files: ', false);

        foreach ($listing as $key => $file) {
            Console::updateProgress($key, $total);
            $this->module->cache->getOrSet(sprintf('%s-file-%s-info', $this->module->id, $file), function() use ($file) {
                return $this->getFileInfo($file);
            }, 60 * 60 * 24 * 31);
        }
        Console::endProgress("done." . PHP_EOL);
    }

    public function getFiles($offset, $limit)
    {
        $listing = array_slice($this->getListing(), $offset , $limit);

        $files = [];

        foreach ($listing as $key => $file) {
            $files[] = $this->module->cache->getOrSet(sprintf('%s-file-%s-info', $this->module->id, $file), function() use ($file) {
                return $this->getFileInfo($file);
            }, 60 * 60 * 24 * 31);
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
            $size = intval($value);
            $modifier = strtolower($value[strlen($value)-1]);
            switch($modifier) {
                case 'g':
                    $size *= 1024;
                case 'm':
                    $size *= 1024;
                case 'k':
                    $size *= 1024;
            }
            return $size;
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
}