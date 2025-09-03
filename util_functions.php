<?php
$scoreList= array('R'=>'0','I'=>'0','A'=>'0','S'=>'0','E'=>'0','C'=>'0');
$scorePercentageList= array('R'=>'0','I'=>'0','A'=>'0','S'=>'0','E'=>'0','C'=>'0');
$result_personality="";

/* for RIASEC test result*/
function getPersonalityTestResults(){
	global $scoreList,$result_personality; array('R'=>'0','I'=>'0','A'=>'0','S'=>'0','E'=>'0','C'=>'0');
	if(isset($_POST['submit']) && isSubmissionComplete()){
		if(count($_POST) <= 15){
			echo "<script> alert('To get good results atleast fill 15 statements'); </script>";
		}
		calculateScoreByCategory('R');
		calculateScoreByCategory('I');
		calculateScoreByCategory('A');
		calculateScoreByCategory('S');
		calculateScoreByCategory('E');
		calculateScoreByCategory('C');
		arsort($scoreList);
		calculateScoreInPercentage($scoreList);
		$iterator=0;
		foreach($scoreList as $key => $value){
			$result_personality.=$key;
			$iterator++;
			if($iterator==3) break;
		}
		if(isset($_POST['can_save_data']) && $_POST['can_save_data']==='true'){
			insertTestResults($result_personality);
		}
		
		// Set session flag to indicate test completion
		if (session_status() === PHP_SESSION_NONE) { session_start(); }
		$_SESSION['test_completed'] = true;
		$_SESSION['result_personality'] = $result_personality;
		$_SESSION['scorePercentageList'] = $scorePercentageList;
	} else{
		header("Location: test_form.php?message=REQ");
	}
 
}

function isSubmissionComplete(){
	global $connection;
	if (!isset($_POST['can_save_data']) || $_POST['can_save_data'] !== 'true') {
		return false;
	}
	$res = mysqli_query($connection, "SELECT statement_id, statement_category FROM statements");
	if (!$res) { return false; }
	while ($row = mysqli_fetch_assoc($res)) {
		$sid = $row['statement_id'];
		$cat = $row['statement_category'];
		$name = $cat . $sid;
		if (!isset($_POST[$name])) { return false; }
		$val = intval($_POST[$name]);
		if ($val < 1 || $val > 5) { return false; }
	}
	return true;
}


/* to find score in each personality type*/
function calculateScoreByCategory($category){
	global $scoreList;
	$value = $scoreList[$category];
	
	for($counter=1;$counter<=5;$counter++){
		$name=$category.$counter;
		if(isset($_POST[$name])){
			$value+=intval($_POST[$name]);
		}
	}
	$scoreList[$category]=$value;
}

/*for calculating percentagewise scores of each personality*/
function calculateScoreInPercentage($scoreList){
	global $scorePercentageList;
	$sum = array_sum($scoreList);
	foreach($scoreList as $key => $value){
		$scorePercentageList[$key]=round(($value/$sum)*100,2);
	}
	 
}
 

// To insert data into database for research purposes
function insertTestResults($result){
	global $scorePercentageList,$connection;
	if (session_status() === PHP_SESSION_NONE) { session_start(); }
	$personalInfoId = isset($_SESSION['personal_info_id']) ? intval($_SESSION['personal_info_id']) : null;

	// Ensure linking column exists
	$colRes = mysqli_query($connection, "SHOW COLUMNS FROM personality_test_scores LIKE 'personal_info_id'");
	if ($colRes && mysqli_num_rows($colRes) === 0) {
		mysqli_query($connection, "ALTER TABLE personality_test_scores ADD COLUMN personal_info_id INT UNSIGNED NULL");
	}
	// Optional timestamp column for ordering
	$tsRes = mysqli_query($connection, "SHOW COLUMNS FROM personality_test_scores LIKE 'created_at'");
	if ($tsRes && mysqli_num_rows($tsRes) === 0) {
		mysqli_query($connection, "ALTER TABLE personality_test_scores ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
	}

	if ($personalInfoId !== null) {
		$query = "INSERT INTO personality_test_scores (personal_info_id, realistic, investigative, artistic, social, enterprising, conventional, result) ";
		$query.= "VALUES({$personalInfoId}, {$scorePercentageList['R']}, {$scorePercentageList['I']}, {$scorePercentageList['A']}, {$scorePercentageList['S']}, ";
		$query.= "{$scorePercentageList['E']}, {$scorePercentageList['C']}, '{$result}')";
	} else {
		$query = "INSERT INTO personality_test_scores (realistic, investigative, artistic, social, enterprising, conventional, result) ";
		$query.= "VALUES({$scorePercentageList['R']}, {$scorePercentageList['I']}, {$scorePercentageList['A']}, {$scorePercentageList['S']}, ";
		$query.= "{$scorePercentageList['E']}, {$scorePercentageList['C']}, '{$result}')";
	}
	
	$insertIntoTestResults = mysqli_query($connection,$query);
	if(!$insertIntoTestResults){
		die("QUERY FAILED".mysqli_error($connection));
	}

	// Capture inserted score id immediately after INSERT
	$scoreId = mysqli_insert_id($connection);

	// Create table to store detailed answers if it does not exist
	$createAnswers = "CREATE TABLE IF NOT EXISTS test_answers (
		id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		score_id INT UNSIGNED NOT NULL,
		personal_info_id INT UNSIGNED NULL,
		statement_id INT UNSIGNED NOT NULL,
		statement_category CHAR(1) NOT NULL,
		answer TINYINT UNSIGNED NOT NULL,
		created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		INDEX idx_score_id (score_id),
		INDEX idx_personal_info_id (personal_info_id),
		INDEX idx_statement (statement_id, statement_category)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
	mysqli_query($connection, $createAnswers);
	if ($scoreId) {
		foreach ($_POST as $key => $value) {
			if (!is_string($key)) { continue; }
			if (preg_match('/^([RIASEC])(\d+)$/', $key, $m)) {
				$cat = $m[1];
				$sid = intval($m[2]);
				$ans = intval($value);
				if ($sid > 0 && $ans >= 1 && $ans <= 5) {
					$piid = $personalInfoId !== null ? $personalInfoId : 0;
					mysqli_query($connection, "INSERT INTO test_answers (score_id, personal_info_id, statement_id, statement_category, answer) VALUES ($scoreId, $piid, $sid, '$cat', $ans)");
				}
			}
		}
	}
}
?>
