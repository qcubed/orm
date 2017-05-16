<?php

use QCubed\Exception\Caller;
use \QCubed\Query\QQ;
use \QCubed\QDateTime;


/**
 * Tests for the ExpandAsArray functionality in QQuery
 * 
 * @package Tests
 */
// If the test is being run in php cli mode, the autoloader does not work.
// Check to see if the models you need exist and if not, include them here.
if(!class_exists('Person')){
    require_once QCUBED_PROJECT_MODEL_DIR .'/Person.php';
}
if(!class_exists('Project')){
    require_once QCUBED_PROJECT_MODEL_DIR .'/Project.php';
}
if(!class_exists('Login')){
    require_once QCUBED_PROJECT_MODEL_DIR .'/Login.php';
}
if(!class_exists('Milestone')){
    require_once QCUBED_PROJECT_MODEL_DIR .'/Milestone.php';
}
if(!class_exists('Address')){
    require_once QCUBED_PROJECT_MODEL_DIR .'/Address.php';
}
if(!class_exists('PersonType')){
    require_once QCUBED_PROJECT_MODEL_DIR .'/PersonType.php';
}
if(!class_exists('TwoKey')){
    require_once QCUBED_PROJECT_MODEL_DIR .'/TwoKey.php';
}
if(!class_exists('ProjectStatusType')){
    require_once QCUBED_PROJECT_MODEL_DIR .'/ProjectStatusType.php';
}
if(!class_exists('Login')){
    require_once QCUBED_PROJECT_MODEL_DIR .'/Login.php';
}

class ExpandAsArrayTests extends \QCubed\Test\UnitTestCaseBase {

	public function testMultiLevel() {
		$arrPeople = Person::LoadAll(
			self::getTestClauses()
		);
				
		$this->assertEquals(12, sizeof($arrPeople), "12 Person objects found");
		$targetPerson = $this->verifyObjectPropertyHelper($arrPeople, 'LastName', 'Wolfe');
		
		$this->helperVerifyKarenWolfe($targetPerson);
		
		$objProjectArray = $targetPerson->_ProjectAsManagerArray;
		$this->assertEquals(2, sizeof($objProjectArray), "2 projects found");
		
		foreach ($objProjectArray as $objProject) {
			$objMilestoneArray = $objProject->_MilestoneArray;
			
			switch ($objProject->Id) {
				case 1:
					$this->assertEquals(3, sizeof($objMilestoneArray), "3 milestones found");
					break;
					
				case 4:
					$this->assertEquals(4, sizeof($objMilestoneArray), "4 milestones found");
					break;
					
				default:
					$this->assertTrue(false, 'Unexpected project found, id: ' . $objProject->Id);
					break;
			}
		}
		
		// Now test a multilevel expansion where first level does not expand by array. Should get duplicate entries at that level.
		$clauses = \QCubed\Query\QQ::Clause(
			\QCubed\Query\QQ::ExpandAsArray(QQN::Person()->Address),
			\QCubed\Query\QQ::Expand(QQN::Person()->ProjectAsManager),
			\QCubed\Query\QQ::ExpandAsArray(QQN::Person()->ProjectAsManager->Milestone)
		);

		$arrPeople = Person::LoadAll(
			$clauses
		);

		// Karen Wolfe should duplicate, since she is managing two projects
		$this->assertEquals(13, sizeof($arrPeople), "13 Person objects found");
		$targetPerson = $this->verifyObjectPropertyHelper($arrPeople, 'LastName', 'Wolfe');

		$objProjectArray = $targetPerson->_ProjectAsManagerArray;
		$this->assertNull($objProjectArray, "No project array found");

		$objProject = $targetPerson->_ProjectAsManager;
		$this->assertNotNull($objProject, "Project found");
		
		$objMilestoneArray = $objProject->_MilestoneArray;
		// since we didn't specify the order, not sure which one we will get, so check for either
		switch ($objProject->Id) {
			case 1:
				$this->assertEquals(3, sizeof($objMilestoneArray), "3 milestones found");
				break;
				
			case 4:
				$this->assertEquals(4, sizeof($objMilestoneArray), "4 milestones found");
				break;
				
			default:
				$this->assertTrue(false, 'Unexpected project found, id: ' . $objProject->Id);
				break;
		}
	}
	
