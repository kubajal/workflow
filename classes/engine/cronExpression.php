<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include "../_startup.php";
?>

<link rel="stylesheet" href="css\workflow.css" type="text/css">
<script type="text/javascript" src="js/workflow.js"></script>
<script type="text/javascript" src="js/jquery.min.js"></script>



<h2>Format</h2>
A cron expression is a string comprised of 5 or 6 fields separated by white space. Fields can contain any of the allowed values, along with various combinations of the allowed special characters for that field. The fields are as follows:

<table>
<tr><td></td><td>Field Name</td><td>Mandatory</td><td>Allowed Values</td><td>Allowed Special Characters
<tr><td>1)</td><td>Minutes</td><td>YES</td><td>0-59</td><td>, - * /</td></tr>
<tr><td>2)</td><td>Hours</td><td>YES</td><td>0-23</td><td>, - * /</td></tr>
<tr><td>3)</td><td>Day of month</td><td>YES</td><td>1-31</td><td>, - * / L W</td></tr>
<tr><td>4)</td><td>Month</td><td>YES</td><td>1-12</td><td>, - * /</td></tr>
<tr><td>5)</td><td>Day of week</td><td>YES</td><td>1-7</td><td>, - * / L #</td></tr>
<tr><td>6)</td><td>Year</td><td>NO</td><td>empty, 1970-2099</td><td>, - * /</td></tr>
</table>
	
<?php

	if (isset($_POST['cron']))
	{
		$cronExpression=$_POST['cron'];;
		$m=$_POST['m'];
		$h=$_POST['h'];
		$d=$_POST['d'];
		$mon=$_POST['mon'];
		$wk=$_POST['wk'];
		$yr=$_POST['yr'];;
	}
	else 
	{
		$m=$h=$d=0;
		$mon=$wk=$yr="*";
		$cronExpression="";
	}
	$script=__FILE__;
	
	echo "<form name='ajaxform' id='ajaxform' action='cronExpression.php' method='post'>
	Minute:<input type='edit' name='m' value='$m' size='6'></input>
	Hour:<input type='edit' name='h' value='$h' size='6'></input>
	Day:<input type='edit' name='d' value='$d' size='6'></input>
	Month:<input type='edit' name='mon' value='$mon' size='6'></input>
	Day of week:<input type='edit' name='wk' value='$wk' size='6'></input>
	Year:<input type='edit' name='yr' value='$yr' size='6'></input>

	<br/>
	<input type='edit' name='cron' id='cron' value='$cronExpression'></input>
	<br/>
	<input type='submit' /></form>";
	
	if (isset($_POST['cron']))
	{
	
	
	require 'lib/cron/CronExpression.php';
	
	if ($cronExpression=="")
		$cronExpression="$m $h $d $mon $wk $yr";
	
	}	
	try
	{
		$cron = \Cron\CronExpression::factory($cronExpression);
		
		
		for($i=0; $i<7 ; $i++)
		{
			$n=$i+1;
			
			echo '<br />'.$n.' '. $cron->getNextRunDate(null, $i)->format('Y-m-d H:i:s');
		}	
	
		echo '<br />Timer Expression:'.$cron;
	}
	catch(Exception $ex)
	{
		echo '<br />Error:'.$ex->getMessage();
	}
	
