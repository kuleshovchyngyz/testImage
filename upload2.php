<?php
$filedata = file_get_contents("php://input");

$f_id = time().'_'.rand(10000000, 99999999);


function getB64Type($str) {
    // $str should start with 'data:' (= 5 characters long!)
    return substr($str, 5, strpos($str, ';')-5);
}

$file_type = getB64Type($filedata);

switch($file_type) {
	case 'image/gif':
		$file_ext = 'gif';
	break;
	case 'image/png':
		$file_ext = 'jpg';
	break;	
	case 'image/jpeg':
	case 'image/jpg':		
	default:
		$file_ext = 'jpg';
	break;
}

$filestore = file_put_contents('upload/'.$f_id.'.txt', $file_type.' +++ '.$filedata);
$filestore = file_put_contents('upload/'.$f_id.'.'.$file_ext, base64_decode($filedata));
base64_to_jpeg($filedata,'upload/new_'.$f_id.'.'.$file_ext);
function base64_to_jpeg($base64_string, $output_file) {
    // open the output file for writing
    $ifp = fopen( $output_file, 'wb' );

    // split the string on commas
    // $data[ 0 ] == "data:image/png;base64"
    // $data[ 1 ] == <actual base64 string>
    $data = explode( ',', $base64_string );

    // we could add validation here with ensuring count( $data ) > 1
    fwrite( $ifp, base64_decode( $data[ 1 ] ) );

    // clean up the file resource
    fclose( $ifp );

    return $output_file;
}

die('ok');