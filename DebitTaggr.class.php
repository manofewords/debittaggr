<?php

define("DB_HOST", "TBD");
define("DB_USER", "TBD");
define("DB_PASSWORD", "TBD");
define("DB_NAME", "TBD");

// TODO: remove, used in index.php
define('TAG_THRESHOLD_L',   160);
define('TAG_THRESHOLD_M',   120);
define('TAG_THRESHOLD_S',   80);
define('TAG_THRESHOLD_XS',  40);
	
define('CONTROLLER_URL', 'http://localhost/debittaggr/controller.php');

class DebitTaggr
{
	var $db;

	function DebitTaggr()
	{
		$this->db = null;
	}

	function connectDb()
	{
		$this->db = mysql_pconnect(DB_HOST, DB_USER, DB_PASSWORD);
		@mysql_select_db(DB_NAME) or die("Unable to connect to database.");
	}

	function disconnectDb()
	{
		mysql_close($this->db);
	}

	function getTagsCount()
	{
		$tagsCount = array();

		$this->connectDb();
		$query  = "SELECT tags ";
		$query .= "FROM splits ";
		$result = @mysql_query($query, $this->db);
		while($row = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			$tags = mb_split(' ', $row['tags']);
			foreach($tags as $tag)
			{
				if(strlen($tag)==0) continue;
				if(isset($tagsCount[$tag])) $tagsCount[$tag] += 1;
				else $tagsCount[$tag] = 1;
			}
		}
		$this->disconnectDb();
		
		return $tagsCount;
	}

	function getStats($tags = null, $dateFrom = null, $dateTo = null)
	{
		$stats = array();

		$this->connectDb();
		$query  = "SELECT s.tags AS tags, s.amount AS amount ";
		$query .= "FROM splits s, debits d ";
		$query .= "WHERE s.debits_id = d.id ";
		if(!is_null($dateFrom)) $query .= "AND d.date >= ".$dateFrom."000000 ";
		if(!is_null($dateTo)) $query .= "AND d.date <= ".$dateTo."999999 ";
		if(!is_null($tags)) 
		{
			$tagsArray = mb_split(' ', trim($tags));
			$query .= "AND (0";
			foreach($tagsArray as $tag) $query .= " OR s.tags LIKE '%".$tag."%'";
			$query .= ") ";
		}
		$result = @mysql_query($query, $this->db);
		while($row = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			$amount = $row['amount']/100;
			if(isset($stats[$row['tags']])) $stats[$row['tags']] -= $amount;
			else $stats[$row['tags']] = -$amount;
		}
		$this->disconnectDb();
		
		return $stats;
	}

	function getDebitByDate($yyyymmdd)
	{
		$debit = array();

		$this->connectDb();
		$query  = "SELECT * ";
		$query .= "FROM debits ";
		$query .= "WHERE date >= ".$yyyymmdd."000000 ";
		$query .= "AND date <= ".$yyyymmdd."999999 ";
		$query .= "ORDER BY date ASC ";
		$result = @mysql_query($query, $this->db);
		if($row = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			$debit = $row;
			$debit['splits'] = $this->getDebitSplits($debit['id']);
		}
		$this->disconnectDb();
		
		return $debit;
	}
	
	function getNextDebit($currentId)
	{
		$debit = array();

		$this->connectDb();
		$query  = "SELECT * ";
		$query .= "FROM debits ";
		$query .= "WHERE id > ".$currentId." ";
		$query .= "ORDER BY id ASC ";
		$query .= "LIMIT 1 ";
		$result = @mysql_query($query, $this->db);
		if($row = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			$debit = $row;
			$debit['splits'] = $this->_getDebitSplits($debit['id']);
		}
		$this->disconnectDb();
		
		return $debit;
	}	
	
	function getPreviousDebit($currentId)
	{
		$debit = array();

		$this->connectDb();
		$query  = "SELECT * ";
		$query .= "FROM debits ";
		$query .= "WHERE id < ".$currentId." ";
		$query .= "ORDER BY id DESC ";
		$query .= "LIMIT 1 ";
		$result = @mysql_query($query, $this->db);
		if($row = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			$debit = $row;
			$debit['splits'] = $this->_getDebitSplits($debit['id']);
		}
		$this->disconnectDb();
		
		return $debit;
	}
	
