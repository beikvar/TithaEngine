<?php include_once("conf.php");


/*
* TithaEngine vBETA-1
* https://github.com/beikvar/TithaEngine
*
*   Copyright 2021 Beikvar
*
*   Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
*   You may obtain a copy of the License at
*
*       http://www.apache.org/licenses/LICENSE-2.0
*
*   Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
*   See the License for the specific language governing permissions and limitations under the License.
*/



// Tools


/* Post ID generator */
function genID($n) {
  $chars="0123456789abcdefghijklmnopqrstuvwxyz";
  $id = ''; $thing=0;
  while($thing==0){
    for ($i=0; $i<$n; $i++) {
      $r = rand(0, strlen($chars)-1);
      $id .= $chars[$r];
    }
    if(strpos(file_get_contents("db/posts/index.txt"),"$id\n") === false){
      $thing=1;
    }
  }
  return $id;
}
function genUID($n){
  $id = genID($n);
  while(file_exists("db/posts/" . $id . ".json") == true){
    genID($n);
  };
  return $id;
}

/* Key generator */

function genKey($n) {
  $chars="0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz;!#$?%:*,_.";
  $id = ''; $thing=0;
  while($thing==0){
    for ($i=0; $i<$n; $i++) {
      $r = rand(0, strlen($chars)-1);
      $id .= $chars[$r];
    }
    if(strpos(file_get_contents("db/posts/index.txt"),"$id\n") === false){
      $thing=1;
    }
  }
  return $id;
}

/* Graphics stuff */
function error() {
  echo '<div><img style="text-align:center" src="img/404.png" width="30%"><br><p>Not found</p></div>';
}
function banner() {
	$MessageArray = file_get_contents("db/banners/index.txt");
  $CurrMsg = explode("\n", $MessageArray);

	// Variable that ounts the number of banners avitable
	$count = count($CurrMsg);

	// Choose a random banner, open info file and show
  $Content = json_decode(file_get_contents("db/banners/" . $CurrMsg[rand(0, $count-1)] . ".json"));
  return '<div id="banner"><a href="' . $Content->{"link"} . '"><img src="' . $Content->{"url"} . '" alt=""></a></div>';
}
function goHome() {
  return '<a href="?" style="color:black;text-decoration:none"><= <img style="text-align:center" src="img/logo.png" height="10px"></a>';
}

/* Show all board's codes */
function boardList(){
	$MessageArray = file_get_contents("db/boards/index.txt");
  $CurrMsg = explode("\n", $MessageArray);

	// Variable that simply counts the number of items in the $MessageArray
	$count = count($CurrMsg);

  // Return variable
  $vaa = "[ ";

	// For every message that array has, we will loop to create a new <div> element and create the content
	for($i = 0; $i < $count; ++$i) {
    // Open content
    $vaa .= "<a href=\"?board=$CurrMsg[$i]\">/$CurrMsg[$i]/</a> ";
  }
  $vaa .= "]<span style=\"margin:0 0 0 10px\">[<a href=\"?reglas\">ToS</a>]</span>";
  return $vaa;
}




