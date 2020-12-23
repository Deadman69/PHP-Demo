<?php
      $baseDirectory = "D:/Programmes/Xampp/htdocs"; // Base repository. "." will work.
      $cookieExpiration = 3600; // cookie expiration time (in seconds). Cookies is overused. Should not set it under 60.
      $attempsLockFail = 3; // After 3 wrong attempts, user will be blocked for the time provided below
      $timeLockFail = 60; // if user fail more than X attempts, he will be blocked for the time provided here
      $password = "admin"; // password to connect

      // DONT TOUCH BELOW

      session_start();
      $shouldEditFile = false; // Don't touch it
      $fileToEdit = ""; // Don't touch it
      $fileToLaunch = ""; // Don't touch it
      $errorLoginMessage = "You are not logged in (or you had provided a wrong password)"; // Don't touch it

      $actualDirectory = $baseDirectory;
      if(isset($_COOKIE["actualDirectory"])) { // If we are in a directory
            $actualDirectory = $_COOKIE["actualDirectory"];
            if(!file_exists($actualDirectory)) // If directory does not exist
                  $actualDirectory = $baseDirectory;
      }

      if(isset($_POST["passwordProvided"])) {
            $passwordProvided = $_POST["passwordProvided"];
            if($passwordProvided == $password) {
                  $errorLoginMessage = "";
                  $_SESSION["isLogged"] = true;
                  $_SESSION["wrongAttemptsCount"] = 0;
                  $_SESSION["blockConnection"] = 0;
            } else {
                  $_SESSION["isLogged"] = false;
                  if($_SESSION["blockConnection"] < time() && $_SESSION["blockConnection"] != 0) {
                        $_SESSION["wrongAttemptsCount"] = 0;
                        $_SESSION["blockConnection"] = 0;
                  }
                  if(isset($_SESSION["wrongAttemptsCount"])) {
                        $_SESSION["wrongAttemptsCount"]++;
                        if($_SESSION["wrongAttemptsCount"] >= $attempsLockFail)
                              $_SESSION["blockConnection"] = time() + $timeLockFail;
                  }
                  else
                        $_SESSION["wrongAttemptsCount"] = 1;

                  $errorLoginMessage = $errorLoginMessage." - ".$_SESSION["wrongAttemptsCount"]." failed attempts";
            }
      }

      if(isset($_COOKIE["disconnect"])) {
            $_SESSION = array();
            if (ini_get("session.use_cookies")) {
                  $params = session_get_cookie_params();
                  setcookie(session_name(), '', time() - 42000,
                        $params["path"], $params["domain"],
                        $params["secure"], $params["httponly"]
                  );
            }
            session_destroy();

            setcookie("disconnect", "", time() - 42000);
            setcookie("actualDirectory", "", time() - 42000);

            echo "<script>window.location.replace('index.php');</script>";
      }

      if(isset($_COOKIE["rmfile"]) && file_exists($_COOKIE["rmfile"])) { // If we want to delete a file and file exist
            $path = htmlspecialchars($_COOKIE["rmfile"]);
            unlink($path);

            setcookie("rmfile", "", time() - 42000);

            redirectTo($actualDirectory);
      }

      if(isset($_COOKIE["rmdir"]) && file_exists($_COOKIE["rmdir"])) { // If we want to delete a folder and folder exist
            $path = htmlspecialchars($_COOKIE["rmdir"]);
            if(!rmdir($path)) {
                  echo "<script>alert('The folder $path could not be suppressed ! Try emptying the folder and check if it is not used by any process.')</script>";
            }

            setcookie("rmdir", "", time() - 42000);

            redirectTo($baseDirectory);
      }

      if(isset($_POST["mkdir"])) { // If we want to create a folder and folder doesn't exist
            $path = htmlspecialchars($_POST["path"])."/".htmlspecialchars($_POST["mkdir"]);
            if(!file_exists($path)) {
                  mkdir($path, 7777, true);
                  setcookie("actualDirectory", $path, time() + $cookieExpiration);
            }
            else
                  echo "<script>alert('The folder $path already exist ! Creation aborted.')</script>";

            redirectTo($path);
      }

      if(isset($_COOKIE["cd"])) { // If we want to change directory
            $actualDirectory = htmlspecialchars($_COOKIE["cd"]);
            setcookie("actualDirectory", $actualDirectory, time() + $cookieExpiration);

            setcookie("cd", "", time() - 42000);
      }

      if(isset($_COOKIE["rename"]) && isset($_COOKIE["path"]) && file_exists($_COOKIE["path"])) { // Rename a file if this file exist (or a folder)
            $rename = htmlspecialchars($_COOKIE["rename"]);
            $path = htmlspecialchars($_COOKIE["path"]);

            $explodedPath = explode("/", $path);
            $explodedPath[count($explodedPath) - 1] = $rename;

            $rename = implode("/", $explodedPath);

            if(file_exists($rename)) {
                  echo "<script>alert('The file $rename already exist ! Rename aborted.')</script>";
            } else
                  rename($path, $rename);

            setcookie("rename", "", time() - 42000);
            setcookie("path", "", time() - 42000);

            redirectTo($actualDirectory);
      }

      if(isset($_COOKIE["download"]) && $_COOKIE["download"] != "") { // If we want to download a specific file

            $path = $_COOKIE["download"];
            setcookie("download", "", time() - 42000);

            header('Content-Type: application/octet-stream');
            header('Content-Length: '. filesize($path));
            header('Content-disposition: attachment; filename='. basename($path));
            header('Pragma: no-cache');
            header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            header('Expires: 0');
            readfile($path);
            exit();

            redirectTo($actualDirectory);
      }

      if(isset($_COOKIE["edit"])) { // If we want to edit a file
            $fileToEdit = $_COOKIE["edit"];
            $shouldEditFile = true;
            setcookie("edit", "", time() - 42000);
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

            redirectTo($actualDirectory);
      }

      if(isset($_POST['nameFileToCreate']) && isset($_POST['path'])) { // If we want to create a new file
            $path = $_POST['path'];
            $fileName = $_POST['nameFileToCreate'];

            if(!file_exists($path."/".$fileName)) {
                  fopen($path."/".$fileName, "w");
            } else
                  echo "<script>alert('Oops, this file already exist !')</script>";

            redirectTo($actualDirectory);
      }

      if (isset($_POST['editFileModify'])) { // If user edit a file
            $shouldEditFile = false;
            $fileToEdit = $_POST["path"];
            file_put_contents($fileToEdit, $_POST['editFileModify']); // Save new text

            $fileToEdit = "";
            redirectTo($actualDirectory);
      }

      if (isset($_COOKIE['newChmod']) && isset($_COOKIE['fileEditCHMOD'])) {
            $newCHMOD = $_COOKIE['newChmod'];
            $file = $_COOKIE['fileEditCHMOD'];
            if (file_exists($file)) {
                  if (!chmod($file, octdec($newCHMOD))) 
                        alert("Oops, CHMOD change has fail !");
            } else
                  echo "<script>alert('This file does not exist !')";
            setcookie("newChmod", "", time() - 42000);
            setcookie("fileEditCHMOD", "", time() - 42000);
            redirectTo($actualDirectory);
      }

      if (isset($_COOKIE["launch"])) {
            $fileToLaunch = $_COOKIE["launch"];
            setcookie("launch", "", time() - 42000);
            redirectTo("index.php");
      }

      function formatSize($bytes, $format = '%.2f', $lang = 'en') { // From http://dev.petitchevalroux.net/php/afficher-taille-fichier-avec-une-unite-php.271.html
            static $units = array('en' => array('B','KB','MB','GB','TB'));
            $translatedUnits = &$units[$lang];
            if(isset($translatedUnits)  === false) {
                  $translatedUnits = &$units['en'];
            }
            $b = (double)$bytes;
            /*On gére le cas des tailles de fichier négatives*/
            if($b > 0) {
                  $e = (int)(log($b,1024));
                  /**Si on a pas l'unité on retourne en To*/
                  if(isset($translatedUnits[$e]) === false) {
                        $e = 4;
                  }
                  $b = $b/pow(1024,$e);
            }
            else { $b = 0; $e = 0; }
            return sprintf($format.' %s',$b,$translatedUnits[$e]);
      }
      function redirectTo($url) { echo "<script>window.location.replace('#');</script>"; }
      function isLogged() { if(isset($_SESSION["isLogged"]) and $_SESSION["isLogged"]) return true; else return false; }
      function isBlocked() { global $attempsLockFail; if(isset($_SESSION["wrongAttemptsCount"]) && $_SESSION["wrongAttemptsCount"] >= $attempsLockFail && isset($_SESSION["blockConnection"]) && $_SESSION["blockConnection"] >= time()) return true; else return false; }
