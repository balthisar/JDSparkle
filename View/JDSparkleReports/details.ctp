<?php
	echo <<<HTML
<h1>Sparkle System Profiling Detail Report for {$reportData['appHeader']['app_name']}</h1>
HTML;

	echo <<<HTML
<div class="jdsparkle-details-section01">

	<h2>Reporting Characteristics</h2>
	<table>
		<thead>
			<tr>
				<th>Attribute</th>
				<th>Value</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Application Name</td>
				<td>{$reportData['appHeader']['app_name']}</td>
			</tr>
			<tr>
				<td>Total Number of Profile Reports</td>
				<td>{$reportData['appHeader']['report_count']}</td>
			</tr>
			<tr>
				<td>Total Distinct IP Addresses in Reporting Period</td>
				<td>{$reportData['appHeader']['distinct_ip_count']}</td>
			</tr>
			<tr>
				<td>Date of First Record</td>
				<td>{$reportData['appHeader']['date_first_unconstrained']}</td>
			</tr>
			<tr>
				<td>Date of First Record in Reporting Period</td>
				<td>{$reportData['appHeader']['date_first']}</td>
			</tr>
			<tr>
				<td>Date of Last Record</td>
				<td>{$reportData['appHeader']['date_last']}</td>
			</tr>
		</tbody>
	</table>

</div>
HTML;

echo <<<HTML

<div class="jdsparkle-details-section02">

	<h2>Unique IP Addresses Summary for Reporting Period</h2>

	<table>
		<thead>
			<tr>
				<th>Date</th>
				<th>Quantity of Reporting IP Addresses</th>
			</tr>
		</thead>
		<tbody>

HTML;

foreach ($reportData['ip_counts'] as $key => $value)
{
	echo <<<HTML
			<tr>
				<td>$key</td>
				<td>$value</td>
			</tr>
HTML;
}

echo <<<HTML
		</tbody>
	</table>

</div>
HTML;

echo <<<HTML
<div class="jdsparkle-details-section03">
	<h2>Attributes Reports for Reporting Period</h2>
HTML;

foreach ($reportData['ip_data'] as $key => $value)
{
	echo <<<HTML
	<table>
		<thead>
			<tr>
				<th colspan="3">$key</th>
			</tr>
			<tr>
				<th>Attribute</th>
				<th>Count</th>
				<th>Percentage</th>
			</tr>
		</thead>
		<tbody>
HTML;

foreach ($value as $keyInner => $valueInner)
{
	echo <<<HTML
			<tr>
				<td>$keyInner</td>
				<td>{$valueInner['count']}</td>
				<td>{$valueInner['percent']}</td>
			</tr>
HTML;
}

echo <<<HTML
		</tbody>
	</table>
HTML;
}

	echo <<<HTML
</div>
HTML;

echo <<<HTML
<div class="jdsparkle-details-section04">
	<h2>Attributes Reports for All Data</h2>
HTML;

foreach ($reportData['all_data'] as $key => $value)
{
	echo <<<HTML
	<table>
		<thead>
			<tr>
				<th colspan="3">$key</th>
			</tr>
			<tr>
				<th>Value</th>
				<th>Count</th>
				<th>Percentage</th>
			</tr>
		</thead>
		<tbody>
HTML;

	foreach ($value as $keyInner => $valueInner)
	{
		echo <<<HTML
			<tr>
				<td>$keyInner</td>
				<td>{$valueInner['count']}</td>
				<td>{$valueInner['percent']}</td>
			</tr>
HTML;
	}

	echo <<<HTML
		</tbody>
	</table>
HTML;

}

echo <<<HTML
</div>
HTML;

?>
