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