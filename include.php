<?
$dctEnvModule = include(__DIR__.'/.env.php');
$module = new $dctEnvModule['CLASS']();
$module->regEntities();
