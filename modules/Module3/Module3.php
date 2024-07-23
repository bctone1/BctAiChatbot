<?php
// modules/Module3/Module3.php
namespace Modules\Module3;

use Modules\ModuleInterface;

class Module3 implements ModuleInterface {
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
        return 'Module 3';
    }

    public function getDescription() {
        return 'This is Module 3.';
    }
}


?>
