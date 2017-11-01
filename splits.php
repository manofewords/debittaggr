<?php

// script to introduce splits

require_once("DebitTaggr.class.php");
$debitTaggr = new DebitTaggr();

$debitTaggr->connectDb();

$query  = "SELECT id, notes, tags, amount ";
$query .= "FROM debits";
$result = @mysql_query($query, $debitTaggr->db);
while($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
	$query2  = "INSERT INTO splits(debits_id, notes, tags, amount) ";
	$query2 .= "VALUES (".$row['id'].",'".$row['notes']."','".$row['tags']."',".$row['amount'].")";
	$result2 = @mysql_query($query2, $debitTaggr->db);
}

$debitTaggr->disconnectDb();

?>