<?php
	function filesize64($file)
	{
		static $iswin;
		if (!isset($iswin)) {
			$iswin = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN');
		}

		static $exec_works;
		if (!isset($exec_works)) {
			$exec_works = (function_exists('exec') && !ini_get('safe_mode') && @exec('echo EXEC') == 'EXEC');
		}

				// try a shell command
		if ($exec_works) {
			$cmd = ($iswin) ? "for %F in (\"$file\") do @echo %~zF" : "stat -c%s \"$file\"";
			@exec($cmd, $output);
			if (is_array($output) && ctype_digit($size = trim(implode("\n", $output)))) {
				return $size;
			}
		}

				// try the Windows COM interface
		if ($iswin && class_exists("COM")) {
			try {
				$fsobj = new COM('Scripting.FileSystemObject');
				$f = $fsobj->GetFile( realpath($file) );
				$size = $f->Size;
			} catch (Exception $e) {
				$size = null;
			}
			if (ctype_digit($size)) {
				return $size;
			}
		}

				// if all else fails
		return filesize($file);
	}

	// filesize_char : give a readable size of a file.
	function filesize_char($file)
	{
		$size = filesize64($file);
		if ($size > pow(2,40))
		{
			return round(filesize64($file)/pow(2,40),2) . 'TB';
		}
		elseif ($size > pow(2,30))
		{
			return round(filesize64($file)/pow(2,30),2) . 'GB';
		}
		elseif ($size > pow(2,20))
		{
			return round(filesize64($file)/pow(2,20),2) . 'MB';
		}
		elseif ($size > pow(2,10))
		{
			return round(filesize64($file)/pow(2,10),2) . 'kB';
		}
		else
		{
			return round(filesize64($file),2) . 'B';
		}
	}

	$dir = "./" . $_GET["dir"];
	$dirname = $_GET["dir"];
	// Test if the directory exist.
	if (is_dir($dir))
	{
		echo '<table id="list"><tbody>';
		echo '<tr><th>Name</th><th>Size</th><th width=10></th></tr>';
		if ($dirname == '' || $dirname == "./") { } // If the directory is the root, no rows will be used for it.
		elseif (dirname($dirname) == '.') { echo '<tr><td><a id="dir" href="?dir=""">/</a></td><td WIDTH=81>-</td><td width=10></td></tr>'; } // If the parent's directory is the root, only display / in a rows.
		else { echo '<tr><td><a id="dir" href="?dir='. dirname($dirname) .'/">/'. dirname($dirname) .'/</a></td><td WIDTH=81>-</td><td width=10></td></tr>'; } // Display the parent's directory if the other test don't works.
		if ($dh = opendir($dir)) 
		{
			while (($file = readdir($dh)) !== false) 
			{
				if( $file != '.' && $file != '..' && $file != 'index.php' && $file != 'ServerPi') 
				{
					if(filetype($dir . $file) == "dir") // Test the type of file (file or dir)
					{
						echo '<tr><td><a id="file" href="?dir='. $_GET["dir"] . $file .'/">' . $file . '/</a></td><td WIDTH=81>-</td><td width=10><a id="file" title="Remove this dir" href="ServerPi/plugins/file_manager/remove.php?adress='. urlencode(dirname(dirname(dirname(__DIR__))) . '/' . $_GET["dir"] . $file) .'/" onclick="return(confirm(\'Are you sure to delete this directory ?\'));">×</a></td></tr>';
					}
					else
					{
						$size = filesize_char(dirname(dirname(dirname(__DIR__))) . '/' . $_GET["dir"] . $file);
						echo '<tr><td><a id="file" href="' .$_GET["dir"]. $file . '">' . $file . '</a></td><td WIDTH=81>'. $size . '</td><td width=10><a id="file" title="Remove this file" href="ServerPi/plugins/file_manager/remove.php?adress='. urlencode(dirname(dirname(dirname(__DIR__))) . '/' . $_GET["dir"] . $file) . '" onclick="return(confirm(\'Are you sure to delete this file ?\'));">×</a></td></tr>';
					}
				}
			}
			closedir($dh);
			echo '</tbody></table>';
		}
	}
	else
		echo '<table id="list"><tr><th>Dossier introuvable</th></tr></table>';
?>