	public function testQuerySingle() {
		$targetPerson = Person::QuerySingle(
			\QCubed\Query\QQ::Equal(QQN::Person()->Id, 7),
			self::getTestClauses()
		);
		
		$this->helperVerifyKarenWolfe($targetPerson);
		
		$objTwoKey = TwoKey::QuerySingle(
			\QCubed\Query\QQ::AndCondition (
				\QCubed\Query\QQ::Equal(QQN::TwoKey()->Server, 'google.com'),
				\QCubed\Query\QQ::Equal(QQN::TwoKey()->Directory, 'mail')
			),
			\QCubed\Query\QQ::Clause(
				\QCubed\Query\QQ::ExpandAsArray(QQN::TwoKey()->Project->PersonAsTeamMember)
			)
		);
		
		$this->assertEquals (count($objTwoKey->Project->_PersonAsTeamMemberArray), 6, '6 team members found.');
	}
	
	public function testEmptyArray() {
		$arrPeople = Person::QuerySingle(
			\QCubed\Query\QQ::Equal(QQN::Person()->Id, 2),
			self::getTestClauses()
			);
			
		$this->assertTrue(is_array($arrPeople->_ProjectAsManagerArray), "_ProjectAsManagerArray is an array");
		$this->assertEquals(0, count($arrPeople->_ProjectAsManagerArray), "_ProjectAsManagerArray has no Project objects");
	}

	public function testNullArray() {
		$arrPeople = Person::QuerySingle(
			\QCubed\Query\QQ::Equal(QQN::Person()->Id, 2)
			);
		
		$this->assertTrue(is_null($arrPeople->_ProjectAsManagerArray), "_ProjectAsManagerArray is null");
	}
	
	public function testTypeExpansion() {		
		$clauses = \QCubed\Query\QQ::Clause(
			\QCubed\Query\QQ::ExpandAsArray (QQN::Person()->PersonType)
		);
		
		$objPerson = 
			Person::QuerySingle(
				\QCubed\Query\QQ::Equal (QQN::Person()->Id, 7),
				$clauses
			);
		
		$intPersonTypeArray = $objPerson->_PersonTypeArray;
		$this->assertEquals(array(
			PersonType::Manager,
			PersonType::CompanyCar)
			, $intPersonTypeArray
			, "PersonType expansion is correct");
	}

	private static function getTestClauses() {
		return \QCubed\Query\QQ::Clause(
			\QCubed\Query\QQ::ExpandAsArray(QQN::Person()->Address),
			\QCubed\Query\QQ::ExpandAsArray(QQN::Person()->ProjectAsManager),
			\QCubed\Query\QQ::ExpandAsArray(QQN::Person()->ProjectAsManager->Milestone)
		);
	}
	
	private function helperVerifyKarenWolfe(Person $targetPerson) {		
		$this->assertEquals(2, sizeof($targetPerson->_ProjectAsManagerArray), "2 projects found");
		$targetProject = $this->verifyObjectPropertyHelper($targetPerson->_ProjectAsManagerArray, 'Name', 'ACME Payment System');
		
		$this->assertEquals(4, sizeof($targetProject->_MilestoneArray), "4 milestones found");
		$this->verifyObjectPropertyHelper($targetProject->_MilestoneArray, 'Name', 'Milestone H');
	}

