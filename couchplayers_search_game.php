<?php
//this project is still under improvements 
//here we are including some of the wp files to use wp functions and composer libraries 

require_once __DIR__.'/composer/vendor/autoload.php';
require('wp-blog-header.php');
require_once( __DIR__ . '/wp-admin/includes/taxonomy.php' );
if ( ! is_admin() ) {
    require_once( __DIR__ . '/wp-admin/includes/post.php' );
}
//here are the API KEYS that you will need to change to your details

\Cloudinary::config(array(
    "cloud_name" => "CLOUD NAME",
    "api_key" => "API KEY",
    "api_secret" => "SECRET"
));

$searchvalue = get_search_query();  //getting value from wp search function

//checking if searcn is vaild
if (!$searchvalue == "") {
$searchplatform = array(
        0 => 6,
        1 => 48,
        2 => 49);



// array passed to the mashape API
$data = array(
'fields' => 'name,slug,genres,summary,popularity,first_release_date,release_dates.human,release_dates.platform,screenshots,videos,cover',
//'filter[release_dates.platform][eq]'=> $searchplatform[$loop],
'order' => 'popularity:desc',
'search' => $searchvalue,
'limit' => '15',
'offset' => '0'
	);

//api key config
$headers = array(
    "X-Mashape-Key" => "MASHAPE API KEY",
    "Accept" => "application/json");

$response = Unirest\Request::get("https://igdbcom-internet-game-database-v1.p.mashape.com/games/", $headers ,$data);
//print answer 
$getresponseval = $response->raw_body;
$getdecodedata = json_decode($getresponseval);
//echo "<pre>";  //for testing purpose
//print_r($getdecodedata);  //for testing purpose

$genres = array (
	5 =>"Shooter",
	7 =>"Music",
	8 =>"Platform",
	9 =>"Puzzle",
	10 =>"Racing",
	11 =>"Real Time Strategy (RTS)",
	12 =>"Role-playing (RPG)",
	13 =>"Simulator",
	14 =>"Sport",
	15 =>"Strategy",
	16 =>"Turn-based strategy (TBS)",
	24 =>"Tactical",
	25 =>"Hack and slash/Beat 'em up",
	26 =>"Quiz/Trivia",
	30 =>"Pinball",
	31 =>"Adventure",
	32 =>"Indie",
	33 =>"Arcade",
	2 =>"Point-and-click",
	4 =>"Fighting"
	);


$platforms = array (
    5 => 'Wii',
    6 => 'PC',
    8 => 'Playstation 2',
    9 => 'Playstation 3',
    12 => 'Xbox 360',
    34 => 'Android',
    37 => 'Nintendo 3DS',
    39 => 'IOS',
    41 => 'Wii U',
    46 => 'Playstation Vita',
    48 => 'Playstation 4',
    49 => 'Xbox One',
    130 => 'Nintendo Switch',
    82 => 'Web Browser',
	74 => 'Windows Phone',
	11 => 'Xbox',
	14 => 'Mac',
	45 => 'Playstation Network',
	55 => 'Mobile'
	);

$chosen = array();

//loop to go trough each data return

for ($a = 15;$a >= 0;$a--)
{
$postname = $getdecodedata[$a]->name;
$postsummary = $getdecodedata[$a]->summary;
$postcoverid = $getdecodedata[$a]->cover->cloudinary_id;
$screenid = $getdecodedata[$a]->screenshots[0]->cloudinary_id;
$screenid1 = $getdecodedata[$a]->screenshots[1]->cloudinary_id;
$screenid2 = $getdecodedata[$a]->screenshots[2]->cloudinary_id;
$videoid = $getdecodedata[$a]->videos[0]->video_id;
//check if  posts exists
if(!post_exists( $postname) ) { 
//check for each data return that is not empty and adds html tags
if(! $postname == ""){

if ($postcoverid != "")
{

similar_text($searchvalue, $postname,$similar);
//echo $similar."  ";

if ($similar < 50){
$testuppercase = strtoupper($searchvalue);
similar_text($testuppercase, $postname,$similar);
}
if ($similar < 50){
$testlowercase = strtolower($searchvalue);
similar_text($testlowercase, $postname,$similar);
}
//echo $similar;
if($similar > 50){


if ($screenid1 != ""){
$screenshot = '<img class="alignnone size-full wp-image-591" src="https://images.igdb.com/igdb/image/upload/t_screenshot_huge/'.$screenid1.'.png" /> <br>';
}else { $screenshot = "no screenhsot was found";}
if ($screenid2 != ""){
$screenshot1 = '<img class="alignnone size-full wp-image-591" src="https://images.igdb.com/igdb/image/upload/t_screenshot_huge/'.$screenid2.'.png" /> <br>';
}else { $screenshot1 = "";}


$background = '<style> body {background-image: url("http://images.igdb.com/igdb/image/upload/e_blur:600/'.$screenid.'.png") !important; background-repeat-y: no-repeat;background-size: contain;} </style>';
if ($videoid != ""){
$video = '<br> [embed]http://www.youtube.com/watch?v='.$videoid.'[/embed] <br>';
}else {$video ="no video was found";}


$platarray = array();

for($b =0 ; $b  <=15; $b++){
$platformreleasedate = $getdecodedata[$a]->release_dates[$b]->human;
$test = $getdecodedata[$a]->release_dates[$b]->platform;
if (array_key_exists($test,$platforms)){
$chosen[$b] =$getdecodedata[$a]->release_dates[$b]->platform; 
$platarray[$b] = $platforms[$chosen[$b]]."    -    ".$platformreleasedate;
}else { $b == 15; }

}
$current = rtrim(implode('</li><li>', $platarray), '</li><li>');
$plat = "<ul><li>".$current."</li></ul>";

$arraygenre = array();
$genreid = array();

for($c =0; $c <= 10; $c++){

$genre =$getdecodedata[$a]->genres[$c];

if ($genre != "")
{
$arraygenre[$c] = $genres[$genre];

$catid = get_cat_ID(arraygenre[$c]);
if ($catid == 0)
{
wp_create_category($arraygenre[$c]);
}
$tempvar = $arraygenre[$c];
$genreid[$c] = get_cat_ID($tempvar);

}else{ $c == 10;}

}
array_push($genreid,23);
$postcontent = $background.'<br>'.$video.'<h2> Description </h2><br>'.$postsummary.'<h2> Game Screenshots</h2><br>'.$screenshot.$screenshot1.'<h2>Available Platforms</h2>'.$plat;
// creating wp_post array to be passed to the function
$my_post = array(
        'post_title' => $postname,
        'post_content' => $postcontent,
        'post_status' => 'publish',
        'post_author' => 1,
        'post_type' => 'post',
        'post_category' => $genreid
	);

// post does not exist

//posts data
$post_id = wp_insert_post( $my_post, $wp_error );


//get image link and downloads image link

if($postcoverid !=""){
$fetchlink ="http://res.cloudinary.com/dsqd0it3k/image/fetch/";
$imgsetting1 ="c_fit,h_229,q_auto:low,w_267/";  //image transformation 
$wp_upload_dir = wp_upload_dir();
$link = $fetchlink.$imgsetting1."https://images.igdb.com/igdb/image/upload/".$postcoverid.".jpg";
$outputimage = __DIR__."/wp-content/uploads/".$postcoverid.".jpg";

file_put_contents($outputimage, file_get_contents($link));
$filename =$postcoverid.".png";


//tag image with post
$getImageFile = $outputimage;
 
$wp_filetype = wp_check_filetype( $getImageFile, null );

$wp_upload_dir = wp_upload_dir();

$attachment_data = array(
    'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ), 
    'post_mime_type' => $wp_filetype['type'],
    'post_title' => sanitize_file_name( $postcoverid ),
    'post_content' => '',
    'post_status' => 'inherit'
);

$attach_id = wp_insert_attachment( $attachment_data, $getImageFile, $post_id );

require_once( ABSPATH . 'wp-admin/includes/image.php' );

$attach_data = wp_generate_attachment_metadata( $attach_id, $getImageFile );

wp_update_attachment_metadata( $attach_id, $attach_data );

set_post_thumbnail( $post_id, $attach_id );
}else {$postcoverid = "";}
//echo $postname." has been posted <br>";
}
}
}
}
}
}


?>
