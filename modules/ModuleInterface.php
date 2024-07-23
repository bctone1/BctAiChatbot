<?php
// modules/ModuleInterface.php
namespace Modules;

interface ModuleInterface {
    public function activate();
    public function deactivate();
    public function isActive();
    public function getName();
    public function getDescription();
}




?>