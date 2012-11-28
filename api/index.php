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
$outdate    = $_GET['outdate'];
$duedate    = $_GET['duedate'];
$callback   = $_GET['callback'];
$firstname  = $_GET['firstname'];
$lastname   = $_GET['lastname'];
$company    = $_GET['company'];
$phone      = $_GET['phone'];
$email      = $_GET['email'];
$address    = $_GET['address'];
$city       = $_GET['city'];
$state      = $_GET['state'];
$postcode   = $_GET['postcode'];
$userid     = $_GET['userid'];
$quantity   = $_GET['quantity'];
$sortby     = $_GET['sortby'];
$page       = $_GET['page'];
$pageitems = $_GET['pageitems'];

/*
$method= 'getAllBooks';//'getBookByIsbn';//'getAllBooks';
$isbn = '9787505962866';
$sortby='visit';
$page = 1;
$pageitems=10;
$auth_token='fa41eade0f06e181360e0a0d1b387007';*/
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
    $result = $oDB->select("SELECT * FROM books LEFT OUTER JOIN category on books.catid = category.catid WHERE isbn10='$isbn' OR  isbn13 = '$isbn' "); //mysql_query("SELECT * FROM books WHERE isbn10=7505715666 OR  isbn13 =7505715666 ");         
    if (mysqli_num_rows($result)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $row_set['isbn10']      = $row['isbn10'];
            $row_set['isbn13']      = $row['isbn13'];
            $row_set['pubdate']     = $row['pubdate'];
            $row_set['img']         = $row['img'];
            $row_set['stock']       = $row['stock'];
			$row_set['visit']       = $row['visit'];
            $row_set['title']       = urlencode($row['title']);
			$row_set['cat_name']    = urlencode($row['cat_name']);
            $row_set['description'] = urlencode(str_replace('"', '&rdquo;', $row['description']));
            $row_set['author']      = urlencode(str_replace('"', '&rdquo;', $row['author']));
            
        }
        //print_r($row_set[]);
        $json = json_encode($row_set);
		
		$oDB->update('books'," isbn10='$isbn' OR  isbn13 = '$isbn' ", " visit = visit + 1 " );
		  
        //print_r_html($row_set);
        if ($callback != '')
            echo $callback . '(' . str_replace(PHP_EOL, '<br>', urldecode($json)) . ')';
        else
            echo str_replace(PHP_EOL, '<br>', urldecode($json));
        
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
            $img    = $jsonobj->image;
            $author = implode(",", $jsonobj->author);
            $oDB->insert('books', 'isbn10,isbn13, title, pubdate, img ,description ,author, stock', "'$jsonobj->isbn10','$jsonobj->isbn13', '$jsonobj->title','$jsonobj->pubdate','$img','$jsonobj->summary','$author',1");
            
            $result = $oDB->select("SELECT * FROM books LEFT OUTER JOIN category on books.catid = category.catid WHERE isbn10='$jsonobj->isbn10' OR  isbn13 = '$jsonobj->isbn13' ");
            
            while ($row = mysqli_fetch_assoc($result)) {
                $row_set['isbn10']      = $row['isbn10'];
                $row_set['isbn13']      = $row['isbn13'];
                $row_set['pubdate']     = $row['pubdate'];
                $row_set['img']         = $row['img'];
                $row_set['stock']       = $row['stock'];
				$row_set['visit']       = $row['visit'];
				$row_set['cat_name']    = urlencode($row['cat_name']);
                $row_set['title']       = urlencode($row['title']);
                $row_set['description'] = urlencode(str_replace('"', '&rdquo;', $row['description']));
                $row_set['author']      = urlencode(str_replace('"', '&rdquo;', $row['author']));
                
            }
            $json = json_encode($row_set);
			
				$oDB->update('books'," isbn10='$isbn' OR  isbn13 = '$isbn' ", " visit = visit + 1 " );
            //print_r_html($row_set);
            if ($callback != '')
                echo $callback . '(' . str_replace(PHP_EOL, '<br>', urldecode($json)) . ')';
            else
                echo str_replace(PHP_EOL, '<br>', urldecode($json));
            
        }
        
        
    }
}
//end getbook

