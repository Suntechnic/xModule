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
        foreach ($lstModulesDirsPathes as $ModuleDirPathe) {
            if () {
                
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
}
