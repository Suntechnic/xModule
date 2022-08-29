<?php
namespace X\Module\Util;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class Options
{
    
    /*
     * получает на вход объект модуля, код вкладки и возвращает массив вкладки или false,
     * если вкладка выключена в config.php
     * Так же может содержать $dctStrings - массив с заголовками вкладки,
     * иначе последние будут подключатся из X_MODULE_UTIL_OPTIONS_
    */
    public static function getTab ($module, string $code, array $dctStrings=[])
    {
        if (!$module->getConfig('optionspage','showtab_'.$code)) return false;
        
        $StrCode = strtoupper($code);
        
        $dctTab = $dctStrings;
        $dctTab['DIV'] = $code;
        if (!$dctTab['TAB']) $dctTab['TAB'] = Loc::getMessage('X_MODULE_UTIL_OPTIONS_TAB_'.$StrCode);
        if (!$dctTab['TITLE']) $dctTab['TITLE'] = Loc::getMessage('X_MODULE_UTIL_OPTIONS_TABTITLE_'.$StrCode);
        if (!$dctTab['SUBTITLE']) $dctTab['SUBTITLE'] = Loc::getMessage('X_MODULE_UTIL_OPTIONS_TABSUBTITLE_'.$StrCode);
        return $dctTab;
    }
    #
    
}