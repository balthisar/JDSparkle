<h1>Sparkle System Profiling Overview</h1>
<h2>Previous 60 days’ data</h2>
<table>
	<thead>
		<tr>
			<th>Application</th>
			<th>Quantity of Reports</th>
			<th>Distinct IPs</th>
			<th>First Report Date</th>
			<th>Last Report Date</th>
		</tr>
	</thead>
	<tbody>
<?php
	foreach ($reportData as $item)
	{
	$slug = $item['JDSparkleReport']['app_name'] . ($secretpassword ? "/" . $secretpassword : "");

	$link = $this->Html->link($item['JDSparkleReport']['app_name'],
						array('controller'=>'JDSparkleReports', 'action' => 'details', 'slug' => $slug)
);
	echo <<< HTML
		<tr>
			<td>$link</td>
			<td>{$item['JDSparkleReport']['report_count']}</td>
			<td>{$item['JDSparkleReport']['distinct_ip_count']}</td>
			<td>{$item['JDSparkleReport']['date_first']}</td>
			<td>{$item['JDSparkleReport']['date_last']}</td>
		</tr>
HTML;
	}
?>
	</tbody>
</table>

<h2>All Data</h2>
<table>
	<thead>
	<tr>
		<th>Application</th>
		<th>Quantity of Reports</th>
		<th>Distinct IPs</th>
		<th>First Report Date</th>
		<th>Last Report Date</th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($reportDataAll as $item)
	{
		$slug = $item['JDSparkleReport']['app_name'] . ($secretpassword ? "/" . $secretpassword : "");

		$link = $this->Html->link($item['JDSparkleReport']['app_name'],
			array('controller'=>'JDSparkleReports', 'action' => 'details', 'slug' => $slug)
		);
		echo <<< HTML
		<tr>
			<td>$link</td>
			<td>{$item['JDSparkleReport']['report_count']}</td>
			<td>{$item['JDSparkleReport']['distinct_ip_count']}</td>
			<td>{$item['JDSparkleReport']['date_first']}</td>
			<td>{$item['JDSparkleReport']['date_last']}</td>
		</tr>
HTML;
	}
	?>
	</tbody>
</table>
