<?php
// modules/Scenario/Scenario.php
namespace Modules\Scenario;

use Modules\ModuleInterface;

class Scenario implements ModuleInterface {
    private $active = false;

    public function activate() {
        $this->active = true;
    }

    public function deactivate() {
        $this->active = false;
    }

    public function isActive() {

        $Scenario_status = get_option('Scenario', 'false');

        if ($Scenario_status === 'true') {
            return true;
        } else if ($Scenario_status === 'false') {
            return false;
        }
        // return $this->active;
    }

    public function getName() {
        return 'Scenario';
    }

    public function getDescription() {
        return '시나리오 기능을 담당하는 모듈';
    }
}


?>