?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link rel="icon" href="img/icon.png">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="styles.css">
<title><?= $title ?></title>
<style>
<?= $style ?>
</style>
</head>
<body>
  <!--[<a href="?action=Remove%20Duplicates">Remove Duplicates</a>]<p>[<a href="index.php?action=Delete%20First">Delete First Message]</p>

	<p><a href="index.php?action=Delete%20Last"> Delete Last Message </a></p>-->
	<?php
	/*if(isset($_GET["action"])) {
		if((file_exists("db/posts/index.txt")) && (filesize("db/posts/index.txt") != 0)) {
			// A button has been clicked on and the file messages.txt exists with content.
			$MessageArray = file("db/posts/index.txt");
			switch($_GET["action"]) {
				case "Delete First":
				array_shift($MessageArray);
				break;
				case "Delete Last":
				array_pop($MessageArray);
				break;
				case "Delete Message":
					if(isset($_GET["message"])) {
            unlink("db/posts/$_GET[message].json");
						//array_splice($MessageArray, $_GET["messages"], 1);
					}
					break;
				case "Remove Duplicates":
				$MessageArray = array_unique($MessageArray);
				$MessageArray = array_values($MessageArray);
					break;

			} // End of switch


			if(count($MessageArray) > 0) {
				$NewMessages = implode($MessageArray);
				$MessageStore = fopen("db/posts/index.txt", "wb");
				// Check if the file is not accessible 
				if($MessageStore === FALSE) {
					echo "There was an error updating the message file!\n";
				}
				else {
					fwrite($MessageStore, $NewMessages);
					fclose($MessageStore);
				}
			/*}
			else {
				unlink("db/index.txt");
			}
		}
	}*/




  if(isset($_GET["post"])){
    // Post page
		if(isset($_GET["t"]) && file_exists("db/posts/$_GET[t].json")){$t=true;}else{$t=false;}
		if(isset($_POST["submit"])) {

      // Create ID
      $id = genUID(6);

			$Subject = stripslashes($_POST["subject"]);
			$Name = stripslashes($_POST["name"]);
			$Message = stripslashes($_POST["message"]);

			// HTMLfy and put the "anon" flair to some users
			$Subject = htmlentities($Subject);
      if($Name=="") {
        $Name="anon";
      } else {
        $Name = htmlentities($Name);
      }
      $Message = htmlentities($Message);

      if (strpos($Message, "\n") !== false) {
        $Message = str_replace("\n", "<br>", $Message);
      }

			// Create a variable that serves a single line of data for us to save to the file
			if($t){
				$res='"' . $_GET["t"] . '"';
				$b_i = json_decode(file_get_contents("db/posts/" . $_GET["t"] . ".json"));
				$board = $b_i->{"board"};
      }else{
				$res='false';
				$board = $_POST["board"];
			}
      $key = password_hash($_POST["key"], PASSWORD_DEFAULT);
			$date = date(DATE_ATOM);
			$MessageRecord = "{\"board\":\"$board\",\"res\":$res,\"subject\":\"$Subject\",\"date\":\"$date\",\"name\":\"$Name\",\"utype\":0,\"msg\":\"$Message\",\"key\":\"$key\"}";

			// Create up the file index.txt and save the file data into a variable
			$DatabaseFile = fopen("db/posts/index.txt", "ab");
      $MessageFile = fopen("db/posts/$id.json", "w+");

			// Check to see if there are issues accessing that file. If so, then handle the error. If not, then post the message.

      // Save ID on Database
			if($DatabaseFile === FALSE) {
				echo "There was an error saving your message!\n";
			} else {
				fWrite($DatabaseFile, "\n$id");
				fclose($DatabaseFile);
				echo "Your message has been saved! \n";
			}

      // Save message on Database
      if($MessageFile === FALSE){
        echo "error";
			} else {
				fWrite($MessageFile, $MessageRecord);
				fclose($MessageFile);
			}

			if($t){header("location: ?t=" . $_GET["t"]);}else{header("location: ?t=" . $id);}
		}
	?>

	<h1> Post New Message </h1>
	<hr/>
	<form action="?post<?php if($t){echo '&t=' . $_GET['t'];} ?>" method="POST">
<?php
if(!$t){
echo '		<label style="font-weight:bold" for="board">Board: </label>' . "\n" . '		<select name="board">' . "\n";
// Open index of categories
$Boards = file_get_contents("db/boards/index.txt");
	$Boards_List = explode("\n", $Boards);
	$Boards_Count = count($Boards_List);
	for($i = 0; $i < $Boards_Count; ++$i) {
	echo '			<option value="' . $Boards_List[$i] . '">/' . $Boards_List[$i] . '/</option>' . $Boards_List[$i] . "</option>\n";
}
echo '		</select> <br />';
} ?>
		<label style="font-weight:bold" for="subject"> Subject: </label>
		<input type="text" name="subject"/> <br/>
		<label style="font-weight:bold" for="name"> Name: </label>
		<input type="text" name="name" /> <br/>
		<textarea name="message" rows="6" cols="50"></textarea><br/>
		<label style="font-weight:bold" for="key">Key: </label><input type="text" name="key" value="<?= genKey(8) ?>" /> <br/>
		Please, keep it in a safe space, it is obligatory to delete a post <br /> <br />
		<input type="submit" name="submit" value="Post Message" />
		<input type="reset" name="reset" value="Reset Form"/>
	</form>
	<hr/>
	<p><a href="index.php"> View Message </a>
<?php




  } elseif(isset($_GET["del"])){
    if(isset($_GET["p"]) && file_exists("db/posts/" . $_GET["p"] . ".json")){
      if(isset($_POST["key"])){
        $Content = json_decode(file_get_contents("db/posts/" . $_GET["p"] . ".json"));
        if(password_verify($_POST["key"], $Content->{"key"})) {
          unlink("db/posts/" . $_GET["p"] . ".json");
          echo "<h1>Post eliminado</h1>";
          header("location: ?board=" . $Content->{"board"});
        }else{
          echo "<h1>Invalid key</h1>";
        }
      }else{
        echo "<div style='text-align:center'><h2>Poné tu llave para eliminar tu post</h2><form action='?del&p=$_GET[p]' method='POST'><input type='password' name='key'><input type='submit'></form></div>";
      }
    }else{$t=false;}




  } elseif(isset($_GET["t"])) {
    // Thread page
if(!file_exists("db/posts/index.txt") || (filesize("db/posts/index.txt") == 0)) {
		echo "The are no things posted.</p>\n";
	} else {
		if(file_exists("db/posts/" . $_GET["t"] . ".json")){

		echo '   <div style="display:block;margin:0 0 10px 0">' . goHome() . ' <span style="float:right">[<a href="?post&t=' . $_GET["t"] . '">New respond</a>]</span></div>';
		$MessageArray = file_get_contents("db/posts/index.txt");
		echo "<div id=\"list\"> \n ";

    $CurrMsg = explode("\n", $MessageArray);

		// Variable that simply counts the number of items in the $MessageArray
		$count = count($CurrMsg);

    // Tests
    $fst=true;

		// For every message that array has, we will loop to create a new <div> element and create the content
		for($i = 0; $i < $count; ++$i) {
      // Open content
      $Content = json_decode(file_get_contents("db/posts/" . $CurrMsg[$i] . ".json"));
      
      // Check if it is a post or a reply
      if(($CurrMsg[$i]== $_GET["t"]) || (isset($Content->{"res"}) && $Content->{"res"} == $_GET["t"])){
        // Show post
			  if($fst){echo "<div class=\"item\">\n";}else{echo "<div class=\"rta\">\n";}
			  if($Content->{"utype"} != 0){
			    $ubdg = ' <img class="bdg" src="img/';
          if($Content->{"utype"} == 1){
            $aus = "Admin";
          }elseif($Content->{"utype"} == 2){
            $aus = "Mod";
          }elseif($Content->{"utype"} == 3){
            $aus = "Verificado";
          }
          $ubdg .= "$aus.png\" title=\"$aus\" alt=\"$aus\">";
			  }else{$ubdg = "";}
        if(!($fst)){echo "<a href=\"?del&p=$CurrMsg[$i]\">[X]</a>\n";}
			  echo "<span class=\"name\">" . $Content->{"name"} . $ubdg . "</span>\n";
        echo "<span class=\"subject\">" . $Content->{"subject"} . "</span>\n";
			  echo "<span class=\"date\">" . $Content->{"date"} . "</span>\n";
			  echo "<div class=\"msg\">" . $Content->{"msg"} . "</div>";
			  if($fst){echo "<br><span class=\"subp\">[<a href=\"?del&p=$CurrMsg[$i]\">Delete</a>]</span>\n";}
			  echo "</div> <hr />\n";
        $fst=false;
      }
		}
		echo"</div>\n";
		} else {error();}
	}




  } elseif(isset($_GET["board"])) {
    // Board page
if(file_exists("db/boards/" . $_GET["board"] . ".json")){

	echo '   <div style="display:block;margin:0 0 10px 0">' . goHome() . ' <span style="float:right">' . boardList() . '</span></div>';

  $board_info = json_decode(file_get_contents("db/boards/" . $_GET["board"] . ".json"));
  $banner = banner();
  echo '<div id="bhome">' . $banner . '<h1> /' . $_GET["board"] . '/ - ' . $board_info->{"title"} . '</h1><p>' . $board_info->{"description"} . '</p><h3>[<a href="?post">New thread</a>]</a></h3></div>';
  if(!file_exists("db/posts/index.txt") || (filesize("db/posts/index.txt") == 0)) {
		echo "The are no things posted.</p>\n";
	} else {
		$MessageArray = file_get_contents("db/posts/index.txt");
		echo "<div id=\"list\"> \n ";

    $CurrMsg = explode("\n", $MessageArray);

		// Variable that simply counts the number of items in the $MessageArray
		$count = count($CurrMsg);
    $s = $count-1;

		// For every message that array has, we will loop to create a new <div> element and create the content
		for($i = 0; $i < $count; ++$i) {
      // Open content
      $Content = json_decode(file_get_contents("db/posts/" . $CurrMsg[$s] . ".json"));
      
      // Check if it is a post or a reply
      if(!($Content->{"res"}) && ($Content->{"board"} == $_GET["board"])){
        // Show post
			  echo "<div class=\"item\">\n";
			  if($Content->{"utype"} != 0){
			    $ubdg = ' <img class="bdg" src="img/';
          if($Content->{"utype"} == 1){
            $aus = "Admin";
          }elseif($Content->{"utype"} == 2){
            $aus = "Mod";
          }elseif($Content->{"utype"} == 3){
            $aus = "Verificado";
          }
          $ubdg .= "$aus.png\" title=\"$aus\" alt=\"$aus\">";
			  }else{$ubdg = "";}
			  echo "<span class=\"name\">" . $Content->{"name"} . $ubdg . "</span>\n";
        echo "<span class=\"subject\">" . $Content->{"subject"} . "</span>\n";
			  echo "<span class=\"date\">" . $Content->{"date"} . "</span>\n";
			  echo "<div class=\"msg\">" . $Content->{"msg"} . "</div>";
			  echo "<br><span class=\"subp\">[<a href=\"?board=$_GET[board]&t=$CurrMsg[$s]\">Respond</a>] [<a href=\"?del&p=$CurrMsg[$s]\">Delete</a>]</span>\n";
			  echo "</div>\n";
      }
      $s--;
		}
		echo"</div>\n";
	}
}




  } elseif(isset($_GET["reglas"])) {
    include "reglas.html";



  } else {
    echo '<div style="text-align:center">' . "\n" . '<h1><a href="?"><span id="title"><img src="img/logo.png" height="30px" width="auto" alt="<?= $title ?>" title="<?= $title ?>"></span></a></h1>' . "\n" . '<h2><span id="desc">' . $description . '</span></h2>';
	  if(!file_exists("db/boards/index.txt") || (filesize("db/boards/index.txt") == 0)) {
		  echo "<p>The are no boards.</p>\n";
		} else {
      echo "<div id=\"boards\">\n<h1>Boards</h1>\n";

			function board_info($b){

        // Open
				$Content = json_decode(file_get_contents("db/boards/" . $b . ".json"));

				// Show
				echo "<li><a href=\"?board=" . $b . "\">" . $Content->{"name"} . "</a></li>\n";
    	}

      // Open index of categories
      $OpenDB_Cats = file_get_contents("db/categories/index.txt");
      $OpenDB_Cats_List = explode("\n", $OpenDB_Cats);
      $OpenDB_Cats_Count = count($OpenDB_Cats_List);
      for($i = 0; $i < $OpenDB_Cats_Count; ++$i) {

        echo "<div class=\"cat\">\n<h2>" . $OpenDB_Cats_List[$i] . "</h2>\n<div class=\"catl\">\n";

        // Open a category file
				$OpenDB_Cats_Bs = json_decode(file_get_contents("db/categories/" . $OpenDB_Cats_List[$i] . ".json"));
        $OpenDB_Cats_Bs_Count = count($OpenDB_Cats_Bs->{"boards"});

				// Show boards
        for($ii = 0; $ii < $OpenDB_Cats_Bs_Count; ++$ii) {
          $OpenDB_Cats_Bs_Content = json_decode(file_get_contents("db/boards/" . $OpenDB_Cats_Bs->{"boards"}[$ii] . ".json"));
				  echo "<li><a href=\"?board=" . $OpenDB_Cats_Bs->{"boards"}[$ii] . "\">" . $OpenDB_Cats_Bs_Content->{"name"} . "</a></li>\n";
        }

        echo "</div>\n</div>";
			}
      echo "</table>\n</div>\n";
		}
  }

	?>
<footer>
&copy;2021 Beikvar - <a href="https://github.com/beikvar/titha">Code</a ><br />
Using TithaEngine - v. β1
<!--<br><br>Este sitio web sigue en desarrollo, solo los desarrolladores tienen derecho a públicar contenido aquí.-->
</footer>
</body>
</html>
