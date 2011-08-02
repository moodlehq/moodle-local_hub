<?
/*
Start of script.
First checks to see if there is already a SQL Table or if it needs to make one!
*/
require_once("../config.php");
$systemcontext = get_context_instance(CONTEXT_SYSTEM);
if (!has_capability('moodle/site:doanything', $systemcontext)) {
  echo "You do not have administration privileges on this Moodle site. These are required for running this script.{$settings['eolchar']}";
  die();
}



if (empty($notable))
{
echo "<html>
<header>
<title>IP-To-Country Automation</title>
</header>
<body bgcolor='black'>
<font color='white'>
<center>
<h3>IP-To-Country Automation</h3>
<br><br>
Do you already have a table made or do you need one made for you?
<br>
<form name='form' method='post'>
<input type=hidden name='notable' value='yes'>
<input type='submit' name='Submit' value='No I Dont Have a Table'></form>
<form name='form' method='post'>
<input type=hidden name='notable' value='no'>
<input type='submit' name='Submit' value='Yes I Already Have a Table'></form>
</body></html>";
}


/*
If they selected that they already had a table the following will be displayed.
*/


if ($notable == "no")
{
echo"<html>
<header>
<title>Fill Out Information</title>
</header>
<body bgcolor='black'>
<font color='white'>
<center>
<h3>SQL Information</h3>
<br><br>
<form name='form' method='post'>

<p><br>SQL Host Name (Usually localhost)<br>
<input type='text' name='dhostn'></p>

<p><br>SQL Username<br>
<input type='text' name='dusername'></p>

<p><br>SQL Password<br>
<input type='text' name='dpassword'></p>

<p><br>SQL Database Name<br>
<input type='text' name='ddb'></p>

<p><br>SQL Table Name<br>
<input type='text' name='dtable'></p>

<p><br>IP From  Field Name<br>
<input type='text' name='dfrom'></p>

<p><br>IP To Field Name<br>
<input type='text' name='dto'></p>

<p><br>Code 2 Field Name<br>
<input type='text' name='dco2'></p>

<p><br>Code 3 Field Name<br>
<input type='text' name='dco3'></p>

<p><br>Country Field Name<br>
<input type='text' name='dco'></p>

<input type=hidden name='notable' value='void'>
<input type=hidden name='complete' value='yes'>
<input type=hidden name='istable' value='yes'>
<input type='submit' name='Submit' value='Fill The Table'>

</center></font></body></html>";
}


/*
If they selected that they didn't have a table then the following is displayed
*/


if ($notable == "yes")
{
echo"<html>
<header>
<title>Fill Out Information</title>
</header>
<body bgcolor='black'>
<font color='white'>
<center>
<h3>SQL Information</h3>
<br><br>
<form name='form' method='post'>

<p><br>SQL Host Name (Usually localhost)<br>
<input type='text' name='dhostn'></p>

<p><br>SQL Username<br>
<input type='text' name='dusername'></p>

<p><br>SQL Password<br>
<input type='text' name='dpassword'></p>

<p><br>SQL Database Name<br>
<input type='text' name='ddb'></p>

<br>
The table will be made for you.<br>

<input type=hidden name='notable' value='void'>
<input type=hidden name='complete' value='yes'>
<input type=hidden name='istable' value='no'>
<input type='submit' name='Submit' value='Make and Fill The Table'>

</body></html>";
}


/*
The following will used to fill the table or if there isn't one make the table then fill it.
*/


if ($complete == "yes")
{

/*
The following will make the table if there isn't one.
*/

if ($istable == "no")
{
MySQL_connect("$dhostn","$dusername","$dpassword") or die("Could not connect to SQL");
MySQL_select_db("$ddb") or die("Could not select database");

$sql = "CREATE TABLE iptoc (
ipFrom double NOT NULL, 
ipTo double NOT NULL, 
code2 char(2) NOT NULL, 
code3 char(3) NOT NULL, 
country varchar(50) 
);";

$result = mysql_query($sql) or die("Invalid query: " . mysql_error().__LINE__.__FILE__);
if ($result) {
$table_s = "Table named iptoc was created!";
} else {
$table_s = "Table named iptoc was unable to be created.";
} 
MySQL_close();

$dtable = 'iptoc';
$dfrom = 'ipFrom';
$dto = 'ipTo';
$dco2 = 'code2';
$dco3 = 'code3';
$dco = 'country';
}

/*
Now we fill the table.
*/

?>
<?
MySQL_connect("$dhostn","$dusername","$dpassword");
MySQL_select_db("$ddb") or die("Could not select database");


$row = 0;
$handle = fopen ("ip-to-country.csv","r");

while ($data = fgetcsv ($handle, 1000, ",")) {
    $query = "INSERT INTO $dtable(`$dfrom`, `$dto`, `$dco2`, `$dco3`, `$dco`) VALUES('".$data[0]."', '".$data[1]."', '".$data[2]."', '".$data[3]."', '".addslashes($data[4])."')";
    $result = mysql_query($query) or die("Invalid query: " . mysql_error().__LINE__.__FILE__);
    $row++;
}

if ($result)
{
echo "<html><head><title>Success</title></head>
<body bgcolor='grey'><font color='black'>
<center>
<h3>Success</h3>
<br><br>";
if (!empty($table_s))
{
echo "$table_s <br>";
}
echo "$row rows were added to $dtable.
</center></font>
</body></html>";
}

fclose ($handle);
MySQL_close();

}

?>

