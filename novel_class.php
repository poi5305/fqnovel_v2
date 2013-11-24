<?php
//中文
include("simple_html_dom.php");

define("FILE_PUT_CONTENTS_ATOMIC_MODE", 0777); 

class Novel_plugin
{
	
};
class Novel extends Novel_plugin
{
	var $html = NULL;
	
	var $current_novel_list = NULL;
	var $current_novel_info = NULL;
	
	var $current_novel_content = Array();
	var $current_search_list = Array();
	
	var $record = "novel_db.txt";
	var $download = "books";
	
	function Novel()
	{
		$this->html = new simple_html_dom();
		$oldmask = umask(0);
		if(!is_dir($this->download))
		{
			mkdir($this->download, 0777);
		}
	}
	
	function login()
	{
		//echo "login\n";
		
		$cookie_file_path = "cookie.txt";
		$login_url = "http://ck101.com/member.php?mod=logging&action=login&infloat=yes&frommessage&inajax=1&ajaxtarget=messagelogin";
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file_path);
		curl_setopt($ch, CURLOPT_COOKIEFILE,$cookie_file_path);	
		curl_setopt($ch, CURLOPT_URL,$login_url);
		$result = curl_exec($ch);
		curl_close($ch);
		
		if(strstr($result, "poi5305"))
			return ;
		
		//$member_login = file_get_contents("1.txt");
		$result = str_replace("]]></root>", "", $result);
		$result = str_replace("<root><![CDATA[", "", $result);
		
		$this->html = str_get_html($result);
		$form = $this->html->find("form",0);
		$url = "http://ck101.com/".$form->action;
		$url = str_replace("&amp;","&",$url);
		
