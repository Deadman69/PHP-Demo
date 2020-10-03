<?php
      $baseDirectory = "."; // Base repository, should let at "."
      $cookieExpiration = 3600; // cookie expiration time (in seconds). Cookies are used to store user position in the folders


      $actualDirectory = $baseDirectory;
      if(isset($_COOKIE["actualDirectory"])) { // If we are in a directory
            $actualDirectory = $_COOKIE["actualDirectory"];
      }

      if(isset($_GET["rmfile"]) && file_exists($_GET["rmfile"])) { // If we want to delete a file and file exist
            $path = htmlspecialchars($_GET["rmfile"]);
            unlink($path);
      }

      if(isset($_GET["rmdir"]) && file_exists($_GET["rmdir"])) { // If we want to delete a folder and folder exist
            $path = htmlspecialchars($_GET["rmdir"]);
            if(!rmdir($path)) {
                  echo "<script>alert('The folder $path could not be suppressed ! Try emptying the folder and check if it is not used by any process.')</script>";
            }
      }

      if(isset($_POST["mkdir"])) { // If we want to create a folder and folder doesn't exist
            $path = htmlspecialchars($_POST["path"])."/".htmlspecialchars($_POST["mkdir"]);
            if(!file_exists($path)) {
                  mkdir($path, 7777, true);
                  setcookie("actualDirectory", $path, time() + $cookieExpiration);
            }
            else
                  echo "<script>alert('The folder $path already exist ! Creation aborted.')</script>";
      }

      if(isset($_GET["cd"])) { // If we want to change directory
            $actualDirectory = htmlspecialchars($_GET["cd"]);
            setcookie("actualDirectory", $actualDirectory, time() + $cookieExpiration);
      }

      if(isset($_GET["rename"]) && isset($_GET["path"]) && file_exists($_GET["path"])) { // Rename a file if this file exist (or a folder)
            $rename = htmlspecialchars($_GET["rename"]);
            $path = htmlspecialchars($_GET["path"]);

            $explodedPath = explode("/", $path);
            $explodedPath[count($explodedPath) - 1] = $rename;

            $rename = implode("/", $explodedPath);

            if(file_exists($rename)) {
                  echo "<script>alert('The file $rename already exist ! Rename aborted.')</script>";
            } else
                  rename($path, $rename);
      }

      if(isset($_GET["download"])) { // If we want to download a specific file
            setcookie("actualDirectory", $path, time() + $cookieExpiration);

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

      if(isset($_FILES["upload"])) { // If we want to upload a file
            $path = htmlspecialchars($_POST["path"]);
            $fileName = "./".basename($_FILES['upload']['name']);
            if($path != ".")
                  $fileName = $path."/".basename($_FILES['upload']['name']);

            if(!file_exists($fileName)) {
                  move_uploaded_file($_FILES['upload']['tmp_name'], $fileName);
                  setcookie("actualDirectory", $path, time() + $cookieExpiration);
            } else
                  echo "<script>alert('The file ".$_FILES['upload']['name']." already exist ! Upload aborted.')</script>";
      }

      function formatSize($bytes, $format = '%.2f', $lang = 'en') { // From http://dev.petitchevalroux.net/php/afficher-taille-fichier-avec-une-unite-php.271.html
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
      <title>File Manager - <?php echo $actualDirectory;?></title>
      <link rel="stylesheet" href="bootstrap.min.css">
      <script src="jquery.min.js"></script>
      <script src="bootstrap.bundle.min.js"></script>
</head>
<body>
      <button onclick="gotoRacine()" class="btn btn-info">Revenir à la racine</button>
      <button onclick="gotoUpperLevel()" class="btn btn-info">Remonter d'un niveau</button><br><br>
      <form enctype="multipart/form-data" method="post" id="formUpload">
            Fichier : <input name="upload" type="file">
            <input type="hidden" name="path" value="<?php echo $actualDirectory;?>">
            <input type="submit" value="Uploader" class="btn btn-success"><br>
      </form>

      <form method="post" id="formFolder">
            Nom : <input name="mkdir" type="text" autocomplete="off">
            <input type="hidden" name="path" value="<?php echo $actualDirectory;?>">
            <input type="submit" value="Créer" class="btn btn-success"><br>
      </form>

      <form method="get" id="formRename">
            Nouveau nom : <input name="rename" type="text" autocomplete="off" id="formRename_rename">
            <input type="hidden" name="path" value="<?php echo $actualDirectory;?>" id="formRename_path">
            <input type="submit" value="Renommer" class="btn btn-success"><br>
      </form>

      <div class="row">
            <div class="col-md-5">
                  <table id="FoldersTable" class="table">
                        <thead>
                              <tr>
                                    <td onclick="showCreateFolder()"><h2>Dossiers (Cliquer pour créer un dossier dans "<?php echo $actualDirectory; ?>")</h2></td>
                                    <td></td>
                              </tr>
                        </thead>
                  </table>
            </div>
            <div class="col-md-5 offset-1">
                  <table id="FilesTable" class="table table-striped">
                        <thead>
                              <tr>
                                    <td onclick="showUpload()"><h2>Fichiers (Cliquer pour upload un fichier dans "<?php echo $actualDirectory; ?>")</h2></td>
                                    <td></td>
                                    <td></td>
                              </tr>
                        </thead>
                        <tbody></tbody>
                  </table>
            </div>
      </div>
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
                              row.insertCell(1).innerHTML = `<div class="dropdown">
                                                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                          Actions
                                                        </button>
                                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                            <a class="dropdown-item" href="?cd=<?php echo $actualDirectory."/".$dir;?>">Navigate</a>
                                                            <a class="dropdown-item" href="#" onclick="renameFileFolder('<?php echo $actualDirectory."/".$dir;?>')">Rename Folder</a>

                                                            <a class="dropdown-item" href="#" onclick="confirmDelete('<?php echo $actualDirectory."/".$dir;?>', true)">Delete Folder</a>
                                                        </div>
                                                      </div>`;
                              </script>
                              <?php
                        }
                  } else {
                        ?><script>
                        var table = document.getElementById("FilesTable");
                        var row = table.insertRow(1);
                        row.insertCell(0).innerHTML = "<?php echo $dir.' ('.formatSize(filesize($actualDirectory.'/'.$dir)).')'; ?>";
                        row.insertCell(1).innerHTML = `<div class="dropdown">
                                                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                          Actions
                                                        </button>
                                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                          <a class="dropdown-item" href="?download=<?php echo $actualDirectory."/".$dir;?>">Download</a>
                                                          <a class="dropdown-item" href="#" onclick="renameFileFolder('<?php echo $actualDirectory."/".$dir;?>')">Rename File</a>

                                                          <a class="dropdown-item" href="#" onclick="confirmDelete('<?php echo $actualDirectory."/".$dir; ?>', false)">Delete File</a>
                                                        </div>
                                                      </div>`;
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
      var formRename = document.getElementById("formRename");

      formUpload.style.display = "none";
      formFolder.style.display = "none";
      formRename.style.display = "none";

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

      function renameFileFolder(pathToRename) {
            var formRename_rename = document.getElementById("formRename_rename");
            var formRename_path = document.getElementById("formRename_path");

            formRename.style.display = "block";
            var fileName = pathToRename.split("/");
            fileName = fileName[fileName.length - 1];

            formRename_rename.value = fileName;
            formRename_path.value = pathToRename;
      }

      function confirmDelete(pathToDelete, type) { // type = boolean, true: folder, false: file
            console.log("ok");
            var message = "Do you really want to delete this ";
            if(type)
                  message += "folder ?";
            else
                  message += "file ?";
            var r = confirm(message);
            if(r) {
                  if(type)
                        window.location.replace("?rmdir=" + pathToDelete);
                  else
                        window.location.replace("?rmfile=" + pathToDelete);
            }
      }
</script>

</html>