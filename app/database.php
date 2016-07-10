<?php

class DB {

	public function __construct(){
		try {
			$this->DB = new MongoDB\Driver\Manager("mongodb://localhost:27017");
		} catch(PDOException $e) {
            echo $e->getMessage();
			die();
		}
	}

	public function executeQuery($a, $b){
		return $this->DB->executeQuery($a, $b);
	}

	public function executeBulkWrite($a, $b, $c){
		return $this->DB->executeBulkWrite($a, $b, $c);
	}

}
