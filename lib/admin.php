<?php

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

namespace X\Module {

    class Admin {
        public static function buildMenu (&$aGlobalMenu, &$aModuleMenu) {
            
            
            foreach ($aModuleMenu as $i=>$arMenu) {
                if ($arMenu['items_id'] == 'menu_util') {
                    $aModuleMenu[$i]['items'][]= array(
                            'parent_menu' => 'menu_util', 
                            //'icon' => 'default_menu_icon',
                            //'page_icon' => 'default_page_icon',
                            'sort' => '900',
                            'text' => Loc::getMessage('X_MODULE_ADMIN_TEXT'),
                            'title' => Loc::getMessage('X_MODULE_ADMIN_TITLE'),
                            'url'  =>  '/bitrix/admin/x_module_modules.php'
                            
                        );
                    break;
                }
            }
        }
        
    }

}