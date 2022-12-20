<?
if(!$USER->IsAdmin()) return;

$dctEnvModule = include(__DIR__.'/.env.php');

$RIGHT = $APPLICATION->GetGroupRight($dctEnvModule['ID']);
if ($RIGHT != 'W') $APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));

if (!\Bitrix\Main\Loader::includeModule($dctEnvModule['ID'])) {
	return;
}

$selfModule = new $dctEnvModule['CLASS']();

$lstModuleOptions = $selfModule->getOptions();
$lstModuleOptionsSets = $selfModule->getOptionsSets();
$lstModuleOptionsTech = [];
foreach ($lstModuleOptionsSets as $CodeOption=>$ValueOption) {
	if ($lstModuleOptions[$CodeOption]) continue;
	$lstModuleOptionsTech[$CodeOption] = ['title'=>$CodeOption];
}

// агенты
$lstModuleAgents = $selfModule->getAgents();

// логи
$lstLogs = $selfModule->getLogs();

// ajax контроллеры
$refAjaxControllers = $selfModule->getAjaxControllers();

$lstTabs = [];





if ($lstModuleOptions && $dctTab = \X\Module\Util\Options::getTab($selfModule,'options')) $lstTabs[] = $dctTab;

	
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Дополнительные вкладки модуля
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



if ($lstModuleAgents
		&& $dctTab = \X\Module\Util\Options::getTab($selfModule,'agents')) $lstTabs[] = $dctTab;
if ($refAjaxControllers
		&& $dctTab = \X\Module\Util\Options::getTab($selfModule,'ajax')) $lstTabs[] = $dctTab;
if ($lstLogs
		&& $dctTab = \X\Module\Util\Options::getTab($selfModule,'logs')) $lstTabs[] = $dctTab;
if ($lstModuleOptionsTech
		&& $dctTab = \X\Module\Util\Options::getTab($selfModule,'optionstech')) $lstTabs[] = $dctTab;
$lstMD = [
		'readme' => $selfModule->getDoc('README'),
		'changelog' => $selfModule->getDoc('CHANGELOG'),
	];
foreach ($lstMD as $key=>$val) {
	if ($val && strlen($val) > 2
			&& $dctTab = \X\Module\Util\Options::getTab($selfModule,$key)) $lstTabs[] = $dctTab;
}


$tabControl = new CAdminTabControl("tabControl", $lstTabs);

$back_url = '/bitrix/admin/settings.php?mid='.$selfModule->MODULE_ID.'&lang='.LANG.'&'.$tabControl->ActiveTabParam();

if($REQUEST_METHOD == "POST" // проверка метода вызова страницы
        && ($save!="" || $apply!="") // проверка нажатия кнопок "Сохранить" и "Применить"
        && $RIGHT=="W"          // проверка наличия прав на запись для модуля
        && check_bitrix_sessid()     // проверка идентификатора сессии
    ) {
    $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
    
	$refOptionsFromRequest = $request->get('options');
	

	// сохраняем настройки модуля
	if ($refOptionsFromRequest['module']) {
		// явные параметры модуля
		foreach ($lstModuleOptions as $codeOption=>$dctOpt) {
			if (isset($refOptionsFromRequest['module'][$codeOption])) {
				$selfModule->setOption($codeOption,$refOptionsFromRequest['module'][$codeOption]);
			} else {
				$selfModule->setOption($codeOption,null);
			}
		}
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Дополнительные параметры модуля
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		// технически параметры модуля
		foreach ($lstModuleOptionsTech as $codeOption=>$dctOpt) {
			if (isset($refOptionsFromRequest['module'][$codeOption])) {
				$selfModule->setOption($codeOption,$refOptionsFromRequest['module'][$codeOption]);
			}
		}
	}
	
	
	// удаление журналов
	$lstDelLogs = $request->get('deletelog');
	foreach ($lstDelLogs as $i=>$name) {
		if ($lstLogs[$i]->getName() === $name) {
			$lstLogs[$i]->delete();
		}
	}
	
	// запуск агентов
	$arAgents = $request->get('agents');
	foreach ($arAgents as $i=>$name) {
		if ($lstModuleAgents[$i]['name'] === $name) {
			$__start_time = hrtime(1);
			eval($name);
			$back_url.'&agents_result['.$i.']='.((hrtime(1)-$__start_time)/pow(10,9));
		}
	}
	
	LocalRedirect($back_url);	
}


?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php"); // второй общий пролог
?>
<?

//if($_REQUEST["mess"] == "ok" && $ID>0)
//    CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("rub_saved"), "TYPE"=>"OK"));
//
//if($message)
//    echo $message->Show();
//elseif($rubric->LAST_ERROR!="")
//    CAdminMessage::ShowMessage($rubric->LAST_ERROR);


?>
<form method="POST" ENCTYPE="multipart/form-data" name="post_form">
    <?// проверка идентификатора сессии ?>
    <?echo bitrix_sessid_post();?>
    <input type="hidden" name="lang" value="<?=LANG?>">
    <?
    // отобразим заголовки закладок
    $tabControl->Begin();
	foreach ($lstTabs as $dctTab) {
		$tabControl->BeginNextTab();
		
		if ($dctTab['SUBTITLE'])
				echo \X\Module\Util\Html::adminTabRow($dctTab['SUBTITLE']);
				
				
		
		
		
		if ($dctTab['DIV'] == 'options') {
			foreach ($lstModuleOptions as $codeOption=>$dctOpt):
				echo \X\Module\Util\Html::adminTabRow(
						$dctOpt['title'],
						\X\Module\Util\Html::optionInput($codeOption, $dctOpt, $selfModule->getOption($codeOption))
					);
			endforeach;
		} elseif ($dctTab['DIV'] == 'optionstech') {
			foreach ($lstModuleOptionsTech as $codeOption=>$dctOpt):
				echo \X\Module\Util\Html::adminTabRow(
						$dctOpt['title'],
						\X\Module\Util\Html::optionInput($codeOption, $dctOpt, $selfModule->getOption($codeOption))
					);
			endforeach;
		} elseif ($dctTab['DIV'] == 'agents') {
			
			$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
			$arAgentsResult = $request->get('agents_result');
			
			foreach ($lstModuleAgents as $i=>$dctAgent):
				echo \X\Module\Util\Html::adminTabRow(
						$dctAgent['title'],
						\X\Module\Util\Html::agent($dctAgent, $arAgentsResult[$i])
					);
			endforeach;
		} elseif ($dctTab['DIV'] == 'ajax') {
			
			foreach ($refAjaxControllers as $class=>$alias):
				echo \X\Module\Util\Html::adminTabRow(
						$class.' => '.$alias
					);
			endforeach;
			
		} elseif ($dctTab['DIV'] == 'logs') {
			foreach ($lstLogs as $i=>$fileLog):
				echo \X\Module\Util\Html::adminTabRow(
						\X\Module\Util\Html::log($i,$fileLog)
					);	
			endforeach;
		} elseif ($lstMD[$dctTab['DIV']]) {
			echo $lstMD[$dctTab['DIV']];
		} else {
			////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// Дополнительные вкладки модуля
			////////////////////////////////////////////////////////////////////////////////////////////////////////////
		}
	}	
    
    // завершение формы - вывод кнопок сохранения изменений
    $tabControl->Buttons(
            array(
              'disabled'=>($RIGHT<'W'),
              'back_url' => $back_url,
            )
        );
	
    $tabControl->End();
    ?>
</form>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