?>
So cron expressions can be as simple as this: * * * * * *
<table>
<tr><td>Expression</td><td>Meaning</td></tr>
 <tr><td>0 12 * * *</td><td>Fire at 12pm (noon) every day</td></tr>
 <tr><td>15 10 * * *</td><td>Fire at 10:15am every day</td></tr>
 <tr><td>15 10 * * *</td><td>Fire at 10:15am every day</td></tr>
 <tr><td>15 10 * * * *</td><td>Fire at 10:15am every day</td></tr>
 <tr><td>15 10 * * * 2015</td><td>Fire at 10:15am every day during the year 2015</td></tr>
 <tr><td>* 14 * * *</td><td>Fire every minute starting at 2pm and ending at 2:59pm, every day</td></tr>
 <tr><td>0/5 14 * * *</td><td>Fire every 5 minutes starting at 2pm and ending at 2:55pm, every day</td></tr>
 <tr><td>0/5 14,18 * * *</td><td>Fire every 5 minutes starting at 2pm and ending at 2:55pm, AND fire every 5 minutes starting at 6pm and ending at 6:55pm, every day</td></tr>
 <tr><td>0-5 14 * * *</td><td>Fire every minute starting at 2pm and ending at 2:05pm, every day</td></tr>
 <tr><td>10,44 14 * 3 3	</td><td>Fire at 2:10pm and at 2:44pm every Wednesday in the month of March.</td></tr>
 <tr><td>15 10 * * 1-5</td><td>Fire at 10:15am every Monday, Tuesday, Wednesday, Thursday and Friday</td></tr>
 <tr><td>15 10 15 * *</td><td>Fire at 10:15am on the 15th day of every month</td></tr>
 <tr><td>15 10 L * *</td><td>Fire at 10:15am on the last day of every month</td></tr>
 <tr><td>15 10 * * 6L</td><td>Fire at 10:15am on the last Friday of every month</td></tr>
 <tr><td>15 10 * * 6L</td><td>Fire at 10:15am on the last Friday of every month</td></tr>
 <tr><td>15 10 * * 6L 2016-2018</td><td>Fire at 10:15am on every last friday of every month during the years 2016, 2017 and 2018</td></tr>
 <tr><td>15 10 * * 6#3</td><td>Fire at 10:15am on the third Friday of every month</td></tr>
 <tr><td>0 12 1/5 * *</td><td>Fire at 12pm (noon) every 5 days every month, starting on the first day of the month.</td></tr>
 <tr><td>11 11 11 11 *</td><td>Fire every November 11th at 11:11am.</td></tr>
</table>	

Special characters
<table>
<tr><td>*</td><td>("all values") - used to select all values within a field. For example, "" in the minute field means *"every minute".</td></tr>

<tr><td>-</td><td>used to specify ranges. For example, "10-12" in the hour field means "the hours 10, 11 and 12".</td></tr>

<tr><td>,</td><td>used to specify additional values. For example, "MON,WED,FRI" in the day-of-week field means "the days Monday, Wednesday, and Friday".</td></tr>

<tr><td>/</td><td>used to specify increments. For example, "0/15" in the seconds field means "the seconds 0, 15, 30, and 45". And "5/15" in the seconds field means "the seconds 5, 20, 35, and 50". You can also specify '/' after the '' character - in this case '' is equivalent to having '0' before the '/'. '1/3' in the day-of-month field means "fire every 3 days starting on the first day of the month".</td></tr>

<tr><td>L </td><td>("last") - has different meaning in each of the two fields in which it is allowed. For example, the value "L" in the day-of-month field means "the last day of the month" - day 31 for January, day 28 for February on non-leap years. If used in the day-of-week field by itself, it simply means "7" or "SAT". But if used in the day-of-week field after another value, it means "the last xxx day of the month" - for example "6L" means "the last friday of the month". When using the 'L' option, it is important not to specify lists, or ranges of values, as you'll get confusing results.</td></tr>

<tr><td>W</td><td>("weekday") - used to specify the weekday (Monday-Friday) nearest the given day. As an example, if you were to specify "15W" as the value for the day-of-month field, the meaning is: "the nearest weekday to the 15th of the month". So if the 15th is a Saturday, the trigger will fire on Friday the 14th. If the 15th is a Sunday, the trigger will fire on Monday the 16th. If the 15th is a Tuesday, then it will fire on Tuesday the 15th. However if you specify "1W" as the value for day-of-month, and the 1st is a Saturday, the trigger will fire on Monday the 3rd, as it will not 'jump' over the boundary of a month's days. The 'W' character can only be specified when the day-of-month is a single day, not a range or list of days.</td></tr>

<tr><td>The 'L' and 'W' </td><td>characters can also be combined in the day-of-month field to yield 'LW', which translates to *"last weekday of the month"*.
<tr><td># </td><td>used to specify "the nth" XXX day of the month. For example, the value of "6#3" in the day-of-week field means "the third Friday of the month" (day 6 = Friday and "#3" = the 3rd one in the month). Other examples: "2#1" = the first Monday of the month and "4#5" = the fifth Wednesday of the month. Note that if you specify "#5" and there is not 5 of the given day-of-week in the month, then no firing will occur that month.
The legal characters and the names of months and days of the week are not case sensitive. MON is the same as mon.		</td></tr>
</table>


	

