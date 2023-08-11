<?php
    session_start();

    $FLAG = true;
    /** @var int $MAX Lunghezza massima del codice del codificatore*/
    $MAX = 4;

    /** @var int $MAX_TENTATIVI Numero massimo di tentativi per indovinare il codice del codificatore*/
    $MAX_TENTATIVI = 9;
    /** @var int $quanti nuemri di tentativi rimasi all'utente */
    $_SESSION['quanti'] = $_SESSION['quanti'] ?? $MAX_TENTATIVI;

    /** @var string $tentativi stringa con i vari tentativi convertiti in numero e separati da carattere speciale / */
    $_SESSION["colori"] = $_SESSION['colori'] ?? "";

    /** @var string $pioli  Concatenzazione dei pioli se non esiste viene settata a stringa vuota*/
    $_SESSION["pioli"] = $_SESSION['pioli'] ?? "";

    /** 
     * Colori possibili da inserire nella combinazione 
     * @var array $COLORI Ogni colore ha un valore attribuito alla sua posizione 
     * 0 = ROSSO
     * 1 = GIALLO
     * 2 = VERDE
     * 3 = BLU
     * 4 = AZZURRO
     * 5 = ARANCIONE
    */
    $COLORI = ["ROSSO", "GIALLO", "VERDE", "BLU", "AZZURRO", "ARANCIONE"];
    $LEN_COLORI = count($COLORI);
    /** 
     * Codice HEX dei colori presenti nell'array $COLORI
     * @var array $COD_COLORI codice HEX dei colori
     * ROSSO = FF0000
     * GIALLO = FFFF00
     * VERDE = 008F39
     * BLU = 0000FF
     * AZZURRO = 007FFF
     * ARANCIONE = FFA500
    */
    $COD_COLORI = ["FF0000", "FFFF00", "008F39", "0000FF", "007FFF", "FFA500"];

    /** @var array $ETICHETTE Stringhe che indicano nome dei valori inviati MAX = 4*/
    $ETICHETTE = ["UNO", "DUE", "TRE", "QUATTRO"];
    $LEN_ETICHETTE = count($ETICHETTE);

    /** @var string $codificatore Contiene il codice segreto generato dal codificatore*/
    $_SESSION['codificatore'] = $_SESSION['codificatore'] ?? "";

    if(!$_SESSION['codificatore'])
        for($i = 0; $i < $MAX; $i++) $_SESSION['codificatore'] .= rand(0, $LEN_COLORI-1);

    /**
     * Summary of stringaToNumero
     * @param string $stringa Stringa in colori da convertire
     * @param string $separatore Separatore stringa per creare un array
     * @param array $COLORI Array di colori default 
     * @return string
     */
    function stringaToNumero($stringa, $separatore, $COLORI){
        $str_copy = explode($separatore, $stringa);
        $str = "";

        foreach ($str_copy as $key => $value) $str .= array_search($value, $COLORI);
        
        return $str;
    }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MasterMind</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <h1>MASTERMIND</h1>
    <?php
        if(isset($_POST["UNO"]) && isset($_POST["DUE"]) && isset($_POST["TRE"]) && isset($_POST["QUATTRO"])){
            $decodificatore = $_POST["UNO"]." ".$_POST["DUE"]." ".$_POST["TRE"]." ".$_POST["QUATTRO"];
            $conv_decodificatore = stringaToNumero($decodificatore, " ", $COLORI);

            $_SESSION["colori"] .= $conv_decodificatore."/";
        
            if ($_SESSION['codificatore'] === $conv_decodificatore) {
                echo "<h1 style=\"text-align: center; color: green\">HAI VINTO!</h1>";
                $FLAG = false;
                session_destroy();
            }


            $_SESSION['quanti']--;
            
            $copia_codificatore = $_SESSION['codificatore'];

            $pos_giusta = 0;
            $pos_sbagliata = 0;

            $codificatoreCounts = array_count_values(str_split($copia_codificatore));
            $decodificatoreCounts = array_count_values(str_split($conv_decodificatore));

            // Conta i pioli neri
            for ($i = 0; $i < strlen($copia_codificatore); $i++) {
                if ($copia_codificatore[$i] === $conv_decodificatore[$i]) {
                    $pos_giusta++;
                    $codificatoreCounts[$copia_codificatore[$i]]--;
                    $decodificatoreCounts[$conv_decodificatore[$i]]--;
                }
            }

            // Conta i pioli bianchi
            foreach ($decodificatoreCounts as $letter => $count) {
                if (array_key_exists($letter, $codificatoreCounts)) {
                    $pos_sbagliata += min($count, $codificatoreCounts[$letter]);
                }
            }

            $_SESSION["pioli"] .= $pos_giusta.$pos_sbagliata."/";
        }else echo "DEVI SCEGLIERE 4 COLORI [OBBLIGATORI]";
    ?>
    <?php
        if ($_SESSION['quanti']) {
    ?>
        <form action="index.php" method="post">
            <div class="container">
                <?php
                for ($i = 0; $i < $LEN_ETICHETTE; $i++) {
                    ?>
                    <div class="container_scelte">
                    <?php
                        foreach ($COLORI as $key => $value) {
                            ?> <input type="radio" name="<?php echo $ETICHETTE[$i]; ?>" value="<?php echo $value; ?>"/>
                            <span for="<?php echo $ETICHETTE[$i]; ?>" class="colore" style="background-color: #<?php echo $COD_COLORI[$key]; ?>;"></span><br><?php
                        }
                    ?></div><?php
                }
                ?>
            </div>
            <input type="submit" value="invio">
        </form>

        <table>
            <tr>
                <th>1° COLORE</th>
                <th>2° COLORE</th>
                <th>3° COLORE</th>
                <th>4° COLORE</th>
                <th>PIOLI</th>
            </tr>
            <?php
            $esp_tentativi = strlen($_SESSION["colori"]) ? explode("/", substr($_SESSION["colori"], 0, strlen($_SESSION["colori"]) - 1)) : $_SESSION["colori"];
            $esp_pioli = strlen($_SESSION['pioli']) ? explode("/", substr($_SESSION['pioli'], 0, strlen($_SESSION['pioli']) - 1)) : $_SESSION['pioli'];

            if ($_SESSION["colori"] != "") {
                foreach ($esp_tentativi as $key => $value) {
                    ?>
                        <tr>
                            <?php
                            for ($i = 0; $i < 4; $i++) {
                                $hex = $COD_COLORI[$value[$i]];
                                $colore = $COLORI[$value[$i]];
                                ?>
                                        <td style="padding: 10px;">
                                            <div class="cerchio" style="border: 1px solid white;background-color: #<?php echo $hex; ?>;box-shadow: 0px 0px 15px #<?php echo $hex; ?>"></div>
                                        </td>
                                    <?php

                            }
                            ?>
                            <td><?php echo substr($esp_pioli[$key], 0, 1) . "-" . substr($esp_pioli[$key], 1, 1); ?></td>
                        </tr>
                        <?php
                }
            }
            ?>
        </table>
    <?php 
        }elseif($FLAG){
            ?>
                <h1>
                    HAI PERSO IL CODICE ESATTO ERA 
                    <?php
                        for($i = 0; $i < 4; $i++){
                            echo "<span style='color:#".$COD_COLORI[$_SESSION['codificatore'][$i]]."'> - ".$COLORI[$_SESSION['codificatore'][$i]]." - </span>";
                        }
                    ?>
                </h1>
            <?php
        }
    ?>
</body>
</html>