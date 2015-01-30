<?php
//Global variables
	$movieData = array();	//holds all the movie pairs and how many times we have seen each pair
	$users = array();	//holds all of the user's ids that have used our app
	$friends_collected = array();	//holds all of the ids of the people (friends of users) that we have collected data from
	
?>

<?php
/*This function will dump all of the data
in the friends_collected array in a text file.  The file will be 
used when ever a user uses the app in order to get the most update version of the 
friends collected ids.
*/

function updateCollectedFile(){
	$fh = fopen("friends_collected.txt", "w+");
	
	foreach($GLOBALS['friends_collected'] as $key => $value){
		fwrite($fh,$value."\n");
	}
	fclose($fh);
}


/*This function will dump all of the data
in the movieData array in a text file.  The file will be 
used when ever a user uses the app in order to get the most update version of the 
movie data.
*/
function updateMovieDataFile(){
	$fh = fopen("movie_data.txt", "w+");
	
	foreach($GLOBALS['movieData'] as $key => $value){
		fwrite($fh,$key . "~" . $value."\n");     //change ">" delimiter to "~"
	}
	
	
	fclose($fh);
}

/*This function will dump all of the data in the users array
 in a text file.  The file will be used when ever a user uses the app
 in order to get the most update version of the ids of the people who used this app.
*/
function updateUserArray(){
	$fh = fopen("users.txt", "w+");
	foreach($GLOBALS['users'] as $key => $value){
		fwrite($fh, $value."\n");
	}
	fclose($fh);
}

/*This function will opend the friends_collected text file and dump
all of the data into the global array friends_collected*/
function loadFriendsCollectedArray(){
	$fh = fopen("friends_collected.txt", "r");
	
	while (!feof($fh)) {
		$line = fgets($fh);
		if($line != "\n" && $line != "0")
			array_push($GLOBALS['friends_collected'], intval($line));
	}
	fclose($fh);
}

/*This function will open the user text file and dump
all of the data into the global array user*/
function loadUserArray(){
	$fh = fopen("users.txt", "r");
	
	while (!feof($fh)) {
		$line = fgets($fh);
		if($line != "\n" && $line != "0")
			array_push($GLOBALS['users'], intval($line));
	}
	fclose($fh);
	
}


/*This function will open the friends_movie_data file and dump
all of the data into the global array movieData
NOTE: the movie_data file is written in the following format:
movieTitle1~movieTitle2>count
where count is the number of users that like BOTH movieTitle1 and movieTitle2.
NOTE 2: The movieData global array is an associative array where the
key is "movieTitle1~movieTitle2" and the value is the number of users that 
like BOTH movieTitle1 and movieTitle2
*/
function loadMoviesArray(){
	$fh = fopen("movie_data.txt", "rb");
	
	while (!feof($fh)) {
		$line = fgets($fh);
		if($line != "\n" && $line != "0"){
			$kv = explode(">", $line);
			$GLOBALS['movieData'][$kv[0]] = intval($kv[1]);
		}
	}
	fclose($fh);
	
}

