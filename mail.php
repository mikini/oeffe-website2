<?php

// get configured email address
require 'oeffemaildest';

$ALLOWED_SIZE = 100; // will die on POST key/values exceeding this
$ALLOW_EMPTY_VALUES=false; // die on empty values?
$EXCLUDE_KEYS=["type"]; // Keys to not echo into mail
$VALIDATE_EMAIL_KEYS = [ "email", "kvitteringsemail" ]; // die if key is present and doesn't contain valid email addresses

// catch obvious fakers
if (empty($_POST))
   die ("Brug formularen på <a href='/'>hjemmesiden</a>, tak.\n");

// Secure key/values from post input
$securepost = array();
$maxkeysize = 0;
foreach($_POST as $key => $value)
{
  $secure = array();
  foreach(array($key,$value) as $input)
    $secure[] = htmlspecialchars(stripslashes(trim($input)));

  if (!$ALLOW_EMPTY_VALUES && strlen($secure[1]) == 0)
    die ("Der mangler lidt information i feltet \"" . $secure[0] . "\", prøv venligst igen.\n");
  if (strlen($secure[1]) > $ALLOWED_SIZE || strlen($secure[0]) > $ALLOWED_SIZE)
      die ("Den tilladte grænse for navn/indhold af feltet \"" . substr($secure[0],0,100) . "\" er overskredet, prøv venligst igen.\n");

  $securepost[$secure[0]] = $secure[1];

  foreach($VALIDATE_EMAIL_KEYS as $mailkey)
      if ($secure[0] == $mailkey)
          if(!filter_var($secure[1],FILTER_VALIDATE_EMAIL))
            die("Indholdet i feltet \"" . $secure[0] . "\" (". $secure[1] .") er ikke en gyldig email-adresse. Prøv venligst igen.\n");
  
  // save length of longest key
  if (strlen($secure[0]) > $maxkeysize)
    $maxkeysize=strlen($secure[0]);
}

// format a textual representation of input
$data='';
foreach($securepost as $key => $value)
{
  // Skip unwanted keys
  foreach($EXCLUDE_KEYS as $exkey)
    if($key==$exkey)
      continue 2;
  $data .= $key;
  for ($i=mb_strlen($key); $i<$maxkeysize; $i++)
    $data .= ' ';
  $data .= ' : ' . $value . "\n";
}

// Get some client id
$ip = $_SERVER['REMOTE_ADDR'];
$agent = $_SERVER['HTTP_USER_AGENT'];
$time = date('c', $_SERVER['REQUEST_TIME']);

