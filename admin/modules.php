<?
// подключим все необходимые файлы:
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог

$RIGHT = $APPLICATION->GetGroupRight('x.module');
if ($RIGHT != 'W') $APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));

if (!\Bitrix\Main\Loader::includeModule('x.module')) {
	return;
}


$lstTabs = array(
    array("DIV" => "modulеs", "TAB" => 'Модуля основанные на xModule', "ICON"=>"main_user_edit", "TITLE"=> 'Список модулей наследуемых от xModule'),
	array("DIV" => "create", "TAB" => 'Создание модуя', "ICON"=>"main_user_edit", "TITLE"=> 'Создание нового модуля на базе xModule')
);
$tabControl = new CAdminTabControl("tabControl", $lstTabs);

$selfModule = new \X\Module\Module;

if($REQUEST_METHOD == "POST" // проверка метода вызова страницы
        && $RIGHT=="W"          // проверка наличия прав на запись для модуля
        && check_bitrix_sessid()     // проверка идентификатора сессии
    ) {
    $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
	
	if ($request->get('create')) {
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
			
			//install/index.php
			$install_index = new \Bitrix\Main\IO\File($MODULE_PATH_ABS.'/install/index.php');
			$Install_indexContent = $install_index->getContents();
			$Install_indexContent = str_replace(
					[
							'X\Module','x_module','x.module','Minisol','https://minisol.ru'
						],
					[		$dctProps['CLASS'],	$dctProps['MODULE_CLASS'],$dctProps['MODULE_ID'],$PARTNER_NAME,$PARTNER_URI
						],
					$Install_indexContent
				);
			$install_index->putContents($Install_indexContent);
			
			//lang/ru/lib/module.php
			$lang_ru_lib_module = new \Bitrix\Main\IO\File($MODULE_PATH_ABS.'/lang/ru/lib/module.php');
			$Lang_ru_lib_moduleContent = $lang_ru_lib_module->getContents();
			$Lang_ru_lib_moduleContent = str_replace(
					[
							'X_M_MODULE_',				'Minisol',		'https://minisol.ru'
						],
					[		$dctProps['MODULE_SP'],		$PARTNER_NAME,	$PARTNER_URI
						],
					$Lang_ru_lib_moduleContent
				);
			$lang_ru_lib_module->putContents($Lang_ru_lib_moduleContent);
			
			//lib/module.php
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
					'/lib/util',
					'/lib/admin.php',
					'/lib/modules.php',
					
					'/lang/ru/lib/admin.php',
					
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
			$dctResponceParams['errors'][] = 'Некорректный id модуля';
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
			echo \X\Module\Util\Html::adminTabRow(
					'<b>'.$module->MODULE_NAME.'</b> ('.$module->MODULE_ID.')<br>'.$module->MODULE_DESCRIPTION,
					$module->isInstalled()?'Установлен':'Не установлен'
				);
			//\Kint::dump($module);
		endforeach;
	} else {
		echo \X\Module\Util\Html::adminTabRow('Пока нет модулей зависимых от xModule');
	}

    
    $tabControl->BeginNextTab();
	echo \X\Module\Util\Html::adminTabRow(
			'ID модуля',
			\X\Module\Util\Html::optionInput('id', [])
		);
	echo \X\Module\Util\Html::adminTabRow(
			'Название партнёра',
			\X\Module\Util\Html::optionInput('partner_name', [])
		);
	echo \X\Module\Util\Html::adminTabRow(
			'Адрес сайта партнёра',
			\X\Module\Util\Html::optionInput('partner_uri', [])
		);
	echo \X\Module\Util\Html::adminTabRow('<input type="submit" name="create" value="Создать" title="Создать модуль" class="adm-btn-save">');

	//********************
	// вторая закладка - параметры автоматической генерации рассылки
	//********************
	
	

    
    // завершение формы - вывод кнопок сохранения изменений

    $tabControl->End();
    ?>
</form>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>