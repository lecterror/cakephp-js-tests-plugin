<div class="test-index">
	<h2>JavaScript Unit Tests</h2>
	<div class="test-menu">
		<div class="active-test">Active test profile: <?php echo $activeProfileName; ?></div>
		<?php
		$testStates = Set::classicExtract($tests, '{.*}.instrumentedIsUpdated');
		$needsInstrumentation = false;

		foreach ($testStates as $key => $state)
		{
			if ($state !== true)
			{
				$needsInstrumentation = true;
				break;
			}
		}
		?>
		<?php if ($needsInstrumentation): ?>
		<div class="instrumentation-message">
			<div>Some of the instrumented tests are not up to date!</div>
			<?php
			echo $this->Form->create
				(
					false,
					array
					(
						'url' => array
						(
							'plugin' => 'js_tests',
							'controller' => 'js_test_runner',
							'action' => 'instrument',
						)
					)
				);
			echo $this->Form->hidden('profile', array('value' => $activeProfileName));
			echo $this->Form->end('Instrument');
			?>
		</div>
		<?php endif; ?>
	</div>
	<div class="test-results">
		<table class="tests-table">
			<thead>
				<tr>
					<th>Test name</th>
					<th>Normal test</th>
					<th>Instrumented test</th>
					<th>Needs update?</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$totalNormalTests = 0;
				$totalInstrumentedTests	= 0;
				$totalNeedUpdateTests = 0;
				?>
				<?php foreach ($tests as $name => $data): ?>
					<?php $totalNormalTests++; ?>
					<tr>
						<td class="test-name"><?php echo $name; ?></td>
						<td>
							<a href="<?php echo $data['normalTestUrl']; ?>">Run</a>
							<a href="<?php echo $data['normalTestUrl']; ?>" target="_blank">[^]</a>
						</td>
						<td>
						<?php if ($data['instrumentedExists']): ?>
							<?php $totalInstrumentedTests++; ?>
							<a href="<?php echo $data['instrumentedTestUrl']; ?>">Run</a>
							<a href="<?php echo $data['instrumentedTestUrl']; ?>" target="_blank">[^]</a>
						<?php else: ?>
							&infin;
						<?php endif; ?>
						</td>
						<td class="<?php echo (($data['instrumentedIsUpdated']) ? '' : 'warn'); ?>">
							<?php if ($data['instrumentedIsUpdated']): ?>
								No
							<?php else: ?>
								<?php $totalNeedUpdateTests++; ?>
								Yes
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				<?php if (empty($tests)): ?>
				<tr>
					<td colspan="4" class="no-tests-message warn">No tests found!</td>
				</tr>
				<?php endif; ?>
			</tbody>
			<tfoot>
				<tr>
					<td>Totals:</td>
					<td><?php echo $totalNormalTests; ?></td>
					<td><?php echo $totalInstrumentedTests; ?></td>
					<td><?php echo $totalNeedUpdateTests; ?></td>
				</tr>
			</tfoot>
		</table>
		<?php //debug($tests); ?>
	</div>
</div>
