# File Manager Module for Yii2

add this to web.php

```php
'modules' => [
    'file-manager' => [
        'class' => 'Gevman\FileManager\Module',
        'assetBundle' => null,
        'uploadFolder' => 'uploads',
        'allowedExtensions' => [
            'images' => ['jpg', 'png', 'gif'],
        ],
        'cacheComponent' => 'cache'
    ],
],
```