<?
if(!$USER->IsAdmin()) return;

$dctEnvModule = include(__DIR__.'/.env.php');

$RIGHT = $APPLICATION->GetGroupRight($dctEnvModule['ID']);
if ($RIGHT != 'W') $APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));

if (!\Bitrix\Main\Loader::includeModule($dctEnvModule['ID'])) {
	return;
}

$rnd = new \Bitrix\Main\Type\RandomSequence;
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

$lstTabs = [];

if ($lstModuleOptions) $lstTabs[] = [
		'DIV' => 'options_module',
		'TAB' => 'Общие параметры', 'ICON'=>'main_user_edit', 'TITLE'=> 'Общие параметры модуля'
	];
if ($lstModuleOptionsTech) $lstTabs[] = [
		'DIV' => 'options_tech_module',
		'TAB' => 'Технические параметры', 'ICON'=>'main_user_edit', 'TITLE'=> 'Технические параметры модуля'
	];

if ($lstModuleAgents) $lstTabs[] = [
		'DIV' => 'agents_module',
		'TAB' => 'Агенты', 'ICON'=>'main_user_edit', 'TITLE'=> 'Агенты модуля'
	];

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
			}
		}
		// технически параметры модуля
		foreach ($lstModuleOptionsTech as $codeOption=>$dctOpt) {
			if (isset($refOptionsFromRequest['module'][$codeOption])) {
				$selfModule->setOption($codeOption,$refOptionsFromRequest['module'][$codeOption]);
			}
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
		if ($dctTab['DIV'] == 'options_module') {
			foreach ($lstModuleOptions as $codeOption=>$dctOpt): $curVal = $selfModule->getOption($codeOption);?>
			<tr>
				<td width="40%"><?=$dctOpt['title']?></td>
				<td width="60%">
				<?if ($dctOpt['options']):
					if (is_array($dctOpt['options'])) {
						$arOptions = $dctOpt['options'];
					} elseif (is_string($dctOpt['options'])) {
						$arOptions = $selfModule->{$dctOpt['options']}();
					}
					?>
					<select name="options[module][<?=$codeOption;?>]" class="typeselect">
						<option value=""></option>
						<?foreach($arOptions as $Val=>$title):?>
						<option value="<?=$Val?>" <?if($Val == $curVal):?>selected<?endif?>><?=$title?></option>
						<?endforeach?>
					</select>
				<?elseif ($dctOpt['value']):
					$Val=$dctOpt['value'];
					
					$InputId = $codeOption.'_'.$rnd->randString(8);?>
					
					<input
							type="checkbox"
							id="<?=$InputId?>"
							name="options[module][<?=$codeOption;?>]"
							value="<?=$Val?>"
							<?if($Val == $curVal):?>checked="checked"<?endif?>
							class="adm-designed-checkbox"
						><label
								class="adm-designed-checkbox-label"
								for="<?=$InputId?>"
								title="<?=$dctOpt['title']?>"
							></label>
				<?else:?>
					<input
							type="text"
							name="options[module][<?=$codeOption;?>]"
							value="<?=$curVal?>"
						/>
				<?endif?>
				</td>
			</tr>
			<?endforeach;
		} elseif ($dctTab['DIV'] == 'options_tech_module') {
			?>
			<tr>
				<th colspan="2" style="text-align:center;">Внимание! Не изменяйте эти параметры если не знаете что это такое!</th>
			</tr>
			<?foreach ($lstModuleOptionsTech as $codeOption=>$dctOpt):?>
				<tr>
					<td width="40%"><?=$dctOpt['title']?></td>
					<td width="60%"><input
							type="text"
							name="options[module][<?=$codeOption;?>]"
							value="<?=$selfModule->getOption($codeOption)?>"
						/></td>
				</tr>
			<?endforeach;
		} elseif ($dctTab['DIV'] == 'agents_module') {
			
			$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
			$arAgentsResult = $request->get('agents_result');
			
			foreach ($lstModuleAgents as $i=>$dctAgent):
				$InputId = 'agent_'.$i.'_'.$rnd->randString(8);
				$TimeResult = $arAgentsResult[$i];
				?>
				<tr>
					<th colspan="2" style="text-align:center;">Отмеченные агенты модуля будут выполнены после сохранения настроек в том же хите.</th>
				</tr>
				<tr>
					<td width="40%"><?=$dctAgent['title']?></td>
					<td width="60%">
						
						<input
							type="checkbox"
							id="<?=$InputId?>"
							name="agents[<?=$i;?>]"
							value="<?=$dctAgent['name']?>"
							class="adm-designed-checkbox"
						><label
								class="adm-designed-checkbox-label"
								for="<?=$InputId?>"
								title="Выполнить"
							></label>
						<?=$dctAgent['name']?>
						<?if ($TimeResult):?>
						<strong>Выполнен. Время выполнения: <?=$TimeResult?></strong>
						<?endif?>
					</td>
				</tr>
			<?endforeach;
		} else {
			
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