<?php

require_once("DebitTaggr.class.php");
$debitTaggr = new DebitTaggr();

if(!isset($_POST["a"])) exit;
$action = $_POST["a"];

switch($action)
{
	case '1': // get stats
		$dateFrom =  date('Ymd', strtotime($_POST['selDayFrom'].' '.$_POST['selMonthFrom'].' '.$_POST['selYearFrom']));
		$dateTo =  date('Ymd', strtotime($_POST['selDayTo'].' '.$_POST['selMonthTo'].' '.$_POST['selYearTo']));
		$stats = $debitTaggr->getStats($_POST['stats_tags'], $dateFrom, $dateTo);
ob_start();
print_r($stats);
$content = ob_get_contents();
ob_end_clean();
echo $content;
		break;
	case '2': // update debit
		$debitTaggr->updateDebit($_POST['debit_id'], $_POST['debit_amount'], $_POST['split_notes'], $_POST['split_tags'], $_POST['split_id'], $_POST['split_amount']);
		break;
	case '3': // get debit by date
		$dateFrom =  $_POST['selYearFrom'].($_POST['selMonthFrom']<10?'0':'').$_POST['selMonthFrom'].($_POST['selDayFrom']<10?'0':'').$_POST['selDayFrom'];
		$debit = $debitTaggr->getDebitByDate($dateFrom);
		header('Content-Type: application/xml');
		echo debitXML($debit);
		break;
	case '4': // get previous debit
		$debit = $debitTaggr->getPreviousDebit($_POST['id']);
		header('Content-Type: application/xml');
		echo debitXML($debit);
		break;
	case '5': // get next debit
		$debit = $debitTaggr->getNextDebit($_POST['id']);
		header('Content-Type: application/xml');
		echo debitXML($debit);
		break;
	case '6': // init tags
		$tags = $debitTaggr->getTagsCount();
		header('Content-Type: application/xml');
		echo tagsXML($tags);
		break;
	default :
		break;
}

function debitXML($debit)
{
	$xml  = '<?xml version="1.0" ?>';
	$xml .= '<debits>';
	$xml .= '<debit>';
	$xml .= '<id>'.$debit['id'].'</id>';
	$xml .= '<amount>'.-($debit['amount']/100).'</amount>';
	$xml .= '<currency>'.$debit['currency'].'</currency>';
	$xml .= '<description>'.$debit['description'].'</description>'; // str_replace("\n", '<br/>', $debit['description'])
	$xml .= '<splits>';
	foreach($debit['splits'] as $split)
	{
		$xml .= '<split>';
		$xml .= '<id>'.$split['id'].'</id>';
		$xml .= '<notes>'.utf8_decode($split['notes']).'</notes>';
		$xml .= '<tags>'.$split['tags'].'</tags>';
		$xml .= '<amount>'.-($split['amount']/100).'</amount>';
		$xml .= '</split>';
	}
	$xml .= '</splits>';
	$xml .= '<date>'.$debit['date'].'</date>';
	$xml .= '</debit>';
	$xml .= '</debits>';
	
	return $xml;
}

function tagsXML($tags)
{
	$xml  = '<?xml version="1.0" ?>';
	$xml .= '<tags>';
	foreach($tags as $name => $count)
	{	
		$xml .= '<tag>';
		$xml .= '<name>'.$name.'</name>';
		$xml .= '<count>'.$count.'</count>';
		$xml .= '</tag>';
	}
	$xml .= '</tags>';
	
	return $xml;
}

?>