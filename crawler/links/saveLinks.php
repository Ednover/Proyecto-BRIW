<?php
// $inputJSON = file_get_contents('php://input');
// $input = json_decode($inputJSON, TRUE); //convert JSON into array
// print_r($input);

// $data = json_decode(file_get_contents('php://input'), true);
// print_r($data);
// echo $data["operacion"];
$request = json_decode(file_get_contents('php://input'));
$info = implode("\n",$request->links);
file_put_contents("../links.txt", $info);

echo json_encode($request);

?>