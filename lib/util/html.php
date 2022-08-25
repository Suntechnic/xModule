<?php
namespace X\Module\Util;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class Html
{
    
    public static function adminTabRow (string $title, string $conten=''): string
    {
        ob_start();
        if ($conten != ''):?>
            <tr>
				<td width="40%"><?=$title?></td>
				<td width="60%">
                    <?=$conten?>
                </td>
            </tr>
        <?else:?>
            <tr>
				<th colspan="2" style="text-align:center;"><?=$title?></th>
			</tr>
        <?endif;
        $result = ob_get_contents();
        ob_end_clean();
        
        return $result;
    }
    
    public static function agent (array $dctAgent, $TimeResult): string
    {
        ob_start();
        
        $InputId = 'agent_'.$i.'_'.(new \Bitrix\Main\Type\RandomSequence)->randString(8);
        ?>
            <input
                type="checkbox"
                id="<?=$InputId?>"
                name="agents[<?=$i;?>]"
                value="<?=$dctAgent['name']?>"
                class="adm-designed-checkbox"
            ><label
                    class="adm-designed-checkbox-label"
                    for="<?=$InputId?>"
                    title="<?=Loc::getMessage('X_MODULE_UTIL_HTML_RUN')?>"
                ></label>
            <?=$dctAgent['name']?>
            <?if ($TimeResult):?>
            <strong><?=Loc::getMessage('X_MODULE_UTIL_HTML_RUNED')?><?=$TimeResult?></strong>
            <?endif?>
        <?
        $result = ob_get_contents();
        ob_end_clean();
        
        return $result;
    }
    
    
    public static function log (integer $i, $fileLog): string
    {
        ob_start();
        $InputId = 'log_'.$i.'_'.(new \Bitrix\Main\Type\RandomSequence)->randString(8);
        ?>
            <?=$fileLog->getName();?>

            <input
                type="checkbox"
                id="<?=$InputId?>"
                name="deletelog[<?=$i;?>]"
                value="<?=$fileLog->getName();?>"
                class="adm-designed-checkbox"
            ><label
                    class="adm-designed-checkbox-label"
                    for="<?=$InputId?>"
                    title="<?=Loc::getMessage('X_MODULE_UTIL_HTML_DELETE')?>"
                ></label>
            <?=Loc::getMessage('X_MODULE_UTIL_HTML_DELETE')?>
            <pre style="max-width: 100%; max-height: 640px; overflow-x: scroll; text-align:left;">
                <?=$fileLog->getFileContents($fileLog->getPath());?>
            </pre>

        <?
        $result = ob_get_contents();
        ob_end_clean();
        
        return $result;
    }
    
    
    public static function optionInput (string $codeOption, array $dctOpt, $curVal=null, string $section='module'): string
    {
        ob_start();
        ?>
            <?if ($dctOpt['options']):
                if (is_array($dctOpt['options'])) {
                    $arOptions = $dctOpt['options'];
                } elseif (is_string($dctOpt['options'])) {
                    $arOptions = $selfModule->{$dctOpt['options']}();
                }
                ?>
                <select name="options[<?=$section?>][<?=$codeOption;?>]" class="typeselect">
                    <option value=""></option>
                    <?foreach($arOptions as $Val=>$title):?>
                    <option value="<?=$Val?>" <?if($Val == $curVal):?>selected<?endif?>><?=$title?></option>
                    <?endforeach?>
                </select>
            <?elseif ($dctOpt['value']):
                $Val=$dctOpt['value'];
                
                $InputId = $codeOption.'_'.(new \Bitrix\Main\Type\RandomSequence)->randString(8);?>
                
                <input
                        type="checkbox"
                        id="<?=$InputId?>"
                        name="options[<?=$section?>][<?=$codeOption;?>]"
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
                        name="options[<?=$section?>][<?=$codeOption;?>]"
                        value="<?=$curVal?>"
                    />
            <?endif?>
        <?
        $result = ob_get_contents();
        ob_end_clean();
        
        return $result;
    }
    
}