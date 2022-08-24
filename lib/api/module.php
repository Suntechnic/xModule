<?php
/*
                                {class}{method}
BX.ajax.runAction('x:module.api.module.config')
    .then(function(responce) {
        console.log(responce);
    });
*/
namespace X\Module\Api;

class Module extends \Bitrix\Main\Engine\Controller
{
    
    private $actionsConfig = [
            //'config' => [
            //        '-prefilters' => [
            //                '\Bitrix\Main\Engine\ActionFilter\Authentication'
            //            ]
            //    ],
        ];
    
    protected function init()
	{
        parent::init();
        foreach ($this->actionsConfig as $name=>$arConfig) $this->setActionConfig($name, $arConfig);
	}
    
    public function configAction ()
    {
        $selfModule = new \X\Module\Module();
        return [
                'assets' => [
                        'images' => '/bitrix/images/'.$selfModule->MODULE_SPACE.'/'.$selfModule->MODULE_UID
                    ],
                'version' => $selfModule->MODULE_VERSION
            ];
    }
}