function readData($userArray){
	$firstArray = array();
	$secondArray = array();
	$i = 0; $j=1; $k = 2;
	$openFile = fopen("movie_data.txt", "rb");
	while(!feof($openFile)){
		$line = fgets($openFile);
		//print $line;
		if($line != "\n" && $line != "0"){ 
			$temp = explode("~", $line);
			$firstArray[$i] = $temp[0];
			$firstArray[$j] = $temp[1];
			$firstArray[$k] = $temp[2];
			$i+=3;
			$j+=3;
			$k+=3;
			//print $firstArray;
		}
	}
	//print_r($firstArray);
	fclose($openFile);
	$result2 = count($firstArray);
	$result1 = count($userArray);
	print_r($userArray);
	echo "<BR>";
	$max = 0; $x = 0;
	$tempArray1 = array();
	for($a=0; $a<$result1; $a++){
		for($b=0; $b<$result2; $b=$b+3){
			
			if($userArray[$a]==$firstArray[$b]){
				if($max < $firstArray[$b+2]){
					$max = $firstArray[$b+2];
					$movieTitle = $firstArray[$b+1];
					echo "Max value $max"."<BR>";
					echo "Name of Movie $movieTitle". "<BR>";
					$tempArray1[$x] = $movieTitle;
					$tempArray1[$x+1] = $max;
					$x+=2;
				}
							
			}
			
		} 
		
		
	}
	print_r($tempArray1);
	echo "<BR>";
	$MAXVAL = 0;
	$count = 0;
	for($x=1; $x<count($tempArray1);$x=$x+2){
		$MAXVAL = $tempArray1[$x];
		for($y=1;$y<count($tempArray1);$y=$y+2){
			if( $MAXVAL <= $tempArray1[$y]){
				$MAXVAL = $tempArray1[$y];
				$index = $y - 1;
				$index2 = $y;
			} 
				
		}
		$tempArray1[$index2] = 0;
		
		if($count == 0){
			echo "Top Recommendation:  $tempArray1[$index] <BR>";
			$count = $count + 1;
		}
		else{
			echo "Next Recommendation: $tempArray1[$index] <BR>";
			$count = $count + 1;
		}
		
	} 
	
	
	

}

/*helper function to push a key-value pair into in associative array*/
function array_push_assoc($array, $key, $value){
 $array[$key] = $value;
 return $array;
}

/*One of the main functions, parseMoviesAndInsert is given an array of movies liked
by one single user.  This function will then iterate through every possible unique pair of movies
in the array and:
	- if the pair is already in the array, increment the value
	- else, add it to the array with a value of 1.
*/
function parseMoviesAndInsert($arr){
	$keys = array_keys($GLOBALS['movieData']);	//get the all of the keys (movie pairs) of the array
	$size = count($arr);
	for($i = 0; $i < $size; $i++){
		for($j = $i+1; $j < $size; $j++){
			$pair1 = $arr[$i] . "~" . $arr[$j];	//we need to check for both ways, x~y and y~x
			$pair2 = $arr[$j] . "~" . $arr[$i];
			$p1Found = in_array($pair1, $keys);
			$p2Found = in_array($pair2, $keys);
			if(!$p1Found && !$p2Found){	//if this pair was not found, put it into array
				$GLOBALS['movieData'] = array_push_assoc($GLOBALS['movieData'], $pair1, 1);
			}
			else if ($p1Found){		//else, update the array by incrmenting
				$x = $GLOBALS['movieData'][$pair1];
				$GLOBALS['movieData'][$pair1] = $x + 1;
			}
			else if ($p2Found){
				$x = $GLOBALS['movieData'][$pair2];
				$GLOBALS['movieData'][$pair2] = $x + 1;
			}	
		}
	}
}	
?>

<?php
require_once 'facebook-php-sdk/src/facebook.php';

// Create our Application instance (replace this with your appId and secret).
$facebook = new Facebook(array(
  'appId'  => '212180385460790',
  'secret' => '451f7548b4a404680337a3780e697892',
  'cookie' => true,
));

$session = $facebook->getSession();

$me = null;
// Session based API call.
if ($session) {
  try {
    $uid = $facebook->getUser();
    $me = $facebook->api('/me');
    $friends = $facebook->api('/me/friends/');
    $likes = $facebook->api('/me/likes');
  } catch (FacebookApiException $e) {
    error_log($e);
  }
}


// login or logout url will be needed depending on current user state.
if ($me) {
  $logoutUrl = $facebook->getLogoutUrl();
} else {
  $loginUrl = $facebook->getLoginUrl();
}

$access_token = $session['access_token'];

