<?php 
    require_once "framework/Model.php";

    class Repartition extends Model {
        public function __construct(public int $operation, public int $user, public int $weight){}

        public static function get_repartition($operation_id, $user_id) : Repartition | bool {
            $query = self::execute("SELECT * FROM repartitions WHERE operation = :operation AND user = :user", ["operation" => $operation_id, "user" => $user_id]);
            $data = $query->fetch();

            if ($query->rowCount() == 0) {
                return false;
            }

            $repartition = new Repartition($data["operation"], $data["user"], $data["weight"]);
            
            return $repartition;
        }

        public static function get_all_repartitions_by_operation($operation_id) : array {
            $query = self::execute("SELECT * FROM repartitions WHERE operation = :operation", ["operation" => $operation_id]);
            $data = $query->fetchAll();
            $res = [];

            foreach($data as $row){
                $res[] = new Repartition($row["operation"], $row["user"], $row["weight"]);
            }
            
            return $res;
        }

        public static function get_total_weight_by_operation($operation_id) : int {
            $total_weight = 0;
            $repartitions = Repartition::get_all_repartitions_by_operation($operation_id);

            foreach($repartitions as $repartition){
                $total_weight += $repartition->weight;
            }

            return $total_weight;
        }

        public static function get_user_weight_by_operation($operation, $user) : bool | int {
            $query = self::execute("SELECT * FROM repartitions WHERE operation = :operation AND user = :user", ["operation" => $operation, "user"=>$user]);

            $data = $query->fetch();

            if ($query->rowCount() == 0) {
                return false;
            }

            $repartition = new Repartition($data["operation"], $data["user"], $data["weight"]);

            return $repartition->weight;
        }

        public static function get_participants_count_by_operation($operation) : bool | int {
            $query = self::execute("SELECT COUNT(*) FROM repartitions WHERE operation = :operation", ["operation" => $operation]);
            
            $data = $query->fetch();

            if($query->rowCount() == 0) {
                return false;
            }

            $res = $data["COUNT(*)"];
        
            return $res;
        }

        public static function is_participant_to_operation($user_id, $operation_id) : bool {
            $query = self::execute("SELECT * FROM repartitions WHERE user = :user AND operation = :operation", ["user" => $user_id, "operation" => $operation_id]);

            if($query->rowCount() == 0) {
                return false;
            } else {
                return true;
            }
        }

        public static function validate_weight_on_add_operation($weight) : array {
            $errors = [];

            if (!($weight > 0)) {
                $errors[] = "Weight must be positive";
            }

            return $errors;
        }

        public function persist() : Repartition {
            if(self::get_repartition($this->operation, $this->user))
            self::execute("UPDATE repartitions SET weight = :weight WHERE operation = :operation AND user = :user", ["operation" => $this->operation, "user" => $this->user, "weight" => $this->weight]);

            else
            self::execute("INSERT INTO repartitions (operation, user, weight) VALUES (:operation, :user, :weight)", ["operation" => $this->operation, "user" => $this->user, "weight" => $this->weight]);

            return $this;
        }

        public function delete() : void {
            self::execute("DELETE FROM repartitions WHERE operation = :operation AND user = :user", ["operation" => $this->operation, "user" => $this->user]);
        }

        public function validate_weight() : array {
            $errors = [];

            if (!(is_integer($this->weight) && $this->weight > 0)) {
                $errors[] = "Weight must be positive";
            }

            return $errors;
        }
    }
?>