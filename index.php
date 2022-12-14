<?php

header("Access-Control-Allow-Origin: *");

extract($_GET, EXTR_PREFIX_ALL, "p");

$qop = "OR";
$start = 0;
$rows = 10;

$searchresults = "";
$totalsearchresults = "";
$spellingresults = "";
$paginationresult = "";
$similarresults = "";
$baseurl = "http://localhost:8983/solr/briwsolr/query?rows=".$rows."&fl=*%2Cscore&q=";
$spellurl = "http://localhost:8983/solr/briwsolr/spell?q=body_es:";

if (!empty($p_qop)) {
    $qop = $p_qop;
}

if (!empty($p_search)) {
    $p_search = trim($p_search);
    $terms = explode(" ", $p_search);
    $start = !empty($p_start) ?  $start = intval($p_start) : 0;

    // spelling
    $spellsuggestions = [];
    $allok = true;

    foreach ($terms as $term) {
        $url = $spellurl . urlencode($term);
        $spell = file_get_contents($url);
        $spellarray = json_decode($spell, true);
        if (isset($spellarray["spellcheck"]["correctlySpelled"])){
            $correct = $spellarray["spellcheck"]["correctlySpelled"];
            if (!$correct && isset($spellarray["spellcheck"]["suggestions"][1]["suggestion"][0]["word"])) {
                $allok = false;
                $spellsuggestions[] = $spellarray["spellcheck"]["suggestions"][1]["suggestion"][0]["word"];
            } else {
                $spellsuggestions[] = $term;
            }
        }
        
    }

    if (!$allok) {
        $spellsuggestions = array_filter($spellsuggestions);
        if (count($spellsuggestions) > 0) {
            $params = $spellsuggestions[0];

            for ($i = 1; $i < count($spellsuggestions); $i++) {
                $params .= " " . $spellsuggestions[$i];
            }

            $protocol = (!empty($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == "on" || $_SERVER["HTTPS"] == "1")) ? "https://" : "http://";
            $name = $_SERVER["SERVER_NAME"];
            $port = $_SERVER["SERVER_PORT"];
            $correctedurl = "<a href='".$protocol.$name.":".$port."/index.php?search=".$params."&qop=".$qop."'>".$params."</a>";
            $spellingresults = "Quizás quisiste decir: ".$correctedurl;
        }
    }
    
    // search
    $search = "body_es:" . $terms[0];
    for ($i = 1; $i < count($terms); $i++) {
        $search .= " " . $qop . " body_es:" . $terms[$i];
    }

    $url = $baseurl . urlencode($search). "&start=". $start;
    $query = file_get_contents($url);
    $array = json_decode($query, true);
    $numFound = $array["response"]["numFound"];
    $maxScore = $array["response"]["maxScore"];

    if ($numFound == 0) {
        $searchresults = "<div><p>No se encontraron resultados :(</p></div>";
    } else {
        $documents = $array['response']['docs'];
        $totalsearchresults = "<p> Se encontraron ".$numFound." resultados. </p>";

        foreach($documents as $doc){
            $title = "<h5>".$doc["titulo_es"][0]."</h5>";
            $snippet = "<p class='mb-1'>".$doc["snippet"][0]."</p>"; 
            $link = "<a href='".$doc["link"][0]."'> link </a>";
            $score = "<p class='mb-1 mt-1'>Relevancia ponderada: ".$doc["score"]/$maxScore."</p>";
            $searchresults .= "<div class='pb-2 pt-2 border-top'>" . $title . $snippet . $link . $score . "</div>";
        }
    }
    
    // pagination
    $protocol = (!empty($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == "on" || $_SERVER["HTTPS"] == "1")) ? "https://" : "http://";
    $name = $_SERVER["SERVER_NAME"];
    $port = $_SERVER["SERVER_PORT"];
    
    $totalpages = ($numFound % $rows > 0 ) ? intdiv($numFound, $rows) + 1 : intdiv($numFound, $rows);
    if ($totalpages > 0) {
        $paginationresult = "Páginas:&nbsp;<ul style='list-style-type: none; padding-left: 0px;'>";
        $currentpage = intdiv($start, $rows) + 1;

        for ($i = 0; $i < $totalpages; $i++) {
            $page = $i + 1;
            $separator = $page < $totalpages ? "&nbsp;-&nbsp;" : "";
            $target = "";
            if ($page != $currentpage) {
                $target = $protocol.$name.":".$port."/index.php?search=".$p_search."&qop=".$qop."&start=".($i * $rows);
                $target = "<a href='".$target."'>".$page."</a>".$separator;
            } else {
                $target = $page.$separator;
            }
            $paginationresult .= "<li style='display: inline-block;'>".$target."</li>";
        }
        $paginationresult .= "</ul>";
    }

    // expansion
    $protocol = (!empty($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == "on" || $_SERVER["HTTPS"] == "1")) ? "https://" : "http://";
    $name = $_SERVER["SERVER_NAME"];
    $port = $_SERVER["SERVER_PORT"];

    $results = [];
    foreach ($spellsuggestions as $term) {
        $url = "https://api.datamuse.com/words?sp=".$term."&v=es&max=6";
        $query = file_get_contents($url);
        $result = json_decode($query, true);
        if (!empty($result) && count($result)> 0) {
            $results[] = $result;
        }
    }

    $params = [];

    $min = PHP_INT_MAX;
    foreach ($results as $result) $min = min($min, count($result));
    foreach ($results as $result) $params[] = array_slice($result, 0, $min);
    if (count($params) > 0) {
        $similarresults = "Búsquedas similares:<br>";
        for ($i = 0; $i < count($params[0]); $i++) {
            $str = "";
            for ($j = 0; $j < count($params); $j++) {
                $str .= $params[$j][$i]["word"]. "&nbsp;";
            }
            $target = $protocol.$name.":".$port."/index.php?search=".$str."&qop=".$qop."&start=".($i * $rows);
            $similarresults .= "<a href='".$target."'>".$str."</a><br>";
        }
    }


} else {
    
    $searchresults = "<p>Introduzca uno o más términos de consulta</p>";

}

