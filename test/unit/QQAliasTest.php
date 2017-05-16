<?php

use QCubed\Exception\Caller;
use \QCubed\Query\QQ;
use \QCubed\QDateTime;

// If the test is being run in php cli mode, the autoloader does not work.
// Check to see if the models you need exist and if not, include them here.
if(!class_exists('Person')){
    require_once QCUBED_PROJECT_MODEL_DIR .'/Person.php';
    
}
if(!class_exists('Project')){
    require_once QCUBED_PROJECT_MODEL_DIR . '/Project.php';
}
/**
 * Validation tests for the SQL Aliasing logic provided in \QCubed\Query\QQ::Alias().
 * 
 * @package Tests
 */
class QQAliasTests extends \QCubed\Test\UnitTestCaseBase {
	public function testAlias1() {
		$objPersonArray = Person::QueryArray(
			\QCubed\Query\QQ::AndCondition(
				\QCubed\Query\QQ::Equal(\QCubed\Query\QQ::Alias(QQN::Person()->ProjectAsTeamMember, 'pm1')->ProjectId, 1),
				\QCubed\Query\QQ::Equal(\QCubed\Query\QQ::Alias(QQN::Person()->ProjectAsTeamMember, 'pm2')->ProjectId, 2)
			)
		);
		
		$this->assertEquals(3, sizeof($objPersonArray));
		$this->verifyObjectPropertyHelper($objPersonArray, 'FirstName', 'Kendall');
		$this->verifyObjectPropertyHelper($objPersonArray, 'LastName', 'Wolfe');
		$this->verifyObjectPropertyHelper($objPersonArray, 'LastName', 'Smith');
	}
	
	public function testAlias2() {
		$objProjectArray = Project::QueryArray(
			\QCubed\Query\QQ::AndCondition(
				\QCubed\Query\QQ::Equal(\QCubed\Query\QQ::Alias(QQN::Project()->ProjectAsRelated, 'related1')->Project->Name, 'Blueman Industrial Site Architecture'),
				\QCubed\Query\QQ::Equal(\QCubed\Query\QQ::Alias(QQN::Project()->ProjectAsRelated, 'related2')->Project->Name, 'ACME Payment System')
			)
		);

		$this->assertEquals(1, sizeof($objProjectArray));
		$this->verifyObjectPropertyHelper($objProjectArray, 'Name', 'ACME Website Redesign');

	}	

	public function testAlias3() {
		$emptySelect = \QCubed\Query\QQ::Select();
		$emptySelect->SetSkipPrimaryKey(true);
		$nVoyel = \QCubed\Query\QQ::Alias(QQN::Person()->ProjectAsManager->Milestone, 'voyel');
		$nConson = \QCubed\Query\QQ::Alias(QQN::Person()->ProjectAsManager->Milestone, 'conson');
		$objPersonArray = Person::QueryArray(
			\QCubed\Query\QQ::IsNotNull($nConson->Id),
			\QCubed\Query\QQ::Clause(
				\QCubed\Query\QQ::Expand($nVoyel, \QCubed\Query\QQ::In($nVoyel->Name, array('Milestone A', 'Milestone E', 'Milestone I')), $emptySelect),
				\QCubed\Query\QQ::Expand($nConson, \QCubed\Query\QQ::NotIn($nConson->Name, array('Milestone A', 'Milestone E', 'Milestone I')), $emptySelect),
				\QCubed\Query\QQ::GroupBy(QQN::Person()->Id),
				\QCubed\Query\QQ::Minimum($nVoyel->Name, 'min_voyel'),
				\QCubed\Query\QQ::Minimum($nConson->Name, 'min_conson'),
				//*** just to avoid build error with pg.
				// Even with an empty select, id is selected;
				// Happily, PG doesn't complain if both id and MIN(id) are selected
				\QCubed\Query\QQ::Expand(QQN::Person()->ProjectAsManager, null, $emptySelect),
				\QCubed\Query\QQ::Minimum(QQN::Person()->ProjectAsManager->Id, 'dummy'),
				//***
				\QCubed\Query\QQ::Select(
					QQN::Person()->FirstName,
					QQN::Person()->LastName
				)
			)
		);
		$this->assertEquals(3, sizeof($objPersonArray));
		$obj = $this->verifyObjectPropertyHelper($objPersonArray, 'LastName', 'Doe');
		$this->assertNull($obj->GetVirtualAttribute('min_voyel'));
		$this->assertEquals('Milestone F', $obj->GetVirtualAttribute('min_conson'));

		$obj = $this->verifyObjectPropertyHelper($objPersonArray, 'LastName', 'Ho');
		$this->assertEquals('Milestone E', $obj->GetVirtualAttribute('min_voyel'));
		$this->assertEquals('Milestone D', $obj->GetVirtualAttribute('min_conson'));

		$obj = $this->verifyObjectPropertyHelper($objPersonArray, 'LastName', 'Wolfe');
		$this->assertEquals('Milestone A', $obj->GetVirtualAttribute('min_voyel'));
		$this->assertEquals('Milestone B', $obj->GetVirtualAttribute('min_conson'));
	}
}