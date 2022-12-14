<?php

header("Access-Control-Allow-Origin: *");

extract($_GET, EXTR_PREFIX_ALL, "p");

$output = "";
$spelling = "";
#$baseurl = 'http://localhost:8983/briwtest/nutch/query?q=';
$baseurl = 'http://localhost:8983/solr/briwtest/query?rows=100&fl=*%2Cscore&q=';
$spellurl = "http://localhost:8983/solr/briwtest/spell?q=body_es:";
$qop = "OR";

if (!empty($p_qop)) {
    $qop = $p_qop;
}

if (!empty($p_search)) {
    $p_search = trim($p_search);
    $terms = explode(" ", $p_search);

    foreach ($terms as $term) {
        $url = $spellurl . urlencode($term);
        $spell = file_get_contents($url);
        $spellarray = json_decode($spell, true);
        
        $correct = $spellarray["spellcheck"]["correctlySpelled"];
        if (!$correct) {
            $spellsuggestions[] = $spellarray["spellcheck"]["suggestions"][1]["suggestion"][0]["word"];
        } else {
            $spellsuggestions[] = $term;
        }
    }

    $spellsuggestions = array_filter($spellsuggestions);
    if (count($spellsuggestions) > 0) {
        $spelling = "Quizás quisiste decir: ";
        $params = $spellsuggestions[0];

        for ($i = 1; $i < count($spellsuggestions); $i++) {
            $params .= " " . $spellsuggestions[$i];
        }

        $spelling .= $params;
        $spelling = "<a href=\"http://localhost:3000/index.php?search=" . $params . "&qop=" . $qop . 
                    "\">" . $spelling . "</a>";
    }
    // spell

    $search = "body_es:" . $terms[0];
    for ($i = 1; $i < count($terms); $i++) {
        $search .= " " . $qop . " body_es:" . $terms[$i];
    }

    $url = $baseurl . urlencode($search);
    $query = file_get_contents($url);
    $prueba = file_get_contents('http://localhost:8983/solr/briwtest/suggest?suggest=true&suggest.build=true&suggest.dictionary=default&wt=json&suggest.q=p');
    $a= json_decode($prueba, true);
    $num = $a['suggest']['default']['p']["numFound"];
    echo $num;
    $array = json_decode($query, true);
    $numFound = $array["response"]["numFound"];
    $maxScore = $array["response"]["maxScore"];

    if ($numFound == 0) {
        $output = "<div><p>No se encontraron resultados :(</p></div>";
    } else {
        $documents = $array['response']['docs'];

        foreach($documents as $doc){
            $title = "<h5>".$doc["titulo_es"][0]."</h5>";
            $snippet = "<p class='mb-1'>".$doc["snippet"][0]."</p>"; 
            $link = "<a href='".$doc["link"][0]."'> link </a>";
            $score = "<p class='mb-1 mt-1'>Relevancia ponderada: ".$doc["score"]/$maxScore."</p>";
            $output .= "<div class='pb-2 pt-2 border-top'>" . $title . $snippet . $link . $score . "</div>";
        }
    }

} else {
    
    $output = "<p>Introduzca uno o más términos de consulta</p>";

}

?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="./css/indexStyle.css" />
    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- jQuery UI library -->
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
    <title>Busqueda y Recuperación</title>
</head>
<html>
    <body>
        <div class="container"><br>
        <form class="mb-2" enctype="multipart/form-data" method="post" action="./updateUrls.php">
            <input type="submit" value="Actualizar URLs" class="btn btn-primary">
        </form>
        <h2>Buscar entre páginas indexadas</h2>
        <form class="input-search row" method="get" action="<?php echo $_SERVER['PHP_SELF'] ?>" autocomplete="off">
            <div class="row g-2">
                <div class="col-6">
                    <input type="search" class="form-control" id="input-search" name="search" placeholder="<?php if (!empty($p_search)) echo $p_search; else echo "Buscar"; ?>">
                    <div id="result"></div>
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
            echo $spelling;
        ?></div>
        <div class="documents mt-3"><?php
            echo $output;
        ?></div>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script>
        $(function() {
            $("#input-search").autocomplete({
                source: "fetchData.php",
            });
        });
        </script>
        <script>
            function showResults(val) {
            res = document.getElementById("result");
            res.innerHTML = '';
            let list = '';
            let terms = autocompleteMatch(val);
            for (i=0; i<terms.length; i++) {
                list += '<li>' + terms[i] + '</li>';
            }
            res.innerHTML = '<ul>' + list + '</ul>';
            }
            var search_terms = ['apple', 'apple watch', 'apple macbook', 'apple macbook pro', 'iphone', 'iphone 12'];

            async function autocompleteMatch(input) {
            if (input == '') {
                return [];
            }
            var reg = new RegExp(input);
            try {
            const queryurl = 'http://localhost:8983/solr/briwtest/suggest?suggest=true&suggest.build=true&suggest.dictionary=default&wt=json&suggest.q=' + input;
            let data = await fetch(queryurl, {
                mode: 'no-cors',
                headers: {
                'Access-Control-Allow-Origin' : '*'
                },
            });
            console.log(data);
            const result = data.json();
            } catch (err) {
                console.log(err);
            }
            var arraySuggestions = [];
            for(i = 0; i > result['suggest']['default']['p']['suggestions'].length; i++){
                arraySuggestions.push(result['suggest']['default']['p']['suggestions'][i]['term']);
            }
            console.log(arraySuggestions);
            return arraySuggestions.filter(function(term) {
                if (term.match(reg)) {
                return term;
                }
            });
            }
        </script>
    </body>
</html-->