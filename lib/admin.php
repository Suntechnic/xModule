<?php
namespace X\Module {

    class Admin {
        public static function buildMenu (&$aGlobalMenu, &$aModuleMenu) {
            
            
            foreach ($aModuleMenu as $i=>$arMenu) {
                if ($arMenu['items_id'] == 'menu_util') {
                    $aModuleMenu[$i]['items'][]= array(
                            "parent_menu" => "menu_util", 
                            //"icon" => "default_menu_icon",
                            //"page_icon" => "default_page_icon",
                            "sort"=>"900",
                            "text"=>"Создание модулей",
                            "title"=>"Модуль конструктор и основание для других модулей",
                            "url" => "/bitrix/admin/x_module_modules.php"
                            
                        );
                    break;
                }
            }
            //\Kint::dump($aModuleMenu);
        }
        
    }

}