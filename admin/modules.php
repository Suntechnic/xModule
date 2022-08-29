<?
// подключим все необходимые файлы:
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог

$RIGHT = $APPLICATION->GetGroupRight('x.module');
if ($RIGHT != 'W') $APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));

if (!\Bitrix\Main\Loader::includeModule('x.module')) {
	return;
}

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);


$lstTabs = array(
    [
			'DIV' => 'modulеs',
			'TAB' => Loc::getMessage('X_MODULE_ADMIN_MODULES_TABBASEDMODULES'),
			'ICON'=>'main_user_edit',
			'TITLE'=> Loc::getMessage('X_MODULE_ADMIN_MODULES_TABTITLEBASEDMODULES'),
		],
	[
			'DIV' => 'create',
			'TAB' => Loc::getMessage('X_MODULE_ADMIN_MODULES_TABCREATED'),
			'ICON'=>'main_user_edit',
			'TITLE'=> Loc::getMessage('X_MODULE_ADMIN_MODULES_TABTITLECREATED'),
		]
);
$tabControl = new CAdminTabControl('tabControl', $lstTabs);

$selfModule = new \X\Module\Module;

if($REQUEST_METHOD == "POST" // проверка метода вызова страницы
        && $RIGHT=="W"          // проверка наличия прав на запись для модуля
        && check_bitrix_sessid()     // проверка идентификатора сессии
    ) {
    $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
	
	if ($request->get('create')) {
		// получение параметров модуля из запроса (формы)
		$dctOptionsModule = $request->get('options')['module'];
		
		$dctProps = \X\Module\Modules::getModuleProps($dctOptionsModule['id']);
		
		if ($dctProps) {
			
			$MODULE_PATH_ABS = \Bitrix\Main\Application::getDocumentRoot()
					.'/local/modules/'.$dctProps['MODULE_DIR'];

			$PARTNER_NAME = $dctOptionsModule['partner_name'];
			$PARTNER_URI = $dctOptionsModule['partner_uri'];
			
			$dir = new \Bitrix\Main\IO\Directory($MODULE_PATH_ABS);
			if ($dir->isExists()) {
				
				CopyDirFiles(
						$dir->getPath().'/include.php',
						$dir->getPath().'/include.php~',
						true,
						true
					);
				unlink($dir->getPath().'/include.php');
				
				CopyDirFiles(
						$dir->getPath(),
						$dir->getPath().'_old_'.time(),
						true,
						true
					);
				$dir->delete();
			}
			
			CopyDirFiles(
					$selfModule->MODULE_PATH_ABS,
					$dir->getPath(),
					true,
					true
				);
			
			// процедуры обработки файлов
			
			// замена литералов и имен классов
			$lstDepNames = [
					'/install/index.php',
					'/install/step.php',
					'/install/unstep.php',
					'/lang/ru/lib/module.php'
				];
			foreach ($lstDepNames as $DepName) {
				$depFile = new \Bitrix\Main\IO\File($MODULE_PATH_ABS.$DepName);
				$DepFileContent = $depFile->getContents();
				$DepFileContent = str_replace(
						[
								'X\Module',
								'x_module',
								'x.module',
								'Minisol',
								'https://minisol.ru',
								'X_MODULE_',
							],
						[
								$dctProps['CLASS'],
								$dctProps['MODULE_CLASS'],
								$dctProps['MODULE_ID'],
								$PARTNER_NAME,
								$PARTNER_URI,
								$dctProps['MODULE_SP']
							],
						$DepFileContent
					);
				$depFile->putContents($DepFileContent);
			}
			
			//Генерация lib/module.php
			$lib_module = new \Bitrix\Main\IO\File($MODULE_PATH_ABS.'/lib/module.php');
			$lib_module->putContents(
'<?php
namespace '.$dctProps['CLASS'].';

\Bitrix\Main\Loader::includeModule(\'x.module\');
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

class Module extends \X\Module\Module
{
	function __construct(array $dctEnvModule=[])
	{
		if(!count($dctEnvModule)) $dctEnvModule = include(dirname(__DIR__).\'/.env.php\');
		return parent::__construct($dctEnvModule);
	}
}'
				);

			// очистка конфигураций
			$lstConfigs = [
					//'/.env.php', // автоконфигурация
					//'/.options.php', // опции
					'/.agents.php',
					'/.handlers.php',
					'/.settings.php'
					
				];
			foreach ($lstConfigs as $Config) {
				\Bitrix\Main\IO\File::putFileContents(
						$MODULE_PATH_ABS.$Config,
						'<? return [];'
					);
			}
			
			// удаление лишних файлов и папок
			$lstPatternsForDel = [
					'/admin',
					'/composer',
					'/lib/util',
					'/lib/admin.php',
					'/lib/modules.php',
					
					'/lang/ru/lib/admin.php',
					'/lang/ru/lib/util',
					'/lang/ru/admin/modules.php',
					
					'/*.md',
					
					'/_example_*',
					'/*/_example_*',
					'/*/*/_example_*',
					'/*/*/*/_example_*'
				];
			$lstPathesForDel = [];
			foreach ($lstPatternsForDel as $Pattern) {
				array_push($lstPathesForDel, ...glob($MODULE_PATH_ABS.$Pattern));
			}
			foreach ($lstPathesForDel as $Path) {
				(new \Bitrix\Main\IO\Directory($Path))->delete(true);
			}
			
		} else {
			$dctResponceParams['errors'][] = Loc::getMessage('X_MODULE_ADMIN_MODULES_ERRORINVALIDID');
		}

	}
	
	
	// процедура созданиея
    LocalRedirect("/bitrix/admin/x_module_modules.php");
}


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php"); // второй общий пролог

//if($_REQUEST["mess"] == "ok" && $ID>0)
//    CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("rub_saved"), "TYPE"=>"OK"));
//
//if($message)
//    echo $message->Show();
//elseif($rubric->LAST_ERROR!="")
//    CAdminMessage::ShowMessage($rubric->LAST_ERROR);


$lstModules = new \X\Module\Modules;


?>
<form method="POST" Action="<?echo $APPLICATION->GetCurPage()?>" ENCTYPE="multipart/form-data" name="post_form">
    <?// проверка идентификатора сессии ?>
    <?echo bitrix_sessid_post();?>
    <input type="hidden" name="lang" value="<?=LANG?>">
    <?
    // отобразим заголовки закладок
    $tabControl->Begin();

    //********************
    // первая закладка - список модулей
    //********************
    $tabControl->BeginNextTab();
    if ($lstModules->count()) {
		foreach ($lstModules as $module):
			$InstalledStatus = $module->isInstalled()?(Loc::getMessage('X_MODULE_ADMIN_MODULES_INSTALLED')):(Loc::getMessage('X_MODULE_ADMIN_MODULES_NOTINSTALLED'));
			echo \X\Module\Util\Html::adminTabRow(
					'<b>'.$module->MODULE_NAME.'</b> ('.$module->MODULE_ID.')<br>'.$module->MODULE_DESCRIPTION,
					$InstalledStatus
				);
			//\Kint::dump($module);
		endforeach;
	} else {
		echo \X\Module\Util\Html::adminTabRow(Loc::getMessage('X_MODULE_ADMIN_MODULES_NOMODULES'));
	}

    
    $tabControl->BeginNextTab();
	echo \X\Module\Util\Html::adminTabRow(
			Loc::getMessage('X_MODULE_ADMIN_MODULES_FIELDMODULEID'),
			\X\Module\Util\Html::optionInput('id', [])
		);
	echo \X\Module\Util\Html::adminTabRow(
			Loc::getMessage('X_MODULE_ADMIN_MODULES_FIELDPARTNER'),
			\X\Module\Util\Html::optionInput('partner_name', [])
		);
	echo \X\Module\Util\Html::adminTabRow(
			Loc::getMessage('X_MODULE_ADMIN_MODULES_FIELDPARTNERSITE'),
			\X\Module\Util\Html::optionInput('partner_uri', [])
		);
	echo \X\Module\Util\Html::adminTabRow('<input type="submit" name="create" value="'.Loc::getMessage('X_MODULE_ADMIN_MODULES_FIELDCREATED').'" class="adm-btn-save">');

	//********************
	// вторая закладка - параметры автоматической генерации рассылки
	//********************
	
	

    
    // завершение формы - вывод кнопок сохранения изменений

    $tabControl->End();
    ?>
</form>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>