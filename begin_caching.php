<?php
	function print_gzipped_output()
{
	$HTTP_ACCEPT_ENCODING = $_SERVER["HTTP_ACCEPT_ENCODING"];
	if( headers_sent() )
	$encoding = false;
	else if( strpos($HTTP_ACCEPT_ENCODING, 'x-gzip') !== false )
	$encoding = 'x-gzip';
	else if( strpos($HTTP_ACCEPT_ENCODING,'gzip') !== false )
	$encoding = 'gzip';
	else
	$encoding = false;

	if( $encoding )
	{
		$contents = ob_get_clean();
		$_temp1 = strlen($contents);
		if ($_temp1 < 2048)    // no need to waste resources in compressing very little data
		print($contents);
		else
		{
			header('Content-Encoding: '.$encoding);
			print("\x1f\x8b\x08\x00\x00\x00\x00\x00");
			$contents = gzcompress($contents, 9);
			$contents = substr($contents, 0, $_temp1);
			print($contents);
		}
	}
	else
	ob_end_flush();
}
	$ip=@$REMOTE_ADDR;

    // Settings
    $cachedir = 'cache/'; // Directory to cache files in (keep outside web root)
    $cachetime = 3600*24*90; // Seconds to cache files for
    $cacheext = 'cache'; // Extension to give cached files (usually cache, htm, txt)

    // Ignore List
    $ignore_list = array(
    );

    // Script
    $page = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; // Requested page
    $cachefile = $cachedir . md5($page) . '.' . $cacheext; // Cache file to either load or create

    $ignore_page = false;
    for ($i = 0; $i < count($ignore_list); $i++) {
        $ignore_page = (strpos($page, $ignore_list[$i]) !== false) ? true : $ignore_page;
    }

    $cachefile_created = ((@file_exists($cachefile)) and ($ignore_page === false)) ? @filemtime($cachefile) : 0;
    @clearstatcache();

    // Show file from cache if still valid
    if (time() - $cachetime < $cachefile_created) {
		
        header('Content-Encoding: gzip');
			print("\x1f\x8b\x08\x00\x00\x00\x00\x00");
        	
    	@readfile($cachefile);
        exit();

    }

    // If we're still here, we need to generate a cache file

    ob_start();

?>