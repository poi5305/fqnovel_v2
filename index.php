<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>卡提諾小說</title>

<link rel="stylesheet" href="themes/css/jqtouch.css" title="jQTouch">
<!-- <script src="src/lib/zepto.min.js" type="text/javascript" charset="utf-8"></script> -->
<!-- <script src="src/jqtouch.min.js" type="text/javascript" charset="utf-8"></script> -->
<!-- Uncomment the following two lines (and comment out the previous two) to use jQuery instead of Zepto. -->
<script src="src/lib/jquery-1.7.min.js" type="application/x-javascript" charset="utf-8"></script>
<script src="src/jqtouch.js" type="text/javascript" charset="utf-8"></script>
<script src="src/jqtouch-jquery.min.js" type="application/x-javascript" charset="utf-8"></script>


<!--
<link type="text/css" rel="stylesheet" media="screen" href="css/jqtouch.css">
<link type="text/css" rel="stylesheet" media="screen" href="css/themes/apple/theme.css">

<script src="js/jquery.js" type="text/javascript" charset="utf-8"></script>
<script src="js/jqtouch.js" type="application/x-javascript" charset="utf-8"></script>
-->

<script>
	var jQT = new $.jQTouch({
        icon: 'ck101.png',
		icon4: 'ck101.png',
        addGlossToIcon: false,
        startupScreen: 'jqt_startup.png',
        statusBar: 'black-translucent',
        themeSelectionSelector: '#jqt #themes ul',
        preloadImages: []
    });
//	var jQT = $.jQTouch({
//		icon: 'ck101.png',
//		icon4: 'ck101.png',
//		statusBar: 'black',
//		addGlossToIcon: false,
//	});

	$(function(){
		dlHref = "";
		tid=0;
		fp=1;
		tp=1;
		localStorage.dlHref = "";
		if(localStorage.page==undefined)localStorage.page=1;
		$("#last").html("<a href='#'>"+"目前頁數 "+localStorage.page+" 上一頁</a>");
		$("#next").html("<a href='#'>"+"目前頁數"+localStorage.page+" 下一頁</a>");
		$("#last").bind("click",function(){
			if(localStorage.page<=1)return 0;
			localStorage.page--;
			$("#last").html("<a href='#'>"+"目前頁數 "+localStorage.page+" 上一頁</a>");
			$("#next").html("<a href='#'>"+"目前頁數"+localStorage.page+" 下一頁</a>");
			readList();
		});
		$("#next").bind("click",function(){
			localStorage.page++;
			$("#last").html("<a href='#'>"+"目前頁數 "+localStorage.page+" 上一頁</a>");
			$("#next").html("<a href='#'>"+"目前頁數"+localStorage.page+" 下一頁</a>");
			readList();
		});
		$("#bookListUpd").click(function(){
			readBookList();
		});
		$("#download").unbind().bind("click touchstart",function(){
			if(dlHref == localStorage.dlHref){
				//alert("剛剛已經下載過摟！！請下載別的，或到書庫下載！！");
			}else{
				localStorage.dlHref = dlHref;
				console.log("fqnovel.php?type=download_novel&page_url="+dlHref);
				$.ajax({
					url: "fqnovel.php?type=download_novel&page_url="+dlHref,
					timeout:10*60*1000
				})
				.done(function(){
					alert("下載完成");
				});
				alert("下載中，可以先看簡介或到書庫查看是否下載完");
			}
		});
		$("#partDownload").bind("touchstart",function(){
			$("#partDownload").attr("href","include/ck101_get.php?type=download&tid="+tid+"&partFrom="+$("#partFrom").val()+"&partTo="+$("#partTo").val());
		});
		$("#fullDownload").tap(function(){
			localStorage["tid_"+tid] = "從"+fp+"到"+tp;
			$("#bookRecord").html("上次下載記錄："+localStorage["tid_"+tid]);
		});
		$("#partDownload").tap(function(){
			localStorage["tid_"+tid] = "從"+$("#partFrom").val()+"到"+$("#partTo").val();
			$("#bookRecord").html("上次下載記錄："+localStorage["tid_"+tid]);
		});
		$("#deleteBook").tap(function(){
			$.get("include/ck101_get.php?type=delete&tid="+tid);
			jQT.goBack();
			readBookList();
		});
		$("#search").change(function(){
			if($(this).val() == ""){
				readList();
			}else{
				searchList();
			}
		});
		
		console.log(localStorage);
		
		readList();
		readBookList();
	});
	function readList(){
		$("#novelList").html("<li>讀取列表中...請稍後</li>");
		
		$.get("fqnovel.php?type=get_novel_list&page="+localStorage.page,function(data){
			$("#novelList").html("");
			var obj = JSON.parse(data);
			//console.log(obj)
			for(var key in obj)
			{
				$("#novelList").append(function(){
					var html = "";
					html+="<li class='arrow'>";
					html+="<a id='"+obj[key]["page_url"]+"' href='#novelInfo' class='noveList' style='font-size:12px;'>";
					html+=obj[key]["class"] + " [文章/人氣]"+ "[" + obj[key]["pages"] + "/" + obj[key]["nums"] + "]";
					html+="<br>";
					html+=obj[key]["name"];
					html+="</a>";
					html+="</li>";	
					return html;
				});
			}
			$(".noveList").unbind();
			$(".noveList").bind("touchstart click",function(){
				dlHref = $(this).attr("id");
				$("#noveInfoTitle").html($(this).html());
				$("#noveInfoContext").html("讀取中...請稍後");
				//console.log("fqnovel.php?type=get_novel_info_content&length=3000&page_url="+dlHref);
				$.get("fqnovel.php?type=get_novel_info_content&length=3000&page_url="+dlHref)
				.done(function(data){
					$("#noveInfoContext").html(data);
				});
			});
		});
	}
	function searchList(){
		$("#novelList").html("<li>搜尋中...請稍後</li>");
		$("#novelList").load("include/ck101_get.php?type=search&value="+$("#search").val(),function(){
			$(".noveList").bind("touchstart",function(){
				dlHref = $(this).attr("id");
				$("#noveInfoTitle").html($(this).html());
				$("#noveInfoContext").html("讀取中...請稍後");
				$("#noveInfoContext").load("include/ck101_get.php?type=getInfo&url="+dlHref);
			});
		});
	}
	function readBookList(){
		$("#bookList").html("<li>讀取列表中...請稍後</li>");
		
		$.get("fqnovel.php?type=get_book_list")
		.done(function(data){
			var obj = JSON.parse(data);
			$("#bookList").html("");
			for(var key in obj)
			{
				var list = obj[key];
				$("#bookList").append(function(){
					var html = "";
					html+="<li class='forward'>";
					html+="<a id='book_"+list["novel_id"]+"' href='#bookManger' class='bookList' style='font-size:12px;'>";
					html+="篇數：<span class='totalPage' >"+list["pages"]+"</span> ";
					html+="人氣：<span class='nums' >"+list["nums"]+"</span> ";
					html+="狀態：";
					if(list["download_page"] == list["pages"]){
						if(list["type"]==0)html += "連載中 點擊下載";
						else html += "已完結";
					}else{
						html+="[下載中 ]"+list["download_page"]+"/"+list["pages"];	
					}
					html+="<br>";
					html+=list["name"];
					html+="</a>";
					html+="</li>";
					return html;
				});
			}
			setBookManger();
		});
		
		//$("#bookList").load("include/ck101_get.php?type=getBookList",function(){
		//	setBookManger();
		//});
	}
	function setBookManger(){
		$(".bookList").unbind().bind("touchstart click",function(){
			var tmp = $(this).attr("id").split("_");
			tid = tmp[1];
			tp = $(this).find(".totalPage").html();
			//$("#bookRecord").html("第一次下載此小說");
			
			var url = "fqnovel.php?type=get_novel&novel_id="+tid;
			var gurl = "ghttp://"+location.host + location.pathname + "fqnovel.php?type=get_novel&novel_id="+tid;
			$("#fullDownload").attr("href",url);
			$("#gfullDownload").attr("href",gurl);
			
			//
			$("#bookMangerInfo").html($(this).html());
			if(localStorage["tid_"+tid] == undefined){
				$("#bookRecord").html("第一次下載此小說");
			}else{
				$("#bookRecord").html("上次下載記錄："+localStorage["tid_"+tid]);
			}
			dlHref = "thread-"+tid+"-1-1.html";
			$("#bookUpd").unbind().bind("touchstart click",function(){
				if(dlHref == localStorage.dlHref){
					alert("剛剛已經下載過摟！！請下載別的，或到書庫下載！！");
				}else{
					localStorage.dlHref = dlHref;
					console.log("fqnovel.php?type=download_novel&page_url="+dlHref);
					$.ajax({
						url: "fqnovel.php?type=download_novel&page_url="+dlHref,
						timeout:10*60*1000
					})
					.done(function(){
						alert("更新完成");
					});
					alert("更新小說中！！");
				}
			});
			
		});
	}

