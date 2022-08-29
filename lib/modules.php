<?php
namespace X\Module;

class Modules implements \Iterator
{
    protected $lstModules = [];

    protected $lstBitrixModuleDirs = [
            '/local/modules',
            '/bitrix/modules',
        ];
    
    function __construct()
    {
        $this->lstModules = [];
        
        $lstModulesDirsPathes = [];
        foreach ($this->lstBitrixModuleDirs as $BxModuleFolder) {
            array_push($lstModulesDirsPathes, ...glob(\Bitrix\Main\Application::getDocumentRoot().$BxModuleFolder.'/*'));
        }
        
        foreach ($lstModulesDirsPathes as $ModuleDirPath) {
            if (\Bitrix\Main\IO\File::isFileExists($ModuleDirPath.'/lib/module.php')) {
                $MODULE_ID = basename($ModuleDirPath);
                if ('x.module' != $MODULE_ID) {
                    if (!\Bitrix\Main\Loader::includeModule($MODULE_ID)) {
                        include($ModuleDirPath.'/lib/module.php');
                    }
                    $dctProps = \X\Module\Modules::getModuleProps($MODULE_ID);;
                    if ($dctProps) {
                        $ModuleObjectClass = '\\'.$dctProps['CLASS'].'\Module';
                        if (class_exists($ModuleObjectClass,true)
                                && get_parent_class($ModuleObjectClass) == 'X\Module\Module'
                            ) {
                            $this->lstModules[] = new $ModuleObjectClass;
                        }
                    }
            }
                
            }
        }
    }
    
    
    public function current()
    {
        return current($this->lstModules);
    }

    public function key()
    {
        return key($this->lstModules);
    }

    public function next(): void
    {
        next($this->lstModules);
    }

    public function rewind(): void
    {
        reset($this->lstModules);
    }

    public function valid(): bool
    {
        return null !== key($this->lstModules);
    }
    
    public function count(): int
    {
        return count($this->lstModules);
    }
    
    
    //
    static public function getModuleProps (string $MODULE_ID): array
    {
        $dctProps['MODULE_DIR'] = $MODULE_ID;
		
		$lstDebris = explode('.',$dctProps['MODULE_DIR']);

		$dctProps['MODULE_PNS'] = $lstDebris[0];
		$dctProps['MODULE_NAME'] = $lstDebris[1];
		$dctProps['MODULE_ID'] = $dctProps['MODULE_PNS'].'.'.$dctProps['MODULE_NAME'];
		
		$dctProps['MODULE_CLASS'] = $dctProps['MODULE_PNS'].'_'.$dctProps['MODULE_NAME'];
		$dctProps['MODULE_SP'] = strtoupper($dctProps['MODULE_PNS']).'_'.strtoupper($dctProps['MODULE_NAME']).'_';
		
		if ($dctProps['MODULE_DIR'] == $dctProps['MODULE_ID']) {
			$dctProps['PNS'] = ucfirst($dctProps['MODULE_PNS']);
			$dctProps['NAME'] = ucfirst($dctProps['MODULE_NAME']);
			$dctProps['CLASS'] = $dctProps['PNS'].'\\'.$dctProps['NAME'];
        } else return [];
        
        return $dctProps;
    }
    
}
