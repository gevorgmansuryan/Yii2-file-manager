# File Manager Module for Yii2

add this to web.php

```php
'bootstrap' => [/* other, */'file-manager'/*, other*/],
// and
'modules' => [
    /* other, */
    'file-manager' => [
        'class' => Gevman\FileManager\Module::class,
        'returnFullPath' => false,
        'uploadFolder' => '/uploads',
        'allowedExtensions' => [
            'images' => ['jpg', 'png', 'gif'],
        ],
        'cacheComponent' => 'cache',
        'beforeAction' => function() {
            if (Yii::$app->user->isGuest) {
                throw new \yii\web\ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
            }
        }
    ],
    /*, other*
],
```

there is also console command for automating file thumb indexing

in console config set @webroot alias first after add this to config file

```php
'bootstrap' => [/* other, */'file-manager'/*, other*/],
// and
'modules' => [
    /* other, */
    'file-manager' => [
        'class' => Gevman\FileManager\Module::class,
        'returnFullPath' => true,
        'uploadFolder' => '/uploads',
        'cacheComponent' => 'cache',
    ],
    /*, other*
],

after run `php yii file-manager/index-files`