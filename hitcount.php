// Simple hit counter using sqlite
// Include in the php page which is to be counted, database name is hardcoded for now.
// Request statistics page by issuing a get request to the page.
<?php

if ($db = new SQLite3('hitcount.db'))
    $db->exec('CREATE TABLE IF NOT EXISTS hits (year STRING, month STRING, day STRING, hour STRING, hits STRING)');
else
  die("database open error!");

// request for stats?
if (count($_GET) > 0)
{
  $totalr = $db->query('SELECT sum(hits) FROM hits;');
  if ($totalr)
    $total = $totalr->fetchArray()[0];

  // total - dump total count directly
  if (!is_null($_GET['total']))
  {
    echo $total;
  }
  // any other make a plain stat page
  else
  {
    echo "<html><h1>ØFFE besøgs-tæller</h1><table border=1>";
    if ($total)
    {
      echo "<tr><td><b>Totale besøg</b></td><td></td><td align=right>".$total."</td></tr>";
    }

    echo "<tr><td><b>Dato</b></td><td><b>Time<b></td><td><b>Besøg<b></td></tr>";
    $result = $db->query('SELECT * FROM hits order by year DESC,month DESC,day DESC,hour DESC;');
    while ( $row = $result->fetchArray(SQLITE3_ASSOC) )
    {
      echo "<tr><td>".$row['year']."-".$row['month']."-".$row['day']."</td><td align=right>".$row['hour']."</td><td align=right>".$row['hits']."</td></tr>";
    }
    echo "</table></html>";
  }
  die();
}

// Record a hit for this hour
$date = getdate();
$where = "WHERE "
  . "year='"  . $date['year']  . "' AND "
  . "month='" . $date['mon']   . "' AND "
  . "day='"   . $date['mday']  . "' AND "
  . "hour='"  . $date['hours'] . "';";

$result = $db->query('SELECT * FROM hits '.$where);
if ( $row = $result->fetchArray(SQLITE3_ASSOC) )
{
  $db->exec("UPDATE hits SET hits='".intval($row['hits']+1)."' ".$where);
}
else
{
  $db->exec("INSERT INTO hits (year, month, day, hour, hits) VALUES ('"
    . $date['year']  . "','"
    . $date['mon']   . "','"
    . $date['mday']  . "','"
    . $date['hours'] . "','"
    . intval(1)
    . "');");
}

?>