else if ($method == 'getCategories') {
    $result = $oDB->select("SELECT cat_name FROM category order by cat_name"); //mysql_query("SELECT * FROM books WHERE isbn10=7505715666 OR  isbn13 =7505715666 ");       
    if (mysqli_num_rows($result)) {
        while ($row = mysqli_fetch_assoc($result)) {
			$row_set['cat_name']    = urlencode($row['cat_name']);           
        }
        //print_r($row_set[]);
        $json = json_encode($row_set);
	  
        //print_r_html($row_set);
        if ($callback != '')
            echo $callback . '(' . str_replace(PHP_EOL, '<br>', urldecode($json)) . ')';
        else
            echo str_replace(PHP_EOL, '<br>', urldecode($json));
        
 }

else if ($method == 'deleteBook') {
    if ($oDB->in_table(" loan ", " isbn ='$isbn' and quantity >0")) {
        if ($callback != '')
            echo $callback . '({"msg":"The book is on loan, you are not able to delete it."})';
        else
            echo '({"msg":"The book is on loan, you are not able to delete it"})';
    } else {
        $oDB->delete("delete from books where isbn10 ='$isbn' or isbn13= '$isbn' ");
        if ($callback != '')
            echo $callback . '({"msg":"Book was removed"})';
        else
            echo '({"msg":"Book was removed"})';
        
    }
    
}

else if ($method == 'getAllBooks') {
    //$result = $oDB->select("SELECT * FROM books LEFT OUTER JOIN loan ON ( books.isbn10 = loan.isbn OR books.isbn13 = loan.isbn ) ");
	 $sSql =  "SELECT books.*,category.*
					FROM books
					LEFT OUTER JOIN category ON books.catid = category.catid "  ;  
	
	if($sortby){
		if($sortby=='record'){
			$sSql =  "SELECT books.*,category.*, count(loan.loanid) as count 
					FROM books
					LEFT OUTER JOIN category ON books.catid = category.catid
					LEFT JOIN loan ON ( loan.isbn = books.isbn10
					OR loan.isbn = books.isbn13 )  group by loan.loanid
					order by count desc";
		}
		else
		{
			 
		   $sSql =  $sSql .  " order by $sortby ";
		   
		}	
	}
	if($page && $pageitems){
		$start =  ($page -1) * $page;
		$end =  $pageitems * $page - 1;
		$sSql =  $sSql .  " LIMIT $start ,  $end" ;
	}
 
	//print_r($sSql);
    $result = $oDB->select($sSql);
    if (mysqli_num_rows($result)) {
        $row_set = array();
        $i       = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $row_set[$i]['isbn10']      = $row['isbn10'];
            $row_set[$i]['isbn13']      = $row['isbn13'];
            $row_set[$i]['pubdate']     = $row['pubdate'];
            $row_set[$i]['img']         = $row['img'];
            $row_set[$i]['stock']       = $row['stock'];
			$row_set['cat_name']    = urlencode($row['cat_name']);
            $row_set[$i]['title']       = urlencode($row['title']);
            $row_set[$i]['description'] = urlencode(str_replace('"', '&rdquo;', $row['description']));
            $row_set[$i]['author']      = urlencode(str_replace('"', '&rdquo;', $row['author']));
            $i++;
        }
        //print_r($row_set[]);
        $json = json_encode($row_set);
        //print_r_html($row_set);
        if ($callback != '')
            echo $callback . '(' . str_replace(PHP_EOL, '<br>', urldecode($json)) . ')';
        else
            echo str_replace(PHP_EOL, '<br>', urldecode($json));
        
    }
    
} else if ($method == 'loanBook') {
    if ($oDB->in_table(" loan ", " isbn ='$isbn' and userid = $userid")) {
        $oDB->update('loan', " isbn ='$isbn' and userid = $userid", " out_date = '$outdate' , quantity = quantity + $quantity ");
        $oDB->update('books', " isbn10='$isbn' OR  isbn13 = '$isbn'", " stock = stock - $quantity ");
    } else {
        $oDB->insert('loan', 'isbn , userid, out_date , quantity', "'$isbn','$userid','$outdate' , $quantity");
        $oDB->update('books', " isbn10='$isbn' OR  isbn13 = '$isbn'", " stock = stock - $quantity ");
    }
    
    
    if ($callback != '')
        echo $callback . '({"msg":"update successfully"})';
    else
        echo '({"msg":"update successfully"})';
} else if ($method == 'returnBook') {
    $oDB->update('loan', " userid = '$userid'  and isbn = '$isbn'  ", " due_date ='$duedate' , quantity = quantity - $quantity");
    $oDB->update('books', " isbn10='$isbn' OR  isbn13 = '$isbn'", " stock = stock + $quantity ");
    if ($callback != '')
        echo $callback . '({"msg":"Book returned"})';
    else
        echo '({"msg":"Book returned"})';
}

