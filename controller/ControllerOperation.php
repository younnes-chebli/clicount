<?php

require_once 'framework/View.php';
require_once 'model/Operation.php';
require_once 'model/Tricount.php';
require_once 'model/Repartition.php';
require_once 'controller/MyController.php';

class ControllerOperation extends MyController {
    public function index() : void {
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);
        $operation = "";
        $tricount = "";
        $previous = "";
        $next = "";
        $initiator_name = "";
        $formated_operation_date = "";
        $participants_count = "";
        $participants_lines = [];
        $success = [];
        $amounts_by_operation_participant = [];

        if(isset($_GET["param1"]) && $_GET["param1"] !== "") {
            $operation_id = $_GET["param1"];
            $operation = $this->get_operation_or_redirect($operation_id);
            $initiator_name = $operation->get_initiator_full_name();
            $tricount_id = $operation->tricount;
            $tricount = Tricount::get_tricount_by_id($tricount_id);
            $operations = $tricount->get_all_operations();
            $participants_count = $operation->get_participants_count();

            $operation_date_parts = explode("-", $operation->operation_date);
            for($i = count($operation_date_parts) - 1; $i >= 0; $i--) {
                $formated_operation_date = $formated_operation_date . "- " . $operation_date_parts[$i];
            }

            $actual = array_search($operation, $operations);
            $actual == 0 ? $previous = null : $previous = $operations[$actual - 1];
            $actual == (count($operations) - 1) ? $next = null : $next = $operations[$actual + 1];

            $operation_participants = $operation->get_participants();
            foreach($operation_participants as $operation_participant) {
                $participants_lines[] = $operation_participant;
                $amounts_by_operation_participant[$operation_participant->id] = $operation->get_user_amount($operation_participant->id);
            }

            if (isset($_GET['param2']) && $_GET['param2'] === "ok")
            $success[] = "Operation saved!";
        } else {
            throw new Exception("Missing param");
        }

