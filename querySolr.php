<?php 

$search = $_POST['search'];

$query = file_get_contents('http://localhost:8983/solr/briwtest/spell?q=' . $search);

echo $query;

?>