<?php

require_once 'model/User.php';
require_once 'framework/View.php';
require_once 'controller/MyController.php';

class ControllerMain extends MyController {
    public function index() : void {
        if($this->user_logged()) {
            $this->redirect("tricount", "index");
        }
        
        (new View("main"))->show();
    }

    public function password_success_service() : void {
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);
        $res = "true";

        if(isset($_POST["password"]) && $_POST["password"] !== "") {
            $password = $_POST["password"];

            if (!User::check_password($password, $user->hashed_password)) {
                $res = "false";
            }
            
        }

        echo $res;
    }

    public function get_value_service() : void {
        echo Configuration::get('justvalidate');
    }

    public function mail_available_service() : void {
        $res = "true";

        if(isset($_POST["value"]) && $_POST["value"] !== ""){
            $user = User::get_user_by_mail($_POST["value"]);
            if($user)
                $res = "false";
        }

        echo $res;
    }

    public function mail_exists_service() : void {
        $res = "false";

        if(isset($_POST["value"])){
            $user = User::get_user_by_mail($_POST["value"]);
            if($user || $_POST["value"] == "")
                $res = "true";
        }

        echo $res;
    }

    public function mail_available_service_edit() : void {
        $res = "true";
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);

        if(isset($_POST["mail"]) && $_POST["mail"] !== ""){
            $mail = $_POST["mail"];
            $oldMail = $user->mail;
            $exists = User::get_user_by_mail($mail);

            if($exists && $mail !== $oldMail)
                $res = "false";
        }

        echo $res;
    }

    public function login() : void {
        $mail = '';
        $password = '';
        $errors = [];

        if (isset($_POST['mail']) && $_POST['mail'] != "" && isset($_POST['password'])) {
            $mail = $_POST['mail'];
            $password = $_POST['password'];

            $errors = User::validate_login($mail, $password);
            if (empty($errors)) {
                $user = User::get_user_by_mail($mail);
                $this->log_user($user);
            }
        }

        (new View("login"))->show(["mail" => $mail, "password" => $password, "errors" => $errors]);
    }

    public function signup() : void {
        $mail = "";
        $full_name = "";
        $iban = "";
        $iban_errors = [];
        $password = "";
        $password_confirm = "";
        $mail_errors = [];
        $full_name_errors = [];
        $passwords_errors = [];

        if(isset($_POST["mail"]) && isset($_POST["full_name"]) && isset($_POST["iban"]) && isset($_POST["password"]) && isset($_POST["password_confirm"])) {
            $mail = $_POST["mail"];
            $full_name = $_POST["full_name"];
            $iban = $_POST["iban"];
            $password = $_POST["password"];
            $password_confirm = $_POST["password_confirm"];

            $user = new User($mail, Tools::my_hash($password), $full_name, $role = "user", $iban);

            $mail_errors = array_merge($mail_errors, $user->validate_mail_unicity());
            $mail_errors = array_merge($mail_errors, $user->validate_mail());
            $full_name_errors = array_merge($full_name_errors, $user->validate_full_name());
            if($iban != "") {
                $iban_errors = array_merge($iban_errors, $user->validate_iban());
            }
            $passwords_errors = array_merge($passwords_errors, User::validate_passwords($password, $password_confirm));

            if(count($mail_errors) == 0 && count($full_name_errors) == 0 && count($passwords_errors) == 0 && count($iban_errors) == 0) {
                $user->persist();
                $user = User::get_user_by_mail($user->mail);
                $this->log_user($user);
            }
        }

        (new View("signup"))->show(["mail" => $mail, "full_name" => $full_name, "iban" => $iban, "iban_errors" => $iban_errors, "password" => $password, "password_confirm" => $password_confirm, "mail_errors" => $mail_errors, "full_name_errors" => $full_name_errors, "passwords_errors" => $passwords_errors]);
    }

    public function settings() : void {
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);
        $success = [];

        if (isset($_GET['param1']) && $_GET['param1'] === "ok")
        $success[] = "Profile saved!";

        (new View("settings"))->show(["user" => $user, "success" => $success]);
    }

    public function edit_profile() {
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);
        $mail = $user->mail;
        $old_mail = $mail;
        $full_name= $user->full_name;
        $iban = $user->iban;
        $mail_errors = [];
        $full_name_errors = [];
        $iban_errors = [];

        if(isset($_POST["mail"]) && isset($_POST["full_name"]) && isset($_POST["iban"])) {
            $new_mail = $_POST["mail"];
            $new_full_name = $_POST["full_name"];
            $new_iban = $_POST["iban"];
            if(trim($new_iban) == "") {
                $new_iban = null;
            }

            $user->mail = $new_mail;
            $user->full_name = $new_full_name;
            $user->iban = $new_iban;

            if($old_mail !== $new_mail)
                $mail_errors = array_merge($mail_errors, $user->validate_mail_unicity());
            $mail_errors = array_merge($mail_errors, $user->validate_mail());
            $full_name_errors = array_merge($full_name_errors, $user->validate_full_name());
            if($new_iban != null) {
                $iban_errors = array_merge($iban_errors, $user->validate_iban());
            }

            if(count($mail_errors) == 0 && count($full_name_errors) == 0 && count($iban_errors) == 0) {
                $user->persist();
                $this->redirect("main", "settings", "ok");
            }
        }

        (new View("edit_profile"))->show(["user" => $user, "mail_errors" => $mail_errors, "full_name_errors" => $full_name_errors, "mail" => $mail, "full_name" => $full_name, "iban" => $iban, "iban_errors" => $iban_errors]);
    }

    public function change_password() : void {
        $user = $this->get_user_or_redirect();
        $user = User::get_user_by_id($user->id);
        $actual_password = "";
        $new_password = "";
        $new_password_confirm = "";
        $actual_password_errors = [];
        $passwords_errors = [];

        if(isset($_POST["actual_password"]) && isset($_POST["new_password"]) && isset($_POST["new_password_confirm"])) {
            $actual_password = $_POST["actual_password"];
            $new_password = $_POST["new_password"];
            $new_password_confirm = $_POST["new_password_confirm"];

            $actual_password_errors = array_merge($actual_password_errors, $user->check_password_on_edit($actual_password));
            $passwords_errors = array_merge($passwords_errors, User::validate_passwords($new_password, $new_password_confirm));

            if(count($actual_password_errors) == 0 && count($passwords_errors) == 0) {
                $user->hashed_password = Tools::my_hash($new_password);
                $user->persist();
                $this->redirect("main", "settings", "ok");             
            }
        }

        (new View("change_password"))->show(["user" => $user, "actual_password" => $actual_password, "new_password" => $new_password, "new_password_confirm" => $new_password_confirm, "actual_password_errors" => $actual_password_errors, "passwords_errors" => $passwords_errors]);
    }
}
