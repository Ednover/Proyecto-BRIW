<?php 
 
// Get search term 
$searchTerm = $_GET['term']; 
echo "<script>console.log(".$searchTerm.")</script>";
echo $searchTerm;

$query = file_get_contents('http://localhost:8983/solr/briwtest/suggest?suggest=true&suggest.build=true&suggest.dictionary=default&wt=json&suggest.q=' . $searchTerm);
$a= json_decode($query, true);
$num = $a['suggest']['default']['p']["numFound"];
echo $num;
// Fetch matched data from the database 

 
// Return results as json encoded array 
//echo json_encode($skillData); 
?>