	public function testSelectSubsetInExpand() {
		$objPersonArray = Person::QueryArray(
			\QCubed\Query\QQ::OrCondition(
				\QCubed\Query\QQ::Like(QQN::Person()->ProjectAsManager->Name, '%ACME%'),
				\QCubed\Query\QQ::Like(QQN::Person()->ProjectAsManager->Name, '%HR%')
			),
			// Let's expand on the Project, itself
			\QCubed\Query\QQ::Clause(
				\QCubed\Query\QQ::Select(QQN::Person()->LastName),
				\QCubed\Query\QQ::Expand(QQN::Person()->ProjectAsManager, null, \QCubed\Query\QQ::Select(QQN::Person()->ProjectAsManager->Spent)),
				\QCubed\Query\QQ::OrderBy(QQN::Person()->LastName, QQN::Person()->FirstName)
			)
		);

		if (PHP_VERSION_ID > 50600) { // PHP unit keeps making backwards incompatible changes
			foreach ($objPersonArray as $objPerson) {
				$this->expectException('\\QCubed\\Exception\\Caller');
				$objPerson->FirstName; // FirstName should throw exception, since it was not selected
				$this->expectException(null);

				$this->assertNotNull($objPerson->Id, "Id should not be null since it's always added to the select list");
				$this->assertNotNull($objPerson->_ProjectAsManager->Id, "ProjectAsManager->Id should not be null since id's are always added to the select list");

				$this->expectException('\\QCubed\\Exception\\Caller');
				$objPerson->_ProjectAsManager->Name; // not selected
				$this->expectException(null);
			}
		}
	}

	public function testSelectSubsetInExpandAsArray() {
		$objPersonArray = Person::LoadAll(
			\QCubed\Query\QQ::Clause(
				\QCubed\Query\QQ::Select(QQN::Person()->FirstName),
				\QCubed\Query\QQ::ExpandAsArray(QQN::Person()->Address, \QCubed\Query\QQ::Select(QQN::Person()->Address->Street, QQN::Person()->Address->City)),
				\QCubed\Query\QQ::ExpandAsArray(QQN::Person()->ProjectAsManager, \QCubed\Query\QQ::Select(QQN::Person()->ProjectAsManager->StartDate)),
				\QCubed\Query\QQ::ExpandAsArray(QQN::Person()->ProjectAsManager->Milestone, \QCubed\Query\QQ::Select(QQN::Person()->ProjectAsManager->Milestone->Name))
			)
		);

		if (PHP_VERSION_ID > 50600) { // PHP unit keeps making backwards incompatible changes
			foreach ($objPersonArray as $objPerson) {
				$this->expectException('\\QCubed\\Exception\\Caller');
				$objPerson->LastName; // Should throw exception, since it was not selected
				$this->expectException(null);

				$this->assertNotNull($objPerson->Id, "Id should not be null since it's always added to the select list");
				if (sizeof($objPerson->_AddressArray) > 0) {
					foreach ($objPerson->_AddressArray as $objAddress) {
						$this->assertNotNull($objAddress->Id, "Address->Id should not be null since it's always added to the select list");

						$this->expectException('\\QCubed\\Exception\\Caller');
						$objAddress->PersonId; // Should throw exception, since it was not selected
						$this->expectException(null);
					}
				}
				if (sizeof($objPerson->_ProjectAsManagerArray) > 0) {
					foreach ($objPerson->_ProjectAsManagerArray as $objProject) {
						$this->assertNotNull($objProject->Id, "Project->Id should not be null since it's always added to the select list");

						$this->expectException('\\QCubed\\Exception\\Caller');
						$objProject->Name; // Should throw exception, since it was not selected
						$this->expectException(null);

						if (sizeof($objProject->_MilestoneArray) > 0) {
							foreach ($objProject->_MilestoneArray as $objMilestone) {
								$this->assertNotNull($objMilestone->Id, "Milestone->Id should not be null since it's always added to the select list");

								$this->expectException('\\QCubed\\Exception\\Caller');
								$objMilestone->ProjectId; // Should throw exception, since it was not selected
								$this->expectException(null);
							}
						}
					}
				}
			}
		}
	}
	
