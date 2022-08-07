<?
#\Bitrix\Main\Loader::includeModule('x.module');
$dctEnvModule = include(__DIR__.'/.env.php');
$module = new $dctEnvModule['CLASS']($dctEnvModule);
$module->regEntities();
$module->loadComposerLibs();