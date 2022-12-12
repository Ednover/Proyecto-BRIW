<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <title>Document</title>
</head>
<html>
    <body>
        <div class="container"><br>
        <form enctype="multipart/form-data" method="post" action="./updateUrls.php">
            <input type="submit" value="Actualizar URLs">
        </form>
        <h2>Buscar entre páginas indexadas</h2>
        <form class="input-search" method="post" action="./querySolr.php">
            <input type="search" id="input-article" name="search" placeholder="Buscar">
            <button type="submit" class="btn-search">Buscar</button>
        </form>
        <div class="documents"></div>
        </div>
    </body>
</html>


<?php
/* if (isset($_POST['SubmitFile'])){
    $search = $_POST['search'];

    $query = file_get_contents('http://localhost:8983/solr/briwtest/spell?q=' . $search);

    $array = json_decode($query, true);

    $results = $array['response']['docs'];

    foreach($results as $key => $doc){
        echo '<br>';
        echo $doc['title'][0];
        echo '<br>';
        echo $doc['description'][0];
        echo '<br>';
        echo $doc['url'][0];
    }

    echo "<script>alert('Se actualizaron las URLs')</script>";
} */
?>