<?php

require_once 'framework/Controller.php';
require_once 'model/Tricount.php';
require_once 'model/Operation.php';

class MyController extends Controller {
    public function index() : void {}

    private function is_participant_to_tricount($user, $tricount) : bool {
        return $tricount->is_participant($user);
    }

    public function auth($user, $tricount) {
        if(!$this->is_participant_to_tricount($user, $tricount))
            $this->redirect("tricount", "index");
    }

    public function get_tricount_or_redirect($tricount_id) : Tricount {
        $tricount = Tricount::get_tricount_by_id($tricount_id);

        if(!$tricount)
                $this->redirect("tricount", "index");

        return $tricount;
    }

    public function get_operation_or_redirect($operation_id) : Operation {
        $operation = Operation::get_operation_by_id($operation_id);

        if(!$operation)
                $this->redirect("tricount", "index");

        return $operation;
    }
}

?>