		$post = Array();
		$post["formhash"] = $form->find("input[name=formhash]",0)->value;
		$post["referer"] = $form->find("input[name=referer]",0)->value;
		$post["username"] = "poi5305";
		$post["password"] = "d5df7c0d69f6bceef7282c117e1167a3";
		$post["questionid"] = "0";
		$post["answer"] = "";
		$post["loginsubmit"] = "true";
		print_r($post);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file_path);
		curl_setopt($ch, CURLOPT_COOKIEFILE,$cookie_file_path);	
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		$result = curl_exec($ch);
		curl_close($ch);
		
		//file_put_contents("tmp.html", $result);
	}
	
	function init_search($text = "神")
	{
		//echo "init_search\n";
		
		
		$this->login();
		
		$cookie_file_path = "cookie.txt";
		$search_url = "http://ck101.com/search.php?mod=forum&adv=yes";
		/*	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file_path);
		curl_setopt($ch, CURLOPT_COOKIEFILE,$cookie_file_path);	
		curl_setopt($ch, CURLOPT_URL,$search_url);
		$result = curl_exec($ch);
		curl_close($ch);
		
		$this->html = str_get_html($result);
		$form = $this->html->find("form",1);
		*/
		
		$url = "http://ck101.com/search.php?mod=forum";
		//$post["formhash"] = $form->find("input[name=formhash]",0)->value;
		$post["srchtxt"] = $text;
		$post["srchuname"] = "";
		$post["srchfid[0]"] = "237";
		$post["srchfid[1]"] = "3419";
		//$post["srchfid[]"] = "all";
		$post["orderby"] = "lastpost";
		$post["ascdesc"] = "desc";
		$post["srchfrom"] = "0";
		$post["before"] = "";
		$post["srchfilter"] = "all";
		$post["searchsubmit"] = "yes";
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file_path);
		curl_setopt($ch, CURLOPT_COOKIEFILE,$cookie_file_path);	
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		$result = curl_exec($ch);
		$url_info = curl_getinfo($ch);
		curl_close($ch);
		//print_r($url_info);
		//$this->parser_search($result);
		return $url_info["redirect_url"];
	}
	function search($text = "神", $page=1)
	{
		session_start();
		$cookie_file_path = "cookie.txt";

		if($page == 1 || !isset($_SESSION["search_url"]))
		{
			$search_url = $this->init_search($text);
			$_SESSION["search_url"] = $search_url;
		}
		$search_url = $_SESSION["search_url"] . "&page=$page";
		//return;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file_path);
		curl_setopt($ch, CURLOPT_COOKIEFILE,$cookie_file_path);	
		curl_setopt($ch, CURLOPT_URL,$search_url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$result = curl_exec($ch);
		curl_close($ch);
		//file_put_contents("tmp.html", $result);
		return $this->parser_search($result);
	}
	function parser_search($text)
	{
		$this->html = str_get_html($text);
		$list = $this->html->find("div#threadlist",0);
		
		$search_list = Array();
		$idx=0;
		foreach($list->find("li") as $li)
		{
			$number_info = $li->find("p.xg1",0)->plaintext;
			list($posts, $nums) = sscanf($number_info, "%d 個回覆 - %d 次查看");
			
			$search_list[$idx] = Array(
				"novel_id" => $li->id
				,"page_url" => "thread-{$li->id}-1-1.html"
				,"class" => ""
				,"name" => $li->find("a",0)->plaintext
				,"pages" => floor($posts/10)+1
				,"posts" => $posts
				,"nums"	=>$nums
			);
			$idx++;
		}
		$this->current_search_list = $search_list;
		return $search_list;
		
	}
	function echo_novel($novel_id, $page_from, $page_end)
	{
		for($i=$page_from; $i<=$page_end; $i++)
		{
			echo file_get_contents("$this->download/$novel_id/$i.txt");
		}
	}
	function get_book_list()
	{
		if(is_file("$this->download/$this->record"))
		{
			return file_get_contents("$this->download/$this->record");
		}
	}
	function update_global_novel_db()
	{
		$novel_db = Array();
		if(is_file("$this->download/$this->record"))
		{
			$novel_db = json_decode(file_get_contents("$this->download/$this->record"), true);
		}
		$novel_id = $this->current_novel_info["novel_id"];
		
		$novel_db[$novel_id] = $this->current_novel_info;
		unset($novel_db[$novel_id]["contents"]);
		file_put_contents("$this->download/$this->record", json_encode($novel_db));
		
	}
	function remove_novel($page_url)
	{
		$novel_page_info = $this->page_url_to_info($page_url);
		$novel_id = $novel_page_info["novel_id"];
		$link = "$this->download/$novel_id";
		if($link != "$this->download/" && is_dir($link))
		{
			$novel_db = json_decode(file_get_contents("$this->download/$novel_id/$this->record"), true);
			for($i=1;$i<=$novel_db["pages"];$i++)
			{
				unlink("$this->download/$novel_id/$i.txt");
			}
			unlink("$this->download/$novel_id/$this->record");
			rmdir("$this->download/$novel_id");
		}
		$novel_db = json_decode(file_get_contents("$this->download/$this->record"), true);
		unset($novel_db[$novel_id]);
		file_put_contents("$this->download/$this->record", json_encode($novel_db));
	}
	function update_novel($page_url, $is_current = false)
	{
		if(!$is_current)
			$this->get_novel_info($page_url);
		$novel_id = $this->current_novel_info["novel_id"];
		
		if(!is_file("$this->download/$novel_id/$this->record"))
		{
			echo "error! no novel_record\n";
			return;
		}
		
		$novel_db = json_decode(file_get_contents("$this->download/$novel_id/$this->record"), true);
		$record = array_merge($novel_db, $this->current_novel_info);
		
		if($this->current_novel_info["posts"] != $novel_db["posts"] || $novel_db["download_page"] != $novel_db["pages"])
		{
			$pages = $this->current_novel_info["pages"];
			for($page = $novel_db["download_page"]; $page <= $pages; $page++)
			{
				$page_url = $this->make_page_url($novel_id, $page);
				$this->get_novel_info($page_url);
				$record["contents"] = array_merge($record["contents"], $this->save_content($novel_id, $page) );
				$this->current_novel_info["download_page"] = $page;
				$this->update_global_novel_db();
				$record["download_page"] = $page;
				file_put_contents("$this->download/$novel_id/$this->record", json_encode($record));
			} 
		}
		
	}
	function download_novel($page_url, $is_current = false)
	{
		
		if(!$is_current)
			$this->get_novel_info($page_url);
		
		$novel_id = $this->current_novel_info["novel_id"];
		
		if(is_dir("$this->download/$novel_id"))
		{
			$this->update_novel($page_url, true);
			return;
		}
		mkdir("$this->download/$novel_id", 0777);
		
		$record = $this->current_novel_info;
		$record["contents"] = Array();
		$record["contents"] = array_merge($record["contents"], $this->save_content($novel_id, 1) );
		
		$pages = $this->current_novel_info["pages"];
		for($page = 2; $page <= $pages; $page++)
		{
			
			$page_url = $this->make_page_url($novel_id, $page);
			$this->get_novel_info($page_url);
			$record["contents"] = array_merge($record["contents"], $this->save_content($novel_id, $page) );
			$this->current_novel_info["download_page"] = $page;
			$this->update_global_novel_db();
			$record["download_page"] = $page;
			file_put_contents("$this->download/$novel_id/$this->record", json_encode($record));
		}
		
		
		
	}
	function save_content($novel_id, $page)
	{
		$record = Array();
		$fp = fopen("$this->download/$novel_id/$page.txt", "a");
		fputs($fp, "\xEF\xBB\xBF");
		foreach($this->current_novel_content as $post => &$content)
		{
			$record[$post] = strlen($content);
			fputs($fp, $content);
		}
		fclose($fp);
		return $record;
	}
	function get_novel_info($page_url)
	{
		$this->html->load_file("http://ck101.com/".$page_url);
		$data = array();
		$data["page_url"] = $page_url;
			
		// posts pages nums
		$info = trim($this->html->find("div#pt span",0)->plaintext);
		
		//[&nbsp;查看:1626 | 回覆:54 | 感謝：1&nbsp;]
		list($nums, $posts) = sscanf($info, "[&nbsp;查看:%d | 回覆:%d | 感謝：1&nbsp;]");
		$data["posts"] = $posts;
		$data["pages"] = floor($data["posts"]/10)+1;
		$data["nums"] = $nums;
		
		// class name type
		//$info = $this->html->find("div[id^post_]",0);
		$info = $this->html->find("div[id='postlist']",0);
		$data["class"] = $info->find("h2",0)->plaintext;
		$data["name"] = $info->find("h1",0)->plaintext;
		if(strstr($data["name"],"已完"))	$data["type"] =1;
		else	$data["type"] =0;
		
		$novel_page_info = $this->page_url_to_info($page_url);
		$data["novel_id"] = $novel_page_info["novel_id"];
		
		// contents
		$idx=1;
		$idx = $this->parser_novel_content($idx);

		
		if($novel_page_info["novel_page"] == 1)
		{
			//http://ck101.com/forum.php?mod=threadlazydata&tid=2866217		
			$this->html->load_file("http://ck101.com/forum.php?mod=threadlazydata&tid=".$data["novel_id"]);
			$idx = $this->parser_novel_content($idx);
		}
		
		$this->current_novel_info = $data;
		return $data;
		
	}
	function get_novel_list($page=1)
	{
		$url = "http://ck101.com/forum-237-$page.html";
		$this->html->load_file($url);
		$data = array();
		foreach($this->html->find("*[id^=normalthread]") as $tbody){
			$idx = count($data);
			$th = $tbody->find("th",0);
			$class = trim($th->find("em",0)->plaintext);
			$data[$idx]["class"] = trim($th->find("em",0)->plaintext);
			$data[$idx]["name"] = trim($th->find("a",1)->plaintext);
			$data[$idx]["page_url"]  = trim($th->find("a",1)->href);
			$data[$idx]["posts"] = trim($tbody->find(".num a",0)->plaintext);
			$data[$idx]["pages"] = floor($data[$idx]["posts"]/10)+1;
			$data[$idx]["nums"]  = trim($tbody->find(".num em",0)->plaintext);
			
			$novel_page_info = $this->page_url_to_info($data[$idx]["page_url"]);
			$data[$idx]["novel_id"] = $novel_page_info["novel_id"];
			
			if(strstr($data[$idx]["name"],"已完"))	$data[$idx]["type"] =1;
			else	$data[$idx]["type"] =0;
			if($data[$idx]["class"] == "[版務公告]")
				array_pop($data);
		}
		$this->current_novel_list = $data;
		return $data;
	}
	function parser_novel_content($idx=1)
	{
		$data = Array();
		$posts = $this->html->find("*[id^=postmessage_]");
		foreach($posts as $content)
		{
			$this->current_novel_content[$idx] = str_replace("&nbsp;", "", $content->plaintext);
			$idx++;
		}
		return $idx;
	}
	
	function getNovelContext($page_url)
	{
		$url = "http://ck101.com/".$page_url;
		$this->html->load_file($url);
		$c="";
		foreach($this->html->find(".t_f") as $content){
			$c.=$content->plaintext;
		}
		$c = str_replace("&nbsp;"," ",$c);
		return $c;
	}
	
	function make_page_url($novel_id, $page)
	{
		return "thread-$novel_id-$page-1.html";
	}
	//! util
	function page_url_to_info($page_url)
	{
		$url = explode("-",$page_url);
		return Array("novel_id"=>$url[1], "novel_page"=>$url[2]);
	}
};
//$aa = new Novel;
//$novel_list = $aa->download_novel("thread-2866217-1-1.html");
//$aa->search("神",2);
//print_r($novel_list);

?>