else if ($method == 'addUser') {
    if (!$oDB->in_table(" member ", " firstname ='$firstname' and lastname = '$lastname' ")) {
        $oDB->insert('member', ' firstname , lastname , company , phone , email ,address ,city , state , postcode  ', " '$firstname' , '$lastname' , '$company' , '$phone' , '$email' ,'$address' ,'$city' , '$state' , '$postcode' ");
        if ($callback != '')
            echo $callback . '({"msg":"User added successfully"})';
        else
            echo '({"msg":"User saved successfully"})';
    } else {
        if ($callback != '')
            echo $callback . '({"msg":"User already existed"})';
        else
            echo '({"msg":"User already existed"})';
    }
    
} else if ($method == 'editUser') {
    $oDB->update('member', " userid='$userid'  ", " firstname='$firstname' , lastname='$lastname' , company='$company' , phone='$phone' , email= '$email' , address='$address' ,city='$city' , state='$state' ,postcode= '$postcode' ");
    if ($callback != '')
        echo $callback . '({"msg":"User saved successfully"})';
    else
        echo '({"msg":"User added successfully"})';
    
    
}

else if ($method == 'getLoans') {
    $result = $oDB->select("SELECT * 
                                    FROM loan
                                    LEFT JOIN member ON member.userid = loan.userid
                                    LEFT JOIN books ON ( loan.isbn = books.isbn10
                                    OR loan.isbn = books.isbn13 )  order by out_date desc  ");
    
    if (mysqli_num_rows($result)) {
        $row_set = array();
        $i       = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $row_set[$i]['userid']    = $row['userid'];
            $row_set[$i]['isbn']      = $row['isbn'];
            $row_set[$i]['loanid']    = $row['loanid'];
            $row_set[$i]['out_date']  = $row['out_date'];
            $row_set[$i]['due_date']  = $row['due_date'];
            $row_set[$i]['quantity']  = $row['quantity'];
            $row_set[$i]['firstname'] = $row['firstname'];
            $row_set[$i]['lastname']  = $row['lastname'];
            $row_set[$i]['title']     = urlencode($row['title']);
            $i++;
        }
        //print_r($row_set[]);
        $json = json_encode($row_set);
        //print_r_html($row_set);
        if ($callback != '')
            echo $callback . '(' . str_replace(PHP_EOL, '<br>', urldecode($json)) . ')';
        else
            echo str_replace(PHP_EOL, '<br>', urldecode($json));
        
    }
    
}

else if ($method == 'getUsers') {
    $result = $oDB->select("SELECT member. * , SUM( loan.quantity ) AS count
                                    FROM member
                                    LEFT OUTER JOIN loan ON member.userid = loan.userid
                                    GROUP BY member.userid");
    
    if (mysqli_num_rows($result)) {
        $row_set = array();
        $i       = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $row_set[$i]['userid']    = $row['userid'];
            $row_set[$i]['fullname']  = $row['fullname'];
            $row_set[$i]['firstname'] = $row['firstname'];
            $row_set[$i]['lastname']  = $row['lastname'];
            $row_set[$i]['company']   = $row['company'];
            $row_set[$i]['phone']     = $row['phone'];
            $row_set[$i]['email']     = $row['email'];
            $row_set[$i]['address']   = $row['address'];
            $row_set[$i]['city']      = $row['city'];
            $row_set[$i]['state']     = $row['state'];
            $row_set[$i]['postcode']  = $row['postcode'];
            $row_set[$i]['count']     = $row['count'];
            $i++;
        }
        //print_r($row_set[]);
        $json = json_encode($row_set);
        //print_r_html($row_set);
        if ($callback != '')
            echo $callback . '(' . str_replace(PHP_EOL, '<br>', urldecode($json)) . ')';
        else
            echo str_replace(PHP_EOL, '<br>', urldecode($json));
        
    }
    
}

?>