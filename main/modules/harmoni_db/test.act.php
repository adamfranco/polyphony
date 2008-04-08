<?php
/**
 * @since 4/3/08
 * @package harmoni.Harmoni_Db
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: test.act.php,v 1.2 2008/04/08 19:43:46 adamfranco Exp $
 */ 

require_once(POLYPHONY.'/main/library/AbstractActions/MainWindowAction.class.php');

/**
 * Test Scripts for the new Harmoni_Db package
 * 
 * @since 4/3/08
 * @package harmoni.Harmoni_Db
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: test.act.php,v 1.2 2008/04/08 19:43:46 adamfranco Exp $
 */
class testAction
	extends MainWindowAction
{
		
	/**
	 * AuthZ
	 * 
	 * @param <##>
	 * @return <##>
	 * @access public
	 * @since 4/3/08
	 */
	public function isAuthorizedToExecute () {
		// Check for authorization
 		$authZManager = Services::getService("AuthZ");
 		$idManager = Services::getService("IdManager");
 		if ($authZManager->isUserAuthorized(
 					$idManager->getId("edu.middlebury.authorization.view"),
 					$idManager->getId("edu.middlebury.authorization.root")))
 		{
			return TRUE;
 		} else {
 			
 			return FALSE;
		}
	}
	
	/**
	 * Execute
	 * 
	 * @return void
	 * @access public
	 * @since 4/3/08
	 */
	public function buildContent () {
		$db = Harmoni_Db::getDatabase('segue_db');
		printpre(get_class($db));
		
// 		$query = $db->select();
// 		$query->addTable('segue_slot');
// 		
// 		$query->addColumn('*');
// // 		$query->addColumn('site_id', 'thesite', 'mytablename');
// // 		
// // 		$query->addTable('node', LEFT_JOIN, "site_id = node_id");
// // 		$subquery = $db->select();
// // 		$subquery->addTable('type');
// // 		$subquery->addColumn('*');
// // 		$query->addDerivedTable($subquery, LEFT_JOIN, "fk_type = mytype.type_id", "mytype");
// 		
// 		$nameKey = $query->addWhereEqual('location_category', 'main');
// 		var_dump($nameKey);
// 		
// 		$query->addOrderBy('shortname', DESCENDING);
// 		
// 		$query->limitNumberOfRows(5);
// // 		$query->startFromRow(2);
// 		
// 		printpre(strval($query));
// 		
// 		$stmt = $query->prepare();
// 		// Execute the query
// 		$stmt->execute();
// 		$result = $stmt->fetchAll();
// 		printpre($result);
// 		
// 		// Execute it again.
// 		$stmt->bindValue($nameKey, 'main');
// 		$stmt->execute();
// 		$result = $stmt->fetchAll();
// 		printpre($result);
		
		
		
		/*********************************************************
		 * Update test
		 *********************************************************/
// 		print "<hr/>";
// 		$query = $db->update();
// 		$query->addTable('segue_slot');
// 		$query->addWhereEqual('shortname', 'simmons');
// 		$query->addRawValue('media_quota', 'NULL');
// 		
// // 		printpre($query);
// 		printpre(strval($query));
// // 		exit;
// 		
// 		$stmt = $query->query();
// 		$result = $stmt->execute();
// 		printpre($result);
		
		/*********************************************************
		 * INSERT test
		 *********************************************************/
		print "<hr/>";
		$query = $db->insert();
		$query->addTable('test');
		$query->addValue('name', 'ned');
		$query->addValue('color', 'green');
		$query->createRow();
		$query->addValue('name', 'joan');
		$query->addValue('color', 'purple');
		
// 		printpre($query);
// 		printpre(strval($query));
// 		exit;
		
// 		$stmt = $query->query();
		
// 		$query = $db->select();
// 		$query->addTable('test');
// 		$query->addColumn('*');
// 		$stmt = $query->query();
// 		$result = $stmt->fetchAll();
// 		printpre($result);
		
		print "\n<h1>The following tests use DBHandler SelectQuery or Harmoni_Db_Select objects</h1>";
		$this->testDbHanderVsHarmoni_Db($db);
		$this->testDbHanderVsHarmoni_Db_fetchAll($db);
		$this->testDbHanderVsHarmoni_Db_preparedSelect($db);
		$this->testDbHanderVsHarmoni_Db_preparedSelectFetchAll($db);
		print "\n<h1>The following tests use string queries rather than the Harmoni_Db_Select objects</h1>";
		$this->testDbHanderVsHarmoni_Db_preparedString($db);
		$this->testSelectWhereEqual($db);
		$this->testSelectWhereEqualJoin($db);
		
		/*********************************************************
		 * Select IN test
		 *********************************************************/

// 		$this->testSelectIN($db);
		
		/*********************************************************
		 * Delete test
		 *********************************************************/
// 		print "<hr/>";
// 		$query = $db->delete();
// 		$query->addTable('test');
// 		$query->addWhereEqual('name', 'adam');
// 		
// // 		printpre($query);
// 		printpre(strval($query));
// // 		exit;
// 		
// 		$stmt = $query->query();
// 		
// 		$query = $db->select();
// 		$query->addTable('test');
// 		$query->addColumn('*');
// 		$stmt = $query->query();
// 		$result = $stmt->fetchAll();
// 		printpre($result);
	}

/*********************************************************
 * Test DB handler vs harmoni db on simple selects
 *********************************************************/
 
 	/**
	 * Test dynamic queries versus prepared statments with temporary table inserts for IN clause.
	 * 
	 * @param Zend_Db_Adapter_Pdo $db
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	public function testDbHanderVsHarmoni_Db ($db) {
		print "\n<h2>Test DBHandler versus the Harmoni_Db with dynamic queries. Each query is a new object. Results are iterated through.</h2>";
		$dbHandler = Services::getService("DatabaseManager");
		$testSet = $this->generatateSelectWhereEqualtestSet($db);
		
		$this->startTest($db);
		$this->testDBHandler($dbHandler, $testSet);
		$result1 = $this->endTest($db);
		$result1['name'] = "DBHandler dynamic SELECT WHERE x=y";
		
		$this->startTest($db);
		$this->testHarmoni_Db_SELECT($db, $testSet);
		$result2 = $this->endTest($db);
		$result2['name'] = "Harmoni_Db dynamic SELECT WHERE x=y";
		
		print $this->getTestResults($result1, $result2);
	}
	
	/**
	 * Test dynamic queries versus prepared statments with temporary table inserts for IN clause.
	 * 
	 * @param Zend_Db_Adapter_Pdo $db
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	public function testDbHanderVsHarmoni_Db_preparedSelect ($db) {
		print "\n<h2>Test DBHandler versus the Harmoni_Db with dynamic queries. Each DBHandler query is a new object, a single prepared Harmoni_Db_Statement is used. Results are iterated through.</h2>";
		$dbHandler = Services::getService("DatabaseManager");
		$testSet = $this->generatateSelectWhereEqualtestSet($db);
		
		$this->startTest($db);
		$this->testDBHandler($dbHandler, $testSet);
		$result1 = $this->endTest($db);
		$result1['name'] = "DBHandler dynamic SELECT WHERE x=y";
		
		$this->startTest($db);
		$this->testPreparedHarmoni_Db_Select($db, $testSet);
		$result2 = $this->endTest($db);
		$result2['name'] = "Harmoni_Db Prepared string SELECT WHERE x=y with iteration";
		
		print $this->getTestResults($result1, $result2);
	}
	
	/**
	 * Test dynamic queries versus prepared statments with temporary table inserts for IN clause.
	 * 
	 * @param Zend_Db_Adapter_Pdo $db
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	public function testDbHanderVsHarmoni_Db_preparedSelectFetchAll ($db) {
		print "\n<h2>Test DBHandler versus the Harmoni_Db with dynamic queries. Each DBHandler query is a new object, a single prepared Harmoni_Db_Statement is used. DBHandler results are iterated through, Harmoni_Db results are loaded with fetchAll().</h2>";
		$dbHandler = Services::getService("DatabaseManager");
		$testSet = $this->generatateSelectWhereEqualtestSet($db);
		
		$this->startTest($db);
		$this->testDBHandler($dbHandler, $testSet);
		$result1 = $this->endTest($db);
		$result1['name'] = "DBHandler dynamic SELECT WHERE x=y";
		
		$this->startTest($db);
		$this->testPreparedHarmoni_Db_SelectFetchAll($db, $testSet);
		$result2 = $this->endTest($db);
		$result2['name'] = "Harmoni_Db Prepared string SELECT WHERE x=y with fetchAll()";
		
		print $this->getTestResults($result1, $result2);
	}
	
	/**
	 * Test dynamic queries versus prepared statments with temporary table inserts for IN clause.
	 * 
	 * @param Zend_Db_Adapter_Pdo $db
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	public function testDbHanderVsHarmoni_Db_preparedString ($db) {
		print "\n<h2>Test DBHandler versus the Harmoni_Db with dynamic queries. Each DBHandler query is a new object, a single prepared Harmoni_Db_Statement is used generated from a string rather than a Harmoni_Db_Select object. DBHandler results are iterated through, Harmoni_Db results are loaded with fetchAll().</h2>";
		$dbHandler = Services::getService("DatabaseManager");
		$testSet = $this->generatateSelectWhereEqualtestSet($db);
		
		$this->startTest($db);
		$this->testDBHandler($dbHandler, $testSet);
		$result1 = $this->endTest($db);
		$result1['name'] = "DBHandler dynamic SELECT WHERE x=y";
		
		$this->startTest($db);
		$this->testPreparedSelectWhereEqual($db, $testSet);
		$result2 = $this->endTest($db);
		$result2['name'] = "Harmoni_Db Prepared string SELECT WHERE x=y with fetchAll()";
		
		print $this->getTestResults($result1, $result2);
	}
	
	/**
	 * Test dynamic queries versus prepared statments with temporary table inserts for IN clause.
	 * 
	 * @param Zend_Db_Adapter_Pdo $db
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	public function testDbHanderVsHarmoni_Db_fetchAll ($db) {
		print "\n<h2>Test DBHandler versus the Harmoni_Db with dynamic queries. Each query is a new object. DBHandler results are iterated through, Harmoni_Db results are loaded with fetchAll().</h2>";
		$dbHandler = Services::getService("DatabaseManager");
		$testSet = $this->generatateSelectWhereEqualtestSet($db);
		
		$this->startTest($db);
		$this->testDBHandler($dbHandler, $testSet);
		$result1 = $this->endTest($db);
		$result1['name'] = "DBHandler dynamic SELECT WHERE x=y";
		
		$this->startTest($db);
		$this->testHarmoni_Db_SELECT_fetchAll($db, $testSet);
		$result2 = $this->endTest($db);
		$result2['name'] = "Harmoni_Db dynamic SELECT WHERE x=y with fetchAll()";
		
		print $this->getTestResults($result1, $result2);
	}
	
	/**
	 * Test dynamic SELECT WHERE IN queries
	 * 
	 * @param object $db
	 * @param array $testSet
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	public function testDBHandler ($dbHandler, array $testSet) {
		foreach ($testSet as $id) {
			$query = new SelectQuery();
			$query->addColumn('*');
			$query->addTable('log_entry');
			$query->addWhereEqual('id', $id);
			$result = $dbHandler->query($query);
			while ($result->hasNext())
				$row = $result->next();
			$result->free();
// 			printpre($result);
		}
	}
	
	
	/**
	 * Test dynamic SELECT WHERE IN queries
	 * 
	 * @param object $db
	 * @param array $testSet
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	public function testHarmoni_Db_SELECT ($db, array $testSet) {
		foreach ($testSet as $id) {
			$query = $db->select();
			$query->addColumn('*');
			$query->addTable('log_entry');
			$query->addWhereEqual('id', $id);
			$stmt = $query->query();
			$result = $stmt->getResult();
			while ($result->hasNext())
				$row = $result->next();
			$result->free();
// 			printpre($result);
		}
	}
	
	/**
	 * Test dynamic SELECT WHERE IN queries
	 * 
	 * @param object $db
	 * @param array $testSet
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	public function testPreparedHarmoni_Db_Select ($db, array $testSet) {
		$query = $db->select();
		$query->addColumn('*');
		$query->addTable('log_entry');
		$query->addWhereEqual('id', 'null');
		$stmt = $query->prepare();
		foreach ($testSet as $id) {
			$stmt->execute(array($id));
			$result = $stmt->getResult();
			while ($result->hasNext())
				$row = $result->next();
			$result->free();
// 			printpre($result);
		}
	}
	
	/**
	 * Test dynamic SELECT WHERE IN queries
	 * 
	 * @param object $db
	 * @param array $testSet
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	public function testPreparedHarmoni_Db_SelectFetchAll ($db, array $testSet) {
		$query = $db->select();
		$query->addColumn('*');
		$query->addTable('log_entry');
		$query->addWhereEqual('id', 'null');
		$stmt = $query->prepare();
		foreach ($testSet as $id) {
			$stmt->execute(array($id));
			$result = $stmt->fetchAll();
// 			printpre($result);
		}
	}
	
	/**
	 * Test dynamic SELECT WHERE IN queries
	 * 
	 * @param object $db
	 * @param array $testSet
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	public function testHarmoni_Db_SELECT_fetchAll ($db, array $testSet) {
		foreach ($testSet as $id) {
			$query = $db->select();
			$query->addColumn('*');
			$query->addTable('log_entry');
			$query->addWhereEqual('id', $id);
			$stmt = $query->query();
			$result = $stmt->fetchAll();
// 			printpre($result);
		}
	}
	
	/**
	 * Test dynamic SELECT WHERE IN queries
	 * 
	 * @param object $db
	 * @param array $testSet
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	public function testHarmoni_Db_string ($db, array $testSet) {
		foreach ($testSet as $id) {
			$queryString = "SELECT * FROM log_entry WHERE id = '$id'";
			$result = $db->fetchAll($queryString);
// 			while ($result->hasNext())
// 				$row = $result->next();
// 			printpre($result);
		}
	}
	
	/*********************************************************
	 * SELECT WHERE= tests
	 *********************************************************/
	
	/**
	 * Test dynamic queries versus prepared statments with temporary table inserts for IN clause.
	 * 
	 * @param Zend_Db_Adapter_Pdo $db
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	public function testSelectWhereEqual ($db) {
		print "\n<h2>Test the Harmoni_Db with dynamic queries versus Harmoni_Db with a single prepared statement. Results are access with fetchAll().</h2>";
		// Id search set will be a two-dimensional array of randomized of ids of
		// differenent numbers
		$testSet = $this->generatateSelectWhereEqualtestSet($db);
		
		$this->startTest($db);
		$this->testDynamicSelectWhereEqual($db, $testSet);
		$result1 = $this->endTest($db);
		$result1['name'] = "Dynamic SELECT WHERE x=y";
		
		$this->startTest($db);
		$this->testPreparedSelectWhereEqual($db, $testSet);
		$result2 = $this->endTest($db);
		$result2['name'] = "Prepared SELECT WHERE x=y";
		
		print $this->getTestResults($result1, $result2);
	}
	
	/**
	 * Test dynamic SELECT WHERE IN queries
	 * 
	 * @param object $db
	 * @param array $testSet
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	public function testDynamicSelectWhereEqual ($db, array $testSet) {
		foreach ($testSet as $id) {
			$queryString = "SELECT * FROM log_entry WHERE id = '$id'";
			$result = $db->fetchAll($queryString);
// 			printpre($result);
		}
	}
	
	/**
	 * Test prepared-insert SELECT WHERE IN queries
	 * 
	 * @param object $db
	 * @param array $testSet
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	public function testPreparedSelectWhereEqual ($db, array $testSet) {
		$selectStatement = $db->prepare("SELECT * FROM log_entry WHERE id = ?");
		foreach ($testSet as $id) {
			$selectStatement->execute(array($id));
			$result = $selectStatement->fetchAll();
// 			printpre($result);
		}
	}
	
	/**
	 * Id search set will be a two-dimensional array of randomized of ids of
	 * differenent numbers
	 * 
	 * @param Zend_Db_Adapter_Pdo $db
	 * @return array
	 * @access private
	 * @since 4/7/08
	 */
	private function generatateSelectWhereEqualtestSet ($db) {
		$result = $db->fetchAll('SELECT COUNT(id) AS numLogs, MIN(id) AS minId, MAX(id) AS maxId FROM log_entry');
		$numLogs = intval($result[0]['numLogs']);
		$minId = intval($result[0]['minId']);
		$maxId = intval($result[0]['maxId']);
		printpre("Testing from log_entry table\n\tRows: $numLogs \n\tMin-Id: $minId \n\tMax-Id: $maxId");
		
		// Create n test sets
		$set = array();
		$n = 5000;
		for ($i = 0; $i < $n; $i++) {
			$set[] = strval(rand($minId, $maxId)); 
		}
		
		return $set;
	}
	
	/*********************************************************
	 * SELECT WHERE= tests with join
	 *********************************************************/
	
	/**
	 * Test dynamic queries versus prepared statments with temporary table inserts for IN clause.
	 * 
	 * @param Zend_Db_Adapter_Pdo $db
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	public function testSelectWhereEqualJoin ($db) {
		print "\n<h2>Test the Harmoni_Db with dynamic queries versus Harmoni_Db with a single prepared statement when joining across two tables. Results are access with fetchAll().</h2>";
		
		// Id search set will be a two-dimensional array of randomized of ids of
		// differenent numbers
		$testSet = array_slice($this->generatateSelectWhereEqualtestSet($db), 0, 500);
		
		$this->startTest($db);
		$this->testDynamicSelectWhereEqualJoin($db, $testSet);
		$result1 = $this->endTest($db);
		$result1['name'] = "Dynamic SELECT Join WHERE x=y";
		
		$this->startTest($db);
		$this->testPreparedSelectWhereEqualJoin($db, $testSet);
		$result2 = $this->endTest($db);
		$result2['name'] = "Prepared SELECT Join WHERE x=y";
		
		print $this->getTestResults($result1, $result2);
	}
	
	/**
	 * Test dynamic SELECT WHERE IN queries
	 * 
	 * @param object $db
	 * @param array $testSet
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	public function testDynamicSelectWhereEqualJoin ($db, array $testSet) {
		foreach ($testSet as $id) {
			$queryString = "SELECT * FROM log_entry LEFT JOIN log_node ON log_entry.id = log_node.fk_entry WHERE id = '$id'";
			$result = $db->fetchAll($queryString);
// 			printpre($result);
		}
	}
	
	/**
	 * Test prepared-insert SELECT WHERE IN queries
	 * 
	 * @param object $db
	 * @param array $testSet
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	public function testPreparedSelectWhereEqualJoin ($db, array $testSet) {
		$selectStatement = $db->prepare("SELECT * FROM log_entry LEFT JOIN log_node ON log_entry.id = log_node.fk_entry  WHERE id = ?");
		foreach ($testSet as $id) {
			$selectStatement->execute(array($id));
			$result = $selectStatement->fetchAll();
// 			printpre($result);
		}
	}
	
	/*********************************************************
	 * SELECT IN tests
	 *********************************************************/
	
	/**
	 * Test dynamic queries versus prepared statments with temporary table inserts for IN clause.
	 * 
	 * @param Zend_Db_Adapter_Pdo $db
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	public function testSelectIN ($db) {
		// Id search set will be a two-dimensional array of randomized of ids of
		// differenent numbers
		$testSet = $this->generatateSelectINtestSet($db);
		
		$this->startTest($db);
		$this->testDynamicSelectIN($db, $testSet);
		$result1 = $this->endTest($db);
		$result1['name'] = "Dynamic SELECT IN";
		
		$this->startTest($db);
		$this->testPreparedInsertSelectIN($db, $testSet);
		$result2 = $this->endTest($db);
		$result2['name'] = "Prepared-inserts SELECT IN";
		
		print $this->getTestResults($result1, $result2);
	}
	
	/**
	 * Test dynamic SELECT WHERE IN queries
	 * 
	 * @param object $db
	 * @param array $testSet
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	public function testDynamicSelectIN ($db, array $testSet) {
		foreach ($testSet as $ids) {
			$queryString = "SELECT * FROM log_entry WHERE id IN ('".implode("', '", $ids)."')";
			$result = $db->fetchAll($queryString);
// 			printpre($result);
		}
	}
	
	/**
	 * Test prepared-insert SELECT WHERE IN queries
	 * 
	 * @param object $db
	 * @param array $testSet
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	public function testPreparedInsertSelectIN ($db, array $testSet) {
		$selectStatement = $db->prepare("SELECT * FROM log_entry WHERE id IN (SELECT * FROM temp_where_in_1)");
		$dropStatement = $db->prepare("DROP TABLE IF EXISTS temp_where_in_1");
		$createStatement = $db->prepare("CREATE TEMPORARY TABLE temp_where_in_1 (val VARCHAR(255))");
		$truncateStatement = $db->prepare("TRUNCATE TABLE temp_where_in_1");
		$insertStatement = $db->prepare("INSERT INTO temp_where_in_1 (val) VALUES (?)");
		
		$dropStatement->execute();
		$createStatement->execute();
		foreach ($testSet as $ids) {
			$truncateStatement->execute();
			foreach ($ids as $id) {
				$insertStatement->execute(array($id));
			}
			$selectStatement->execute();
			$result = $selectStatement->fetchAll();
// 			printpre($result);
		}
	}
	

	
	/**
	 * Id search set will be a two-dimensional array of randomized of ids of
	 * differenent numbers
	 * 
	 * @param Zend_Db_Adapter_Pdo $db
	 * @return array
	 * @access private
	 * @since 4/7/08
	 */
	private function generatateSelectINtestSet ($db) {
		$result = $db->fetchAll('SELECT COUNT(id) AS numLogs, MIN(id) AS minId, MAX(id) AS maxId FROM log_entry');
		$numLogs = intval($result[0]['numLogs']);
		$minId = intval($result[0]['minId']);
		$maxId = intval($result[0]['maxId']);
		printpre("Testing from log_entry table\n\tRows: $numLogs \n\tMin-Id: $minId \n\tMax-Id: $maxId");
		
		// Create n test sets
		$sets = array();
		$n = 10;
		for ($i = 0; $i < $n; $i++) {
			$set = array();
			// Each test set will have between 1 and 500 ids to search for
			$numInSet = rand(1, 500);
			for ($j = 0; $j < $numInSet; $j++) {
				$set[] = strval(rand($minId, $maxId)); 
			}
			$sets[] = $set;
		}
		
		return $sets;
	}
	
	
	/*********************************************************
	 * Methods for running tests.
	 *********************************************************/
	
		/**
	 * Start a new test
	 * 
	 * @return void
	 * @access protected
	 * @since 4/7/08
	 */
	protected function startTest ($db) {
		$this->startNumPrepared = $db->getNumPrepared();
		$this->startNumExecutions = $db->getNumExecuted();
		
		require_once(HARMONI."/utilities/Timer.class.php");
		$this->testTimer = new Timer;
		$this->testTimer->start();
	}
	
	/**
	 * End a test and return an array of results
	 * 
	 * @param $db
	 * @return array
	 * @access protected
	 * @since 4/7/08
	 */
	protected function endTest ($db) {
		if (!isset($this->testTimer))
			throw new OperationFailedException("Test not started, could not end.");
		
		$results = array();
		
		$this->testTimer->end();
		$results['time'] = $this->testTimer->getTime();
		unset($this->testTimer);
		
		$results['numPrepared'] = $db->getNumPrepared() - $this->startNumPrepared;
		unset($this->startNumPrepared);
		
		$results['numExecuted'] = $db->getNumExecuted() - $this->startNumExecutions;
		unset($this->startNumExecutions);
		
		return $results;
	}
	
	/**
	 * Print out results from the test
	 * 
	 * @param array $set1
	 * @param array $set2
	 * @return string
	 * @access protected
	 * @since 4/7/08
	 */
	protected function getTestResults (array $set1, array $set2) {
		if (isset($set1['name']))
			$name1 = $set1['name'];
		else
			$name1 = "Test 1";
			
		if (isset($set2['name']))
			$name2 = $set2['name'];
		else
			$name2 = "Test 2";
		
		if ($set1['time'] < $set2['time']) {
			$smaller = $set1['time'];
			$larger = $set2['time'];
			$label = 'faster';
		} else {
			$smaller = $set2['time'];
			$larger = $set1['time'];
			$label = 'slower';
		}
		
		$diff = $larger - $smaller;
		$pc = round((abs($larger - $smaller)/(0.5 * ($larger + $smaller))) * 100);
		$diffMessage = "<strong>".$name1."</strong> is ".$pc."% ".$label." than <strong>".$name2."</strong>";
			
		return "
<table border='1'>
	<tr>
		<th>&nbsp;</th>
		<th>$name1</th>
		<th>$name2</th>
	</tr>
	<tr>
		<th>Statements Prepared:</th>
		<td>".$set1['numPrepared']."</td>
		<td>".$set2['numPrepared']."</td>
	</tr>
	<tr>
		<th>Statements Executed:</th>
		<td>".$set1['numExecuted']."</td>
		<td>".$set2['numExecuted']."</td>
	</tr>
	<tr>
		<th>Overall Time:</th>
		<td>".sprintf("%1.6f", $set1['time'])."</td>
		<td>".sprintf("%1.6f", $set2['time'])."</td>
	</tr>
	<tr>
		<td colspan='3'>$diffMessage</td>
	</tr>
</table>
";	
	}
}

?>