<?php
// Now the script has run, generate a new cache file
if(! $fp = @fopen($cachefile, 'w')){
	echo 'cant open';
}
$cache = ob_get_contents();
$cache = gzcompress($cache,9);
// save the contents of output buffer to the file
if( @fwrite($fp, $cache)===FALSE) echo 'write error';

@fclose($fp);
//ob_end_flush();
print_gzipped_output();


?>