<?php
include("./crawlerScripts/http.php");
include("./crawlerScripts/parse.php");
include("./crawlerScripts/addresses.php");
include("./crawlerScripts/httpCodes.php");

function getHTML($page){
    $SourceCode = http_get($page, "");
    return $SourceCode;
}

function getLinks($html, $page_base){
    $_links = array();
    $link_array = parse_array($html['FILE'], $beg_tag="<a", $close_tag=">" );
    for($i=0; $i<count($link_array); $i++){
        $link = get_attribute($tag=$link_array[$i], $attribute="href");
        $resloved_link_address = resolve_address($link, $page_base);
        $downloaded_link = http_get($resloved_link_address, $page_base);
        if($downloaded_link['STATUS']['http_code'] == 200 && ($downloaded_link != $page_base)){
            array_push($_links,$downloaded_link['STATUS']['url'] );
        }
    }
    $_links = array_unique($_links);
    return $_links;
}

function quitar_tildes($cadena) {
    $no_permitidas= array ("á","é","í","ó","ú","Á","É","Í","Ó","Ú","ñ","À","Ã","Ì","Ò","Ù","Ã™","Ã ","Ã¨","Ã¬","Ã²","Ã¹","ç","Ç","Ã¢","ê","Ã®","Ã´","Ã»","Ã‚","ÃŠ","ÃŽ","Ã”","Ã›","ü","Ã¶","Ã–","Ã¯","Ã¤","«","Ò","Ã","Ã„","Ã‹");
    $permitidas= array ("a","e","i","o","u","A","E","I","O","U","n","N","A","E","I","O","U","a","e","i","o","u","c","C","a","e","i","o","u","A","E","I","O","U","u","o","O","i","a","e","U","I","A","E");
    $texto = str_replace($no_permitidas, $permitidas ,$cadena);
    return $texto;
}

function rip_tags($string) {
    // ----- remove HTML script ans style -----
    $string =preg_replace('/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', "", $string);
    // ----- remove HTML TAGs -----
    $string = preg_replace ('/<[^>]*>/', ' ', $string);
    // ----- remove control characters -----
    $string = str_replace("\r", '', $string);    // --- replace with empty space
    $string = str_replace("\n", ' ', $string);   // --- replace with space
    $string = str_replace("\t", ' ', $string);   // --- replace with space
    $string = quitar_tildes($string);
    $string = preg_replace("/[^a-zA-Z0-9\s]+/", "", $string);
    // ----- remove multiple spaces -----
    $string = trim(preg_replace('/ {2,}/', ' ', $string));
    return $string;
}

function remove_stop_word($str){
    $PATH = './stopwords/spanish.txt';
    $stopwords = file_get_contents($PATH);
    $stopwords = str_replace("\r", '', $stopwords);
    $stopwords = str_replace("\n", ' ', $stopwords);
    $stopwords = explode(' ',$stopwords);
    $str= explode(' ',$str);
    foreach ($str as $key => $word) {
        if (in_array($word, $stopwords)) $str[$key] = '';
    }   
    $str = implode(' ',$str);
    return $str = trim(preg_replace('/ {2,}/', ' ', $str));
}

function savePageDB($body, $title, $url){
    $snippet = substr($body, 0, 100);
    $body = remove_stop_word($body);
    $data = array(array("body_es" => $body, "snippet" => $snippet, "titulo_es" => $title, "link" => $url));                                                                    
    $data_string = json_encode($data);
    
    $ch = curl_init('http://localhost:8983/solr/briwsolr/update?commitWithin=1000&overwrite=true&wt=json');                                                                      
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
        'Content-Type: application/json',                                                                                
        'Content-Length: ' . strlen($data_string))                                                                       
    );                                                                                                                   
    $result = curl_exec($ch);
}

function visitLevel1($links){
    foreach ($links as $key => $link) {
        $htmlPage = getHTML($link);
        $stripPage = rip_tags(mb_strtolower($htmlPage['FILE']));
        $title_excl = rip_tags(return_between($htmlPage['FILE'], "<title>", "</title>",EXCL));
        savePageDB($stripPage, $title_excl, $link);
  
    }
}

function visitLevel0($pages){
    foreach ($pages as $key => $page) {
        $htmlPage = getHTML($page);
        $links = array_unique(getLinks($htmlPage, $page));
        $stripPage = rip_tags(mb_strtolower($htmlPage['FILE']));
        $title_excl = rip_tags(return_between($htmlPage['FILE'], "<title>", "</title>",EXCL));
        savePageDB($stripPage, $title_excl, $page);
        visitLevel1($links);
    
    }
}

$file = file_get_contents("links.txt");
$pages = explode("\n", $file);

visitLevel0($pages);
echo "<script>alert('Indexación con exito')</script>";
echo "<script>window.location.href = '../index.php'</script>";
?>
