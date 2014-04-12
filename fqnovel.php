<?php
include("novel_class.php");
$novel = new Novel;

if(isset($argv[1]))
{
	for($i=1;$i<$argc;$i++)
	{
		$av = explode("=", $argv[$i]);
		$_GET[$av[0]] = $av[1];
	}
}

if(isset($_GET["type"]))
	$type = $_GET["type"];

switch($type)
{
	case "get_novel_list":
		// unit_test url fqnovel.php?type=get_novel_list&page=1
		$page = $_GET["page"];
		$data = $novel->get_novel_list($page);
		echo json_encode($data);
		break;
	case "search":
		$page =	$_GET["page"];
		$text = $_GET["text"];
		$data = $novel->search($text, $page);
		echo json_encode($data);	
		break;
	case "get_novel_info_content":
		// unit_test url fqnovel.php?type=get_novel_info&length=3000&page_url=thread-2866217-1-1.html
		$page_url = $_GET["page_url"];
		$length = $_GET["length"];
		
		$data = $novel->get_novel_info($page_url);
		$data["content"] = "";
		$len = 0;
		foreach( $novel->current_novel_content as $content)
		{
			$data["content"] .= $content;
			if(strlen($data["content"]) > $length)
				break;
		}
		echo $data["content"];
		break;
	case "download_novel":
		$page_url = $_GET["page_url"];
		$novel->download_novel($page_url);
		break;
	case "remove_book":
		$page_url = $_GET["page_url"];
		$novel->remove_novel($page_url);
		break;
	case "get_book_list":
		echo $novel->get_book_list();
		break;
	case "get_novel":
		$novel_list = json_decode($novel->get_book_list(), true);
		$novel_id = $_GET["novel_id"];
		if(isset($_GET["page_from"]))
		{
			$page_from = $_GET["page_from"];
		}
		else
		{
			$page_from = 1;
		}
		if(isset($_GET["page_end"]))
		{
			$page_end = $_GET["page_end"];
		}
		else
		{
			$page_end = $novel_list[$novel_id]["pages"];
		}
		//echo "$page_from : $page_end";
		
		header("Content-type: text/plain");
		header("Content-type: text/plain; charset=UTF-16");
		header('Content-Disposition: attachment; filename*=UTF-8\'\'' . urlencode ( $novel_list[$novel_id]["name"]."txt" ) );
		
		//fputs($fp, "\xEF\xBB\xBF");
		//echo $novel_list[$novel_id]["name"] . "\n\n";
		//echo "程式作者：Andy \n";
		
		fputs($fp, "\xFE\xFF");
		echo iconv("UTF-8", "UTF-16", $novel_list[$novel_id]["name"]."\n\n");
		echo iconv("UTF-8", "UTF-16", "程式作者：Andy \n");
		
		$novel->echo_novel($novel_id, $page_from, $page_end);
		
		break;
	default:
		echo "Error! no such type\n";
		break;
}


//$novel_list = $aa->remove_novel("thread-2866217-1-1.html");
//print_r($novel_list);

	
?>