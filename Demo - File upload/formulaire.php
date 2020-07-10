<!DOCTYPE html>
<html>
<head>
	<title>Ma page</title>
</head>
<body>
	<form method="POST" action="upload.php" enctype="multipart/form-data">
	    <!-- On limite le fichier à 100Ko -->
	    <input type="hidden" name="MAX_FILE_SIZE" value="100000">
	    Fichier : <input type="file" name="avatar">
	    <input type="submit" name="envoyer" value="Envoyer le fichier">
	</form>
</body>
</html>