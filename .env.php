<?
$tmpMODULE_PATH_ABS = __DIR__;
$tmpMODULE_DIR = basename($tmpMODULE_PATH_ABS);
$tmpMODULE_SPACE = explode('.',$tmpMODULE_DIR)[0];
$tmpMODULE_UID = explode('.',$tmpMODULE_DIR)[1];

// переопределение путей для обхода мультисайтовости со ссылками 
$tmpDebrisMODULE_PATH_ABS = explode('/',$tmpMODULE_PATH_ABS);
$tmpMODULE_PATH = '/'.implode('/',array_slice($tmpDebrisMODULE_PATH_ABS,count($tmpDebrisMODULE_PATH_ABS)-3));
$tmpMODULE_PATH_ABS = \Bitrix\Main\Application::getDocumentRoot().$tmpMODULE_PATH;

return [
        'PATH_ABS' => $tmpMODULE_PATH_ABS, // абсолютный путь к папке модуля
        'PATH' => $tmpMODULE_PATH, // путь к папке модуля от корня сайта
        'DIR' => $tmpMODULE_DIR, // имя папки модуля
        'ID' => $tmpMODULE_DIR, // id модуля
        'SPACE' => $tmpMODULE_SPACE, // пространство модуля (код партнера)
        'UID' => $tmpMODULE_UID, // индентификатор модуля
        'CODE' => strtoupper($tmpMODULE_UID), // код модуля (идентификатор в верхнем регистра)
        'NS' => '\\'.ucfirst($tmpMODULE_SPACE).'\\'.ucfirst($tmpMODULE_UID), // пространство имен модуля
        'CLASS' => '\\'.ucfirst($tmpMODULE_SPACE).'\\'.ucfirst($tmpMODULE_UID).'\Module', // класс модуля
    ];
