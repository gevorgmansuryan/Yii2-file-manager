<?php
/**
 * Created by PhpStorm.
 * User: Gev
 * Date: 07/20/2018
 * Time: 20:52
 */

namespace Gevman\FileManager\commands;


use yii\console\Controller;
use Gevman\FileManager\components\Manager;

class DefaultController extends Controller
{
    /**
     * This command will perform file indexing for file-manager
     * @return int Exit code
     */
    public function actionIndexFiles()
    {
        $manager = new Manager(['module' => $this->module]);
        $manager->getFiles(true);
    }

    public function getUniqueID()
    {
        return $this->id;
    }
}