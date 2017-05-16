<?php
use QCubed\Exception\Caller;
use \QCubed\Query\QQ;
use \QCubed\QDateTime;

/**
 *
 * @package Tests
 */

if(!class_exists('TypeTest')){
	require_once QCUBED_PROJECT_MODEL_DIR .'/TypeTest.php';
}

class QQMathOpTests extends \QCubed\Test\UnitTestCaseBase {

	protected function setUp()
	{
		TypeTest::DeleteAll(); // prepare for test in case a test was interrupted and objects did not get deleted
	}


	public function testMathOp() {
		$objTest = new TypeTest();
		$objTest->TestFloat = 1.0;
		$objTest->Save();
		
		$objTest2 = new TypeTest();
		$objTest2->TestFloat = 2.0;
		$objTest2->Save();
		
		$objResArray = TypeTest::QueryArray(\QCubed\Query\QQ::GreaterThan(\QCubed\Query\QQ::MathOp('*', QQN::TypeTest()->TestFloat, 2.0), 3.0));
		$this->assertEquals(1, count($objResArray));
		if (count($objResArray) > 0) {
			$objRes = $objResArray[0];
			$this->assertNotNull($objRes);
			if ($objRes) {
				$this->assertEquals(2.0, $objRes->TestFloat);
			}
		}
		
		$objResArray = TypeTest::QueryArray(\QCubed\Query\QQ::GreaterThan(\QCubed\Query\QQ::MathOp('*', 2.0, QQN::TypeTest()->TestFloat), 3.0));
		$this->assertEquals(1, count($objResArray));
		if (count($objResArray) > 0) {
			$objRes = $objResArray[0];
			$this->assertNotNull($objRes);
			if ($objRes) {
				$this->assertEquals(2.0, $objRes->TestFloat);
			}
		}
		
		$objResArray = TypeTest::QueryArray(\QCubed\Query\QQ::GreaterThan(\QCubed\Query\QQ::MathOp('*', QQN::TypeTest()->TestFloat, QQN::TypeTest()->TestFloat), 3.0));
		$this->assertEquals(1, count($objResArray));
		if (count($objResArray) > 0) {
			$objRes = $objResArray[0];
			$this->assertNotNull($objRes);
			if ($objRes) {
				$this->assertEquals(2.0, $objRes->TestFloat);
			}
		}
		
		$objTest->Delete();
		$objTest2->Delete();
	}

	public function testMul() {
		$objTest = new TypeTest();
		$objTest->TestFloat = 1.0;
		$objTest->Save();
		
		$objTest2 = new TypeTest();
		$objTest2->TestFloat = 2.0;
		$objTest2->Save();
		
		$objResArray = TypeTest::QueryArray(\QCubed\Query\QQ::GreaterThan(\QCubed\Query\QQ::Mul(QQN::TypeTest()->TestFloat, 2.0), 3.0));
		$this->assertEquals(1, count($objResArray));
		if (count($objResArray) > 0) {
			$objRes = $objResArray[0];
			$this->assertNotNull($objRes);
			if ($objRes) {
				$this->assertEquals(2.0, $objRes->TestFloat);
			}
		}
		
		$objTest->Delete();
		$objTest2->Delete();
	}

	public function testDiv() {
		$objTest = new TypeTest();
		$objTest->TestFloat = 4.0;
		$objTest->Save();
		
		$objTest2 = new TypeTest();
		$objTest2->TestFloat = 8.0;
		$objTest2->Save();
		
		$objResArray = TypeTest::QueryArray(\QCubed\Query\QQ::GreaterThan(\QCubed\Query\QQ::Div(QQN::TypeTest()->TestFloat, 2.0), 3.0));
		$this->assertEquals(1, count($objResArray));
		if (count($objResArray) > 0) {
			$objRes = $objResArray[0];
			$this->assertNotNull($objRes);
			if ($objRes) {
				$this->assertEquals(8.0, $objRes->TestFloat);
			}
		}
		
		$objTest->Delete();
		$objTest2->Delete();
	}

