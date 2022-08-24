<?php
// файл должен возвращать массив параметров модуля
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
return [
        'debug' => [
                'title' => Loc::getMessage('X_MODULE_OPTIONS_DEBUG'),
                'value' => 'Y'
            ],
//		'option_text' => [
//                'title' => 'Текстовое значение',
//                'default' => 'Текст'
//            ],
//        'option_checkbox' => [
//                'title' => 'Флажок',
//                'default' => '',
//                'value' => 92
//            ],
//		'option_select' => [
//                'title' => 'Список',
//                'default' => '',
//                'options' => [
//                    1 => 'Один',
//                    2 => 'Два',
//                    3 => 'Три'
//                ]
//            ]
    ];