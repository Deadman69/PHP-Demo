<?php
      $baseDirectory = ".";
      $actualDirectory = $baseDirectory;
      if(isset($_COOKIE["actualDirectory"])) {
            $actualDirectory = $_COOKIE["actualDirectory"];
      }



      if(isset($_GET["rmfile"]) && file_exists($_GET["rmfile"])) {
            $path = htmlspecialchars($_GET["rmfile"]);
            unlink($path);
      }

      if(isset($_GET["rmdir"]) && file_exists($_GET["rmdir"])) {
            $path = htmlspecialchars($_GET["rmdir"]);
            rmdir($path);
      }

      if(isset($_POST["mkdir"]) && !file_exists($_POST["path"]."/".$_POST["mkdir"])) {
            $path = htmlspecialchars($_POST["path"])."/".htmlspecialchars($_POST["mkdir"]);
            mkdir($path, 7777, true);
            setcookie("actualDirectory", $path);
      }

      if(isset($_GET["cd"])) {
            $actualDirectory = htmlspecialchars($_GET["cd"]);
            setcookie("actualDirectory", $actualDirectory);
      }

      if(isset($_GET["download"])) {
            setcookie("actualDirectory", $path);

            $path = $_GET["download"];
            header('Content-Type: application/octet-stream');
            header('Content-Length: '. filesize($path));
            header('Content-disposition: attachment; filename='. basename($path));
            header('Pragma: no-cache');
            header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            header('Expires: 0');
            readfile($path);
            exit();
      }

      if(isset($_FILES["upload"])) {
            $path = htmlspecialchars($_POST["path"]);
            if($path == ".")
                  move_uploaded_file($_FILES['upload']['tmp_name'], "./".basename($_FILES['upload']['name']));
            else
                  move_uploaded_file($_FILES['upload']['tmp_name'], $path."/".basename($_FILES['upload']['name']));
            setcookie("actualDirectory", $path);
      }

      function formatSize($bytes, $format = '%.2f',$lang = 'en') { // From http://dev.petitchevalroux.net/php/afficher-taille-fichier-avec-une-unite-php.271.html
            static $units = array(
            'en' => array(
            'B',
            'KB',
            'MB',
            'GB',
            'TB'
            ));
            $translatedUnits = &$units[$lang];
            if(isset($translatedUnits)  === false)
            {
                  $translatedUnits = &$units['en'];
            }
            $b = (double)$bytes;
            /*On gére le cas des tailles de fichier négatives*/
            if($b > 0)
            {
                  $e = (int)(log($b,1024));
                  /**Si on a pas l'unité on retourne en To*/
                  if(isset($translatedUnits[$e]) === false)
                  {
                        $e = 4;
                  }
                  $b = $b/pow(1024,$e);
            }
            else
            {
                  $b = 0;
                  $e = 0;
            }
            return sprintf($format.' %s',$b,$translatedUnits[$e]);
      }
?>

<!DOCTYPE html>
<html>
<head>
      <title>Test Gestionnaire</title>
      <link rel="stylesheet" href="bootstrap.min.css">
</head>
<body>
      <button onclick="gotoRacine()" class="btn btn-info">Revenir à la racine</button>
      <button onclick="gotoUpperLevel()" class="btn btn-info">Remonter d'un niveau</button><br><br>
      <form enctype="multipart/form-data" method="post" id="formUpload">
            Fichier : <input name="upload" type="file">
            <input type="hidden" name="path" value="<?php echo $actualDirectory;?>">
            <input type="submit" value="Uploader"><br>
      </form>

      <form method="post" id="formFolder">
            Nom : <input name="mkdir" type="text">
            <input type="hidden" name="path" value="<?php echo $actualDirectory;?>">
            <input type="submit" value="Créer"><br>
      </form>

      <table id="FoldersTable" class="table">
            <thead>
                  <tr>
                        <td><h2>Dossiers <a onclick="showCreateFolder()">(Cliquer pour créer un dossier dans "<?php echo $actualDirectory; ?>")</a></h2></td>
                        <td></td>
                  </tr>
            </thead>
            <tbody></tbody>
      </table><br><br><br>
      <table id="FilesTable" class="table">
            <thead>
                  <tr>
                        <td><h2>Fichiers <a onclick="showUpload()">(Cliquer pour upload un fichier dans "<?php echo $actualDirectory; ?>")</a></h2></td>
                        <td></td>
                        <td></td>
                  </tr>
            </thead>
            <tbody></tbody>
      </table>

      <?php
            $directory = opendir( $actualDirectory );
            while( $dir = readdir($directory) ) 
            {
                  if (is_dir( $actualDirectory . "/" . $dir) )
                  {
                        if($dir != "." && $dir != "..") {
                              ?><script>
                              var table = document.getElementById("FoldersTable");
                              var row = table.insertRow(1);
                              row.insertCell(0).innerHTML = "<?php echo $dir; ?>";
                              row.insertCell(1).innerHTML = "<a href='index.php?rmdir=<?php echo $actualDirectory."/".$dir;?>'>X</a>";
                              row.insertCell(2).innerHTML = "<a href='index.php?cd=<?php echo $actualDirectory."/".$dir;?>'>Go to</a>";
                              </script>
                              <?php
                        }
                  } else {
                        ?><script>
                        var table = document.getElementById("FilesTable");
                        var row = table.insertRow(1);
                        row.insertCell(0).innerHTML = "<?php echo $dir.' ('.formatSize(filesize($actualDirectory.'/'.$dir)).')'; ?>";
                        row.insertCell(1).innerHTML = "<a href='index.php?rmfile=<?php echo $actualDirectory."/".$dir;?>'>X</a>";
                        row.insertCell(2).innerHTML = "<a href='index.php?download=<?php echo $actualDirectory."/".$dir;?>'>Download</a>";
                        </script>
                        <?php
                  }
            }
            closedir($directory);
      ?>

</body>

<script type="text/javascript">
      var formUpload = document.getElementById("formUpload");
      var formFolder = document.getElementById("formFolder");

      formUpload.style.display = "none";
      formFolder.style.display = "none";

      function showUpload() {
            formUpload.style.display = "block";
      }

      function showCreateFolder() {
            formFolder.style.display = "block";
      }


      function gotoRacine() {
            window.location.replace("?cd=<?php echo $baseDirectory; ?>");
      }
      function gotoUpperLevel() {
            var path = "<?php echo $actualDirectory; ?>";
            var newPath = "";

            var pathWay = path.split("/");
            pathWay.forEach((item, index) => {
                  if(index == 0) // racine = .
                        newPath = item;
                  else if(index != pathWay.length - 1)
                        newPath = newPath + "/" + item;
            })

            window.location.replace("index.php?cd=" + newPath);
      }
</script>

</html>