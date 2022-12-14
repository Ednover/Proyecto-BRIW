<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <title>Document</title>
</head>
    <body>
        <div class="container">
            <br><h2>URLs para indexar</h2>
            <form enctype="multipart/form-data" method="post" action="./crawler/executeCrawler.php">
                <textarea class="form-control" name="urlscontent" id="urls" cols="50" rows="10"><?php
                        $dir = $_SERVER["DOCUMENT_ROOT"] . "/crawler/links.txt";
                        $fp = fopen($dir,'r');
                        $content = fread($fp, filesize($dir));
                        echo $content;
                        fclose($fp);
                    ?></textarea><br>
                <input type="submit" class="btn btn-primary" name="SubmitFile" value="Realizar Crawling">
                <button type="button" class="btn btn-dark" onclick="location.href='./index.php'">Regresar</button>
            </form>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    </body>
</html>