<?php

Configure::write
	(
		'JsTests.Profiles',
		array
		(
			'default' => array
			(
				'dir' => array
				(
					'normal_root'			=> JS,
					'normal_tests'			=> JS.'tests/',
					'instrumented_root'		=> WWW_ROOT.'js_instrumented/',
					'instrumented_tests'	=> WWW_ROOT.'js_instrumented/tests/'
				),
				'url' => array
				(
					'normal_root'			=> JS_URL,
					'normal_tests'			=> JS_URL.'tests/',
					'instrumented_root'		=> 'js_instrumented/',
					'instrumented_tests'	=> 'js_instrumented/tests/',
				),
				'params' => array
				(
					'tests'		=> '*.test.html',
					'name'		=> '^(?P<name>[a-zA-Z_\-0-9]+).test.html$',
					'files'		=> array('%name%.test.js', '%name%.lib.js'),
				),
				'instrumentation' => array
				(
					'noInstrument'		=> array('tests'/*, 'jquery'*/),
					'exclude'			=> array('.svn'),
				),
			),
			'invalid' => array()
		)
	);

Configure::write
	(
		'JsTests.JSCoverage',
		array
		(
			'executable'	=> '/usr/bin/jscoverage'
		)
	);

Configure::write('JsTests.ActiveProfile', 'default');
