#!/bin/bash
# v0.0.1

cd ..

# подготавливаем переменны
MODULE_DIR=$1;

IFS='.' read -ra debris <<< "$MODULE_DIR"

MODULE_PNS=${debris[0]};
MODULE_NAME=${debris[1]};
MODULE_ID=$MODULE_DIR;
MODULE_CLASS="${debris[0]}_${debris[1]}";
MODULE_SP=`echo $MODULE_PNS | tr a-z A-Z`_M_`echo $MODULE_NAME | tr a-z A-Z`_;

if ! [ "$MODULE_DIR" == "$MODULE_PNS.$MODULE_NAME" ]; then
    echo "Ошибка в id модуля";
    exit 1;
fi

PNS=`echo ${MODULE_PNS:0:1} | tr a-z A-Z`${MODULE_PNS:1};
NAME=`echo ${MODULE_NAME:0:1} | tr a-z A-Z`${MODULE_NAME:1};
CLASS="$PNS\\$NAME";

# создаем копию модуля
rm -rf $1;
cp -R 'x.module' $1;
cd $1;

# выполняем автозамену в файле иснталяции
sed -i "s/X\\\Module/$PNS\\\\\\$NAME/g" install/index.php;
sed -i "s/x_module/$MODULE_CLASS/g" install/index.php;
sed -i "s/x.module/$MODULE_ID/g" install/index.php;

# выполняем автозамену в файле переводов
sed -i "s/X_M_MODULE_/$MODULE_SP/g" lang/ru/lib/module.php;

# замена класса в модуле
echo "<?php namespace Foo\Bar; \Bitrix\Main\Loader::includeModule('x.module'); \Bitrix\Main\Localization\Loc::loadMessages(__FILE__); class Module extends \X\Module\Module {function __construct(array \$dctEnvModule=[]) {if(!count(\$dctEnvModule))\$dctEnvModule = include(dirname(__DIR__).'/.env.php'); return parent::__construct(\$dctEnvModule);}}" > lib/module.php;

# анкоммент подключения модуля в include.php
sed -i "s/#\\\Bitrix\\\Main\\\Loader::includeModule('x.module');/\\\Bitrix\\\Main\\\Loader::includeModule('x.module');/g" include.php;

# удаление файлов-примеров
find . -name "_example_*" -delete

# удаление скриптов, кроме скрипта сборки
find . -name "*.sh" -not -name "build.sh" -delete

# удаление лишних md замена README
find . -name "*.md" -not -name "README.md" -delete
echo "# Модуль $MODULE_ID" > README.md;