// indmelding
if ($securepost['type'] == 'indmeld')
{
  // Generate mail for ØFFE staffers
  $staffsubject = '[ØFFE] Ny indmelding: ' . $securepost['fornavn'] . " " . $securepost['efternavn'];
  $staffmsg = <<<EOF
Hej ØFFE-administrator.

Dette er en automatisk genereret email fra indmeldingsformularen på ØFFE's
hjemmeside.

Nedenfor er gengivet de oplysninger der blev indtastet i formularen.

$data

Indmeldingen blev foretaget
På tidspunktet  : $time
Fra IP-adressen : $ip
Med browseren   : $agent

Hilsen
ØFFE's hjemmeside
EOF;

  // Generate message for user
  $useraddress = $securepost['email'];
  $usersubject = '[ØFFE] Velkommen som medlem';
  $usermsg = <<<EOF
Hej {$securepost['fornavn']}.

Tak for din indmelding, du er godt på vej til at blive medlem af ØFFE.

Som beskrevet på hjemmesiden skal du nu sørge for at indbetale kontingent, se hvorledes på http://øffe.dk/#blivmedlem. Vi vil vende tilbage til dig når vi har bekræftet indbetalingen, hvorefter du vil få tilsendt et medlemsnummer så du kan komme i gang med at bestille dejlige lokale fødevarer. Forvent op til en uges ekspeditionstid.

Har du problemer eller spørgsmål, så svar blot på denne email eller spørg evt. i gruppen på Facebook (se http://øffe.dk/#kontakt).

Du har tilmeldt dig med følgende information:

$data

Økologiske hilsner
ØFFE

PS: indmeldingen blev foretaget fra IP-adressen $ip med browseren "$agent".
EOF;

  $htmlheadline = "Indmelding modtaget";
}

// bestilling
else if($securepost['type']=='bestil')
{
  // Generate mail for ØFFE staffers
  $staffsubject = '[ØFFE] Ny bestilling fra medlem nr. ' . $securepost['medlemsnummer'];
  $staffmsg = <<<EOF
Hej ØFFE-administrator.

Dette er en automatisk genereret email fra bestillingsformularen på ØFFE's
hjemmeside.

Nedenfor er gengivet oplysningerne fra bestillingen.

$data

Bestillingen blev foretaget
På tidspunktet  : $time
Fra IP-adressen : $ip
Med browseren   : $agent

Hilsen
ØFFE's hjemmeside
EOF;

  // Generate message for user
  $useraddress = $securepost['kvitteringsemail'];
  $usersubject = '[ØFFE] Kvittering for dine bestilte varer';
  $usermsg = <<<EOF
Hej ØFFE-medlem nr. {$securepost["medlemsnummer"]}.

Tak for din bestilling.

For at effektuere den, og være sikker på at dine varer vil være klar på udleveringsdagen {$securepost['udleveringsdato']}, skal du nu sørge for at betale beløbet på kr. {$securepost['samlet_pris']}.

Dette gøres på en af de måder der er beskrevet på hjemmesiden (se http://www.øffe.dk/#betaling), angiv venligst dit medlemsnummer i overførselsmeddelelsen.

Er betalingen ikke ØFFE i hænde ved bestillingsperiodens lukning ({$securepost['betalingsfrist']}), kan vi desværre ikke garantere at der vil være varer til dig på udleveringsdagen.

Har du problemer eller spørgsmål, så svar blot på denne email eller spørg evt. i gruppen på Facebook (se http://øffe.dk/#kontakt).

Information om din bestilling:

$data

Økologiske hilsner
ØFFE

PS: bestillingen blev foretaget fra IP-adressen $ip med browseren "$agent".
EOF;

  $htmlheadline = "Bestilling modtaget";

}
else
{
    die("Du har vist ramt en ufærdig formular. Prøv noget andet.");
}


// Generate page output
$htmldata = str_replace("\n\n","</p><p>", $usermsg);
$htmldata = str_replace("\n","<br>", $htmldata);

$htmlheader = <<<EOF
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>ØFFE - Økologisk FødevareFællesskab Esbjerg</title>

    <!-- Bootstrap Core CSS -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Theme CSS -->
    <link href="css/freelancer.min.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body id="page-top" class="index">

    <!-- Navigation -->
    <nav id="mainNav" class="navbar navbar-default navbar-fixed-top navbar-custom">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header page-scroll">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span> Menu <i class="fa fa-bars"></i>
                </button>
                <a class="navbar-brand" href="/">ØFFE</a>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container-fluid -->
    </nav>

    <section id="om">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
EOF;

$htmlfooter= <<<EOF
                </div>
            </div>
        </div>
    </section>
    <!-- jQuery -->
    <script src="vendor/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Plugin JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>

    <!-- Contact Form JavaScript -->
    <script src="js/jqBootstrapValidation.js"></script>
    <script src="js/contact_me.js"></script>

    <!-- Theme JavaScript -->
    <script src="js/freelancer.min.js"></script>

</body>

</html>
EOF;

echo <<<EOF
$htmlheader
                    <h2>$htmlheadline</h2>
                    <p>
                    Vi har sendt dig en email med følgende indhold:
                    <p>
                    <hr width="80%">
                    <p>
                    $htmldata
                    </p>
$htmlheader
EOF;

// send mails
//$headers  =  "Content-Type: text/plain; charset=UTF-8\n";
$headers = "From: =?UTF-8?B?" . base64_encode("Økologisk FødevareFællesskab Esbjerg") . "?= <oeffesbjerg@gmail.com>\n"; // doesn't get encoded by mb_send_mail()
mb_language("uni");
if (isset($useraddress))
  mb_send_mail($useraddress, $usersubject, $usermsg, $headers);
foreach($oeffemaildest as $staffaddress)
  mb_send_mail($staffaddress, $staffsubject, $staffmsg, $headers);
