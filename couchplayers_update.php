<?php

//here we are including some of the wp files to use wp functions and composer libraries 

require_once __DIR__.'/composer/vendor/autoload.php';
require('wp-blog-header.php');
require_once( __DIR__ . '/wp-admin/includes/taxonomy.php' );
if ( ! is_admin() ) {
require_once( __DIR__ . '/wp-admin/includes/post.php' );
}

//here are the API KEYS that you will need to change to your details

\Cloudinary::config(array(
    "cloud_name" => "CLOUD_NAME",
    "api_key" => "API_KEY",
    "api_secret" => "SECRET_API"
));

//setting date to correct Timezone and converting it to Epoch time/ unix time

$date = date ('Y-m-d');
$stampdate = strtotime($date) + 604800;
$fullstampdate = ($stampdate * 1000) + 7200000;

//igdb api request

$data = array(
'fields' => 'name,slug,genres,summary,first_release_date,release_dates.human,release_dates.platform,screenshots,videos,cover',
'search' => '*',
'limit' => '8',
'offset' => '0',
'order' => 'first_release_date:asc',
'filter[release_dates.date][gte]' => $fullstampdate
	);

//api key config
$headers = array(
    "X-Mashape-Key" => "MASHAPE KEY",
    "Accept" => "application/json");

$response = Unirest\Request::get("https://igdbcom-internet-game-database-v1.p.mashape.com/games/", $headers ,$data);
//print answer 
$getresponseval = $response->raw_body;  //response of json array
$getdecodedata = json_decode($getresponseval);  //decoding json array
echo "<pre>";  // for testing purpose 
print_r($getdecodedata);  // for testing purpose to print json array return

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
//genres array

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

//platform array

$chosen = array();

//loop to go trough each data return

for ($a = 0;$a <=7 ;$a++)
{
$postname = $getdecodedata[$a]->name;
//check if  posts exists
if(!post_exists( $postname) ) { 
//check for each data return that is not empty and adds html tags
if(! $postname == ""){
$postsummary = $getdecodedata[$a]->summary;
$postcoverid = $getdecodedata[$a]->cover->cloudinary_id;
$screenid = $getdecodedata[$a]->screenshots[0]->cloudinary_id;
$screenid1 = $getdecodedata[$a]->screenshots[1]->cloudinary_id;
$screenid2 = $getdecodedata[$a]->screenshots[2]->cloudinary_id;
$videoid = $getdecodedata[$a]->videos[0]->video_id;

if ($screenid1 != ""){
$screenshot = '<img class="alignnone size-full wp-image-591" src="https://images.igdb.com/igdb/image/upload/t_screenshot_huge/'.$screenid1.'.png" alt="'.$postname.'" /> <br>';
}else { $screenshot = "no screenshot was found";}
if ($screenid2 != ""){
$screenshot1 = '<img class="alignnone size-full wp-image-591" src="https://images.igdb.com/igdb/image/upload/t_screenshot_huge/'.$screenid2.'.png" alt="'.$postname.'" /> <br>';
}else { $screenshot1 = "";}


$background = '<style> body {background-image: url("http://images.igdb.com/igdb/image/upload/t_screenshot_huge/'.$screenid.'.png") !important; background-repeat-y: no-repeat;background-size: contain;}</style>';
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


$postcontent = $background.' <br> '.$video.' <h2> Description </h2><br>'.$postsummary.'<h2> Game Screenshots</h2><br>'.$screenshot.$screenshot1.'<h2>Available Platforms</h2>'.$plat;

// creating wp_post array to be passed to the function

$my_post = array(
        'post_title' => $postname,
        'post_content' => $postcontent,
        'post_status' => 'publish',
        'post_author' => 1,
        'post_type' => 'post',
        'post_category' => $genreid
	);

//wordpress post data

$post_id = wp_insert_post( $my_post, $wp_error );


//get image link and downloads image link


if($postcoverid !=""){
$fetchlink ="http://res.cloudinary.com/dsqd0it3k/image/fetch/";
$imgsetting1 ="c_fit,h_229,q_auto:low,w_267/"; //tranforming image setting
$wp_upload_dir = wp_upload_dir(); //using wp function to upload image
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
}else {$postcoverid ="";}
echo $postname." has been posted <br>";
}
}
}

?>
