<?php
require_once __DIR__."/config.php";
class PDOSQL
{
	public $pdo;
	public function __construct() {
		$dbhost = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";port=".DB_PORT.";charset=utf8mb4";

		try {
			$pdo = new PDO($dbhost,DB_USER,DB_PASSWORD,array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
		} catch (PDOException $e) {
			var_dump($e);
			exit();
		}
		$this->pdo = $pdo;
	}

	public function execute($sql,$arr=array(),$type=array()) {
		$pre = $this->pdo->prepare($sql);
		foreach ($arr as $key => $value) {
			if (isset($type[$key])) {
				$pre->bindValue(":".$key, $value,$type[$key]);
			}else{
				$pre->bindValue(":".$key, $value);			
			}
		}
		try {
			$pre->execute();
			$result = $pre->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
    		var_dump($e->getMessage());
    		debug_print_backtrace();
		}
		return $result;
	}
}
