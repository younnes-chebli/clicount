<?php

    require_once "framework/Model.php";
    require_once "model/Tricount.php";
    require_once "model/Repartition.php";
    require_once "model/Operation.php";

    class User extends Model {
        public function __construct(public string $mail, public string $hashed_password, public string $full_name, public string $role, public ?string $iban = null, public ?int $id = null) {}

        public static function get_user_by_mail($mail) : bool | User {
            $query = self::execute("SELECT * FROM users WHERE mail = :mail", ["mail" => $mail]);

            $data = $query->fetch();

            if ($query->rowCount() == 0) {
                return false;
            }

            return new User($data["mail"], $data["hashed_password"], $data["full_name"], $data["role"], $data["iban"], $data["id"]);
        }

        public static function get_user_by_id($id) : bool | User {
            $query = self::execute("SELECT * FROM users WHERE id = :id", ["id" => $id]);

            $data = $query->fetch();

            if ($query->rowCount() == 0) {
                return false;
            }

            return new User($data["mail"], $data["hashed_password"], $data["full_name"], $data["role"], $data["iban"], $data["id"]);
        }

        public static function validate_login(string $mail, string $password) : array {
            $errors = [];
            $user = User::get_user_by_mail($mail);

            if ($user) {
                if (!self::check_password($password, $user->hashed_password)) {
                    $errors[] = "Wrong password. Please try again.";
                }
            } else {
                $errors[] = "Can't find a user with the mail '$mail'. Please sign up.";
            }

            return $errors;
        }

        public static function check_password(string $clear_password, string $hash) : bool {
            return $hash === Tools::my_hash($clear_password);
        }

        private static function validate_password($password) : string {
            if (strlen($password) < 8 || strlen($password) > 512) {
                return "Password must be between 8 and 512 characters";
            }
            if (!((preg_match("/[A-Z]/", $password)) && preg_match("/\d/", $password) && preg_match("/['\";:,.\/?\\-]/", $password))) {
                return "Password must contain at least 1 number, 1 uppercase letter and 1 punctuation mark.";
            }

            return "";
        }

        public static function validate_passwords($password, $password_confirm) : array {
            $errors = [];

            if(self::validate_password($password) != "")
                $errors[] = self::validate_password($password);
            if($password != $password_confirm) {
                $errors[] = "Please enter same passwords";
            }

            return $errors;
        }

        public function persist() : User {
            if(self::get_user_by_id($this->id))
                self::execute("UPDATE users SET mail = :mail, hashed_password = :hashed_password, full_name = :full_name, role = :role, iban = :iban WHERE id=:id", ["id" => $this->id, "mail" => $this->mail, "hashed_password" => $this->hashed_password, "full_name" => $this->full_name, "role" => $this->role, "iban" => $this->iban]);
            else
                self::execute("INSERT INTO users (mail, hashed_password, full_name, role, iban) VALUES (:mail, :hashed_password, :full_name, :role, :iban)", ["mail" => $this->mail, "hashed_password" => $this->hashed_password, "full_name" => $this->full_name, "role" => $this->role, "iban" => $this->iban == "" ? null : $this->iban]);
                
            return $this;
        }

        public function check_password_on_edit($clear_password) : array {
            $errors = [];

            if($this->hashed_password !== Tools::my_hash($clear_password)) {
                $errors[] = "Wrong password. Please try again.";
            }

            return $errors;
        }
        
        public function validate_mail_unicity() : array {
            $errors = [];
            $user = self::get_user_by_mail($this->mail);

            if($user) {
                $errors[] = "Mail already exists";
            }

            return $errors;
        }

        public function validate_mail() : array {
            $errors = [];

            if (!(isset($this->mail) && is_string($this->mail) && (filter_var($this->mail, FILTER_VALIDATE_EMAIL)) && strlen($this->mail) <= 256)) {
                $errors[] = "Mail is not valid";
            }

            return $errors;
        }

        public function validate_full_name() : array {
            $errors = [];

            if (!(isset($this->full_name) && is_string($this->full_name) && strlen($this->full_name) >= 3 && strlen($this->full_name) <= 256)) {
                $errors[] = "Fullname must be between 3 and 256 characters";
            }

            return $errors;
        }

        public function validate_iban() : array {
            $errors = [];

            if (!(isset($this->full_name) && is_string($this->iban) && strlen($this->iban) >= 20 && strlen($this->iban) <= 256)) {
                $errors[] = "IBAN must be between 20 and 256 characters";
            }

            return $errors;
        }

        public function get_all_tricounts() : array {
            return Tricount::get_all_by_user($this);
        }

        public function is_participant_to_operation($operation_id) : bool {
            return Repartition::is_participant_to_operation($this->id, $operation_id);
        }

        public function is_participant_to_tricount($tricount) : bool {
            return $tricount->is_participant($this);
        }

        public function get_total_to_pay($operations) : float {
            $res = 0;

            foreach($operations as $operation) {
                $res += $operation->get_total_to_pay_by_user($this->id);
            }

            $res = round($res, 2);
            return $res;
        }
    }

?>