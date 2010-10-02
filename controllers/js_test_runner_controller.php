<?php
/*
	CakePHP JsTests Plugin - JavaScript unit tests and code coverage
	Copyright (C) 2010-3827 dr. Hannibal Lecter (http://lecterror.com/)

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class JsTestRunnerController extends JsTestsAppController
{
	var $name = 'JsTestRunner';
	var $uses = array();
	var $components = array('RequestHandler', 'TestHandler');

	function run()
	{
		$passed = $this->TestHandler->checkProfile($this->activeProfileData);

		if (!$passed)
		{
			$tests = array();
		}
		else
		{
			$tests = $this->TestHandler->loadTests($this->activeProfileName, $this->activeProfileData);
		}

		$allProfiles = Configure::read('JsTests.Profiles');
		$availableProfiles = array();

		foreach ($allProfiles as $profileName => $profileData)
		{
			$availableProfiles[$profileName] = $this->TestHandler->checkProfile($profileData);
		}

		$this->set('activeProfileName', $this->activeProfileName);
		$this->set('activeProfileData', $this->activeProfileData);
		$this->set(compact('tests', 'availableProfiles'));
	}

	function instrument()
	{
		if (!$this->RequestHandler->isPost())
		{
			$this->redirect($this->referer());
		}

		$profile = $this->data['profile'];
		$profileData = Configure::read(sprintf('JsTests.Profiles.%s', $profile));

		if (!$this->TestHandler->checkProfile($profileData))
		{
			$this->Session->setFlash('Instrumentation failed: profile not configured correctly!');
			$this->redirect($this->referer());
		}

		$output = $this->TestHandler->instrument($profileData);

		if ($output['exitCode'] != 0)
		{
			$this->Session->setFlash(sprintf('Instrumentation failed: JSCoverage returned a status of %s', $output['exitCode']));
			$this->Session->write('JSCoverage.output', serialize($output['output']));
			$this->redirect($this->referer());
		}
		else
		{
			$this->Session->setFlash('Instrumentation successfull');
			$this->redirect($this->referer());
		}
	}
}
