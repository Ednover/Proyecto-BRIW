<?php
$searchTerm = $_POST['palabra'];
$searchTerm = str_replace(" ", '+', $searchTerm);
$array = explode("+", $searchTerm);
$url = 'http://localhost:8983/solr/briwsolr/suggest?suggest=true&suggest.build=true&suggest.dictionary=default&wt=json&suggest.q=' . $array[0];
$query = file_get_contents($url);
$responseSug = json_decode($query, true);
$suggestions = $num = $responseSug['suggest']['default'][$array[0]]["suggestions"];

foreach($suggestions as $key => $value){
	echo $value['term']."<br>";
}
?>