?>
<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title>php-sdk</title>
    <style>
      body {
        font-family: 'Lucida Grande', Verdana, Arial, sans-serif;
      }
      h1 a {
        text-decoration: none;
        color: #3b5998;
      }
      h1 a:hover {
        text-decoration: underline;
      }
    </style>
  </head>
  <body>
    <!--
      We use the JS SDK to provide a richer user experience. For more info,
      look here: http://github.com/facebook/connect-js
    -->
    <div id="fb-root"></div>
    <script>
      window.fbAsyncInit = function() {
        FB.init({
          appId   : '<?php echo $facebook->getAppId(); ?>',
          session : <?php echo json_encode($session); ?>, // don't refetch the session when PHP already has it
          status  : true, // check login status
          cookie  : true, // enable cookies to allow the server to access the session
          xfbml   : true // parse XFBML
        });

        // whenever the user logs in, we refresh the page
        FB.Event.subscribe('auth.login', function() {
          window.location.reload();
        });
      };

      (function() {
        var e = document.createElement('script');
        e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
        e.async = true;
        document.getElementById('fb-root').appendChild(e);
      }());
    </script>



    <h1>Movie Recommendation System</h1>

    <?php 
    	loadMoviesArray(); //get the movie data from text file
    	//if user is signed in
    	if ($me):?>
	    <a href="<?php echo $logoutUrl; ?>">
	      <img src="http://static.ak.fbcdn.net/rsrc.php/z2Y31/hash/cxrz4k7j.gif">
	    </a>

	    <h3>Hello <?php echo $me['name']; ?></h3>
	    <?php 
	    	loadUserArray();	//get the user data from text file
	    	loadFriendsCollectedArray();	//get the friends collected text file
	    	
	    	if(!in_array($uid, $GLOBALS['users'])){	//if the user didnt already use this (aka have his movies collected)
	    		print "Collecting you and your friend's movies...<br>";
	    		array_push($GLOBALS['users'], $uid);	//update user array with current user id
	    		array_push($GLOBALS['friends_collected'], $uid);	//update collected array with current user id
		    	$mylikes = $likes['data'];		//get the user's likes
		    	$myMovies = array();			
		    	foreach ($mylikes as $x){		//iterate over user's likes
		    		if ($x['category'] == "Movie"){
		    			array_push($myMovies, $x['name']);	//if movie, push into single array
		    		}
		    	}
		    	//print_r($myMovies);
		    	
		    	parseMoviesAndInsert($myMovies); //process movies and insert into moviesData
		    	
		    	$flikes = $facebook->api(array(	
        				'method' => 'fql.query', 
        				'query' => 'SELECT uid, movies FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1 = ' . $uid . ')', 
       					'access_token' => $access_token));	//call this query to get big array with all of your friend's liked movies
       					
       			$size = count($flikes);
       			$i = 0;	$p25 = 0; $p50 = 0; $p75 = 0;
       			print "0%	";	
       			foreach($flikes as $f){
       				$p = $i / $size;
       				if($p > 0.25 && $p < 0.5 && $p25 == 0){		//for showing progress
       					print "		25%	"; $p25 = 1;
       				}
       				if($p > 0.50 && $p < 0.75 && $p50 == 0){
       					print "		50%	"; $p50 = 1;
       				}
       				if($p > 0.75 && $p < 1.0 && $p75 == 0){
       					print "		75%	"; $p75 = 1;
       				}
       				
       				$movies = array();
       				if(in_array($f['uid'], $GLOBALS['friends_collected'])){	//if we have already processed this person's movies, skip
					continue;
				}
				array_push($GLOBALS['friends_collected'], $f['uid']);	//push current friend into collected array
				$movies = explode(", ", $f['movies']);	//make array out of string "movie1, movie2, movie3"
				if (count($movies) > 20)	//if friend has too many movies, skip him for effeciency purposes
					continue;
					
				parseMoviesAndInsert($movies);	//process movies
				$i++;
			}
			print "		100%<br>";
			
			updateUserArray();	//write back to file
			updateCollectedFile();
			updateMovieDataFile();
			
			print "Done collecting movies!<br>";
		}
		print "Finding some movies to recommend..." . "<br>";
		readData($myMovies);
		
		
	    ?>
    <?php else: ?>
	    <strong><em>You are not Connected.</em></strong>
	    <div>
	       Login first!<fb:login-button perms="user_likes, friends_likes"></fb:login-button>
	    </div>
    <?php endif ?>


  </body>
</html>