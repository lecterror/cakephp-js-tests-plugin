<?php

class TestHandlerComponent extends Object
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
			$instrumentedTestFileURL = Router::url($profileData['url']['instrumented_tests'].$testMainFileName);
			$instrumentedTestURL = sprintf('%s%s?u=%s', $profileData['url']['instrumented_root'], 'jscoverage.html', $instrumentedTestFileURL);

			$this->_tests[$profileName][$testName]['normalTestURL'] = Router::url($profileData['url']['normal_tests'].$testMainFileName);
			$this->_tests[$profileName][$testName]['instrumentedTestURL'] = Router::url($instrumentedTestURL);
		}

		return $this->_tests[$profileName];
	}

	function checkProfile($profileData)
	{
		$passed = true;

		if (!Set::check($profileData, 'dir.normal_root'))
		{
			trigger_error('Normal root dir not set - you will not be able to run instrumentation!');
			$passed = false;
		}

		if (!Set::check($profileData, 'dir.normal_tests'))
		{
			trigger_error('Normal test dir not set - no tests will be detected!');
			$passed = false;
		}

		// @todo: continue testing

		return $passed;
	}
}
