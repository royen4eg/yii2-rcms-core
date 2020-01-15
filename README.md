# yii2-rcms-core
Core of RCMS project

RCMS Project is created to simplify control over content across user's project.
To reach that, it provides several administrative tools to create and control content.
Below you will find a list of modules that will improve usage of RCMS.

Please note that this project is in early development stage and based purely on author's enthusiasm.
Please leave ideas, notes and issues to this project on official [github page](https://github.com/royen4eg/yii2-rcms-core/issues)


**yii2-rcms-core** is the base module for other RCMS components. 
It is included into requirements of other modules and will be downloaded automatically when required other components.


#### Currently available modules:
* [yii2-rcms-core](https://github.com/royen4eg/yii2-rcms-core)
* [yii2-rcms-content-manager](https://github.com/royen4eg/yii2-rcms-content-manager)

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

To allow access to migration and console commands, modify console configuration as follow:

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
          'class' => 'rcms\core\Module',
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

Current module have custom migration table `{{user}}`.
Other modules that extends functionality of RCMS might have other tables that should be migrated.

```
$ php yii rcms/migrate
```

Migration of User table will automatically create admin record in table (analogy of default `admin/admin`)

Note: Before migration it is recommended to modify table prefix in **global settings**