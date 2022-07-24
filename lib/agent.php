<?php
namespace X\Module;

\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

class Agent
{

    
    public static function Iterator (): string
    {
        $module = new \X\Module\Module;
        $n = $module->getOption('hiden_i');
        $module->setOption('hiden_i', intval($n)+1);
        return '\X\Module\Agent::Itereator();';
    }
    
}