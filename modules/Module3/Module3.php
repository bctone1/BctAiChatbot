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
        $Menu = get_option('Menu', 'false');

        if($Menu === 'true'){
            return true;
        }else if($Menu === 'false'){
            return false;
        }
    }

    public function getName() {
        return 'Menu';
    }

    public function getDescription() {
        return 'Front Menu';
    }
}


?>
