# php-mega-nz-api
A php API wrapper to connect to mega.nz API, in full object oriented mode.

This library takes care of not disabling https certificate checking when
calling for mega servers.

This library is in construction, only few of its methods are available, but
contributions are welcome!

This library is inspired by the [smartinm/mega-php-client](https://github.com/smartinm/mega-php-client) library and the 
[tutorial by Julien Marchand](http://julien-marchand.fr/blog/using-the-mega-api-with-php-examples/).

## Installation

The installation of this library is made via composer.
Download `composer.phar` from [their website](https://getcomposer.org/download/).
Then add to your composer.json :

```json
	"require": {
		...
		"php-extended/php-mega-nz-api": "^1",
		...
	}
```
Then run `php composer.phar update` to install this library.
The autoloading of all classes of this library is made through composer's autoloader.

## Basic Usage

For downloading the files of a folder on mega, and put them into a folder on
your filesystem, please look at [the example script](download_to_folder.php).

This library offers an API to see files, and download them, as follows :

The constructor needs the full url to access mega. This means that the url
fragment should contains the node id, and the key to decode the node. 
`Mega::__construct($full_url):Mega`

Once a new `Mega` object is created, you may search for its root folder with
the method : `Mega::getRootNodeInfo():MegaNode;`

Once you have a `MegaNode` object, you may search for its children with the
method :
`Mega::getChildren(MegaNodeId $node_id):MegaNode[];`

The `MegaNodeId` object may be obtained with the `MegaNode:getNodeId():MegaNodeId`
method. Node Ids are common objects to refer to specific nodes in a hierarchy,
and the `Mega` class may retrieve `MegaNode` objects with the method
`Mega::getNodeInfo(MegaNodeId $node_id):MegaNode`.

To separate nodes that represents folders and node that represents files, use
the `MegaNode::getNodeType():integer` method. If the value is `MegaNode::TYPE_FOLDER`,
then the node represents a folder, and if the value is `MegaNode::TYPE_FILE`,
then it represents a downloadable file.

Then, to download files (folders are not downloadable for obvious reasons), 
use the `Mega::downloadFile(MegaNode $node):string` method, that
returns the raw string data of the downloaded file, unencrypted.

Beware that this method does not uses streams directly to your filesystem, and
may use a lot of memory if the file is really big.

To check the size of a file (folders have no size given Mega's API), use the
`MegaNode::getNodeSize():integer` method. Beware that 32 bits system
(or 32 bits php as windows have) may not handle the file sizes for large files
very well.

Finally, almost every method of this library throws `PhpExtended\Mega\MegaException`
dues to various factors, mainly because cryptographic errors that may occur anywhere.
Remember to encapsulate calls to this library to `try { ... } catch(MegaException $e) { ... }`
blocks.

## License

MIT (See [license file](LICENSE)).