        (new View("operation"))->show(["user" => $user, "operation" => $operation, "tricount" => $tricount, "previous" => $previous, "next" => $next, "initiator_name" => $initiator_name, "formated_operation_date" => $formated_operation_date, "participants_count" => $participants_count, "participants_lines" => $participants_lines, "success" => $success, "amounts_by_operation_participant" => $amounts_by_operation_participant]);
    }

    public function delete_operation_service() : void {
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);
        $operation_id = "";

        if( isset($_POST["operation_id"])){
            $operation = Operation::get_operation_by_id($_POST["operation_id"]);
            $tricount = Tricount::get_tricount_by_id($operation->tricount);
            $this->auth($user, $tricount);
            $operation->delete();
        }

        echo $operation_id;
    }

    public function edit_operation() : void {
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);
        $operation = "";
        $title = "";
        $amount = "";
        $amount_errors = [];
        $operation_date = "";
        $initiator = "";
        $operation_participants = [];
        $tricount_participants = [];
        $checked_ids = [];
        $weights_by_id = [];
        $repartitions = [];
        $checkbox_error = "";
        $operation_participants_ids = [];
        $weight_errors = [];
        $tricount_participants_json = "";

        if(isset($_GET["param1"]) && $_GET["param1"] !== "") {
            $operation_id = $_GET["param1"];
            $operation = $this->get_operation_or_redirect($operation_id);
            $tricount_id = $operation->tricount;
            $tricount = Tricount::get_tricount_by_id($tricount_id);

            $this->auth($user, $tricount);

            $title = $operation->title;
            $title_errors = [];
            $amount = $operation->amount;
            $operation_date = $operation->operation_date;
            $operation_date_errors = [];
            $initiator_id = $operation->initiator;
            $initiator = User::get_user_by_id($initiator_id);
            $operation_participants = $operation->get_participants();
            $tricount_participants = $tricount->get_participants();
            $tricount_participants_json = $tricount->get_participants_as_json();
            $tricount_participants_ids = [];
            foreach($tricount_participants as $tricount_participant) {
                $tricount_participants_ids[] = $tricount_participant->id;
            }
            foreach($operation_participants as $operation_participant) {
                $checked_ids[] = $operation_participant->id;
            }
            $operation_participants_ids = $checked_ids;
            $repartitions = Repartition::get_all_repartitions_by_operation($operation_id);
            foreach($repartitions as $repartition) {
                $weights_by_id[$repartition->user] = $repartition->weight;
            }

            if(isset($_POST["title"]) && isset($_POST["amount"]) && isset($_POST["operation_date"]) && $_POST["operation_date"] != "" && isset($_POST["initiator"]) && $_POST["initiator"] != "") {
                $new_title = $_POST["title"];
                $new_amount = floatval($_POST["amount"]);
                $new_operation_date = $_POST["operation_date"];
                $new_initiator = $_POST["initiator"];
                if (!empty($_POST["participants"])) {
                    $checked_ids = $_POST["participants"];
                    foreach($checked_ids as $checked_id) {
                        $checked_id = intval($checked_id);
                    }
                } else {
                    $checkbox_error = "Choose at least one!";
                }
                if(!empty($_POST["weight"])) {
                    $weights_by_id = $_POST["weight"];
                    foreach($weights_by_id as $weight_by_id) {
                        $weight_by_id = intval($weight_by_id);
                    }
                }
                
                $operation->title = $new_title;
                $operation->amount = $new_amount;
                $operation->operation_date = $new_operation_date;
                $operation->initiator = $new_initiator;
                
                $title_errors = array_merge($title_errors, $operation->validate_title());
                $amount_errors = array_merge($amount_errors, $operation->validate_amount());
                $operation_date_errors = array_merge($operation_date_errors, $operation->validate_operation_date());

                if(count($title_errors) == 0 && count($operation_date_errors) == 0 && count($amount_errors) == 0 && $checkbox_error == "") {
                    foreach($tricount_participants_ids as $tricount_participant_id) {
                        if(!in_array($tricount_participant_id, $checked_ids)) {
                            $repartition = Repartition::get_repartition($operation->id, $tricount_participant_id);
                            if($repartition) {
                                $repartition->delete();
                            }    
                        }
                    }
                    
                    foreach($checked_ids as $checked_id) {
                        $repartition = new Repartition($operation->id, $checked_id, intval($weights_by_id[$checked_id]));
                        $weight_errors = array_merge($weight_errors, $repartition->validate_weight());

                        if(count($weight_errors) == 0) {
                            $repartition->persist();
                        }
                    }

                    if(count($weight_errors) == 0){
                        $operation->persist();

                        $repartitions = Repartition::get_all_repartitions_by_operation($operation->id);
        
                        $this->redirect("operation", "index", $operation->id, "ok");
                    }
                }
            }
        } else {
            throw new Exception("Missing param");
        }

        (new View("edit_operation"))->show(["user" => $user, "operation" => $operation, "title" => $title, "title_errors"=> $title_errors, "amount" => $amount, "amount_errors" => $amount_errors, "operation_date" => $operation_date, "operation_date_errors" => $operation_date_errors, "initiator" => $initiator, "tricount_participants" => $tricount_participants, "checked_ids" => $checked_ids, "weights_by_id" => $weights_by_id, "repartitions" => $repartitions, "checkbox_error" => $checkbox_error, "operation_participants_ids" => $operation_participants_ids, "weight_errors" => $weight_errors, "tricount_participants_json" => $tricount_participants_json]);
    }

    public function delete_operation_confirm() : void {
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);
        $operation = "";

        if(isset($_GET["param1"]) && $_GET["param1"] != "") {
            $operation_id = $_GET["param1"];
            $operation = $this->get_operation_or_redirect($operation_id);
            $tricount = $operation->get_tricount();

            $this->auth($user, $tricount);

        } else {
            throw new Exception("Missing param");
        }

        (new View("delete_operation_confirm"))->show(["user" => $user, "operation" => $operation]);
    }

    public function delete_operation() : void {
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);

        if(isset($_POST["param"]) && $_POST["param"] != "") {
            $operation_id = $_POST["param"];
            $operation = $this->get_operation_or_redirect($operation_id);
            $tricount = $operation->get_tricount();

            $this->auth($user, $tricount);

            if($operation) {
                $operation->delete();
                $this->redirect("tricount", "tricount", $operation->tricount);
            } else {
                throw new Exception("Action non permitted");
            }
        }
    }

    public function add_operation() : void {
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);
        $tricount = "";
        $operation = "";
        $title = "";
        $title_errors = [];
        $amount = "";
        $amount_errors = [];
        $operation_date = date('Y-m-d');
        $operation_date_errors = [];
        $initiator = "";
        $tricount_participants = [];
        $checked_ids = [];
        $checkbox_error = "";
        $weight_errors = [];
        $weights_by_id = [];
        $tricount_participants_json = "";

        if(isset($_GET["param1"]) && $_GET["param1"] != "") {
            $tricount_id = $_GET["param1"];
            $tricount = $this->get_tricount_or_redirect($tricount_id);
            $tricount_participants = $tricount->get_participants();
            $tricount_participants_json = $tricount->get_participants_as_json($operation);

            $this->auth($user, $tricount);

            if(isset($_POST["title"]) && isset($_POST["amount"]) && isset($_POST["operation_date"]) && isset($_POST["initiator"])) {
                $title = $_POST["title"];
                $amount = floatval($_POST["amount"]);
                $operation_date = $_POST["operation_date"];
                $initiator = $_POST["initiator"];
                if(!empty($_POST["participants"])) {
                    $checked_ids = $_POST["participants"];
                    foreach($checked_ids as $checked_id) {
                        $checked_id = intval($checked_id);
                    }
                } else {
                    $checkbox_error = "Choose at least one!";
                }
                if(!empty($_POST["weight"])) {
                    $weights_by_id = $_POST["weight"];
                    foreach($weights_by_id as $weight_by_id) {
                        $weight_by_id = intval($weight_by_id);
                    }
                }

                $operation = new Operation($title, $tricount->id, $amount, $operation_date, $initiator);

                $title_errors = array_merge($title_errors, $operation->validate_title());
                $amount_errors = array_merge($amount_errors, $operation->validate_amount());
                $operation_date_errors = array_merge($operation_date_errors, $operation->validate_operation_date());

                foreach($checked_ids as $checked_id) {
                    $weight_errors = array_merge($weight_errors, Repartition::validate_weight_on_add_operation($weights_by_id[$checked_id]));
                }

                if(count($title_errors) == 0 && count($operation_date_errors) == 0 && count($amount_errors) == 0 && $checkbox_error == "" && count($weight_errors) == 0) {
                    $operation->persist();

                    foreach($checked_ids as $checked_id) {
                        $repartition = new Repartition($operation->id, $checked_id, $weights_by_id[$checked_id]);

                        $repartition->persist();
                    }

                    $this->redirect("tricount", "tricount", $tricount->id);
                }
            }    
        } else {
            throw new Exception("Missing param");
        }

        (new View("add_operation"))->show(["user" => $user, "tricount" => $tricount, "operation" => $operation, "title" => $title, "title_errors" => $title_errors, "amount" => $amount, "amount_errors" => $amount_errors, "operation_date" => $operation_date, "operation_date_errors" => $operation_date_errors, "initiator"=> $initiator, "tricount_participants" => $tricount_participants, "checked_ids" => $checked_ids, "checkbox_error" => $checkbox_error, "weight_errors" => $weight_errors, "weights_by_id" => $weights_by_id, "tricount_participants_json" => $tricount_participants_json]);
    }
}

?>