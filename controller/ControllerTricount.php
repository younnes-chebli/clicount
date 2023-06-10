<?php

require_once 'framework/View.php';
require_once 'model/User.php';
require_once 'model/Tricount.php';
require_once 'controller/MyController.php';

class ControllerTricount extends MyController {
    
    public function index() : void {
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);
        $tricounts = $user->get_all_tricounts();

        (new View("list_tricounts"))->show(["user" => $user, "tricounts" => $tricounts]);
    }

    public function title_available_service() : void {
        $tricounts = "";

        if(isset($_GET["param1"]) && $_GET["param1"] !== ""){
            $tricounts = Tricount::get_tricounts_by_creator_as_json($_GET["param1"]);
        }

        echo $tricounts;
    }

    public function title_available_justvalidate_service() : void {
        $res = "true";

        if(isset($_POST["title"]) && $_POST["title"] !== "" && isset($_POST["creator"]) && $_POST["creator"] !== ""){
            $exists = Tricount::get_tricount_by_title_by_creator($_POST["title"], $_POST["creator"]);

            if($exists)
                $res = "false";
        }

        echo $res;
    }

    public function title_available_justvalidate_service_edit() : void {
        $res = "true";

        if(isset($_POST["title"]) && $_POST["title"] !== "" && isset($_POST["creator"]) && $_POST["creator"] !== "" && isset($_POST["oldTitle"]) && $_POST["oldTitle"] !== ""){
            $title = $_POST["title"];
            $oldTitle = $_POST["oldTitle"];
            $exists = Tricount::get_tricount_by_title_by_creator($_POST["title"], $_POST["creator"]);

            if($exists && $title !== $oldTitle)
                $res = "false";
        }

        echo $res;
    }

    public function get_participants_service() : void {
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);
        $participants= "";

        if(isset($_GET["param1"]) && $_GET["param1"] !== ""){
            $tricount = Tricount::get_tricount_by_id($_GET["param1"]);
            $this->auth($user, $tricount);
            $participants = $tricount->get_participants_as_json();
        }

        echo $participants;
    }

    public function get_persons_service() : void {
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);
        $persons= "";

        if(isset($_GET["param1"]) && $_GET["param1"] !== ""){
            $tricount = Tricount::get_tricount_by_id($_GET["param1"]);
            $this->auth($user, $tricount);
            $persons = $tricount->get_non_participants_as_json();
        }

        echo $persons;
    }

    public function get_deletables_service() : void {
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);
        $deletables= "";

        if(isset($_GET["param1"]) && $_GET["param1"] !== ""){
            $tricount = Tricount::get_tricount_by_id($_GET["param1"]);
            $this->auth($user, $tricount);
            $deletables = $tricount->get_deletables_as_json();
        }

        echo $deletables;
    }

    public function add_participant_service() : void {
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);
        $participant_id = "";

        if(isset($_GET["param1"]) && $_GET["param1"] !== ""
                && isset($_POST["participant_id"])){
            $tricount = Tricount::get_tricount_by_id($_GET["param1"]);
            $participant_id = $_POST["participant_id"];
            $this->auth($user, $tricount);
            $tricount->add_participation($participant_id);
        }

        echo $participant_id;
    }

    public function delete_participant_service() : void {
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);
        $participant_id = "";

        if(isset($_GET["param1"]) && $_GET["param1"] !== ""
                && isset($_POST["participant_id"])){
            $tricount = Tricount::get_tricount_by_id($_GET["param1"]);
            $participant_id = $_POST["participant_id"];
            $this->auth($user, $tricount);
            $tricount->delete_participation($participant_id);
        }

        echo $participant_id;
    }

    public function delete_tricount_service() : void {
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);
        $tricount_id = "";

        if( isset($_POST["tricount_id"])){
            $tricount = Tricount::get_tricount_by_id($_POST["tricount_id"]);
            $this->auth($user, $tricount);
            $tricount->delete();
        }

        echo $tricount_id;
    }

    public function tricount() : void {
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);
        $tricount = "";
        $operations = [];
        $total = 0;
        $my_total = 0;
        $success = [];

        if(isset($_GET["param1"]) && $_GET["param1"] !== "") {
            $tricount_id = $_GET["param1"];
            $tricount = $this->get_tricount_or_redirect($tricount_id);
            $total = $tricount->get_total();
            $operations = $tricount->get_all_operations();
            $my_total = $user->get_total_to_pay($operations);

            if (isset($_GET['param2']) && $_GET['param2'] === "ok")
            $success[] = "Tricount saved!";    
        } else {
            throw new Exception("Missing param");
        }

        (new View("tricount"))->show(["user" => $user, "tricount" => $tricount, "operations" => $operations, "total" => $total, "my_total" => $my_total, "success" => $success]);
    }

    public function delete_tricount_confirm() : void{
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);

        if(isset($_GET["param1"]) && $_GET["param1"] !== "" ){
            $id_tricount = $_GET["param1"];
            $tricount = $this->get_tricount_or_redirect($id_tricount);
        }
        (new View("delete_tricount_confirm"))->show(["user" => $user, "tricount" => $tricount]);
    }

    public function delete_tricount() : void{
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);

        if(isset($_POST["tricount"]) && $_POST["tricount"] != "") {
            $tricount_id = $_POST["tricount"];
            $tricount = $this->get_tricount_or_redirect($tricount_id);

            $this->auth($user, $tricount);

            $tricount->delete();
            $this->redirect("tricount");
        }
    }
    
    public function add_tricount() : void {
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);
        $title = "";
        $description = "";
        $title_errors = [];
        $description_errors = [];

        if(isset($_POST["title"]) && isset($_POST["description"])) {
            $title = $_POST["title"];
            $description = $_POST["description"];

            $tricount = new Tricount($title, $user->id, $description);

            $title_errors = array_merge($title_errors, $tricount->validate_title());
            $description_errors = array_merge($description_errors, $tricount->validate_description());

            if(count($title_errors) == 0 && count($description_errors) == 0) {
                $tricount->persist();
                $this->redirect("tricount", "index");
            }
        }

        (new View("add_tricount"))->show(["user" => $user, "title" => $title, "title_errors" => $title_errors, "description" => $description, "description_errors" => $description_errors]);
    }

    public function edit_tricount() : void {
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);
        $title = "";
        $description = "";
        $title_errors = [];
        $description_errors = [];
        $participants = [];
        $persons = [];
        $deletables = [];

        if(isset($_GET["param1"]) && $_GET["param1"] !== "") {
            $tricount_id = $_GET["param1"];
            $tricount = $this->get_tricount_or_redirect($tricount_id);

            $this->auth($user, $tricount);

            $title = $tricount->title;
            $description = $tricount->description;    
            $participants = $tricount->get_participants();
            $participants_json = $tricount->get_participants_as_json();
            $persons = $tricount->get_non_participants();
            $persons_json = $tricount->get_non_participants_as_json();
            $deletables = $tricount->get_deletables();
            $deletables_json = $tricount->get_deletables_as_json();
            if(isset($_POST["title"]) && isset($_POST["description"]) && isset($_POST["person"])) {
                $new_title = $_POST["title"];
                $new_description = $_POST["description"];

                $tricount->title = $new_title;
                $tricount->description = $new_description;

                if($title !== $new_title)
                    $title_errors = array_merge($title_errors, $tricount->validate_title());
                $description_errors = array_merge($description_errors, $tricount->validate_description());

                if(count($title_errors) == 0 && count($description_errors) == 0) {
                    $tricount->persist();

                    if($_POST["person"] != "Add Participant") {
                        $person_id = $_POST["person"];
                        $tricount->add_participation(intval($person_id));
                    }
                    
                    $this->redirect("tricount", "tricount", $tricount->id, "ok");
                }
            }
        } else {
            throw new Exception("Missing param");
        }

        (new View("edit_tricount"))->show(["user" => $user, "tricount" => $tricount, "title" => $title, "title_errors" => $title_errors, "description" => $description, "description_errors" => $description_errors, "participants" => $participants, "persons" => $persons,"deletables"=>$deletables, "participants_json" => $participants_json, "persons_json" => $persons_json, "deletables_json" => $deletables_json]);
    }

    public function delete_participant_confirm() : void {
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);

        if(isset($_GET["param1"]) && $_GET["param1"] !== "" && isset($_GET["param2"]) && $_GET["param2"] !== ""  ){
            $participant_id = $_GET["param1"];
            $tricount_id = $_GET["param2"];
            $tricount = $this->get_tricount_or_redirect($tricount_id);
            $participant = User::get_user_by_id($participant_id);

            $this->auth($user, $tricount);
        }
        (new View("delete_participant_confirm"))->show(["user" => $user, "participant" => $participant,"tricount" => $tricount]);
    }

    public function delete_participant() : void {
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);

        if(isset($_POST["participant"]) && $_POST["participant"] != "" && isset($_POST["tricount"]) && $_POST["tricount"] != "") {
            $participant_id = $_POST["participant"];
            $tricount_id = $_POST["tricount"];
            $tricount = Tricount::get_tricount_by_id($tricount_id);

            $this->auth($user, $tricount);

            $tricount->delete_participation($participant_id);
            
            if($user->is_participant_to_tricount($tricount)) {
                $this->redirect("tricount", "tricount", $tricount->id, "ok");
            } else {
                $this->redirect("tricount", "index");
            }
        }
    }

    public function add_participant() : void {
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);
        $participants = [];
        $persons = [];
        $success = [];
        $deletables = [];

        if(isset($_GET["param1"]) && $_GET["param1"] !== "") {
            $tricount_id = $_GET["param1"];
            $tricount = Tricount::get_tricount_by_id($tricount_id);

            $this->auth($user, $tricount);

            $participants = $tricount->get_participants();
            $persons = $tricount->get_non_participants();
            $deletables = $tricount->get_deletables();

            if(isset($_POST["person"]) && $_POST["person"] != "Add Participant") {
                $person_id = $_POST["person"];
                $tricount->add_participation(intval($person_id));
                $this->redirect("tricount", "add_participant", $tricount->id, "ok");
        }

        if (isset($_GET['param2']) && $_GET['param2'] === "ok")
            $success[] = "New participant added!";
        } else {
            throw new Exception("Missing param");
        }

        (new View("add_participant"))->show(["user" => $user, "tricount" => $tricount, "participants" => $participants, "persons" => $persons, "success" => $success, "deletables" => $deletables]);
    }

    public function balance() : void {
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);
        $tricount = "";
        $operations = [];
        $participants = [];
        $balances = [];

        if(isset($_GET["param1"]) && $_GET["param1"] !== "") {
            $tricount_id = $_GET["param1"];
            $tricount = Tricount::get_tricount_by_id($tricount_id);
            $operations = $tricount->get_all_operations();
            $participants = $tricount->get_participants();

            foreach($participants as $participant) {
                $account = 0;

                foreach($operations as $operation) {

                    if($participant->id == $user->id) {
                        $name = $participant->full_name." (me)";
                    } else {
                        $name = $participant->full_name;
                    }
                    
                    $account = $operation->allocate_expense($participant->id, $account);
                    $account = round($account, 2);
                    $balances[$name] = $account;
                }
            }
            
        } else {
            throw new Exception("Missing param");
        }
    
        (new View("balance"))->show(["user" => $user, "balances" => $balances, "tricount"=>$tricount]);
    }
    
}

?>