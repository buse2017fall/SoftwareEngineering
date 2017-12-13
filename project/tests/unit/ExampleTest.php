<?php

// Author: Lu Min
// Unit test 

class ExampleTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests

    public function testSomeFeature()
    {
		$user = User::find(1);
		$user->setEmail('correct@email.com');
		$user->save();
		$user = User::find(1);
		$this->assertEquals('correct@email.com', $user->getEmail());
    }
	
	function testSavingUser()
	{
		$user = new User();
		$user->setName('Miles');
		$user->setSurname('Davis');
		$user->save();
		$this->assertEquals('Miles Davis', $user->getFullName());
		$this->tester->seeInDatabase('users', ['name' => 'Miles', 'surname' => 'Davis']);
	}
	
	function testUserNameCanBeChanged()
	{
		// create a user from framework, user will be deleted after the test
		$id = $this->tester->haveRecord('users', ['name' => 'miles']);
		// access model
		$user = User::find($id);
		$user->setName('bill');
		$user->save();
		$this->assertEquals('bill', $user->getName());
		// verify data was saved using framework methods
		$this->tester->seeRecord('users', ['name' => 'bill']);
		$this->tester->dontSeeRecord('users', ['name' => 'miles']);
	}
	
	function testUserNameCanBeChanged()
	{
		// create a user from framework, user will be deleted after the test
		$id = $this->tester->haveInRepository('Acme\DemoBundle\Entity\User', ['name' => 'miles']);
		// get entity manager by accessing module
		$em = $this->getModule('Doctrine2')->em;
		// get real user
		$user = $em->find('Acme\DemoBundle\Entity\User', $id);
		$user->setName('bill');
		$em->persist($user);
		$em->flush();
		$this->assertEquals('bill', $user->getName());
		// verify data was saved using framework methods
		$this->tester->seeInRepository('Acme\DemoBundle\Entity\User', ['name' => 'bill']);
		$this->tester->dontSeeInRepository('Acme\DemoBundle\Entity\User', ['name' => 'miles']);
	}
	
	
	
	function testAddUserStory()
	{

	}
	
	function testDeleteUserStory()
	{
		
	}
	
	function testAdvancedAddStory()
	{
		
	}
	
	function testSortByHand()
	{
		
	}
	
	function testSortByDate()
	{
		
	}
	
	function testSortByModified()
	{
		
	}
	
	function testSortByPoint()
	{
		
	}
	
	function testSearch()
	{
		
	}
	
	function testAddDescription()
	{
		
	}
	
	function testAddSprint()
	{
		
	}
	
	function testChangeSprint()
	{
		
	}
	
	function testShowDescription()
	{
		
	}
	
	function testHideDescription()
	{
		
	}
	
	function testShowTag()
	{
		
	}
	
	function testDeleteTag()
	{
		
	}
	
	function testClear()
	{
		
	}
	
	function testShowCompletedStory()
	{
		
	}
	
	function testLogout()
	{
		
	}
}