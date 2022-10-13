<?php
namespace X\Module;

\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

class Module extends \CModule
{
    // параметры модуля
    public $MODULE_SPACE; // пространство размещения модуля (x)
	public $MODULE_UID; // идентификатора модуля (module)
    public $MODULE_CODE; // код модуля - идентификатор в апперкейсе (MODULE)
    
    public $MODULE; // deprecated
    public $MODULE_ID; // id модуля (x.module)
    
    public $MODULE_BXCLASS; // bitrix-класс модуля (x_module)
    
    public $MODULE_NS; // неймспейс классов модуля (\X\Module)
	
	public $MODULE_DIR; // имя папки модуля == MODULE или MODULE_ID
    public $MODULE_PATH; // путь к папке модуля
	public $MODULE_PATH_ABS; // абсолютный путь к папке модуля
    
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    
    // конфиги для загрузки
    private $CONFIG = [];
    
    
    private $LogDirPath = false;
    private $debug = false;
    
    function __construct(array $dctEnvModule=[]) {
        
        if (!count($dctEnvModule)) $dctEnvModule = include(dirname(__DIR__).'/.env.php');
        
        // определение путенй
        $this->MODULE_PATH_ABS = $dctEnvModule['PATH_ABS'];
        $this->MODULE_PATH = $dctEnvModule['PATH'];
        $this->MODULE_DIR = $dctEnvModule['DIR'];
        
        // именование модуля
        $this->MODULE_ID = $dctEnvModule['ID'];
        $this->MODULE = $dctEnvModule['ID'];
        
        $this->MODULE_SPACE = $dctEnvModule['SPACE'];
        $this->MODULE_UID = $dctEnvModule['UID'];
        $this->MODULE_CODE = $dctEnvModule['CODE'];
        
        $this->MODULE_BXCLASS = $this->MODULE_SPACE.'_'.$this->MODULE_UID;
        
        // пространство имен
        $this->MODULE_NS = $dctEnvModule['NS'];
		
        // строковй префикс
		$this->MODULE_SP = strtoupper($this->MODULE_SPACE).'_'.$this->MODULE_CODE."_";
        
        // загрузка данных о модуле
        $this->loadVersion();
        $this->loadDdscription();
        
        // отладка
        $this->LogDirPath =
            \Bitrix\Main\Application::getDocumentRoot()
            .'/local/log/modules/'
            .$this->MODULE_DIR;
        
        if ($this->getOption('debug') == 'Y') $this->debug = 1;
    }
    
    private function loadDdscription ()
    {
        $this->MODULE_NAME = GetMessage($this->MODULE_SP.'INSTALL_NAME');
		$this->MODULE_DESCRIPTION = GetMessage($this->MODULE_SP.'INSTALL_DESCRIPTION');
		$this->PARTNER_NAME = GetMessage($this->MODULE_SP.'PARTNER');
		$this->PARTNER_URI = GetMessage($this->MODULE_SP.'PARTNER_URI');
    }
    
