<?php
    require_once "framework/Model.php";
    require_once "model/User.php";
    require_once "model/Operation.php";

    class Tricount extends Model {
        public function __construct(public string $title, public int $creator, public ?string $description = null,  public $created_at = null, public ?int $id = null) {}

        public static function get_all_by_user($user) : array {

            $query = self::execute("SELECT * FROM tricounts WHERE id IN (SELECT tricount from subscriptions WHERE user = :user)", ["user" => $user->id]);
            
            $data = $query->fetchAll();
            $res = [];
    
            foreach($data as $row){
                $res[] = new Tricount($row["title"], $row["creator"], $row["description"], $row["created_at"], $row["id"]);
            }
    
            return $res;
        }
        
        public static function get_tricount_by_title_by_creator($title, $creator) : bool | Tricount {
            $query = self::execute("SELECT * FROM tricounts WHERE title = :title AND creator = :creator", ["title" => $title, "creator" => $creator]);

            $row = $query->fetch();
    
            if ($query->rowCount() == 0) {
                return false;
            }
    
            return new Tricount($row["title"], $row["creator"], $row["description"], $row["created_at"], $row["id"]);
        }

        public static function get_tricounts_by_creator($creator) : array {
            $query = self::execute("SELECT * FROM tricounts WHERE creator = :creator", ["creator" => $creator]);
    
            $data = $query->fetchAll();
            $res = [];
    
            foreach($data as $row){
                $res[] = new Tricount($row["title"], $row["creator"], $row["description"], $row["created_at"], $row["id"]);
            }
    
            return $res;
        }

        public static function get_tricount_by_id($tricount_id) : bool | Tricount {
            $query = self::execute("SELECT * FROM tricounts WHERE id = :id", ["id" => $tricount_id]);

            $row = $query->fetch();
    
            if ($query->rowCount() == 0) {
                return false;
            }
    
            return new Tricount($row["title"], $row["creator"], $row["description"], $row["created_at"], $row["id"]);
        }

        public function delete(){

            self::execute("DELETE FROM repartition_template_items WHERE repartition_template in (SELECT id FROM repartition_templates WHERE tricount = :tricount)", ["tricount" => $this->id]);
            self::execute("DELETE FROM repartition_templates WHERE tricount = :tricount", ["tricount" => $this->id]);
            self::execute("DELETE FROM repartitions WHERE operation in  (SELECT id FROM operations WHERE tricount = :tricount)", ["tricount" => $this->id]);
            self::execute("DELETE FROM operations WHERE tricount = :tricount", ["tricount" => $this->id]);
            self::execute("DELETE FROM subscriptions WHERE tricount = :tricount", ["tricount" => $this->id]);
            self::execute("DELETE FROM tricounts WHERE id = :id", ["id" => $this->id]);
        }

        public function persist() : Tricount {
            if(self::get_tricount_by_id($this->id))
                self::execute("UPDATE tricounts SET title = :title, description = :description WHERE id=:id", ["id" => $this->id, "title" => $this->title, "description" => $this->description]);
            else {
                self::execute("INSERT INTO tricounts (title, creator, description) VALUES (:title, :creator, :description)", ["title" => $this->title, "creator" => $this->creator, "description" => $this->description == "" ? NULL : $this->description]);
                $this->id = self::lastInsertId();
                $this->add_participation($this->creator);
            }
                
            return $this;
        }

        public function validate_title() : array {
            $errors = [];
            $tricount = self::get_tricount_by_title_by_creator($this->title, $this->creator);

            if (!(isset($this->title) && is_string($this->title) && strlen($this->title) >= 3 && strlen($this->title) <= 256)) {
                $errors[] = "Title must be between 3 and 256 characters";
            }

            if($tricount) {
                $errors[] = "Tricount already exists";
            }
            
            return $errors;
        }
        
        public function validate_description() : array {
            $errors = [];

            if (isset($this->description) && strlen($this->description) > 0) {
                if (!(is_string($this->description) && strlen(trim($this->description)) >= 3 && strlen($this->description) <= 1024)) {
                    $errors[] = "Description must be between 3 and 1024 characters";
                }
            }

            return $errors;
        }

        public function add_participation($user_id) : void {
            self::execute("INSERT INTO subscriptions (tricount, user) VALUES (:tricount, :user)", ["tricount" => $this->id, "user" => $user_id]);
        }

        public function delete_participation($user_id) : void {
            self::execute("DELETE FROM subscriptions WHERE tricount = :tricount and user = :user", ["tricount" => $this->id, "user" => $user_id]);
        }
        
        public function get_participants() : array {
            $query = self::execute("SELECT * FROM users WHERE id IN (SELECT user FROM subscriptions WHERE tricount = :tricount)
                                    ORDER BY users.full_name ASC", ["tricount" => $this->id]);
            
            $data = $query->fetchAll();
            $res = [];
    
            foreach($data as $row){
                $res[] = new User($row["mail"], $row["hashed_password"], $row["full_name"], $row["role"], $row["iban"], $row["id"]);
            }
    
            return $res;
        }
        
        public function get_non_participants() : array {
            $query = self::execute("SELECT * FROM users WHERE id NOT IN (SELECT user FROM subscriptions WHERE tricount = :tricount)
                                    ORDER BY users.full_name ASC", ["tricount" => $this->id]);
            
            $data = $query->fetchAll();
            $res = [];
    
            foreach($data as $row){
                $res[] = new User($row["mail"], $row["hashed_password"], $row["full_name"], $row["role"], $row["iban"], $row["id"]);
            }
    
            return $res;
        }

        public function get_deletables() : array | null {
            $query = self::execute("SELECT * FROM users WHERE id IN (SELECT user FROM subscriptions WHERE tricount = :id)
                                    AND id NOT IN (SELECT creator FROM tricounts WHERE id = :id)
                                    AND id NOT IN (SELECT id FROM users WHERE id IN (SELECT user from repartitions WHERE operation IN (SELECT id FROM operations WHERE tricount = :id)))",
                                    ["id" => $this->id]);

            $data = $query->fetchAll();
            $res = [];

            foreach($data as $row){
                $res[] = new User($row["mail"], $row["hashed_password"], $row["full_name"], $row["role"], $row["iban"], $row["id"]);
            }
    
            return $res;

        }
        
        public function get_all_operations() : array {
            return Operation::get_all_operations_by_tricount($this->id);
        }

        public function is_participant($user) : bool {
            $query = self::execute("SELECT * FROM users WHERE id IN (SELECT user FROM subscriptions WHERE tricount = :tricount AND user = :user)", ["tricount" => $this->id, "user" => $user->id]);
                
            if ($query->rowCount() == 0) {
                return false;
            }
    
            return true;
        }

        public function get_total() : float {
            $query = self::execute("SELECT SUM(amount) from operations WHERE id IN (SELECT id FROM operations WHERE tricount = :id)", ["id" => $this->id]);

            $res = $query->fetch();
            if($res[0] != null) {
                $res = round($res[0], 2);
            } else {
                $res = 0;
            }

            return $res;
        }

        public static function get_tricounts_by_creator_as_json($creator) : string {
            $tricounts = self::get_tricounts_by_creator($creator);
            $table = [];

            foreach($tricounts as $tricount) {
                $row = [];
                $row["title"] = $tricount->title;
                $table[] = $row;
            }

            return json_encode($table);
        }

        public function get_participants_as_json() : string {
            $participants = $this->get_participants();
            $table = [];

            foreach($participants as $participant) {
                $row = [];
                $row["id"] = $participant->id;
                $row["mail"] = $participant->mail;
                $row["full_name"] = $participant->full_name;
                $row["role"] = $participant->role;
                $row["iban"] = $participant->iban;
                $table[] = $row;
            }

            return json_encode($table);
        }

        public function get_non_participants_as_json() : string {
            $persons = $this->get_non_participants();
            $table = [];

            foreach($persons as $person) {
                $row = [];
                $row["id"] = $person->id;
                $row["mail"] = $person->mail;
                $row["full_name"] = $person->full_name;
                $row["role"] = $person->role;
                $row["iban"] = $person->iban;
                $table[] = $row;
            }

            return json_encode($table);
        }

        public function get_deletables_as_json() : string {
            $deletables = $this->get_deletables();
            $table = [];

            foreach($deletables as $deletable) {
                $row = [];
                $row = $deletable->id;
                $table[] = $row;
            }

            return json_encode($table);
        }
    }
?>