if($fileToLaunch == "") {
?>

<!DOCTYPE html>
<html>
<head>
      <?php
      if(isLogged()) {
      ?>
            <title>File Manager - <?php echo $actualDirectory;?></title>
      <?php
      } else {
      ?>
            <!-- Password is not stored here, go away script kiddie.
            You should try to edit google page with the source code.

            (Did you really think it will work ??) -->
            <title>File Manager - Login</title>
      <?php
      }
      ?>
      <link rel="stylesheet" href="bootstrap.min.css">
      <script src="jquery.min.js"></script>
      <script src="bootstrap.bundle.min.js"></script>
</head>
<body>
      <?php
      if(isLogged()) {
            if($shouldEditFile) { ?>
                  <form method="post">
                        <div class="form-group">
                              <?php
                                    $numberLines = substr_count(file_get_contents($fileToEdit), "\n");
                                    if($numberLines > 20)
                                          $numberLines = 20;
                              ?>
                              <textarea name="editFileModify" class="form-control" rows="<?php echo $numberLines; ?>"><?php echo htmlspecialchars(file_get_contents($fileToEdit)); ?></textarea>
                        </div>
                        <input type="hidden" name="path" value="<?php echo $fileToEdit;?>"><br>
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <button type="reset" class="btn btn-danger">Reset</button>
                  </form>
                  <br><br><br><br><br><br><br><br><br><br><br><br>
            <?php } ?>
            <button onclick="gotoRacine()" class="btn btn-info">Go to home</button>
            <button onclick="gotoUpperLevel()" class="btn btn-info">Go one level upper</button>
            <button onclick="disconnect()" class="btn btn-danger" style="float: right;">Disconnect</button><br><br>
            <form enctype="multipart/form-data" method="post" id="formUpload">
                  File : <input name="upload" type="file">
                  <input type="hidden" name="path" value="<?php echo $actualDirectory;?>">
                  <input type="submit" value="Upload" class="btn btn-success"><br>
            </form>
            <form enctype="multipart/form-data" method="post" id="formCreateFile">
                  Or enter the name of the file to create : <input name="nameFileToCreate" type="text" autocomplete="off">
                  <input type="hidden" name="path" value="<?php echo $actualDirectory;?>">
                  <input type="submit" value="Create" class="btn btn-success"><br><br><br>
            </form>

            <form method="post" id="formFolder">
                  Name : <input name="mkdir" type="text" autocomplete="off">
                  <input type="hidden" name="path" value="<?php echo $actualDirectory;?>">
                  <input type="submit" value="Create" class="btn btn-success"><br>
            </form>
            <div id="formRename">
                  New name : <input name="rename" type="text" autocomplete="off" id="formRename_rename">
                  <input type="hidden" name="path" value="<?php echo $actualDirectory;?>" id="formRename_path">
                  <button class="btn btn-success" onclick="renameFunc()">Rename</button><br>
            </div>
            <div class="row">
                  <div class="col-md-5">
                        <table id="FoldersTable" class="table">
                              <thead>
                                    <tr>
                                          <td onclick="showCreateFolder()"><h2>Folders (Click to create a folder in "<?php echo $actualDirectory; ?>")</h2></td>
                                          <td></td>
                                    </tr>
                              </thead>
                        </table>
                  </div>
                  <div class="col-md-5 offset-1">
                        <table id="FilesTable" class="table table-striped">
                              <thead>
                                    <tr>
                                          <td onclick="showUpload()"><h2>Files (Click to upload a file in "<?php echo $actualDirectory; ?>")</h2></td>
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
                  $autoincrementValue = 0;
                  while( $dir = readdir($directory) ) 
                  {
                        if (is_dir( $actualDirectory . "/" . $dir) )
                        {
                              if($dir != "." && $dir != "..") {
                                    ?><script>
                                    var table = document.getElementById("FoldersTable");
                                    var row = table.insertRow(1);
                                    row.insertCell(0).innerHTML = `<a onclick="cd('<?php echo $actualDirectory."/".$dir;?>')"><?php echo $dir; ?></a>`;
                                    row.insertCell(1).innerHTML = `<div class="dropdown">
                                                              <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                Actions
                                                              </button>
                                                              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                                  <a class="dropdown-item" href="#" onclick="cd('<?php echo $actualDirectory."/".$dir;?>')">Navigate</a>
                                                                  <a class="dropdown-item" href="#" onclick="renameFileFolder('<?php echo $actualDirectory."/".$dir;?>')">Rename Folder</a>
                                                                  <a class="dropdown-item" href="#" customCHMOD="<?php echo substr(sprintf('%o', fileperms($actualDirectory."/".$dir)), -4); ?>" onclick="showUpdateCHMOD(<?php echo $autoincrementValue; ?>, '<?php echo $actualDirectory."/".$dir;?>')" id="buttonCHMOD_<?php echo $autoincrementValue; ?>">Change CHMOD</a>

                                                                  <a class="dropdown-item" href="#" onclick="confirmDelete('<?php echo $actualDirectory."/".$dir;?>', true)" onmouseover="this.style.background='red';" onmouseout="this.style.background='#ffffff';">Delete Folder</a>
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
                                                                <a class="dropdown-item" onclick="downloadFile('<?php echo $actualDirectory."/".$dir; ?>')">Download</a>
                                                                <a class="dropdown-item" href="#" onclick="renameFileFolder('<?php echo $actualDirectory."/".$dir;?>')">Rename File</a>
                                                                <a class="dropdown-item" href="#" onclick="launchFile('<?php echo $actualDirectory."/".$dir;?>')">Launch file</a>
                                                                <a class="dropdown-item" href="#" onclick="editFile('<?php echo $actualDirectory."/".$dir; ?>')">Edit File</a>
                                                                <a class="dropdown-item" href="#" customCHMOD="<?php echo substr(sprintf('%o', fileperms($actualDirectory."/".$dir)), -4); ?>" onclick="showUpdateCHMOD(<?php echo $autoincrementValue; ?>, '<?php echo $actualDirectory."/".$dir;?>')" id="buttonCHMOD_<?php echo $autoincrementValue; ?>">Change CHMOD</a>

                                                                <a class="dropdown-item" href="#" onclick="confirmDelete('<?php echo $actualDirectory."/".$dir; ?>', false)" onmouseover="this.style.background='red';" onmouseout="this.style.background='#ffffff';">Delete File</a>
                                                              </div>
                                                            </div>`;
                              </script>
                              <?php
                        }

                        $autoincrementValue++;
                  }
                  closedir($directory);
      } elseif(!isLogged() && !isBlocked()) {
            ?>
            <p><?php echo $errorLoginMessage; ?></p>
            <form method="post">
                  <div class="form-group">
                        Password: <input type="password" name="passwordProvided">
                  </div>
                  <div class="form-group">
                        <button type="submit">Connect</button>
                  </div>
            </form>
      <?php
      } else {
      ?>
            <p>Oops, you have been blocked because you have too much failed attempts !<br>
            Please wait <?php echo $_SESSION["blockConnection"] - time(); ?> seconds before try again.</p>
      <?php } ?>

</body>

<script type="text/javascript">
      <?php
      if(isLogged()) {
      ?>
      var formUpload = document.getElementById("formUpload");
      var formFolder = document.getElementById("formFolder");
      var formRename = document.getElementById("formRename");
      var formCreateFile = document.getElementById("formCreateFile");

      formUpload.style.display = "none";
      formFolder.style.display = "none";
      formRename.style.display = "none";
      formCreateFile.style.display = "none";

      function showUpload() {
            formUpload.style.display = "block";
            formCreateFile.style.display = "block";
            formFolder.style.display = "none";
            formRename.style.display = "none";
      }
      function showCreateFolder() {
            formUpload.style.display = "none";
            formCreateFile.style.display = "none";
            formFolder.style.display = "block";
            formRename.style.display = "none";
      }
      function renameFileFolder(pathToRename) {
            var fileName = pathToRename.split("/");
            fileName = fileName[fileName.length - 1];

            document.getElementById("formRename_rename").value = fileName;
            document.getElementById("formRename_path").value = pathToRename;
            
            formUpload.style.display = "none";
            formCreateFile.style.display = "none";
            formFolder.style.display = "none";
            formRename.style.display = "block";
      }
      function gotoRacine() {
            document.cookie = "cd=" + '<?php echo $baseDirectory; ?>';
            window.location.replace("index.php");
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

            document.cookie = "cd=" + newPath;
            window.location.replace("index.php");
      }
      function cd(pathToGo) {
            document.cookie = "cd=" + pathToGo;
            window.location.replace("index.php");
      }
      function editFile(pathToEdit) {
            document.cookie = "edit=" + pathToEdit;
            window.location.replace("index.php");
      }
      function launchFile(pathToLaunch) {
            document.cookie = "launch=" + pathToLaunch;
            window.location.replace("index.php");            
      }
      function renameFunc() {
            document.cookie = "rename=" + document.getElementById("formRename_rename").value;
            document.cookie = "path=" + document.getElementById("formRename_path").value;
            window.location.replace("index.php");
      }
      function disconnect() {
            if(confirm("Do you want to disconnect ?")) {
                  document.cookie = "disconnect=true";
                  window.location.replace("index.php");
            }
      }
      function downloadFile(pathToDownload) {
            document.cookie = "download=" + pathToDownload;
            window.location.replace("index.php");
      }
      function confirmDelete(pathToDelete, type) { // type = boolean, true: folder, false: file
            var message = "Do you really want to delete this ";
            if(type)
                  message += "folder ?";
            else
                  message += "file ?";
            if(confirm(message)) {
                  if(type) {
                        document.cookie = "rmdir=" + pathToDelete;
                        window.location.replace("index.php");
                  }
                  else {
                        document.cookie = "rmfile=" + pathToDelete;
                        window.location.replace("index.php");
                  }
            }
      }
      function showUpdateCHMOD(id, pathToModify) {
            var actualCHMOD = document.getElementById("buttonCHMOD_" + id);
            var newCHMOD = prompt("Enter the new CHMOD", actualCHMOD.getAttribute("customCHMOD"));

            if (newCHMOD != null) {
                  document.cookie = "newChmod=" + newCHMOD;
                  document.cookie = "fileEditCHMOD=" + pathToModify;
                  window.location.replace("index.php");
            }
      }

      <?php } ?>
</script>

</html>
<?php } else { include_once($fileToLaunch); }?>