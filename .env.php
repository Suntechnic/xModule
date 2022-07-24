<?
$tmpMODULE_PATH_ABS = __DIR__;
$tmpMODULE_DIR = basename($tmpMODULE_PATH_ABS);
$tmpMODULE_SPACE = explode('.',$tmpMODULE_DIR)[0];
$tmpMODULE_UID = explode('.',$tmpMODULE_DIR)[1];
return [
        'PATH_ABS' => $tmpMODULE_PATH_ABS, // абсолютный путь к папке модуля
        'PATH' => substr($tmpMODULE_PATH_ABS,strlen(\Bitrix\Main\Application::getDocumentRoot())), // путь к папке модуля от корня сайта
        'DIR' => $tmpMODULE_DIR, // имя папки модуля
        'ID' => $tmpMODULE_DIR, // id модуля
        'SPACE' => $tmpMODULE_SPACE, // пространство модуля (код партнера)
        'UID' => $tmpMODULE_UID, // индентификатор модуля
        'CODE' => strtoupper($tmpMODULE_UID), // код модуля (идентификатор в верхнем регистра)
        'NS' => '\\'.ucfirst($tmpMODULE_SPACE).'\\'.ucfirst($tmpMODULE_UID), // пространство имен модуля
        'CLASS' => '\\'.ucfirst($tmpMODULE_SPACE).'\\'.ucfirst($tmpMODULE_UID).'\Module', // класс модуля
    ];