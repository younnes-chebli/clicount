<?php
    require_once "framework/Model.php";
    require_once "model/Tricount.php";
    require_once "model/Repartition.php";
    require_once "model/User.php";

    class Operation extends Model {
        public function __construct(public string $title, public int $tricount, public float $amount, public $operation_date, public int $initiator, public $created_at  = null, public ?int $id = null) {}

        public static function get_operation_by_id($operation_id) : bool | Operation {
            $query = self::execute("SELECT * FROM operations WHERE id = :id", ["id" => $operation_id]);

            $row = $query->fetch();
    
            if ($query->rowCount() == 0) {
                return false;
            }
    
            return new Operation($row["title"], $row["tricount"], $row["amount"], $row["operation_date"], $row["initiator"], $row["created_at"], $row["id"]);
        }

        public static function get_all_operations_by_tricount($tricount_id) : array {
            $query = self::execute("SELECT * FROM operations WHERE tricount = :tricount ORDER BY operation_date ASC", ["tricount" => $tricount_id]);
            
            $data = $query->fetchAll();
            $res = [];
    
            foreach($data as $row){
                $res[] = new Operation($row["title"], $row["tricount"], $row["amount"], $row["operation_date"], $row["initiator"], $row["created_at"], $row["id"]);
            }
    
            return $res;
        }

        public function persist() : Operation {
            if(self::get_operation_by_id($this->id))
                self::execute("UPDATE operations SET title = :title, amount = :amount, operation_date = :operation_date, initiator = :initiator WHERE id=:id", ["id" => $this->id, "title" => $this->title, "amount" => $this->amount, "operation_date" => $this->operation_date, "initiator"=> $this->initiator]);
            else {
                self::execute("INSERT INTO operations (title, tricount, amount, operation_date, initiator) VALUES (:title, :tricount, :amount, :operation_date, :initiator)", ["title" => $this->title, "tricount" => $this->tricount, "amount" => $this->amount, "operation_date" => $this->operation_date, "initiator"=> $this->initiator]);
                $this->id = self::lastInsertId();
            }
              
            return $this;
        }

        public function delete() : void {
            self::execute("DELETE FROM repartitions WHERE operation = :operation", ["operation" => $this->id]);

            self::execute("DELETE FROM operations WHERE id = :id", ["id" => $this->id]);
        }

        public function validate_operation_date() : array {
            $errors = [];
            $today = date('Y-m-d');

            if (!(isset($this->operation_date) && $this->operation_date <= $today && strlen($this->operation_date) > 0)) {
                $errors[] = "Operation date must be at most today";
            }

            return $errors;
        }

        public function validate_title() : array {
            $errors = [];

            if (!(is_string($this->title) && strlen($this->title) >= 3 && strlen($this->title) <= 256)) {
                $errors[] = "Operation title must be between 3 and 256 characters";
            }

            return $errors;
        }

        public function validate_amount() : array {
            $errors = [];

            if (!(is_double($this->amount) && $this->amount > 0 && strlen($this->amount) > 0)) {
                $errors[] = "Amount must be a positive decimal";
            }

            return $errors;
        }    

        public function get_initiator_full_name() : bool | string {
            $query = self::execute("SELECT * FROM users WHERE id IN (SELECT initiator from operations WHERE id = :id)", ["id" => $this->id]);
            
            $data = $query->fetch();

            if($query->rowCount() == 0) {
                return false;
            }

            $res = $data["full_name"];
        
            return $res;
        }

        public function get_total_weight() : int {
            return Repartition::get_total_weight_by_operation($this->id);
        }

        public function get_user_weight($user_id) : bool | int {
            return Repartition::get_user_weight_by_operation($this->id, $user_id);
        }

        public function get_total_to_pay_by_user($user_id) : float {
            $user_weight = $this->get_user_weight($user_id); 
            $total_weight = $this->get_total_weight();
            $amount = $this->amount;
            if($total_weight != 0) {
                $total_to_pay = ($user_weight / $total_weight) * $amount;
            } else {
                $total_to_pay = 0;
            }
            return $total_to_pay;
        }

        public function get_participants_count() : bool | int {
            return Repartition::get_participants_count_by_operation($this->id);
        }

        public function get_participants() : array {
            $query = self::execute("SELECT * FROM users WHERE id IN (SELECT user FROM repartitions WHERE operation = :operation)", ["operation" => $this->id]);
            
            $data = $query->fetchAll();
            $res = [];
    
            foreach($data as $row){
                $res[] = new User($row["mail"], $row["hashed_password"], $row["full_name"], $row["role"], $row["iban"], $row["id"]);
            }
    
            return $res;
        }

        public function get_user_amount(int $participant_id) : float {
            return round(($this->get_user_weight($participant_id) / $this->get_total_weight()) * $this->amount, 2);
        }

        public function get_participants_ids() {
            $query = self::execute("SELECT * FROM repartitions WHERE operation = :operation",
                                    array("operation" => $this->id));
            
            $data = $query->fetchAll();
            $res = [];
    
            foreach($data as $row){
                $res[] = $row["user"];
            }
    
            return $res;
        }
        
        public function allocate_expense($user_id, $account) : float {
            $total_to_pay = $this->get_total_to_pay_by_user($user_id);
            $total_account = $account - $total_to_pay;

            if($user_id == $this->initiator){
                $total_account += $this->amount;
            }

            return $total_account;
        }

        public function get_tricount() : null | Tricount {
            return Tricount::get_tricount_by_id($this->tricount);
        }
    }
?>