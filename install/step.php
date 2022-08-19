<?if(!check_bitrix_sessid()) return;
if (!class_exists('Minisol\Multidelivery\Module')) include __DIR__.'/../lib/module.php';
$dctEnvModule = include(__DIR__.'/../.env.php');
echo CAdminMessage::ShowNote(GetMessage((new $dctEnvModule['CLASS'])->MODULE_SP.'INSTALL_MESSAGE'));