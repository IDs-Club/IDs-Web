<?php
	header('Content-Type:application/json; charset=UTF-8');
	require_once('db_lib.php');
	$oDB = new db;
	
	
	function print_r_html($arr)
	{
	?><pre><?
		print_r($arr);
	?></pre><?
	}
	
	$method     = $_GET['method'];
	$auth_token = $_GET['auth_token'];
	$isbn       = $_GET['isbn'];
	$userid     = $_GET['userid'];
	$fullname   = $_GET['fullname'];
	$outdate    = $_GET['outdate'];
	$duedate    = $_GET['dudate'];
	$callback   = $_GET['callback'];
	
	//$method='getBookByIsbn';
	//$isbn = '9787505962866';
	//$auth_token='fa41eade0f06e181360e0a0d1b387007';
	//$callback='callback';
	//print_r_html($jsonobj);
	if ($auth_token != 'fa41eade0f06e181360e0a0d1b387007') {
		if ($callback != '')
			echo $callback . '({"msg":"auth failed"})';
		else
			echo '({"msg":"auth failed"})';
		
		exit;
	}
	//testing
	
	
	if ($method == 'getBookByIsbn') {
		$result = $oDB->select("SELECT * FROM books WHERE isbn10='$isbn' OR  isbn13 = '$isbn' "); //mysql_query("SELECT * FROM books WHERE isbn10=7505715666 OR  isbn13 =7505715666 ");         
		if (mysqli_num_rows($result)) {
			
			while ($row = mysqli_fetch_assoc($result)) {
				$row_set['isbn10']        = $row['isbn10'];
				$row_set['isbn13']       = $row['isbn13'];
				$row_set['pubdate']      = $row['pubdate'];
				$row_set['img']          = $row['img'];
				$row_set['title']        = urlencode($row['title']);
				$row_set['description']  = urlencode(str_replace('"','&rdquo;',$row['description'])); 
				$row_set['author'] = urlencode(str_replace('"','&rdquo;',$row['author']));
				
			}
			//print_r($row_set[]);
			$json = json_encode($row_set);
			//print_r_html($row_set);
			if ($callback != '')
				echo $callback . '(' . str_replace(PHP_EOL, '<br>', urldecode($json) ). ')';
			else
				echo str_replace(PHP_EOL, '<br>', urldecode($json) );
			
		} else {
			//echo "'$jsonobj->isbn10','$jsonobj->isbn13', '$jsonobj->title','$jsonobj->pubdate'," .$jsonobj->images->small;
			
			$url = "https://api.douban.com/v2/book/isbn/" . $isbn;
			$ch  = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			if (!$output = curl_exec($ch)) {
				if ($callback != '')
					echo $callback . '({"msg":"api load failed"})';
				else
					echo '({"msg":"api load failed"})';
			}
			curl_close($ch);
			
			$jsonobj = json_decode($output);
			
			
			if ($jsonobj) {
				$img = $jsonobj->image;
				$author = implode(",",$jsonobj->author);
				$oDB->insert('books', 'isbn10,isbn13, title, pubdate, img ,description ,author', "'$jsonobj->isbn10','$jsonobj->isbn13', '$jsonobj->title','$jsonobj->pubdate','$img','$jsonobj->summary','$author'");
				
				$result = $oDB->select("SELECT * FROM books WHERE isbn10='$jsonobj->isbn10' OR  isbn13 = '$jsonobj->isbn13' ");
				
			while ($row = mysqli_fetch_assoc($result)) {
				$row_set['isbn10']        = $row['isbn10'];
				$row_set['isbn13']       = $row['isbn13'];
				$row_set['pubdate']      = $row['pubdate'];
				$row_set['img']          = $row['img'];
				$row_set['title']        = urlencode($row['title']);
				$row_set['description']  = urlencode(str_replace('"','&rdquo;',$row['description'])); 
				$row_set['author'] = urlencode(str_replace('"','&rdquo;',$row['author']));
				
			}
				$json = json_encode($row_set);
			//print_r_html($row_set);
				if ($callback != '')
					echo $callback . '(' . str_replace(PHP_EOL, '<br>', urldecode($json) ). ')';
				else
					echo str_replace(PHP_EOL, '<br>', urldecode($json) );
				
			}
			
			
		}
	}
	//end getbook
	
	else if ($method == 'getAllBooks') {
		$result = $oDB->select("SELECT * FROM books LEFT OUTER JOIN loan ON ( books.isbn10 = loan.isbn OR books.isbn13 = loan.isbn ) ");
		if (mysqli_num_rows($result)) {
			$row_set  = array();
			$i = 0;
			while ($row = mysqli_fetch_assoc($result)) {
				$row_set[$i]['isbn10']        = $row['isbn10'];
				$row_set[$i]['isbn13']       = $row['isbn13'];
				$row_set[$i]['pubdate']      = $row['pubdate'];
				$row_set[$i]['img']          = $row['img'];
				$row_set[$i]['userid']       = $row['userid'];
				$row_set[$i]['out_date']     = $row['out_date'];
				$row_set[$i]['due_date']     = $row['due_date'];
				$row_set[$i]['title']        = urlencode($row['title']);
				$row_set[$i]['description']  = urlencode(str_replace('"','&rdquo;',$row['description'])); 
				$row_set[$i]['author'] = urlencode(str_replace('"','&rdquo;',$row['author']));
				$i++;
			}
			//print_r($row_set[]);
			$json = json_encode($row_set);
			//print_r_html($row_set);
			if ($callback != '')
				echo $callback . '(' . str_replace(PHP_EOL, '<br>', urldecode($json) ). ')';
			else
				echo str_replace(PHP_EOL, '<br>', urldecode($json) );
			
		}
		
	} 
	else if ($method == 'loanBook') {
		if ($oDB->in_table(" loan ", " isbn ='$isbn' "))
			$oDB->update('loan', " isbn='$isbn' , userid='$userid', out_date = '$outdate'", " isbn ='$isbn' ");
		else
			$oDB->insert('loan', 'isbn , userid, out_date', "'$isbn','$userid','$outdate'");
		
		if ($callback != '')
			echo $callback . '({"msg":"update successfully"})';
		else
			echo '({"msg":"update successfully"})';
	} 
	else if ($method == 'returnBook') {
		$oDB->update('loan', " due_date = '$duedate' ", " isbn ='$isbn' ");
		if ($callback != '')
			echo $callback . '({"msg":"Book returned"})';
		else
			echo '({"msg":"Book returned"})';
	}
    else if ($method == 'addUser') {
		if (!$oDB->in_table(" member ", " userid ='$userid' ")) {
			$oDB->insert('member', 'userid , fullname ', " '$userid' ,'$fullname' ");
			if ($callback != '')
				echo $callback . '({"msg":"User added successfully"})';
			else
				echo '({"msg":"User added successfully"})';
		} else {
			if ($callback != '')
				echo $callback . '({"msg":"User already existed"})';
			else
				echo '({"msg":"User already existed"})';
		}
		
	}
	 else if ($method == 'getUsers') {
		 
		 
	}
	
?>