<?php
      $baseDirectory = "D:/Programmes/Xampp/htdocs"; // Base repository. "." will not work. Use "realP()" in console when logged to show the actual path
      $cookieExpiration = 3600; // cookie expiration time (in seconds). Cookies is overused. Should not set it under 60.
      $attempsLockFail = 3; // After 3 wrong attempts, user will be blocked for the time provided below
      $timeLockFail = 60; // if user fail more than X attempts, he will be blocked for the time provided here
      $password = "admin"; // password to connect
      $basefilename = "index.php"; // this filename (should be "index.php")

      // DONT TOUCH BELOW

      session_start();
      $shouldEditFile = false; // Don't touch it
      $fileToEdit = ""; // Don't touch it
      $fileToLaunch = ""; // Don't touch it
      $errorLoginMessage = "You are not logged in (or you had provided a wrong password)"; // Don't touch it

      $actualDirectory = $baseDirectory;

      if(isset($_SESSION["isFirstLoad"]) && $_SESSION["isFirstLoad"]) { // Executed once when user is connected
            $_SESSION["isFirstLoad"] = false;
      }

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
                  $_SESSION["isFirstLoad"] = true;
                  $_SESSION["wrongAttemptsCount"] = 0;
                  $_SESSION["blockConnection"] = 0;

                  echo "<script>window.location.replace('$basefilename');</script>";
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

            echo "<script>window.location.replace('$basefilename');</script>";
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

      if(isset($_POST['submitUpload'])){
            $path = htmlspecialchars($_POST["path"]);
            $countfiles = count($_FILES['upload']['name']);
            
            for($i = 0; $i < $countfiles; $i++){
                  $fileName = "./".basename($_FILES['upload']['name'][$i]);
                  if($path != ".")
                        $fileName = $path."/".basename($_FILES['upload']['name'][$i]);
                  
                  if(!file_exists($fileName)) {
                        move_uploaded_file($_FILES['upload']['tmp_name'][$i], $fileName);
                        setcookie("actualDirectory", $path, time() + $cookieExpiration);
                  } else
                        echo "<script>alert('The file ".$_FILES['upload']['name'][$i]." already exist ! Upload aborted.')</script>";
            }

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

      function formatSize($bytes, $format = '%.2f', $lang = 'en') { static $units = array('en' => array('B','KB','MB','GB','TB')); $translatedUnits = &$units[$lang]; if(isset($translatedUnits)  === false) { $translatedUnits = &$units['en']; } $b = (double)$bytes; if($b > 0) { $e = (int)(log($b,1024)); if(isset($translatedUnits[$e]) === false) { $e = 4; } $b = $b/pow(1024,$e); } else { $b = 0; $e = 0; } return sprintf($format.' %s',$b,$translatedUnits[$e]); } // From http://dev.petitchevalroux.net/php/afficher-taille-fichier-avec-une-unite-php.271.html 
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
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
      <script src="jquery.min.js"></script>
      <script src="bootstrap.bundle.min.js"></script>
      <style type="text/css">
            .modal { display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgb(0,0,0);background-color: rgba(0,0,0,0.4); }
            .modal-content { background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; }
            .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; }
            .close:hover, .close:focus { color: black; text-decoration: none; cursor: pointer; }
      </style>
