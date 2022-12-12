<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <title>Bootstrap demo</title>
</head>
<html>
    <body>
        <div class="container">
            <br><h2>URLs del crawler</h2>
            <form enctype="multipart/form-data" method="post" action="./updateUrls.php">
                <textarea class="form-control" name="urlscontent" id="urls" cols="50" rows="10"><?php
                        $dir = $_SERVER["DOCUMENT_ROOT"] . "/file_uploads/urls.txt";
                        $fp = fopen($dir,'r');
                        $content = fread($fp, filesize($dir));
                        echo $content;
                        fclose($fp);
                    ?></textarea><br>
                <input type="submit" class="btn btn-primary" name="SubmitFile" value="Actualizar URLs">
                <button type="button" class="btn btn-dark" onclick="location.href='./index.php'">Regresar</button>
            </form>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    </body>
</html>

<?php
if (isset($_POST['SubmitFile'])){
    $uploaddir = $_SERVER["DOCUMENT_ROOT"] . "/file_uploads/";
    if (!file_exists($uploaddir))
            if (!mkdir($uploaddir, 0755))
                die ("No se pudo crear directorio de archivos.");

    $content = $_POST['urlscontent'];
    echo "<script>console.log('" . $content . "' );</script>";
    $fileName = "urls.txt"; // cannot be an online resource
    $Saved_File = fopen($uploaddir . $fileName, 'w+');
    fwrite($Saved_File, $content);
    fclose($Saved_File);
    echo "<script>alert('Se actualizaron las URLs')</script>";
    echo "<script>window.location.href = './index.php'</script>";
}
?>