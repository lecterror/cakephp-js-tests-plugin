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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php __('CakePHP: the rapid development php framework:'); ?>
		<?php echo $title_for_layout; ?>
	</title>
	<?php
		echo $this->Html->meta('icon');

		echo $this->Html->css('cake.generic');

		echo $scripts_for_layout;
	?>
	<script type="text/javascript">
		function toggleJsCoverage()
		{
			var elem = document.getElementById('jscoverage-output');

			if (elem.style.display == 'block')
			{
				elem.style.display = 'none';
			}
			else
			{
				elem.style.display = 'block';
			}
		}
	</script>
	<style type="text/css">
		h3 {font-size: 170%; padding-top: 1em}
		a {font-size: 120%}
		li {line-height: 140%}
		.test-menu {float: left; margin-right: 24px; width: 400px;}
		.test-results {float: left; width: 67%;}
		form div {margin-bottom: 0px !important;}

		.active-test,
		.available-profiles
		{
			background-color: #e3f2e1;
			margin-top: 10px;
			padding: 10px;
			-moz-border-radius: 7px;
			-moz-box-shadow: 3px 3px 4px;
		}

		.available-profiles ul
		{
			margin-top: 10px;
			padding: 2px;
		}

		.available-profiles a
		{
			font-size: medium;
		}

		.instrumentation-message
		{
			background-color: #eed0d0;
			margin-top: 10px;
			padding: 10px;
			text-align: center;
			-moz-border-radius: 7px;
			-moz-box-shadow: 3px 3px 4px;
		}

		.no-tests-message
		{
			text-align: center;
		}

		.tests-table
		{
			width: 100%;
			margin-top: 10px;
			background-color: #e3f2e1;
			-moz-border-radius: 7px;
			-moz-box-shadow: 3px 3px 4px;
			-webkit-box-shadow: 0px 2px 13px #999;
		}

		.tests-table th
		{
			font-weight: bold;
			text-align: center;
		}

		.tests-table tbody td
		{
			font-weight: normal;
			text-align: center;
		}

		.tests-table tfoot td
		{
			background-color: #e3f2e1;
			text-align: center;
			font-weight: bold;
		}

		.tests-table td.test-name,
		.tests-table th.test-name
		{
			text-align: left;
		}

		.ok { color:#FFFFFF !important; background-color:#BFFFBF !important; }
		.fail { color:#FFFFFF !important; background-color:#CC0000 !important; }
		.warn { color:#000000 !important; background-color:#FFFF88 !important; }

		#jscoverage-output
		{
			font-family: "DejaVu Sans Mono", "Courier New", monospace;
			display: none;
		}

		#toggle-jscoverage-output
		{
			font-size: large;
		}
	</style>
</head>
<body>
	<div id="container">
		<div id="header">
			<h1><?php echo $this->Html->link(__('CakePHP JsTests Plugin: unit tests and code coverage for JavaScript in CakePHP', true), 'http://lecterror.com/'); ?></h1>
		</div>
		<div id="content">

			<?php echo $this->Session->flash(); ?>

			<?php
			$jscoverageOutput = $this->Session->read('JSCoverage.output');

			if (!empty($jscoverageOutput))
			{
				$jscoverageOutput = unserialize($jscoverageOutput);
				$this->Session->delete('JSCoverage.output');
				?>
				<script type="text/javascript">
					var flash = document.getElementById('flashMessage');

					var link = document.createElement('a');
					link.id = 'toggle-jscoverage-output';
					link.setAttribute('href', '#');
					link.onclick = function() { toggleJsCoverage(); return false; };
					link.innerHTML = '[View output]';

					var spacer = document.createElement('div');
					spacer.style.display = 'inline';
					spacer.innerHTML = '&nbsp;&nbsp;';

					if (flash)
					{
						flash.appendChild(spacer);
						flash.appendChild(link);
					}
				</script>
				<div id="jscoverage-output" class="notice">
				<?php foreach ($jscoverageOutput as $line): ?>
					<?php echo $line; ?><br />
				<?php endforeach; ?>
				</div>
				<?php
			}
			?>

			<?php echo $content_for_layout; ?>

		</div>
		<div id="footer">
			<?php echo $this->Html->link(
					$this->Html->image('cake.power.gif', array('alt'=> __('CakePHP: the rapid development php framework', true), 'border' => '0')),
					'http://www.cakephp.org/',
					array('target' => '_blank', 'escape' => false)
				);
			?>
		</div>
	</div>
	<?php echo $this->element('sql_dump'); ?>
</body>
</html>
