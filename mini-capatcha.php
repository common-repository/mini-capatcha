<?PHP
/**
 * @mini-capatcha file
 * @author walleve <uulele@gmail.com>
 */

error_reporting(0);

//+------ basic config -----------------
$width='50';	                        //width
$height='25';	                        //height
$bgcolor = array('255','255','255');	//background color
$borders = array('220','215','215');	//border colors
$disturbcolor = array(220,200,200);	    
//+-----------------------------

session_start();

$post_rand = $_GET['p']?$_GET['p']:'';
$post_id = $_GET['id']?$_GET['id']:0;

$_SESSION['capatcha_'.$post_id][$post_rand] = array();
$start_i = mt_rand(0,20);
$code = substr(md5(mkcapatcha()|$post_id),$start_i,4);
$_SESSION['capatcha_'.$post_id][$post_rand] = strtolower($code);

@header("Expires: -1");
@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
@header("Pragma: no-cache");

if(function_exists('imagecreate') && function_exists('imagecolorset') && function_exists('imagecopyresized') && function_exists('imagecolorallocate') && function_exists('imagesetpixel') && function_exists('imagechar') && function_exists('imagecreatefromgif') && function_exists('imagepng')) {
    //create img
    $im = ((function_exists('imagecreatetruecolor')) && PHP_VERSION >= '4.3')?imagecreatetruecolor($width, $height):imagecreate($width, $height);

    //create bg color
	$backgroundcolor = imagecolorallocate ($im, $bgcolor[0], $bgcolor[1], $bgcolor[2]);
    imagefill($im,0,0,$backgroundcolor);

    $im = img_disturb($im,$width,$height,$disturbcolor);
    
    //add numbers
	$numorder = array(1, 2, 3, 4);
	shuffle($numorder);
	$numorder = array_flip($numorder);
	for($i = 1; $i <= 4; $i++) {
		$x = $numorder[$i] * 10 + mt_rand(0, 4) - 2;
		$y = mt_rand(0, 5);
        
		$text_color = imagecolorallocate($im, mt_rand(50, 200), mt_rand(50, 128), mt_rand(50, 200));
		imagestring($im, 5, $x + 5, ($y + 3)%360, $code[$numorder[$i]], $text_color);
	}
    
	//create borders
	$im = img_border($im,$width,$height,$borders);

    //output img
	header('Content-type: image/png');
	imagepng($im);
	imagedestroy($im);

}

/**
 * @random capatcha
 */
function mkcapatcha() {
	$capatcha = random(6);
	$s = sprintf('%04s', base_convert($capatcha,10,24));
	$capatcha = array();
	$capatcha_units = 'BCEFGHKMNPQRVWXY2346789';
	for($i = 0; $i < 4; $i++) {
		$unit = ord($s{$i});
		$capatcha[$i]= ($unit >= 0x30 && $unit <= 0x39) ? $capatcha_units[$unit - 0x30] : $capatcha_units[$unit - 0x57];
	}
    shuffle($capatcha);
    $secstr = implode('',$capatcha);
    if(strlen($secstr)<4){
        $secstr.=mt_rand(0,9);
    }
	return $secstr;
}

/**
 * @function random
 */
function random($length) {
	PHP_VERSION < '4.2.0' ? mt_srand((double)microtime() * 1000000) : mt_srand();
	$seed = base_convert(md5(print_r($_SERVER, 1).microtime()), 16, 10);
	$seed = str_replace('0', '', $seed).'012340567890';
	$hash = '';
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $seed[mt_rand(0, $max)];
	}
	return $hash;
}

/**
 * @function img_disturb
 */
function img_disturb($im,$width,$height,$disturbcolor) {
	$linenums = $height/10;
	for($i=0; $i <= $linenums; $i++) {
		$color = imagecolorallocate($im, $disturbcolor[0], $disturbcolor[1], $disturbcolor[2]);
		$x = mt_rand(0, $width);
		$y = mt_rand(0, $height);
		if(mt_rand(0, 1)) {
			imagearc($im, $x, $y, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, 360), mt_rand(0, 360), $color);
		} else {
			imageline($im, $x, $y, mt_rand(0, 20), mt_rand(0, mt_rand($height, $width)), $color);
		}
	}
	return $im;
}

/**
 * @function img_border
 */
function img_border($im,$width,$height,$borders){
    $bordercolor = imagecolorallocate($im , $borders[0], $borders[1], $borders[2]);
	imagerectangle($im, 0, 0, $width-1, $height-1, $bordercolor);
    return $im;
}
?>
