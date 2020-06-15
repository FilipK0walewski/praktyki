<?php
    session_start();


    if(isset($_POST['ddate']) && isset($_POST['city'])){
        
        date_default_timezone_set("europe/warsaw");
        $_SESSION['OK'] = true;
        $city = $_POST['city'];
        $date = $_POST['ddate'];
        $today = date('Y-m-d');
        $endDay = date('Y-m-d', strtotime( $today .'+5 day'));

        if($date == 0){
            $_SESSION['OK'] = false;
            $_SESSION['dateError'] = "Wybierz datę";
        }
        else if($date < $today){
            $_SESSION['OK'] = false;
            $_SESSION['dateError'] = "Podano wsteczną datę";
        }
        else if($date > $endDay){
            $_SESSION['OK'] = false;
            $_SESSION['dateError'] = "Prognoza pogody na 5 dni";
        }

        

        $ch = curl_init();
        $link = "http://api.openweathermap.org/data/2.5/forecast?q=".$city."&appid=b5b20131b81f375cecb483cd3570870f&units=metric";
        curl_setopt($ch, CURLOPT_URL, $link); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $response= curl_exec($ch); 
        $response = json_decode($response);

        $cod= $response->cod;
        
        if($cod == 404){
            $_SESSION['OK'] = false;
            $_SESSION['cityError'] = "Nie znaleziono miasta";
        }
        else if($cod == 400){
            $_SESSION['OK'] = false;
            $_SESSION['cityError'] = "Nie podano miasta";
        }
        else{
            $h = array();
            $tempMin = array();
            $tempMax = array();
            $humidity = array();
            $pressure = array();
            $icon = array();

            for($i = 0; $i < 40; $i++){
                $date2 = $response->list[$i]->dt_txt;
                $hour = substr($date2, 10, strlen($date2));
                $date2 = substr($date2, 0, 10);

                if($date == $date2){
                    array_push($h, $hour);
                    array_push($tempMin, $response->list[$i]->main->temp_min);
                    array_push($tempMax, $response->list[$i]->main->temp_max);
                    array_push($humidity, $response->list[$i]->main->humidity);
                    array_push($pressure, $response->list[$i]->main->pressure);
                    array_push($icon, $response->list[$i]->weather[0]->icon);
                }
                
            }

            
        }
        $_SESSION['c'] = $city;
        $_SESSION['d'] = $date;
        

        curl_close($ch);
    

    }

?>


<!DOCTYPE HTML>
<html lang = "pl">
<head>
    <meta charset = "utf-8"/>
    <meta http-equiv = "X-UA-Compatible" content = "IE = edge, chlrome = 1"/>

    <link rel="style.css" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/purecss@2.0.3/build/pure-min.css" integrity="sha384-cg6SkqEOCV1NbJoCu11+bm0NvBRc8IYLRGXkmNrqUBfTjmMYwNKPWBTIKyw9mHNJ" crossorigin="anonymous">
    <link rel="stylesheet" href="dist/flatpickr.css">
    <link rel="stylesheet" href="dist/ie.css">
    <link rel="stylesheet" href="dist/plugins/confirmDate/confirmDate.css">
    <link rel="stylesheet" href="dist/plugins/monthSelect/style.css">

    <title>Check weather on flatearth</title>
</head>
<body>

    <div id = "container">
        <form method = "post">
            
            Data: <br /> <input placeholder = "yyyy-mm-dd" value="<?php
                if (isset($_SESSION['d']))
                {
                    echo $_SESSION['d'];
                    unset($_SESSION['d']);
                }
            ?>" class = "date" name = "ddate"/> <br />

            <?php
                if (isset($_SESSION['dateError']))
                {
                    echo '<div class="error">'.$_SESSION['dateError'].'</div>';
                    unset($_SESSION['dateError']);
                }
            ?>

            Miasto: <br /> <input type="text" value="<?php
                if (isset($_SESSION['c']))
                {
                    echo $_SESSION['c'];
                    unset($_SESSION['c']);
                }
            ?>" name="city" /> <br />

            <?php
                if (isset($_SESSION['cityError']))
                {
                    echo '<div class="error">'.$_SESSION['cityError'].'</div>';
                    unset($_SESSION['cityError']);
                }
            ?>

            <input type="submit" value="Sprawdź" />

            <?php
                if(isset($_SESSION['OK']) && $_SESSION['OK'] == true) {

                    
                    $n = count($h);

                    for($i = 0; $i < $n; $i++){

                        $iconURL = "http://openweathermap.org/img/wn/".$icon[$i]."@2x.png";
                        ?>
                            <div id = "block">
                                Godzina:<?php echo $h[$i]?><br />
                                Temperatura minimalna: <?php echo $tempMin[$i]?><br />
                                Temperatura maksymalna: <?php echo $tempMax[$i]?><br />
                                Wilgotność: <?php echo $humidity[$i]?><br />
                                Ciśnienie: <?php echo $pressure[$i]?><br />
                                <img src = <?php echo $iconURL ?>>

                                <br /><br />
                            </div>

                        <?php

                        
                    }
                }
            ?>

        </form>
    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/classlist/1.2.20171210/classList.min.js"></script>
    <script src="./dist/flatpickr.js"></script>
    <script src="./dist/plugins/rangePlugin.js"></script>
    <script src="./dist/plugins/confirmDate/confirmDate.js"></script>
    <script src="./dist/plugins/minMaxTimePlugin.js"></script>
    <script src="./dist/plugins/monthSelect/index.js"></script>
    <script src="./dist/plugins/scrollPlugin.js"></script>
    <script src="./dist/plugins/weekSelect/weekSelect.js"></script>

  <script>
    var fp = flatpickr(".date", {

    })
  </script>

</body>

</html>