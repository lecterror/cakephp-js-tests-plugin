<?php
/**
	CakePHP JsTests Plugin - JavaScript unit tests and code coverage

	Copyright (C) 2010-3827 dr. Hannibal Lecter / lecterror
	<http://lecterror.com/>

	Multi-licensed under:
		MPL <http://www.mozilla.org/MPL/MPL-1.1.html>
		LGPL <http://www.gnu.org/licenses/lgpl.html>
		GPL <http://www.gnu.org/licenses/gpl.html>
*/

App::uses('Component', 'Controller');
App::uses('Router', 'Routing');

class TestHandlerComponent extends Component
{
	var $name = 'TestHandler';
	var $_tests = array();

	/**
	 * Detects tests for a given profile, reads all the data for the test
	 * and prepares everything for testing.
	 *
	 * @param string $profileName
	 * @param array $profileData
	 * @return array A set of detected tests for a profile.
	 */
	function loadTests($profileName, $profileData)
	{
		$this->_tests[$profileName] = array();

		// detect tests
		$testsGlob = $profileData['dir']['normal_tests'].$profileData['params']['tests'];
		$detectedTests = glob($testsGlob);

		foreach ($detectedTests as $testFullPath)
		{
			// get the name and related test files
			$testMainFileName = basename($testFullPath);

			$matches = array();
			$testFiles = array();

			$testName = preg_match('#'.$profileData['params']['name'].'#', $testMainFileName, $matches);
			$testName = isset($matches['name']) ? $matches['name'] : $matches[0];

			foreach ($profileData['params']['files'] as $pattern)
			{
				$relatedPattern = dirname($testFullPath).DS.str_replace('%name%', $testName, $pattern);
				$testFiles = array_merge($testFiles, glob($relatedPattern));
			}

			$this->_tests[$profileName][$testName]['mainTestFile'] = $testMainFileName;
			$this->_tests[$profileName][$testName]['normalTestPath'] = $testFullPath;
			$this->_tests[$profileName][$testName]['instrumentedTestPath'] = $profileData['dir']['instrumented_tests'].$testMainFileName;

			$this->_tests[$profileName][$testName]['normalRelatedTestFiles'] = $testFiles;

			$instrumentedRelatedTestFiles = str_replace($profileData['dir']['normal_tests'], $profileData['dir']['instrumented_tests'], $testFiles);
			$this->_tests[$profileName][$testName]['instrumentedRelatedTestFiles'] = $instrumentedRelatedTestFiles;

			// check for instrumented version
			$instrumentedExists = file_exists($profileData['dir']['instrumented_tests'].$testMainFileName);
			$this->_tests[$profileName][$testName]['instrumentedExists'] = $instrumentedExists;
			$this->_tests[$profileName][$testName]['instrumentedIsUpdated'] = false;

			// check if the instrumented version is up to date
			if ($instrumentedExists)
			{
				$lastNormalModification = filemtime($testFullPath);

				foreach ($testFiles as $testFile)
				{
					if (file_exists($testFile))
					{
						$tmp_mtime = filemtime($testFile);
						$lastNormalModification = $tmp_mtime > $lastNormalModification ? $tmp_mtime : $lastNormalModification;
					}
				}

				$lastInstrumentedModification = filemtime($this->_tests[$profileName][$testName]['instrumentedTestPath']);

				foreach ($instrumentedRelatedTestFiles as $testFile)
				{
					if (file_exists($testFile))
					{
						$tmp_mtime = filemtime($testFile);
						$lastInstrumentedModification = $tmp_mtime > $lastInstrumentedModification ? $tmp_mtime : $lastInstrumentedModification;
					}
				}

				$this->_tests[$profileName][$testName]['instrumentedIsUpdated'] = $lastInstrumentedModification >= $lastNormalModification;
			}

			// finally, generate URLs
			$rootUrl = Router::url('/');
			$instrumentedTestFileURL = $rootUrl.$profileData['url']['instrumented_tests'].$testMainFileName;
			$instrumentedTestURL = sprintf('%s%s%s?u=%s', $rootUrl, $profileData['url']['instrumented_root'], 'jscoverage.html', $instrumentedTestFileURL);

			$this->_tests[$profileName][$testName]['normalTestUrl'] = $rootUrl.$profileData['url']['normal_tests'].$testMainFileName;
			$this->_tests[$profileName][$testName]['instrumentedTestUrl'] = $instrumentedTestURL;
		}

		return $this->_tests[$profileName];
	}

