<?php
// Utilizaremos conexion PDO PHP
$searchTerm = $_POST['palabra'];
echo "<script>console.log(".$searchTerm.")</script>";
//echo $searchTerm."<br>";
$url = 'http://localhost:8983/solr/briwtest/suggest?suggest=true&suggest.build=true&suggest.dictionary=default&wt=json&suggest.q=' . $searchTerm;
$query = file_get_contents($url);
//echo $url."<br>";
$a= json_decode($query, true);
//echo json_encode($a);
$num = $a['suggest']['default'][$searchTerm]["numFound"];
//echo "<br>".$num;
$suggestions = $num = $a['suggest']['default'][$searchTerm]["suggestions"];

foreach($suggestions as $key => $value){
	echo $value['term']."<br>";
}


/* $sql = "SELECT * FROM lista_paises WHERE pais_nombre LIKE (:keyword) ORDER BY pais_id ASC LIMIT 0, 7";
$query = $pdo->prepare($sql);
$query->bindParam(':keyword', $keyword, PDO::PARAM_STR);
$query->execute();
$lista = $query->fetchAll();
foreach ($lista as $milista) {
	// Colocaremos negrita a los textos
	$pais_nombre = str_replace($_POST['palabra'], '<b>'.$_POST['palabra'].'</b>', $milista['pais_nombre']);
	// Aquì, agregaremos opciones
    echo '<li onclick="set_item(\''.str_replace("'", "\'", $milista['pais_nombre']).'\')">'.$pais_nombre.'</li>';
} */
?>