<?php
$searchTerm = $_POST['palabra'];
$url = 'http://localhost:8983/solr/briwtest/suggest?suggest=true&suggest.build=true&suggest.dictionary=default&wt=json&suggest.q=' . $searchTerm;
$query = file_get_contents($url);
$responseSug = json_decode($query, true);
$suggestions = $num = $responseSug['suggest']['default'][$searchTerm]["suggestions"];

foreach($suggestions as $key => $value){
	echo $value['term']."<br>";
}
?>