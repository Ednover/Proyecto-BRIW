<?php 

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
?>