</head>
<body>
      <?php
      if(isLogged()) {
            if($shouldEditFile) { ?>
                  <div class="modal" id="editModal">
                        <div class="modal-content">
                              <form method="post">
                                    <span class="close" id="closeModalEdit">&times;</span>
                                    <div class="form-group">
                                          <?php
                                                $numberLines = substr_count(file_get_contents($fileToEdit), "\n");
                                                if($numberLines > 20)
                                                      $numberLines = 20;
                                          ?>
                                          <textarea name="editFileModify" class="form-control" rows="<?php echo $numberLines; ?>"><?php echo htmlspecialchars(file_get_contents($fileToEdit)); ?></textarea>
                                    </div>
                                    <input type="hidden" name="path" value="<?php echo $fileToEdit;?>">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                    <button type="reset" class="btn btn-danger">Reset</button>
                              </form>
                        </div>
                  </div>
            <?php } ?>
            <button onclick="gotoRacine()" class="btn btn-info">Go to home</button>
            <button onclick="gotoUpperLevel()" class="btn btn-info">Go one level upper</button>
            <button onclick="disconnect()" class="btn btn-danger" style="float: right;">Disconnect</button>
            <div class="modal" id="fileModal">
                  <div class="modal-content">
                        <form enctype="multipart/form-data" method="post" id="formUpload">
                              <span class="close" id="closeModalFile">&times;</span>
                              File : <input name="upload[]" type="file" multiple>
                              <input type="hidden" name="path" value="<?php echo $actualDirectory;?>">
                              <input type="submit" name="submitUpload" value="Upload" class="btn btn-success"><br>
                        </form>
                        <form enctype="multipart/form-data" method="post" id="formCreateFile">
                              Or enter the name of the file to create : <input name="nameFileToCreate" type="text" autocomplete="off">
                              <input type="hidden" name="path" value="<?php echo $actualDirectory;?>">
                              <input type="submit" value="Create" class="btn btn-success"><br><br><br>
                        </form>
                  </div>
            </div>
            <div class="modal" id="folderModal">
                  <div class="modal-content">
                        <form method="post" id="formFolder">
                              <span class="close" id="closeModalFolder">&times;</span>
                              Name : <input name="mkdir" type="text" autocomplete="off">
                              <input type="hidden" name="path" value="<?php echo $actualDirectory;?>">
                              <input type="submit" value="Create" class="btn btn-success"><br>
                        </form>
                  </div>
            </div>
            <div class="modal" id="renameModal">
                  <div class="modal-content">
                        <form id="formRename" onsubmit="renameFunc()">
                              <span class="close" id="closeModalFile">&times;</span>
                              New name : <input name="rename" type="text" autocomplete="off" id="formRename_rename">
                              <input type="hidden" name="path" value="<?php echo $actualDirectory;?>" id="formRename_path">
                              <input type="submit" class="btn btn-success" value="Rename">
                        </form>
                  </div>
            </div>

            <div class="row" style="margin-top: 2%;">
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
            <script>
            <?php
                  $directory = opendir( $actualDirectory );
                  $autoincrementValue = 0;
                  while( $dir = readdir($directory) ) {
                        if (is_dir( $actualDirectory . "/" . $dir) ) {
                              if($dir != "." && $dir != "..") {
                                    ?>
                                    var row = document.getElementById("FoldersTable").insertRow(1);
                                    var tempRow = row.insertCell(0)
                                    tempRow.innerHTML = "<?php echo $dir; ?>";
                                    tempRow.onclick = function(){ cd('<?php echo $actualDirectory."/".$dir;?>') };
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
                                    <?php
                              }
                        } else {
                              ?>
                              var row = document.getElementById("FilesTable").insertRow(1);
                              var tempRow = row.insertCell(0);
                              tempRow.innerHTML = "<?php echo $dir.' ('.formatSize(filesize($actualDirectory.'/'.$dir)).')'; ?>";
                              tempRow.onclick = function(){ editFile('<?php echo $actualDirectory."/".$dir; ?>') };
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
                              <?php
                        }

                        $autoincrementValue++;
                  }
                  closedir($directory);
            ?> </script> <?php
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
      function showUpload() {
            var modal = document.getElementById("fileModal");
            var span = document.getElementById("closeModalFile");
            modal.style.display = "block";
            span.onclick = function() {
              modal.style.display = "none";
            }
            window.onclick = function(event) { // When the user clicks anywhere outside of the modal, close it
              if (event.target == modal) {
                modal.style.display = "none";
              }
            }
      }
      function showCreateFolder() {
            var modal = document.getElementById("folderModal");
            var span = document.getElementById("closeModalFolder");
            modal.style.display = "block";
            span.onclick = function() {
              modal.style.display = "none";
            }
            window.onclick = function(event) { // When the user clicks anywhere outside of the modal, close it
              if (event.target == modal) {
                modal.style.display = "none";
              }
            }
      }
      function renameFileFolder(pathToRename) {
            var fileName = pathToRename.split("/");
            fileName = fileName[fileName.length - 1];

            document.getElementById("formRename_rename").value = fileName;
            document.getElementById("formRename_path").value = pathToRename;
            
            var modal = document.getElementById("renameModal");
            var span = document.getElementById("closeModalRename");
            modal.style.display = "block";
            span.onclick = function() {
              modal.style.display = "none";
            }
            window.onclick = function(event) { // When the user clicks anywhere outside of the modal, close it
              if (event.target == modal) {
                modal.style.display = "none";
              }
            }
      }
      function gotoRacine() {
            document.cookie = "cd=" + '<?php echo $baseDirectory; ?>';
            window.location.replace("<?php echo $basefilename; ?>");
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
            window.location.replace("<?php echo $basefilename; ?>");
      }
      function cd(pathToGo) {
            document.cookie = "cd=" + pathToGo;
            window.location.replace("<?php echo $basefilename; ?>");
      }
      function editFile(pathToEdit) {
            document.cookie = "edit=" + pathToEdit;
            window.location.replace("<?php echo $basefilename; ?>");
      }
      function launchFile(pathToLaunch) {
            document.cookie = "launch=" + pathToLaunch;
            window.location.replace("<?php echo $basefilename; ?>");            
      }
      function renameFunc() {
            document.cookie = "rename=" + document.getElementById("formRename_rename").value;
            document.cookie = "path=" + document.getElementById("formRename_path").value;
            window.location.replace("<?php echo $basefilename; ?>");
      }
      function disconnect() {
            if(confirm("Do you want to disconnect ?")) {
                  document.cookie = "disconnect=true";
                  window.location.replace("<?php echo $basefilename; ?>");
            }
      }
      function downloadFile(pathToDownload) {
            document.cookie = "download=" + pathToDownload;
            window.location.replace("<?php echo $basefilename; ?>");
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
                        window.location.replace("<?php echo $basefilename; ?>");
                  }
                  else {
                        document.cookie = "rmfile=" + pathToDelete;
                        window.location.replace("<?php echo $basefilename; ?>");
                  }
            }
      }
      function showUpdateCHMOD(id, pathToModify) {
            var actualCHMOD = document.getElementById("buttonCHMOD_" + id);
            var newCHMOD = prompt("Enter the new CHMOD", actualCHMOD.getAttribute("customCHMOD"));

            if (newCHMOD != null) {
                  document.cookie = "newChmod=" + newCHMOD;
                  document.cookie = "fileEditCHMOD=" + pathToModify;
                  window.location.replace("<?php echo $basefilename; ?>");
            }
      }

      function realP() { console.log("<?php echo getcwd(); ?>"); }

      <?php if($shouldEditFile) { ?>
      var modal = document.getElementById("editModal");
      var span = document.getElementById("closeModalEdit");
      modal.style.display = "block";
      span.onclick = function() {
        modal.style.display = "none";
      }
      window.onclick = function(event) { // When the user clicks anywhere outside of the modal, close it
        if (event.target == modal) {
          modal.style.display = "none";
        }
      }
      <?php } } ?>
</script>

</html>
<?php } else { include_once($fileToLaunch); }?>