?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="./css/indexStyle.css" /> 
    <link rel="stylesheet" href="css/style.css" />   
    <title>Busqueda y Recuperación</title>
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/sistema.js"></script>
</head>
<html>
    <body>
        <div class="container"><br>
        <form class="mb-2" enctype="multipart/form-data" method="post" action="./updateUrls.php">
            <input type="submit" value="Actualizar URLs" class="btn btn-primary">
        </form>
        <h2>Buscar entre páginas indexadas</h2>
        <form class="input-search row" method="get" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            <div class="row g-2">
                <div class="col-6 input_container">
                    <input autocomplete="off" type="search" class="form-control" id="input-search" name="search" onkeyup="autocompletar()" placeholder="Buscar" value="<?php if (!empty($p_search)) echo $p_search; ?>">
                    <ul id="lista_id"></ul>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-search">Buscar</button>
                </div>
            </div>
            <div class="row g-2">
                <div class="col-auto">
                    <label class="form-label text-center" for="qop">Opciones: </label>
                </div>
                <div class="col-auto">
                    <select class="form-select col-auto" name="qop">
                        <option value="AND" <?php if ($qop === "AND") echo "selected"; ?>>AND</option>
                        <option value="OR" <?php if ($qop === "OR" ) echo "selected"; ?>>OR</option>
                    </select>
                </div>
            </div>
        </form>
        <div class="spell mt-2"><?php
            echo $spellingresults;
        ?></div>
        <div class ="similar-results"><?php
            echo $similarresults;
        ?></div>
        <div class="n-results"><?php 
            echo $totalsearchresults; 
        ?></div>
        <div class="documents mt-3"><?php
            echo $searchresults;
        ?></div>
        <div class="pagination"><?php
            echo $paginationresult;
        ?></div>

        </div>
        <!-- jQuery library -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <!-- jQuery UI library -->
        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/themes/smoothness/jquery-ui.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
    </body>
</html>