	public function testSub() {
		$objTest = new TypeTest();
		$objTest->TestFloat = 2.0;
		$objTest->Save();
		
		$objTest2 = new TypeTest();
		$objTest2->TestFloat = 4.0;
		$objTest2->Save();
		
		$objResArray = TypeTest::QueryArray(\QCubed\Query\QQ::GreaterOrEqual(\QCubed\Query\QQ::Sub(QQN::TypeTest()->TestFloat, 1.0), 3.0));
		$this->assertEquals(1, count($objResArray));
		if (count($objResArray) > 0) {
			$objRes = $objResArray[0];
			$this->assertNotNull($objRes);
			if ($objRes) {
				$this->assertEquals(4.0, $objRes->TestFloat);
			}
		}
		
		$objTest->Delete();
		$objTest2->Delete();
	}

	public function testAdd() {
		$objTest = new TypeTest();
		$objTest->TestFloat = 1.0;
		$objTest->Save();
		
		$objTest2 = new TypeTest();
		$objTest2->TestFloat = 2.0;
		$objTest2->Save();
		
		$objResArray = TypeTest::QueryArray(\QCubed\Query\QQ::GreaterThan(\QCubed\Query\QQ::Add(QQN::TypeTest()->TestFloat, 1.5), 3.0));
		$this->assertEquals(1, count($objResArray));
		if (count($objResArray) > 0) {
			$objRes = $objResArray[0];
			$this->assertNotNull($objRes);
			if ($objRes) {
				$this->assertEquals(2.0, $objRes->TestFloat);
			}
		}
		
		$objTest->Delete();
		$objTest2->Delete();
	}

	public function testNeg() {
		$objTest = new TypeTest();
		$objTest->TestFloat = -1.0;
		$objTest->Save();

		$objTest2 = new TypeTest();
		$objTest2->TestFloat = -2.0;
		$objTest2->Save();

		$objResArray = TypeTest::QueryArray(\QCubed\Query\QQ::GreaterThan(\QCubed\Query\QQ::Neg(QQN::TypeTest()->TestFloat), 1.0));
		$this->assertEquals(1, count($objResArray));
		if (count($objResArray) > 0) {
			$objRes = $objResArray[0];
			$this->assertNotNull($objRes);
			if ($objRes) {
				$this->assertEquals(-2.0, $objRes->TestFloat);
			}
		}

		$objTest->Delete();
		$objTest2->Delete();
	}



	public function testOrderBy() {
		$objTest = new TypeTest();
		$objTest->TestFloat = 1.0;
		$objTest->Save();
		
		$objTest2 = new TypeTest();
		$objTest2->TestFloat = 2.0;
		$objTest2->Save();
		
		$objResArray = TypeTest::QueryArray(\QCubed\Query\QQ::LessThan(
			\QCubed\Query\QQ::Virtual('mul1', \QCubed\Query\QQ::Mul(QQN::TypeTest()->TestFloat, -2.0))
			, -1.0
		),
		\QCubed\Query\QQ::Clause(
			\QCubed\Query\QQ::OrderBy(\QCubed\Query\QQ::Virtual('mul1'))
			, \QCubed\Query\QQ::Expand(\QCubed\Query\QQ::Virtual('mul1'))
		));
		$this->assertEquals(2, count($objResArray));
		if (2 == count($objResArray)) {
			$objRes = $objResArray[0];
			$this->assertNotNull($objRes);
			if ($objRes) {
				$this->assertEquals(2.0, $objRes->TestFloat);
				$this->assertEquals(-4.0, $objRes->GetVirtualAttribute('mul1'));
			}
			$objRes = $objResArray[1];
			$this->assertNotNull($objRes);
			if ($objRes) {
				$this->assertEquals(1.0, $objRes->TestFloat);
				$this->assertEquals(-2.0, $objRes->GetVirtualAttribute('mul1'));
			}
		}
		
		$objTest->Delete();
		$objTest2->Delete();
	}

