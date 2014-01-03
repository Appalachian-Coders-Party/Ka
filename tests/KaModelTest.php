<?php
	require_once "/usr/local/Cellar/php53/5.3.28/lib/php/PHPUnit/Extensions/Database/Autoload.php";
	require_once "../library/KaModel.php";
	define('DATABASE_HOST', 'localhost');
	define('DATABASE_NAME', 'KaUnitTest');
	define('DATABASE_USERNAME', 'root');
	define('DATABASE_PASSWORD', 'root');

	class KaModelTest extends PHPUnit_Extensions_Database_TestCase{

		static private $pdo = null;
		private $conn = null;

		public function getConnection()
		{
			if ($this->conn === null) {
				if (self::$pdo == null) {
					self::$pdo = new PDO("mysql:host=".DATABASE_HOST.";dbname=".DATABASE_NAME."", DATABASE_USERNAME, DATABASE_PASSWORD); 
				}
				$this->conn = $this->createDefaultDBConnection(self::$pdo, 'KaUnitTest');
			}

			return $this->conn;
		}
								 
		public function getDataSet()
		{
			return $this->createXMLDataSet(dirname(__FILE__).'/seed.xml');
		}

		// Begin Testing constructor()
		//------------------------------------------------------

		public function testConstructor()
		{
			$model=new KaModel('users');
			$model_fields=$model->getFields();
			$this->assertEquals(count($model_fields),4);
			
			$array=array("id"=>"", "name"=>"", "email"=>"", "number"=>"");
			$this->assertEquals(array_diff_assoc($array,$model_fields),array());
		}

		public function testConstructorBadTable()
		{
			$model=new KaModel('badtable');
			$model_fields=$model->getFields();
			$this->assertTrue(count($model_fields)===0);
			$this->assertEquals($model->getTable(),'badtable');
		}

		// End Testing constructor()
		//------------------------------------------------------

		// Begin Testing save()
		//------------------------------------------------------

		public function testSaveGoodArray()
		{
			$model=new KaModel('users');
			$array=array("name"=>"Red Foxx","email"=>"red@email.com", "number"=>4);
			$model->load($array);

			$this->assertEquals(3, $this->getConnection()->getRowCount('users'), "Pre-Condition");
			$model_fields=$model->getFields();
			$this->assertTrue(!is_numeric($model_fields['id']));
			$model->save();
			$this->assertEquals(4, $this->getConnection()->getRowCount('users'), "Post-Condition");
			$model_fields=$model->getFields();
			$this->assertTrue(is_numeric($model_fields['id']));
		}

		// End Testing save()
		//------------------------------------------------------

		// Begin Testing delete()
		//------------------------------------------------------

		public function testDeleteGoodInt()
		{
			$model=new KaModel('users');
			$this->assertEquals(3, $this->getConnection()->getRowCount('users'), "Pre-Condition");
			$model->load(1);
			$model_fields=$model->getFields();
			$this->assertEquals($model_fields['name'], "Colin");
			$model->delete();
			$model_fields=$model->getFields();
			$this->assertEquals($model_fields['name'], '');
			$this->assertEquals(2, $this->getConnection()->getRowCount('users'), "Post-Condition");
		}

		public function testDeleteNoId()
		{
			$model=new KaModel('users');
			$this->assertEquals(3, $this->getConnection()->getRowCount('users'), "Pre-Condition");
			$array=array("name"=>"Fred", "email"=>"fred@email.com", "number"=>3);
			$model->load($array);
			$model_fields=$model->getFields();
			$this->assertEquals($model_fields['name'], "Fred");
			$return_value=$model->delete();
			$this->assertTrue($return_value===0);
			$this->assertEquals(3, $this->getConnection()->getRowCount('users'), "Pre-Condition");
		}

		// Finished Testing delete()
		//------------------------------------------------------

		// Begin Testing load()
		//------------------------------------------------------

		public function testLoadString()
		{
			$model=new KaModel('users');
			$return_value=$model->load('string');
			$this->assertEquals(0,$return_value);
		}

		public function testLoadNonAssoc()
		{
			$model=new KaModel('users');
			$return_value=$model->load(array('a','b','c'));
			$this->assertEquals(0,$return_value);
		}

		public function testLoadFullGoodAssoc()
		{
			$array=array("name"=>"Fred Test", "email"=>"fred@email.com", "number"=>4);
			$model=new KaModel('users');
			$return_value=$model->load($array);
			$this->assertTrue($return_value===null);

			$model_fields=$model->getFields();
			$this->assertEquals($model_fields['id'], '');
			$this->assertEquals($model_fields['name'], 'Fred Test');
			$this->assertEquals($model_fields['email'], 'fred@email.com');
			$this->assertTrue($model_fields['number']===4);	
		}

		public function testLoadPartialGoodAssoc()
		{
			$array=array("email"=>"fred@email.com", "number"=>4);
			$model=new KaModel('users');
			$return_value=$model->load($array);
			$this->assertTrue($return_value===null);

			$model_fields=$model->getFields();
			$this->assertEquals($model_fields['id'], '');
			$this->assertEquals($model_fields['name'], '');
			$this->assertEquals($model_fields['email'], 'fred@email.com');
			$this->assertTrue($model_fields['number']===4);	
		}

		public function testLoadFullBadAssoc()
		{
			$array=array("bad1"=>"fred@email.com", "bad2"=>4);
			$model=new KaModel('users');
			$return_value=$model->load($array);
			$this->assertTrue($return_value===null);

			$model_fields=$model->getFields();
			$this->assertEquals($model_fields['id'], '');
			$this->assertEquals($model_fields['name'], '');
			$this->assertEquals($model_fields['email'], '');
			$this->assertEquals($model_fields['number'], '');	
		}

		public function testLoadPartialGoodBadAssoc()
		{
			$array=array("email"=>"fred@email.com", "bad2"=>4);
			$model=new KaModel('users');
			$return_value=$model->load($array);
			$this->assertTrue($return_value===null);

			$model_fields=$model->getFields();
			$this->assertEquals($model_fields['id'], '');
			$this->assertEquals($model_fields['name'], '');
			$this->assertEquals($model_fields['email'], 'fred@email.com');
			$this->assertEquals($model_fields['number'], '');	
		}

		public function testLoadGoodInt()
		{
			$model=new KaModel('users');
			$return_value=$model->load(1);
			$this->assertTrue($return_value===null);

			$model_fields=$model->getFields();
			$this->assertEquals($model_fields['id'], 1);
			$this->assertEquals($model_fields['name'], 'Colin');
			$this->assertEquals($model_fields['email'], 'colin@email.com');
			$this->assertEquals($model_fields['number'], 36);	
		}

		public function testLoadNonMatchingInt()
		{
			$model=new KaModel('users');
			$return_value=$model->load(10);
			$this->assertTrue($return_value===null);

			$model_fields=$model->getFields();
			$this->assertEquals($model_fields['id'], '');
			$this->assertEquals($model_fields['name'], '');
			$this->assertEquals($model_fields['email'], '');
			$this->assertEquals($model_fields['number'], '');	
		}

		public function testLoadZero()
		{
			$model=new KaModel('users');
			$return_value=$model->load(0);
			$this->assertTrue($return_value===0);

			$model_fields=$model->getFields();
			$this->assertEquals($model_fields['id'], '');
			$this->assertEquals($model_fields['name'], '');
			$this->assertEquals($model_fields['email'], '');
			$this->assertEquals($model_fields['number'], '');	
		}

		public function testLoadNegativeInt()
		{
			$model=new KaModel('users');
			$return_value=$model->load(-1);
			$this->assertTrue($return_value===0);

			$model_fields=$model->getFields();
			$this->assertEquals($model_fields['id'], '');
			$this->assertEquals($model_fields['name'], '');
			$this->assertEquals($model_fields['email'], '');
			$this->assertEquals($model_fields['number'], '');	
		}
			
		public function testLoadFloat()
		{
			$model=new KaModel('users');
			$return_value=$model->load(1.23);
			$this->assertTrue($return_value===null);

			$model_fields=$model->getFields();
			$this->assertEquals($model_fields['id'], 1);
			$this->assertEquals($model_fields['name'], 'Colin');
			$this->assertEquals($model_fields['email'], 'colin@email.com');
			$this->assertEquals($model_fields['number'], 36);	
		}

		// Finished Testing load()
		//------------------------------------------------------

		// Start Testing is_assoc()
		//------------------------------------------------------

		public function testIsAssocGoodArray()
		{
			$model=new KaModel('users');
			$array=array("key1"=>"value1");
			$this->assertTrue($model->is_assoc($array));
		}

		public function testIsAssocBadrray()
		{
			$model=new KaModel('users');
			$array=array('a','b','c');
			$this->assertTrue(!$model->is_assoc($array));
		}

		public function testIsAssocString()
		{
			$model=new KaModel('users');
			$array="value1";
			$this->assertTrue(!$model->is_assoc($array));
		}

		public function testIsAssocZero()
		{
			$model=new KaModel('users');
			$array=0;
			$this->assertTrue(!$model->is_assoc($array));
		}

		// Finished Testing is_assoc()
		//------------------------------------------------------

	}

?>
