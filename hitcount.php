<?php
// Simple hit counter using sqlite
// Include in the php page which is to be counted, database name is hardcoded for now.
// Request statistics page by issuing a get request to the page.

$count=true;
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
    die();
  }
  else if (!is_null($_GET['nocount']))
     $count=false;
  // any other make a plain stat page
  else
  {
    echo "<html><h1>ØFFE besøgs-tæller</h1>";

    echo "<ul>";
    if ($total)
    {
      echo "<li>Totale besøg: ".$total."</li>";
    }
    // Get lowest year, month, day and display
    echo "</ul>";

    echo "<h2>Besøg pr. døgn</h2>";
    echo "<table border=1><th>Døgn tilbage</th><th>Besøg</th></tr>";
    $time=time();
    foreach(array(0,1,2,3,4,5,6,7,8,9,10,11,12,13) as $daysback)
    {
      $date = getdate($time-60*60*24*$daysback);
      $where = "WHERE "
        . "year='"  . $date['year']  . "' AND "
        . "month='" . $date['mon']   . "' AND "
        . "day='"   . $date['mday']  . "'";
      $datecountr = $db->query('SELECT sum(hits) FROM hits '.$where.';');
      if ($datecountr)
      {
        $datecount = $datecountr->fetchArray()[0];
        echo "<tr><td>".$daysback."</td><td align=right>".$datecount."</td></tr>";
      }
    }
    echo "</table>";

    echo "<h2>Akkumuleret time-statistik</h2>";
    echo "<table border=1>";
    echo "<tr><th>Time</th><th>Besøg</th><th>Relativt</th></tr>";
    foreach(array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23) as $hour)
    {
      $result = $db->query("SELECT hour, sum(hits) FROM hits WHERE hour=$hour;");
      while ( $row = $result->fetchArray(SQLITE3_BOTH) )
      {
          echo "<tr><td>".$hour."</td><td align=right>".$row[1]."</td><td align=right>".intval(($row[1]/$total)*100)." %</td></tr>";
      }
    }
    echo "</table>";

    echo "<h2>Besøg pr. time</h2>";
    echo "<table border=1>";
    echo "<tr><th>Dato</th><th>Time</th><th>Besøg</th></tr>";
    $result = $db->query('SELECT * FROM hits order by year DESC,month DESC,day DESC,hour DESC;');
    while ( $row = $result->fetchArray(SQLITE3_ASSOC) )
    {
      echo "<tr><td>".$row['year']."-".$row['month']."-".$row['day']."</td><td align=right>".$row['hour']."</td><td align=right>".$row['hits']."</td></tr>";
    }
    echo "</table>";

    echo "</html>";
    die();
  }
}

if ($count)
{

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
}
?>