	public function testOrderByDesc() {
		$objTest = new TypeTest();
		$objTest->TestFloat = 1.0;
		$objTest->Save();
		
		$objTest2 = new TypeTest();
		$objTest2->TestFloat = 2.0;
		$objTest2->Save();
		
		$objResArray = TypeTest::QueryArray(\QCubed\Query\QQ::LessThan(
			\QCubed\Query\QQ::Virtual('mul1', \QCubed\Query\QQ::Mul(QQN::TypeTest()->TestFloat, -2.0))
			, -1.0
		),
		\QCubed\Query\QQ::Clause(
			\QCubed\Query\QQ::OrderBy(\QCubed\Query\QQ::Virtual('mul1'), 'DESC')
			, \QCubed\Query\QQ::Expand(\QCubed\Query\QQ::Virtual('mul1'))
		));
		$this->assertEquals(2, count($objResArray));
		if (2 == count($objResArray)) {
			$objRes = $objResArray[0];
			$this->assertNotNull($objRes);
			if ($objRes) {
				$this->assertEquals(1.0, $objRes->TestFloat);
				$this->assertEquals(-2.0, $objRes->GetVirtualAttribute('mul1'));
			}
			$objRes = $objResArray[1];
			$this->assertNotNull($objRes);
			if ($objRes) {
				$this->assertEquals(2.0, $objRes->TestFloat);
				$this->assertEquals(-4.0, $objRes->GetVirtualAttribute('mul1'));
			}
		}
		
		$objTest->Delete();
		$objTest2->Delete();
	}

