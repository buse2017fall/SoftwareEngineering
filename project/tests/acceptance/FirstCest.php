<?php


class FirstCest
{
    public function HomeWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->see('Propal');
    }
	
	public function loginPartWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->see('admin_login');
    }
	
	public function loginWorks(AcceptanceTester $I)
	{
		$I->am('user');
		$I->wantTo('login to website');
		$I->amOnPage('/');
		$I->click('admin_login');
		$I->fillField('password','1234');
		$I->click('admin_login');
		$I->see('admin_logout');
	}
	
	public function addUserStoryWorks(AcceptanceTester $I)
	{
		$I->am('user');
		$I->wantTo('add a new user story named test');
		$I->amOnPage('/');
		$I->fillField('New story', 'test');
		$I->click(["id" => "newtask_submit"]);
		//$I->seeIndatabase('tasklist', ["task-title" => "jkjk"]);
	}
	
	public function backlogWorks(AcceptanceTester $I)
	{
		$I->am('user');
		$I->wantTo('check my project backlog');
		$I->amOnPage('/');
		$I->click('Backlog');
		$I->see('Sprint');
	}
	
	public function deleteUserStory(AcceptanceTester $I)
	{
		$I->am('user');
		$I->wantTo('delete a new user story named test');
		$I->amOnPage('/');
		$I->click(["id" => "cmenu_delete"]);
		$I->expect('cannot see a user story named test');
		$I->dontSee('test');
	}
	
	public function addSprintWorks(AcceptanceTester $I)
	{
		$I->am('user');
		$I->wantTo('add a new sprint named Sprint2');
		$I->amOnPage('/');
		$I->dontSee('new sprint');
		$I->click("#lists > div.mtt-tabs-add-button");
		$I->see('new');
	}
	
	public function advancedWorks(AcceptanceTester $I)
	{
		$I->am('user');
		$I->amOnPage('/');
		$I->click(["class" => "mtt-img-button"]);
		$I->see('New story');
		$I->fillField('task', 'advanced Story');
		$I->fillField('note', 'This is a test for advanced story edition.');
		$I->selectOption('prio', '1');
		$I->click('Save');
	}
	
	public function editDescription(AcceptanceTester $I)
	{
		$I->am('user');
		$I->amOnPage('/');
		$I->wantTo('edit user story description');
		$I->click(["id" => "cmenu_note"]);
		$I->fillField('note', 'This is a test for editing user story.');
		$I->click('Save');
	}
	
	public function cancelEditDescription(AcceptanceTester $I)
	{
		$I->am('user');
		$I->amOnPage('/');
		$I->wantTo('see canceling edit user story description working');
		$I->click(["id" => "cmenu_note"]);
		$I->fillField('note', 'This is a test for editing user story.');
		$I->click('Cancel');
	}
	
	public function searchWorks(AcceptanceTester $I)
	{
		$I->am('user');
		$I->amOnPage('/');
		$I->wantTo('search user stories');
		$I->click('#toolbar');
		$I->see('Searching for');
	}
	
	public function SettingWorks(AcceptanceTester $I)
	{
		$I->am('user');
		$I->amOnPage('/');
		$I->click('Settings');
		$I->see('Back');
		$I->click('<< Back');
		$I->see('Backlog');
	}
	
	public function changeSprintName(AcceptanceTester $I)
	{
		$I->am('user');
		$I->amOnPage('/');
		$I->wantTo('change sprint name');
		$I->click(['class' => 'list-action']);
		$I->click('#btnRenameList');
	}
	
	public function sortByDate(AcceptanceTester $I)
	{
		$I->am('user');
		$I->amOnPage('/');
		$I->wantTo('sort user stories by date');
		$I->click(['class' => 'list-action']);
		$I->click('#sortByDateCreated');
	}
	
	public function sortByPoint(AcceptanceTester $I)
	{
		$I->am('user');
		$I->amOnPage('/');
		$I->wantTo('sort user stories by points');
		$I->click(['class' => 'list-action']);
		$I->click('#sortByPrio');
	}
	
	public function sortByDateModify(AcceptanceTester $I)
	{
		$I->am('user');
		$I->amOnPage('/');
		$I->wantTo('sort user stories by date modified');
		$I->click(['class' => 'list-action']);
		$I->click('#sortByDateModified');
	}
	
	public function checkLogout(AcceptanceTester $I)
	{
		$I->am('user');
		$I->amOnPage('/');
		$I->wantTo('log out');
		$I->click('admin_logout');
		$I->see('Public Tasks');
		$I->see('Propal');
	}
}
