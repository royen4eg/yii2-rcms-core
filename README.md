# yii2-rcms-core
Core of RCMS project

This is the base for other RCMS components. 
It is included into requirements of other modules and will be downloaded automatically when required other components.


## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).


Either run

```
$ php composer.phar require royen4eg/yii2-rcms-core "*"
```

or add

```
"royen4eg/yii2-rcms-core": "*"
```

to the ```require``` section of your `composer.json`.

## Usage

Once the extension is installed, simply modify your application configuration as follows:

```php
return [
    'bootstrap' => [
        //...
        'rcmsAdmin'
        //...
    ],
    //...
    'modules' => [
        'rcmsAdmin' => [
          'class' => 'rcms\core\AdminModule',
        ],
        'rcmsFront' => [
          'class' => 'rcms\core\FrontModule',
        ],
    ]
    //...
];
```
You may use any name instead of rcmsAdmin as it will fill comfortable.
For simple config it will be used as url for access to admin dashboard.

You will access it with URL like:
```
http://localhost/path/to/index.php?r=rcmsAdmin/
```
or if you have enabled pretty URLs:
```
http://localhost/rcmsAdmin/
```

For the start of usage and before migrations, it is highly recommend to change global configurations of RCMS table preffix. For it use the link below:
```
http://localhost/rcmsAdmin/global-settings
```


## Applying Migrations

Current module have custom migration table "{{user}}"
```
$ php yii rcms-migrate --migration-path=@rcms/core/migrations
```
