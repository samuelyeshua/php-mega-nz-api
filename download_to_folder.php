<?php

/*
 * Example script on how to download recursively a folder from mega.
 * This script to launch from command line needs 2 arguments :
 * - A valid url to Mega, with full fragment (with the folder id and the key)
 * - A valid path on your system where to put all the downloaded data
 *
 * This script will create a mirror folder hierarchy as the one on Mega on
 * your filesystem, and put all folders names and file contents in clear.
 */

ini_set('memory_limit', -1);

use PhpExtended\Mega\Mega;
use PhpExtended\Mega\MegaNode;

global $argv;

if(!isset($argv[1]))
	throw new Exception('The first argument must be the url of Mega folder.');
if(!isset($argv[2]))
	throw new Exception('The second argument must be a folder path on your fs to store the data.');

$mega_url = $argv[1];
$target_path = $argv[2];
$realpath = realpath($target_path);
if($realpath === false)
	throw new Exception('The path "'.$target_path.'" does not exists.');
if(!is_writable($realpath))
	throw new Exception('The path "'.$realpath.'" is not writeable.');

$composer_path = __DIR__.'/vendor/autoload.php';
if(!is_file($composer_path))
	throw new Exception('The composer vendor directory is not up. Please run composer first.');

require_once $composer_path;

$mega = new Mega($mega_url);

echo "Getting node hierarchy info from Mega...\n";
$root = $mega->getRootNodeInfo();

/**
 * Function that download recursively files and folders from mega, and writes
 * them onto your filesystem.
 *
 * @param Mega $mega
 * @param MegaNode $node
 * @param string $path
 * @throws Exception
 */
function download(Mega $mega, MegaNode $node, $path)
{
	$filepath = $path.DIRECTORY_SEPARATOR.$node->getAttributes()->getName();
	if($node->getNodeType() === MegaNode::TYPE_FOLDER)
	{
		echo "Making directory ".$filepath."\n";
		if(!is_dir($filepath))
		{
			$res = mkdir($filepath);
			if($res === false)
				throw new Exception('Impossible to make directory "'.$filepath.'"');
		}
		foreach($mega->getChildren($node) as $childNode)
		{
			download($mega, $childNode, $filepath);
		}
	}
	
	if($node->getNodeType() === MegaNode::TYPE_FILE)
	{
		echo "Downloading file ".$node->getAttributes()->getName()."\n";
		if(!is_file($filepath))
		{
			$data = $mega->downloadFile($node);
			$res = file_put_contents($filepath, $data, LOCK_EX);
			if($res === false)
				throw new Exception('Impossible to write contents at "'.$filepath.'"');
		}
	}
}

// here the magic happens
download($mega, $root, $realpath);
