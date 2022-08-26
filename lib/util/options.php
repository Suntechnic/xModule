<?php
namespace X\Module\Util;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class Options
{
    
    
    
    
    
    
    
    public static function getTab ($module, string $code)
    {
        
        if (!$module->getConfig('optionspage','showtab_'.$code)) return false;
        
        $StrCode = strtoupper($code);
        $dctTab = [
                'DIV' => $code,
                'TAB' => Loc::getMessage('X_MODULE_UTIL_OPTIONS_TAB_'.$StrCode),
                'TITLE' => Loc::getMessage('X_MODULE_UTIL_OPTIONS_TABTITLE_'.$StrCode),
                'SUBTITLE' => Loc::getMessage('X_MODULE_UTIL_OPTIONS_TABSUBTITLE_'.$StrCode)
            ];
        return $dctTab;
    }
    
}