</script>
<link rel="stylesheet" type="text/css" href="css/iphone.css" media="screen" />
</head>

<body>
	<div id="home">
    	<div class="toolbar">
            <h1>卡提諾小說</h1>
            <a href="#book" class="button">書庫</a>
        </div>
        <ul>
        	<li><input type="text" id="search" placeholder="搜尋" /></li>
        	<li id="last"></li>
        </ul>
        <ul id="novelList" class="edgetoedge">
        </ul>
        <ul><li id="next"></li></ul>
    </div>
    
	<div id="novelInfo">
    	<div class="toolbar">
        	<a href="#" class="back">首頁</a>
            <h1>小說介紹</h1>
            <a id="download" href="#" class="button">下載</a>
        </div>
        <div id="noveInfoTitle" style="background:#FFF; margin-bottom:10px; text-align:center;"></div>
        <div id="noveInfoContext" style="padding:10px; ">
        </div>
    </div>
    <div id="book">
    	<div class="toolbar">
        	<a href="#" class="back">首頁</a>
            <h1>書庫</h1>
            <a id="bookListUpd" href="#" class="button">更新</a>
        </div>
        <ul id="bookList">
        
        </ul>
    </div>
    <div id="bookManger">
    	<div class="toolbar">
        	<a id="back" href="#" class="back">回書庫</a>
            <h1>小說下載</h1>
            <a id="bookUpd" href="#" class="button">更新</a>
        </div>
        <ul class="rounded">
        	<li id="bookMangerInfo"></li>
            <li id="bookRecord"></li>
        </ul>
        <ul class="rounded">
        	<li>
                從<input type="tel" id="partFrom" value="1" style="width:30px;" />
                到<input type="tel" id="partTo" value="2" style="width:30px;" />
            </li>
        	<li><a id="partDownload" target="_blank" href="#">分段下載此小說</a></li>
        	<li><a id="fullDownload" target="_blank" href="#">完整下載此小說</a></li>
        	<li><a id="gpartDownload" target="_blank" href="#">GoodReader 分段下載</a></li>
        	<li><a id="gfullDownload" target="_blank" href="#">GoodReader 完整下載</a></li>
        </ul>
        <ul class="rounded">
        	<li><a id="deleteBook" href="#">移除此小說</a></li>
        </ul>
    </div>
</body>
</html>