	public function testMultiLeafExpansion() {
		$objMilestone = Milestone::QuerySingle(
			\QCubed\Query\QQ::Equal (QQN::Milestone()->Id, 1),
			\QCubed\Query\QQ::Clause(
				\QCubed\Query\QQ::ExpandAsArray(QQN::Milestone()->Project->ManagerPerson->ProjectAsTeamMember),
				\QCubed\Query\QQ::ExpandAsArray(QQN::Milestone()->Project->PersonAsTeamMember)
			)
		);
		
		$objProjectArray = $objMilestone->Project->ManagerPerson->_ProjectAsTeamMemberArray;
		$objPeopleArray = $objMilestone->Project->_PersonAsTeamMemberArray;
		
		$this->assertTrue(is_array($objProjectArray), "_ProjectAsTeamMemberArray is an array");
		$this->assertEquals(2, count($objProjectArray), "_ProjectAsTeamMemberArray has 2 Project objects");
		
		$this->assertTrue(is_array($objPeopleArray), "_PersonAsTeamMemberArray is an array");
		$this->assertEquals(5, count($objPeopleArray), "_PersonAsTeamMemberArray has 5 People objects");
		
		// try through a unique relationship
		$objLogin = Login::QuerySingle(
			\QCubed\Query\QQ::Equal (QQN::Login()->PersonId, 7),
			\QCubed\Query\QQ::Clause(
				\QCubed\Query\QQ::ExpandAsArray(QQN::Login()->Person->ProjectAsTeamMember),
				\QCubed\Query\QQ::ExpandAsArray(QQN::Login()->Person->ProjectAsManager)
			)
		);
		
		$objProjectArray = $objLogin->Person->_ProjectAsTeamMemberArray;
		
		$this->assertTrue(is_array($objProjectArray), "_ProjectAsTeamMemberArray is an array");
		$this->assertEquals(2, count($objProjectArray), "_ProjectAsTeamMemberArray has 2 Project objects");
		
		$objProjectArray = $objLogin->Person->_ProjectAsManagerArray;
		
		$this->assertTrue(is_array($objProjectArray), "_ProjectAsManagerArray is an array");
		$this->assertEquals(2, count($objProjectArray), "_ProjectAsManagerArray has 2 Project objects");
				
	}

	public function testConditionalExpansion() {
		$clauses = \QCubed\Query\QQ::Clause(
			\QCubed\Query\QQ::ExpandAsArray(QQN::Person()->Address),
			\QCubed\Query\QQ::Expand(QQN::Person()->ProjectAsManager, \QCubed\Query\QQ::Equal (QQN::Person()->ProjectAsManager->ProjectStatusTypeId, ProjectStatusType::Open)),
			\QCubed\Query\QQ::ExpandAsArray(QQN::Person()->ProjectAsManager->Milestone),
			\QCubed\Query\QQ::OrderBy(QQN::Person()->Id)
		);
		
		$targetPersonArray = Person::LoadAll (
			$clauses
		);
		
		$targetPerson = reset($targetPersonArray);
		
		$this->assertEquals ($targetPerson->Id, 1, "Person 1 found.");
		$this->assertNotNull ($targetPerson->_ProjectAsManager, "Person 1 has a project.");

		$targetPerson = end($targetPersonArray);
		
		$this->assertEquals ($targetPerson->Id, 12, "Person 12 found.");
		$this->assertNull ($targetPerson->_ProjectAsManager, "Person 12 does not have a project.");

	}

