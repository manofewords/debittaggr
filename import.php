<?php 
	require_once("DebitTaggr.class.php");
	$debitTaggr = new DebitTaggr();
?>
<html>
	<head>
	</head>
	<body>
<?php
	if (isset($_FILES["import"]) && isset($_POST["upload"]))
	{
		// map : {{"name";...},{"type";...},{"tmp_name";...},{"error";...},{"size";...}}
		$fileArray = $_FILES["import"];
		$filePath = $fileArray["tmp_name"];
		if($debitTaggr->import($filePath)) echo 'FILE UPLOADED SUCCESSFULLY';
		else echo 'AN ERROR OCCURRED, TRY AGAIN';
	}
	else
	{
?>
		<form method="post" enctype="multipart/form-data" action="import.php">
			IMPORT : <input type="file" name="import" />
			<input type="submit" name="upload" value="GO" />
		</form>
<?php
	}
?>
	</body>
</html>