	/**
	 * Checks profile data for any errors, should be called before invoking a test profile
	 * to make sure everything is configured properly.
	 *
	 * @param array $profileData
	 * @return bool True if the profile data is correct, false if any setting is missing.
	 */
	function checkProfile($profileData, $verbose = false)
	{
		$passed = true;

		$checks = array
			(
				'dir.normal_root'				=> 'Normal root dir not set - you will not be able to run instrumentation!',
				'dir.normal_tests'				=> 'Normal test dir not set - no tests will be detected!',
				'dir.instrumented_root'			=> 'Instrumented root dir not set - instrumentation may not be possible!',
				'dir.instrumented_tests'		=> 'Instrumented test dir not set - instrumentation may not be possible!',
				'url.normal_root'				=> 'Normal root URL not set - you will not be able to run instrumentation!',
				'url.normal_tests'				=> 'Normal test URL not set - no tests will be detected!',
				'url.instrumented_root'			=> 'Instrumented root URL not set - instrumentation may not be possible!',
				'url.instrumented_tests'		=> 'Instrumented test URL not set - instrumentation may not be possible!',
				'params.tests'					=> 'Main test param not set - tests will not be detected!',
				'params.name'					=> 'Test name detection regex not set - tests might not work properly!',
				'params.files'					=> 'Related test file patterns not set - additional test files will not be checked for instrumentation!',
				'instrumentation.noInstrument'	=> 'Instrumentation exceptions not set - this can be empty but you may see invalid code coverage!',
				'instrumentation.exclude'		=> 'Instrumentation excludes not set - if not used this can be left empty but it must exist!',
			);

		foreach ($checks as $key => $error)
		{
			if (!Set::check($profileData, $key))
			{
				if ($verbose == true)
				{
					throw new CakeException($error);
				}

				$passed = false;
			}
		}

		return $passed;
	}

	/**
	 * Run instrumentation for a test profile.
	 *
	 * @param array $profileData
	 */
	function instrument($profileData)
	{
		$jsbin = Configure::read('JsTests.JSCoverage.executable');

		if (!file_exists($jsbin))
		{
			trigger_error('JSCoverage executable not found!');
			return;
		}

		$noInstrument = array();
		$exclude = array();

		foreach ($profileData['instrumentation']['noInstrument'] as $item)
		{
			$noInstrument[] = sprintf('--no-instrument="%s"', $item);
		}

		foreach ($profileData['instrumentation']['exclude'] as $item)
		{
			$exclude[] = sprintf('--exclude="%s"', $item);
		}

		$sourceDir = $profileData['dir']['normal_root'];
		$targetDir = $profileData['dir']['instrumented_root'];

		$command = sprintf
			(
				'%s -v %s %s "%s" "%s"',
				$jsbin,
				join(' ', $noInstrument),
				join(' ', $exclude),
				str_replace('\\', '/', $sourceDir),
				str_replace('\\', '/', $targetDir)
			);

		$output = array();
		$exitCode = null;

		if (DIRECTORY_SEPARATOR != '\\')
		{
			$command = $command.' 2>&1';
		}
		#else
		#{
		#	$command = str_replace('\\', '/', $command);
		#}

		#pr($command);die;
		if (!defined('CAKEPHP_UNIT_TEST_EXECUTION'))
		{
			exec($command, $output, $exitCode);
		}

		return array('output' => $output, 'exitCode' => $exitCode);
	}
}
