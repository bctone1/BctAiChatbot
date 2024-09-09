<?php
// modules/STTEvaluation/STTEvaluation.php
namespace Modules\STTEvaluation;

use Modules\ModuleInterface;

class STTEvaluation implements ModuleInterface {
    private $active = false;
    

    public function activate() {
        $this->active = true;
    }

    public function deactivate() {
        $this->active = false;
    }

    public function isActive() {
        $STTEvaluation_status = get_option('STT Evaluation', 'false');

        if ($STTEvaluation_status === 'true') {
            return true;
        } else if ($STTEvaluation_status === 'false') {
            return false;
        }
    }
    

    public function getName() {
        return 'STT Evaluation';
    }

    public function getDescription() {
        return 'STT 성능 평가 모듈';
    }
}





?>