	function _getDebitSplits($id)
	{
		$splits = array();

		$this->connectDb();
		$query  = "SELECT * ";
		$query .= "FROM splits ";
		$query .= "WHERE debits_id = ".$id;
		$result = @mysql_query($query, $this->db);
		while($row = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			$splits[] = $row;
		}
		$this->disconnectDb();
		
		return $splits;
	}
	
	function updateDebit($debit_id, $debit_amount, $split_notes, $split_tags, $split_id, $split_amount)
	{				
		for($i = 0; $i < count($split_notes); $i++)
		{
			$amount = $split_amount[$i];
			if(strlen($amount) == 0) continue; // a split should at least have an amount
			$amount *= -100;
			
			$tags = strip_tags($split_tags[$i]);
			$tags = addslashes($tags);
			$tags = utf8_encode($tags);
			$tags = strtoupper($tags);
			$tags = trim($tags);
		
			$notes = strip_tags($split_notes[$i]);
			$notes = addslashes($notes);
			$notes = utf8_encode($notes);
			
			$id = $split_id[$i];
			
			if(strlen($id)) $this->_updateSplit($id, $debit_id, $amount, $tags, $notes);
			else $this->_createSplit($debit_id, $amount, $tags, $notes);
		}
	}
	
	function _updateSplit($id, $debits_id, $amount, $tags, $notes)
	{
		$this->connectDb();
		$query  = "UPDATE splits ";
		$query .= "SET notes = '".$notes."', ";
		$query .= "tags = '".$tags."', ";
		$query .= "amount = ".$amount." ";
		$query .= "WHERE id = ".$id." ";
		$query .= "AND debits_id = ".$debits_id;
		$result = @mysql_query($query, $this->db);
		$this->disconnectDb();		
	}
	
	function _createSplit($debits_id, $amount, $tags, $notes)
	{
		$this->connectDb();
		$query  = "INSERT INTO splits(debits_id, notes, tags, amount) ";
		$query .= "VALUES (".$debits_id.",'".$notes."','".$tags."',".$amount.")";
		$result = @mysql_query($query, $this->db);
		$this->disconnectDb();		
	}
	
	function import($filePath)
	{
		$content = file_get_contents($filePath);
		
		$this->connectDb();
		$query = "INSERT INTO debits(amount, description, date, currency) VALUES ";
		
		/*
		1	Date d'évaluation
		2	Relation bancaire
		3	Portefeuille
		4	Produit
		5	IBAN
		6		Monn.
		7	Date du
		8	Date au
		9		Description
		10		Date de conclusion
		11	Date de comptabilisation
		12	Date de valeur
		13		Description 1
		14		Description 2
		15		Description 3
		16	N° de transaction
		17	Cours des devises du montant initial en montant du décompte
		18	Sous-montant
		19		Débit
		20	Crédit
		21	Solde
		*/
		$pattern = "([^;]*);"; // 1st field
		$full_pattern = "|";
		$nb_fields = 21;
		for($i=0; $i<$nb_fields-1; $i++) {
			$full_pattern .= $pattern;
		}
		$full_pattern .= "([^;]*)\n|is"; // last field
		preg_match_all($full_pattern, $content, $matches, PREG_SET_ORDER);
		foreach($matches as $match)
		{
			// $match[0] is the full pattern!
			$date = $match[10];
			$description = $match[9].' '.$match[13].' '.$match[14].' '.$match[15];
			$currency = $match[6];
			$amount = str_replace("'","", $match[19]);

			if(!is_numeric($amount)) continue;
			
			$date = substr($date, 6, 4).substr($date, 3, 2).substr($date, 0, 2).'000000'; // in: 10.08.2012, out: 20120810000000
			$description = addslashes($description);
			$description = htmlspecialchars($description);

			$amount *= -100;

			$query .= "($amount, '$description', $date, '$currency'),";
		}

		$query = substr($query, 0, strlen($query)-1);

		$result = mysql_query($query, $this->db);
		$this->disconnectDb();
		unlink($filePath);

		return true;
	}
}

?>