	public function testSelect() {
		$objTest = new TypeTest();
		$objTest->TestFloat = 1.0;
		$objTest->Save();
		
		$objTest2 = new TypeTest();
		$objTest2->TestFloat = 2.0;
		$objTest2->Save();
		
		$objResArray = TypeTest::QueryArray(\QCubed\Query\QQ::LessThan(
			\QCubed\Query\QQ::Virtual('mul1', \QCubed\Query\QQ::Mul(QQN::TypeTest()->TestFloat, -2.0))
			, -1.0
		),
		\QCubed\Query\QQ::Clause(
			\QCubed\Query\QQ::OrderBy(\QCubed\Query\QQ::Virtual('mul1'))
			, \QCubed\Query\QQ::Expand(\QCubed\Query\QQ::Virtual('mul1'))
			, \QCubed\Query\QQ::Select(\QCubed\Query\QQ::Virtual('mul1'))
		));
		$this->assertEquals(2, count($objResArray));
		if (2 == count($objResArray)) {
			$objRes = $objResArray[0];
			$this->assertNotNull($objRes);
			if ($objRes) {
				$blnError = false;
				try {
					$objRes->TestFloat;
				}
				catch (Exception $e) {
					$blnError = true;
				}
				$this->assertTrue($blnError, 'Accessing table column that was not loaded throws exception.');
				$this->assertEquals(-4.0, $objRes->GetVirtualAttribute('mul1'));
			}
			$objRes = $objResArray[1];
			$this->assertNotNull($objRes);
			if ($objRes) {
				$blnError = false;
				try {
					$objRes->TestFloat;
				}
				catch (Exception $e) {
					$blnError = true;
				}
				$this->assertTrue($blnError, 'Accessing table column that was not loaded throws exception.');
				$this->assertEquals(-2.0, $objRes->GetVirtualAttribute('mul1'));
			}
		}
		
		$objTest->Delete();
		$objTest2->Delete();
	}
	/**
	 * Tests to ensure the example to work
	 */
	public function testExample() {
		$objPersonArray = Person::QueryArray(
			/* Only return the persons who have AT LEAST ONE overdue project */
			\QCubed\Query\QQ::GreaterThan(\QCubed\Query\QQ::Sub(QQN::Person()->ProjectAsManager->Spent, QQN::Person()->ProjectAsManager->Budget), 20)
		);
		$this->assertGreaterThan(0, count($objPersonArray));

		foreach ($objPersonArray as $objPerson) {
			$this->assertNotNull($objPerson->FirstName);
			$this->assertNotNull($objPerson->LastName);
		}

		$objPersonArray = Person::QueryArray(
			/* Only return the persons who have AT LEAST ONE overdue project */
			\QCubed\Query\QQ::GreaterThan(
				\QCubed\Query\QQ::Virtual('diff', \QCubed\Query\QQ::Sub(
					QQN::Person()->ProjectAsManager->Spent
					, QQN::Person()->ProjectAsManager->Budget
				))
				, 20
			),
			\QCubed\Query\QQ::Clause(
				/* The most overdue first */
				\QCubed\Query\QQ::OrderBy(\QCubed\Query\QQ::Virtual('diff'), 'DESC')
				/* Required to access this field with GetVirtualAttribute */
				, \QCubed\Query\QQ::Expand(\QCubed\Query\QQ::Virtual('diff'))
			)
		);
		$this->assertGreaterThan(0, count($objPersonArray));

		foreach ($objPersonArray as $objPerson) {
			$this->assertNotNull($objPerson->FirstName);
			$this->assertNotNull($objPerson->LastName);
			$this->assertNotNull($objPerson->GetVirtualAttribute('diff'));
		}

		$objPersonArray = Person::QueryArray(
			/* Only return the persons who have AT LEAST ONE overdue project */
			\QCubed\Query\QQ::GreaterThan(
				\QCubed\Query\QQ::Virtual('diff', \QCubed\Query\QQ::MathOp(
					'-', // Note the minus operation sign here
					QQN::Person()->ProjectAsManager->Spent
					, QQN::Person()->ProjectAsManager->Budget
				))
				, 20
			),
			\QCubed\Query\QQ::Clause(
				/* The most overdue first */
				\QCubed\Query\QQ::OrderBy(\QCubed\Query\QQ::Virtual('diff'), 'DESC')
				/* Required to access this field with GetVirtualAttribute */
				, \QCubed\Query\QQ::Expand(\QCubed\Query\QQ::Virtual('diff'))
				, \QCubed\Query\QQ::Select(array(
					\QCubed\Query\QQ::Virtual('diff')
					, QQN::Person()->FirstName
					, QQN::Person()->LastName
				))
			)
		);
		$this->assertGreaterThan(0, count($objPersonArray));

		foreach ($objPersonArray as $objPerson) {
			$this->assertNotNull($objPerson->FirstName);
			$this->assertNotNull($objPerson->LastName);
			$this->assertNotNull($objPerson->GetVirtualAttribute('diff'));
		}

		$objPersonArray = Person::QueryArray(
			/* Only return the persons who have AT LEAST ONE overdue project */
			\QCubed\Query\QQ::GreaterThan(
				\QCubed\Query\QQ::Virtual('absdiff', \QCubed\Query\QQ::Abs(
					\QCubed\Query\QQ::Sub(
						QQN::Person()->ProjectAsManager->Spent
						, QQN::Person()->ProjectAsManager->Budget
					)
				))
				, 20
			),
			\QCubed\Query\QQ::Clause(
				/* The most overdue first */
				\QCubed\Query\QQ::OrderBy(\QCubed\Query\QQ::Virtual('absdiff'), 'DESC')
				/* Required to access this field with GetVirtualAttribute */
				, \QCubed\Query\QQ::Expand(\QCubed\Query\QQ::Virtual('absdiff'))
				, \QCubed\Query\QQ::Select(array(
					\QCubed\Query\QQ::Virtual('absdiff')
					, QQN::Person()->FirstName
					, QQN::Person()->LastName
				))
			)
		);
		$this->assertGreaterThan(0, count($objPersonArray));

		foreach ($objPersonArray as $objPerson) {
			$this->assertNotNull($objPerson->FirstName);
			$this->assertNotNull($objPerson->LastName);
			$this->assertNotNull($objPerson->GetVirtualAttribute('absdiff'));
		}
	}
}