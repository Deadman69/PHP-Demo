<?php
$referer = "http://challenge01.root-me.org/programmation/ch1/";
$opts = array(
       'http'=>array(
           'header'=>array("Referer: $referer\r\nCookie: PHPSESSID=0c4475d79adc59cc4ec414646bfd8c76\r\n")
       )
);
$context = stream_context_create($opts);


$toRemove = Array("<html><body><link rel='stylesheet' property='stylesheet' id='s' type='text/css' href='/template/s.css' media='all' /><iframe id='iframe' src='https://www.root-me.org/?page=externe_header'></iframe>U<sub>n+1</sub> = ", "<br /><br />You have only 2 seconds to send the result with the HTTP GET method (at http://challenge01.root-me.org/programmation/ch1/ep1_v.php?result=...)</body></html>", "<br />", 	"</sub>", "<sub>");

$html = file_get_contents("http://challenge01.root-me.org/programmation/ch1/", false, $context);
$html = str_replace ($toRemove, "", $html);
echo htmlentities($html);

$arrayLeft = explode (" ", $html);

$formule = "($arrayLeft[1] + Un) + (n * $arrayLeft[9])";
echo "<br><br><br>Formule : ".$formule;
$Uzero = $arrayLeft[12];
$Uzero = str_replace ("You", "", $Uzero);
$Uzero = "$Uzero";
echo "<br>Uzero : ".$Uzero;
$Ufind = $arrayLeft[15];
$Ufind = str_replace ("U", "", $Ufind);
echo "<br>Ufind : ".$Ufind;

// $Uzero = 50;
// $Ufind = 60;

$x = 0;
$finalnumber = $Uzero;
while($x < $Ufind) {
	$calcul = (intval($arrayLeft[1]) + intval($finalnumber)) + ($x * intval($arrayLeft[9]));
	$finalnumber = $calcul;

	$x = $x + 1;
}
echo "<br>Final number : ".$finalnumber."(x:$x)";


$html = file_get_contents("http://challenge01.root-me.org/programmation/ch1/ep1_v.php?result=$finalnumber", false, $context);
echo "<br><br><br><br>".htmlentities($html);
?>
<!-- 
<script>
	var xmlHttp = new XMLHttpRequest();
    xmlHttp.open("GET", "http://challenge01.root-me.org/programmation/ch1/ep1_v.php?result=<?php echo $finalnumber; ?>", false);
    xmlHttp.send();
    console.log(xmlHttp.responseText);
</script> -->