	public function testConditionalExpansion2() {
		$clauses = \QCubed\Query\QQ::Clause(
			\QCubed\Query\QQ::Expand(QQN::Login()->Person->ProjectAsManager, \QCubed\Query\QQ::Equal (QQN::Login()->Person->ProjectAsManager->ProjectStatusTypeId, ProjectStatusType::Open)),
			\QCubed\Query\QQ::ExpandAsArray(QQN::Login()->Person->ProjectAsManager->Milestone),
			\QCubed\Query\QQ::ExpandAsArray(QQN::Login()->Person->Address),
			\QCubed\Query\QQ::OrderBy(QQN::Login()->Person->Id)
		);

		$cond = \QCubed\Query\QQ::In (QQN::Login()->PersonId, [1,3,7]);
		$targetLoginArray = Login::QueryArray (
			$cond,
			$clauses
		);

		$targetLogin = reset($targetLoginArray);
		$this->assertEquals ($targetLogin->Person->Id, 1, "Person 1 found.");
		$this->assertNotNull ($targetLogin->Person->_ProjectAsManager, "Person 1 has an open project.");

		$targetLogin = next($targetLoginArray);
		$this->assertEquals ($targetLogin->Person->Id, 3, "Person 3 found.");
		$this->assertNull ($targetLogin->Person->_ProjectAsManager, "Person 3 does not have an open project.");

		$targetLogin = next($targetLoginArray);
		$this->assertEquals ($targetLogin->Person->Id, 7, "Person 7 found.");
		$this->assertNull ($targetLogin->Person->_ProjectAsManager, "Person 7 does have an open project.");

	}


	public function testConditionalExpansion3() {

		// A complex join with conditions. Find all team members of completed projects which have an open child project.
		$clauses = \QCubed\Query\QQ::Clause(
			\QCubed\Query\QQ::Expand(QQN::Person()->ProjectAsTeamMember->Project, \QCubed\Query\QQ::Equal(QQN::Person()->ProjectAsTeamMember->Project->ProjectStatusTypeId, ProjectStatusType::Completed)),
			\QCubed\Query\QQ::Expand(QQN::Person()->ProjectAsTeamMember->Project->ProjectAsRelated->Project, \QCubed\Query\QQ::Equal(QQN::Person()->ProjectAsTeamMember->Project->ProjectAsRelated->Project->ProjectStatusTypeId, ProjectStatusType::Open))
		);

		$cond = \QCubed\Query\QQ::IsNotNull(QQN::Person()->ProjectAsTeamMember->Project->ProjectAsRelated->Project->Id); // Filter out unsuccessful joins

		$targetPersonArray = Person::QueryArray (
			$cond,
			$clauses
		);

		$targetPerson = reset($targetPersonArray);

		$this->assertEquals(ProjectStatusType::Completed, $targetPerson->ProjectAsTeamMember->ProjectStatusTypeId, "Found completed parent project");
		$this->assertEquals(ProjectStatusType::Open, $targetPerson->ProjectAsTeamMember->ProjectAsRelated->ProjectStatusTypeId, "Found open child project");

	}

	public function testConditionalExpansionReverse() {
		// Get all people, and projects they are managing if the projects are open.
		$a = Person::QueryArray(
			\QCubed\Query\QQ::All(),
			[
				\QCubed\Query\QQ::ExpandAsArray(QQN::Person()->ProjectAsManager, \QCubed\Query\QQ::Equal(QQN::Person()->ProjectAsManager->ProjectStatusTypeId, ProjectStatusType::Open)),
				\QCubed\Query\QQ::OrderBy(QQN::Person()->Id)
			]
		);

		$this->assertEquals(3, $a[0]->_ProjectAsManagerArray[0]->Id);
	}

	public function testConditionalExpansionAssociation() {
		// Conditional expansion on association nodes really can only work with the PK of the join.

		// Get all projects, and also expand on related projects if the id is 1
		$a = Project::QueryArray(
			\QCubed\Query\QQ::All(),
			[
				\QCubed\Query\QQ::ExpandAsArray(QQN::Project()->ParentProjectAsRelated, \QCubed\Query\QQ::Equal(QQN::Project()->ParentProjectAsRelated->ProjectId, 1)),
				\QCubed\Query\QQ::ExpandAsArray(QQN::Project()->ProjectAsRelated, \QCubed\Query\QQ::Equal(QQN::Project()->ProjectAsRelated->Project->Id, 1)),
				\QCubed\Query\QQ::OrderBy(QQN::Project()->Id)
			]
		);

		$this->assertEquals(1, $a[2]->_ParentProjectAsRelatedArray[0]->Id);
	}
}
