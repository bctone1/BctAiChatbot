<?php
// modules/Module4/Module4.php
namespace Modules\Module4;

use Modules\ModuleInterface;

class Module4 implements ModuleInterface {
    private $active = false;

    public function activate() {
        $this->active = true;
    }

    public function deactivate() {
        $this->active = false;
    }

    public function isActive() {
        return $this->active;
    }

    public function getName() {
        return 'Module 4';
    }

    public function getDescription() {
        return 'This is Module 4.';
    }
}