    private function loadVersion ()
    {
        $arModuleVersion = array();
        include($this->MODULE_PATH_ABS.'/install/version.php');
        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }
    }
    
    public function loadComposerLibs ()
    {
        include($this->MODULE_PATH_ABS.'/composer/vendor/autoload.php');
    }
    
    public function getSetup (string $SetupName): array
    {
        $SetupFile = '.'.strtolower($SetupName).'.php';
        if (!is_array($this->CONFIG[$SetupName])) {
            if (\Bitrix\Main\IO\File::isFileExists($this->MODULE_PATH_ABS.'/'.$SetupFile))
                $this->CONFIG[$SetupName] = include($this->MODULE_PATH_ABS.'/'.$SetupFile);
            if (!is_array($this->CONFIG[$SetupName])) $this->CONFIG[$SetupName] = [];
        }
        return $this->CONFIG[$SetupName];
    }
    
    
    /*
     * возвращает масив перехватчиков
    */
    public function getHandlers (): array
    {
        return $this->getSetup('handlers');
    }
    
    /*
     * возвращает масив настроек модуля
    */
    public function getOptions (): array
    {
        return $this->getSetup('options');
    }
    #
    
    /*
     * возвращает опции установленные для модуля
    */
    public function getOptionsSets (): array
    {
        return \Bitrix\Main\Config\Option::getForModule($this->MODULE_ID);
    }
    #
    
    /*
     * возвращает ключ хранения опции в БД по коду опции
    */
    public function getOptionKey (string $code): string
    {
        return $code;
    }
    
    
    public function getOption (string $code)
    {
        $refOptions = $this->getOptions();
        if ($refOptions[$code]) $default = $refOptions[$code]['default'];
        return \Bitrix\Main\Config\Option::get(
                $this->MODULE,
                $this->getOptionKey($code),
                $default
            );
    }
    
    public function setOption (string $code, $value)
    {
        return \Bitrix\Main\Config\Option::set(
                $this->MODULE,
                $this->getOptionKey($code),
                $value
            );
    }
    
    public function getSettings (string $key='')
    {
        $dctSettings = $this->getSetup('settings');
        if ($key) return $dctSettings[$key];
        return $dctSettings;
    }
    
    public function getAjaxControllers (): array
    {
        $refControllers = $this->getSettings('controllers')['value']['namespaces'];
        if (!is_array($refControllers)) return [];
        return $refControllers;
    }
    
    /*
     * возвращает паметр конфигурации, или секуцию конфигурации, или полностью конфигурационный массив
    */
	public function getConfig (string $section='', string $param='')
    {
        $dctSettings = $this->getSetup('config');
        if ($section == '') return $dctSettings;
        if ($param == '') return $dctSettings[$section];
        return $dctSettings[$section][$param];
    }
    #
	
    /*
     * вовзрващает компоненты модуля
    */
	public function getComponents ()
    {
		return array_map(
				function ($p) {return str_replace($this->MODULE_PATH_ABS.'/install/components/'.$this->MODULE_SPACE.'/','',$p);}, 
				glob($this->MODULE_PATH_ABS.'/install/components/'.$this->MODULE_SPACE.'/[abcdefghijklmnopqrstuvwxyz\.]*')
			);
    }
    #
    
    /*
     * возвращает список файлов из папки /admin/
    */
    public function getAdminFiles ()
    {
		return array_map(
				function ($p) {return basename($p);}, 
				glob($this->MODULE_PATH_ABS.'/admin/[-_abcdefghijklmnopqrstuvwxyz\.]*.php')
			);
    }
    #
    
    /*
     * возвращает список библиотек модуля
    */
    public function getJs() {
		return array_map(
				function ($p) {return str_replace($this->MODULE_PATH_ABS.'/install/js/'.$this->MODULE_SPACE.'/','',$p);}, 
				glob($this->MODULE_PATH_ABS.'/install/js/'.$this->MODULE_SPACE.'/[abcdefghijklmnopqrstuvwxyz\.]*')
			);
    }
    #
    
    /*
     * возвращает список папок с изображениями для модуля
     * обычно /install/images/{MODULE_SPACE}/{MODULE_UID}
    */
    public function getImg () {
		return array_map(
				function ($p) {return str_replace($this->MODULE_PATH_ABS.'/install/images/'.$this->MODULE_SPACE.'/','',$p);}, 
				glob($this->MODULE_PATH_ABS.'/install/images/'.$this->MODULE_SPACE.'/[abcdefghijklmnopqrstuvwxyz\.]*')
			);
    }
    #
	
    #TODO: придумать обобщенный способ автозагрузки сущностьей - просто переместить /tables/ в lib?
    private $regEntities = false;
    private $lstEntities = false;
    public function regEntities ()
    {
        if ($this->regEntities === false) {
            $this->regEntities = [];
            
            $arFilesEntity = glob($this->MODULE_PATH_ABS.'/tables/[abcdefghijklmnopqrstuvwxyz]*.php');
            if (count($arFilesEntity)) {
                foreach ($arFilesEntity as $file) {
                    $entityName = '\\'.ucfirst($this->MODULE_SPACE)
                            .'\\'.ucfirst($this->MODULE_UID)
                            .'\\'.ucfirst(str_replace('.php','',basename($file))).'Table';
                    $this->regEntities[$entityName] = str_replace(\Bitrix\Main\Application::getDocumentRoot(),'',$file);
                }
                
                \Bitrix\Main\Loader::registerAutoLoadClasses(null, $this->regEntities);
            }
        }
        return $this->regEntities;
    }
	public function getEntities (): array
    {
        if ($this->lstEntities === false) {
            $this->lstEntities = [];
            foreach ($this->regEntities() as $entityName=>$_) {
                $this->lstEntities[] = $entityName::getEntity();
            }
        }
		
        return $this->lstEntities;
    }
    
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // методы установки
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    public function InstallFiles () {
        
		// компоненты
		$arComponents = $this->getComponents();
		if (count($arComponents)) {
			foreach ($arComponents as $compName) {
				CopyDirFiles(
						$this->MODULE_PATH_ABS.'/install/components/'.$this->MODULE_SPACE.'/'.$compName,
						\Bitrix\Main\Application::getDocumentRoot().'/bitrix/components/'.$this->MODULE_SPACE.'/'.$compName,
						true,
						true
					);
			}
		}
        
        // js
		$arJs = $this->getJs();
		if (count($arJs)) {
			foreach ($arJs as $libName) {
				CopyDirFiles(
						$this->MODULE_PATH_ABS.'/install/js/'.$this->MODULE_SPACE.'/'.$libName,
						\Bitrix\Main\Application::getDocumentRoot().'/bitrix/js/'.$this->MODULE_SPACE.'/'.$libName,
						true,
						true
					);
			}
		}
        
        
        // картинки
        // штатно размещаются в /install/images/{MODULE_SPACE}/{MODULE_UID}
		$arImg = $this->getImg();
		if (count($arImg)) {
			foreach ($arImg as $GroupName) {
				CopyDirFiles(
						$this->MODULE_PATH_ABS.'/install/images/'.$this->MODULE_SPACE.'/'.$GroupName,
						\Bitrix\Main\Application::getDocumentRoot().'/bitrix/images/'.$this->MODULE_SPACE.'/'.$GroupName,
						true,
						true
					);
			}
		}
        
        
        // файлы админки
		$arAdminFiles = $this->getAdminFiles();
		if (count($arAdminFiles)) {
			foreach ($arAdminFiles as $admFile) {
                
                $contentAdminFile = '<?
                $path = $_SERVER[\'DOCUMENT_ROOT\'].\''.$this->MODULE_PATH.'/admin/'.$admFile.'\';
                if (file_exists($path)) require($path);
                ';
                
                $pathAdminFile =\Bitrix\Main\Application::getDocumentRoot().'/bitrix/admin/'.$this->MODULE_BXCLASS.'_'.$admFile;
                
                $adminFile = new \Bitrix\Main\IO\File($pathAdminFile);
                
                if (!$adminFile->isExists()) {
                    $adminFile->putContents($contentAdminFile);
                }
			}
		}
        
        
        
        
        return true;
    }
    
    public function UnInstallFiles() {
        
		// компоненты
		$arComponents = $this->getComponents();
		if (count($arComponents)) {
			foreach ($arComponents as $compName) {
				DeleteDirFilesEx('/bitrix/components/'.$this->MODULE_SPACE.'/'.$comp_name);
			}
		}
        
        // файлы адаминки
        $arAdminFiles = $this->getAdminFiles();
		if (count($arAdminFiles)) {
			foreach ($arAdminFiles as $admFile) {
				DeleteDirFilesEx('/bitrix/admin/'.$this->MODULE_BXCLASS.'_'.$admFile);
			}
		}
        
        // js
		$arJs = $this->getJs();
		if (count($arJs)) {
			foreach ($arJs as $libName) {
				DeleteDirFilesEx('/bitrix/js/'.$this->MODULE_SPACE.'/'.$libName);
			}
		}
        
        // картинки
		$arImg = $this->getImg();
		if (count($arImg)) {
			foreach ($arImg as $GroupName) {
				CopyDirFiles(
						$this->MODULE_PATH_ABS.'/install/images/'.$this->MODULE_SPACE.'/'.$GroupName,
						\Bitrix\Main\Application::getDocumentRoot().'/bitrix/images/'.$this->MODULE_SPACE.'/'.$GroupName,
						true,
						true
					);
			}
		}
		
		// файлы в upload если модуль насоздавал
		DeleteDirFilesEx('/upload/'.$this->MODULE_ID);
        return true;
    }
	
	
	public function InstallTables() {
		$lstEntities = $this->getEntities();
		if (count($lstEntities)) {
			$connection = \Bitrix\Main\Application::getInstance()->getConnection();
			
			foreach ($lstEntities as $entity) {
				$tableName = $entity->getDBTableName();
				
				if (!$connection->isTableExists($tableName)) {
					$entity->createDbTable();
				}
			}
		}
		
        return true;
    }
    
    public function UnInstallTables() {
		$lstEntities = $this->getEntities();
		if (count($lstEntities)) {
			$connection = \Bitrix\Main\Application::getInstance()->getConnection();
			
			foreach ($lstEntities as $entity) {
				$tableName = $entity->getDBTableName();
				
				if ($connection->isTableExists($tableName)) $connection->dropTable($tableName);
			}
		}
        return true;
    }
	
	public function InstallEvents () {
		foreach ($this->getHandlers() as $arEvent) {
            \Bitrix\Main\EventManager::getInstance()->registerEventHandler(
                    $arEvent['module'],
					$arEvent['event'],
					$this->MODULE_ID,
					$arEvent['class'], 
					$arEvent['method']
                );
		}
		return true;
	}

	public function UnInstallEvents () {
		foreach ($this->getHandlers() as $arEvent) {
			\Bitrix\Main\EventManager::getInstance()->unRegisterEventHandler(
					$arEvent['module'],
					$arEvent['event'],
					$this->MODULE_ID,
					$arEvent['class'], 
					$arEvent['method']
				);
		}
		return true;
	}
    
    public function getAgents ()
    {
        return $this->getSetup('agents');
	}
    
    public function InstallAgents (): bool
    {
        foreach ($this->getAgents() as $dctAgent) {
            if ($dctAgent['name'] && $dctAgent['interval']) {
                \CAgent::AddAgent(
                        $dctAgent['name'], 
                        $this->MODULE_ID, 
                        $dctAgent['period']?$dctAgent['period']:'N', 
                        $dctAgent['interval']
                    );
            }
            
        }
		return true;
	}
    
    public function UnInstallAgents (): bool
    {
        \CAgent::RemoveModuleAgents($this->MODULE_ID);
		return true;
	}
    
    
    
    public function DoInstall() {
		global $DB, $APPLICATION, $step;
        
        $this->InstallFiles();
        $this->InstallTables();
        RegisterModule($this->MODULE_ID);
        $this->InstallEvents();
        $this->InstallAgents();
        
        $installScript =  new \Bitrix\Main\IO\File($this->MODULE_PATH_ABS.'/install/install.php');
        if ($installScript->isExists()) include($installScript->getPath());
        
		$APPLICATION->IncludeAdminFile(
				GetMessage($this->MODULE_SP."INSTALL_TITLE"),
				$this->MODULE_PATH_ABS.'/install/step.php'
			);
    }
    
    public function DoUninstall() {
		global $DB, $APPLICATION, $step;
        
        $this->UnInstallAgents();
        $this->UnInstallEvents();
        UnRegisterModule($this->MODULE_ID);
        $this->UnInstallTables();
        $this->UnInstallFiles();
        
        $unInstallScript =  new \Bitrix\Main\IO\File($this->MODULE_PATH_ABS.'/install/uninstall.php');
        if ($unInstallScript->isExists()) include($unInstallScript->getPath());
        
		$APPLICATION->IncludeAdminFile(
				GetMessage($this->MODULE_SP."INSTALL_TITLE"),
				$this->MODULE_PATH_ABS.'/install/unstep.php'
			);
    }
	
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // debug
    
    public function log (string $Msg, array $arData=[], string $FileName='main' ) {
        if (!$this->debug) return;
        
        $Data = "-------------------------------------------------------------------------------\n"
                .date('d.m.Y H:i:s')."\n"
                .$Msg."\n"
                .print_r($arData,true)."\n\n";
        \Bitrix\Main\IO\File::putFileContents($this->LogDirPath.'/'.$FileName.'.log', $Data, \Bitrix\Main\IO\File::APPEND);
    }
    
    /*
     * возвращает список объектов io файлов-логов
    */
    public function getLogs (): array
    {
        if (!$this->LogDirPath) return [];
        
        $lstLogsFiles = [];
        $logDirectory = new \Bitrix\Main\IO\Directory($this->LogDirPath);
        if ($logDirectory->isExists() && $logDirectory->isDirectory()) {
            foreach ($logDirectory->getChildren() as $logFile) {
                if ($logFile->isFile()
                        && $logFile->getExtension() == 'log'
                    ) {
                    $lstLogsFiles[] = $logFile;
                }
            }
        }
        
        return $lstLogsFiles;
    }
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // файлы документации
    
    private $parserMD;
    public function getDoc (string $FileName)
    {
        $FileName = strtoupper($FileName).'.md';
        
        if (\Bitrix\Main\IO\File::isFileExists($this->MODULE_PATH_ABS.'/'.$FileName)) {
            $file = new \Bitrix\Main\IO\File($this->MODULE_PATH_ABS.'/'.$FileName);
            
            if (!$this->parserMD) $this->parserMD = new \cebe\markdown\MarkdownExtra();
            return $this->parserMD->parse($file->getContents());
        }
        return '';
    }
    
    
}