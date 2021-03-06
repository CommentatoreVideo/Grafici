<?php
$file=file_get_contents("https://www.umanetexpo.net/expo2015Server/UECDL/grafici/as_1920/report&3Ct$6.asp");
$elenco=json_decode($file,true);
$periodoInizioM;
$periodoFineM=0;
$periodoInizio=-1;
$periodoFine=-1;
$periodi=dividiPeriodi($elenco["intestazione"]);
$numeroPeriodi=count($periodi);

if(isset($_GET["periodoInizio"]))
  if($_GET["periodoInizio"]=="12_09_2013")
    $periodoInizio=0;
  else {
    $periodoInizioM=$_GET["periodoInizio"];
    if(controllaData($periodoInizioM))
      $periodoInizio=trovaData($periodoInizioM,$periodi);
    else
      $periodoInizio=0;
  }
else $periodoInizio=0;

if(isset($_GET["periodoFine"])) {
  $periodoFineM=$_GET["periodoFine"];
  if($periodoFineM=="oggi")
  $periodoFine=$numeroPeriodi-1; 
  else {
  if(controllaData($periodoFineM))
    $periodoFineM=trovaData($periodoFineM,$periodi);
  if($periodoFineM<$periodoInizio) {
    $periodoFine=$periodoInizio-1;
  }else $periodoFine=$periodoFineM;
}
}else $periodoFine=$numeroPeriodi;

if(isset($_GET["nome"]))
  $nome=$_GET["nome"];
else
  $nome="";
$voti=[];
$indice=0;
for ($i=0; $i < count($elenco["risultati"]); $i++)
  if($elenco["risultati"][$i][0]==$nome||trim($elenco["risultati"][$i][0])==$nome)
    for($j=0; $j<count($elenco["risultati"][$i]); $j++)
      if(($j-2)%3==0) {
        if($indice>=$periodoInizio&&$indice<=$periodoFine) 
          array_push($voti,$elenco["risultati"][$i][$j]);
        $indice++;
      }
$nPeriodiValidi=$periodoFine-$periodoInizio+1;
        
function elencaVoti($v) {
  for ($i=0; $i < count($v); $i++)
    echo $v[$i].",";
}
function controllaData($s) {
  //Data: gg_mm_aaaa
  ////////0123456789
  //Controllo che la lunghezza sia corretta
  if(strlen($s)!=10)
    return false;
  //Controllo che giorno, mese e anno siano numeri
  if(!is_numeric(substr($s,0,2)))
    return false;
  if(!is_numeric(substr($s,3,2)))
    return false;
  if(!is_numeric(substr($s,6,4)))
    return false;
  //Fine dei controlli
  return true;
}
function dividiPeriodi($s) {
  $periodi=explode('&', $s);
  $n=array_filter($periodi,"controllaData");
  return array_values($n);
}
function trovaData($s,$p) {
  for ($i=0; $i < count($p); $i++)
    if($s==$p[$i])
      return $i;
  return count($p);
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js"></script>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
  <title>Grafico</title>
</head>

<body>
  <div class="container-fluid" id="contenitore">
      <canvas id="grafico"></canvas>
  </div>
  <div id="periodi">
    <form action="" method="get">
    <select name="periodoInizio" id="periodoInizio">
      <?php
      foreach($periodi as $p)
        echo "<option value=".$p.">".$p."</option>\n";
      ?>
    </select>
    <select name="periodoFine" id="periodoFine">
    <?php
      foreach($periodi as $p)
        echo "<option value=".$p.">".$p."</option>\n";
    ?>
    <option value="oggi">Oggi</option>
    </select>
    <input type="hidden" name="nome" value="<?php echo $nome?>">
    <input type="submit" value="Invia">
    </form>
  </div>
  <script src="secondarie.js"></script>
  <script>
    let risultati, intestazione;
    let file;
    //Prendo il file con la memoria
    fetch("https://www.umanetexpo.net/expo2015Server/UECDL/grafici/as_1920/report&3Ct$6.asp")
    .then(d => d.json())
    .then(d => {
      file = d;
      finito()
    })
    .catch(e => console.error(e));

    function finito(alunni = []) {
      risultati = file.risultati;
      intestazione = file.intestazione;
      let nPeriodi=<?php echo $nPeriodiValidi?>;
      let date = prendiDate(intestazione,<?php echo $periodoInizio.",".$periodoFine?>);
      let voti = [<?php elencaVoti($voti) ?>];
      let nome = "<?php echo $nome ?>";
      resetCanvas(); //Serve per evitare che i grafici si sovrappongano
      creaGrafico(date, nome,voti);
    }
//Seleziono le date correnti
<?php
    echo "$(\"#periodoInizio\").children().eq(".$periodoInizio.").attr(\"selected\",\"selected\");\n";
    if($periodoFineM=="oggi")
    echo "$(\"#periodoFine\").children().eq($(\"#periodoFine\").children().length-1).attr(\"selected\",\"selected\");\n";
    else echo "$(\"#periodoFine\").children().eq(".$periodoFine.").attr(\"selected\",\"selected\");\n";
    ?>
    $("#periodoInizio").change(()=>{
      //Prendo i due select
      let periodoInizio=document.getElementById("periodoInizio");
      let periodoFine=document.getElementById("periodoFine");
      $("#periodoFine").empty(); //Svuoto il select finale
      let indice=periodoInizio.options.selectedIndex; //Prendo l'indice selezionato
      //Riempio il select finale solo con i voti validi
      for(let i=0; i<periodoInizio.options.length; i++)
      if(i>=indice) {
        let option=document.createElement("option");
        option.value=periodoInizio.options[i].value;
        option.innerText=periodoInizio.options[i].value;
        $("#periodoFine").append(option);
      }
      //Aggiungo la voce ultimo
      let option=document.createElement("option");
        option.value="oggi";
        option.innerText="Oggi";
        $("#periodoFine").append(option);
    })
  </script>
</body>

</html>