<?php
// modules/ModuleManager.php
namespace Modules;

class ModuleManager {
    private $modules = [];

    public function __construct() {
        $this->loadModules();
    }

    private function loadModules() {
        $moduleDirs = glob(__DIR__ . '/*' , GLOB_ONLYDIR);
        foreach ($moduleDirs as $dir) {
            $moduleFile = $dir . '/' . basename($dir) . '.php';
            if (file_exists($moduleFile)) {
                require_once $moduleFile;
                $className = '\\Modules\\' . basename($dir) . '\\' . basename($dir);
                if (class_exists($className) && in_array(ModuleInterface::class, class_implements($className))) {
                    array_unshift($this->modules, new $className());
                }
            }
        }
    }

    public function getModules() {
        return $this->modules;
    }

    public function activateModule($moduleName) {
        if (isset($this->modules[$moduleName])) {
            $this->modules[$moduleName]->activate();
        }
    }

    public function deactivateModule($moduleName) {
        if (isset($this->modules[$moduleName])) {
            $this->modules[$moduleName]->deactivate();
        }
    }
}






?>