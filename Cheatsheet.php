<?php

/* 
 *Author: Giedrius Kristinaitis 
 */

/***********************************************************
*	             PHP Cheatsheet               
*	     (Requires understanding of PHP) 
*	     
*	     This file contains random stuff written 
*	     in PHP. Why? Because why not...
************************************************************/

// ********* PHP Syntax ********* //
function arraySyntax(){
	$array1 = array(0, 1, 2, 3);
	$array2 = ["three", "two", "three"];
	$array3 = ["key1" => "value1", "key2" => 26];
}

function foreachLoopSyntax(){
	$array1 = ["three", "two", "three"];
	$array2 = ["key1" => "value1", "key2" => 26];

	foreach($array1 as $value){
		// ...
	}

	foreach($array2 as $key => $value){
		// ...
	}
}

// declaration of an abstract class
abstract class ClassSyntaxA {

	public abstract function doStuff();
}

// declaration of an interface
interface InterfaceSyntaxA {

	public function foo();
}

// an interface can extend other interface
interface InterfaceSyntaxB extends InterfaceSyntaxA {

	public function bar();
}

// classes can extend other classes and implement interfaces
class ClassSyntaxB extends ClassSyntaxA implements InterfaceSyntaxB {

	private $variable1;
	protected $variable2;
	public $variable3;

	public function __construct(){}
	public function __destruct(){}
	
	public function doStuff(){}
	public function foo(){}
	public function bar(){}
}
// ********* End of PHP Syntax ********* //

// ********* PHP Data Objects (PDO) and SQL Database ********* //
class PHPDataObjects {

	// PDO object which is used to perform operations with the database
	private $pdo;

	// information to create a connection to a database
	private $driver = "mysql";
	private $host = "localhost";
	private $dbname = "my_database_name"; // optional
	private $charset = "utf8mb4"; // optional

	// database user's information
	private $username = "root";
	private $password = "";

	public function __construct(){
		$this->createConnectionToDatabase();
	}

	public function createConnectionToDatabase(){
		// data source name (dsn) variable which is used to connect to a database
		// dbname property is optional here
		$dsn = $this->driver . ":host=" . $this->host . ";charset=" . $this->charset;

		// options for the PDO object
		// for example, the default fetch mode attribute (fetch associative array or object)
		$options = array();

		$this->pdo = new PDO($dsn, $this->username, $this->password, $options);
	}

	public function destroyConnectionToDatabase(){
		// to destroy the connection all references to the PDO object must be set to null
		// if this is not done, the connection is automatically destroyed when the script execution ends
		$this->pdo = null;
	}

	/* 	
	*	PDO uses prepared statements to manipulate data in the database to ensure
	*	protection against XSS (cross-site scripting) attacks which can be used to 
	* 	steal user's stuff and SQL injection which can ruin the whole database
	*
	*	Prepared statements should be used with queries that need variables
	*	Queries that don't need variables can be executed with query() function
	*
	*   Other SQL statements can be executed with exec() function
	*
	*	The statement only needs to be prepared once and can be executed multiple times
	*/
	public function prepareStatement($statement){
		return $this->pdo->prepare($statement);
	}

	/* 
	*	$variables - array containing all variables needed for the query
	*   PDO supports positional and named placeholders
	*
	*   Examples:
	*	Query: "SELECT * FROM someTable WHERE email = :email AND phone = :phone;" | Array: array("email" => $email, "phone" => $phone)
	*	Query: "SELECT * FROM someTable WHERE email = ? AND phone = ?;" | Array: [$email, $phone] 
	*/
	public function executeStatement($statement, $variables){
		$statement->execute($variables);
	}

	public function execStatement($statement){
		return $this->pdo->exec($statement);
	}

	public function fetchObjectData($statement){
		while($data = $statement->fetchObject()){
			// do something with the data
			// data is returned as an object with fields from the database table
			// if there is an id returned from the database, then there is 
			// an id field in the object
		}
	}

	public function fetchAssoc($statement){
		while($data = $statement->fetch(PDO::FETCH_ASSOC)){
			// do something with the data
			// accessed like this: $data['fieldName']
		}
	}

	public function getPDO(){
		return $this->pdo;
	}
}

// demonstration of PDO
function doStuffWithPDO(){
	$pdo = new PHPDataObjects();
	$pdo->execStatement("CREATE DATABASE IF NOT EXISTS test;");
	$pdo->execStatement("CREATE TABLE IF NOT EXISTS test.users (id int(10) NOT NULL PRIMARY KEY AUTO_INCREMENT, name varchar(255) NOT NULL, description text NOT NULL);");

	// prepare insertion statement
	$statement = $pdo->prepareStatement("INSERT INTO test.users (name, description) VALUES (?, ?);");

	function generateRandomString($length){
		$string = "";

		for($i = 0; $i < $length; $i++){
			$string .= chr(mt_rand(65, 95));
		}

		return $string;
	}

	// execute the prepared insertion statement 5 times with different data
	for($i = 0; $i < 5; $i++){
		$name = generateRandomString(10);
		$description = generateRandomString(20);
		$pdo->executeStatement($statement, [$name, $description]);
	}

	// execute selection statement with query() since it doest need any variables
	$statement = $pdo->getPDO()->query("SELECT * FROM test.users");

	// fetch selected data
	while($data = $statement->fetchObject()){
		echo $data->id . ". " . $data->name . " " . $data->description . "<br/>";
	}
}

// ********* END of PHP Data Objects (PDO) and SQL Database ********* //

// ********* PHP File Handling ********* //
class SingleFileHandler {

	private $file;
	private $fileName;
	private $fileSize;
	private $fileOpen = false;

	public static $fileOpenModes = [
		"read_only" => "r",
		"write_only_erase" => "w",
		"write_only_preserve" => "a",
		"create_for_write_only_error_if_exists" => "x",
		"read_and_write" => "r+",
		"read_write_erase" => "w+",
		"read_write_preserve" => "a+",
		"create_for_read_write_error_if_exists" => "x+",
	];

	public function __construct($fileName){
		$this->fileName = $fileName;
	}

	// opens the file with a given open mode: read only, write only...
	public function createOrOpenFile($openMode){
		$this->file = fopen($this->fileName, $openMode) or die("Unable to open the file.");
		$this->fileOpen = true;
		$this->getFileSize();
	}

	// closes the file
	public function closeFile(){
		fclose($this->file);
		$this->fileOpen = false;
	}

	// returns the size of the file
	public function getFileSize(){
		$this->fileSize = filesize($this->fileName);
		return $this->fileSize;
	}

	// returns content of the file as a string
	public function readWholeFile(){
		if(!$this->fileOpen){
			echo "Open the file before reading!";
			return;
		}

		return fread($this->file, $this->fileSize);
	}

	// reads the by a given function
	// expected fgets or fgetc
	private function readFileByFunction($function){
		if(!$this->fileOpen){
			echo "Open the file before reading!";
			return;
		}elseif($function != "fgets" && $function != "fgetc"){
			echo "Invalid reading function";
			return;
		}

		$data = array();
		$index = 0;

		while(!feof($this->file)){
			// could also be done like this: call_user_func($function, $params);
			// when it is an object function: call_user_func(array($object, $function), $params);
			$data[$index++] = $function($this->file); 
		}

		return $data;
	}

	// reads the file line by line and returns all the lines as an array
	public function readLineByLine(){
		return $this->readFileByFunction("fgets");
	}

	// reads the file character by character and returns all the characters as an array
	public function readCharByChar(){
		return $this->readFileByFunction("fgetc");
	}

	// deletes a file
	// file deletion is done by unlinking from the file
	// when no links to the file are left, it is deleted
	public function deleteFile(){
		unlink($this->fileName);
	}

	// checks if a file exists
	public static function fileExists($fileName){
		return file_exists($fileName);
	}
}

// gets the content of a web page and returns it as a string
function readURL($url){
	return file_get_contents($url);
}

function demonstrateFileHandling(){
	$handler = new SingleFileHandler("something.txt");
	$handler->createOrOpenFile(SingleFileHandler::$fileOpenModes['read_write_preserve']);

	$data = $handler->readLineByLine();

	foreach($data as $value){
		echo $value . "<br/>";
	}

	$handler->closeFile();
	$handler->deleteFile();
}
// ********* End of PHP File Handling ********* //

// ********* Cookies and Sessions ********* //
class Cookie {

	private $name;
	private $value;
	private $expirationTime;
	private $path;
	private $domain;
	private $secure;
	private $httpOnly;

	public function __construct($name){
		$this->name = $name;
	}

	public function setProperty($property, $value){
		$this->$property = $value;
	}

	public function getProperty($property){
		return $this->$property;
	}

	// setcookie() must be called before <html> tag
	public function setCookie(){
		setCookie($this->name, $this->value, $this->expirationTime,
			$this->path, $this->domain, $this->secure, $this->httpOnly);
	}

	public function getCookieValue(){
		if(isset($_COOKIE[$this->name])){
			$this->value = $_COOKIE[$this->name];
			return $_COOKIE[$this->name];
		}
	}

	public function timeLeft(){
		$seconds = $this->expirationTime - time();

		if($seconds <= 0){
			echo "Cookie already expired";
			return;
		}

		// format and return the time in seconds
		$date1 = new DateTime("@0");
		$date2 = new DateTime("@$seconds");

		return $date1->diff($date2)->format('%a days, %h hours, %i minutes and %s seconds');
	}

	// cookies are deleted by setting their expiration time in the past
	public function deleteCookie(){
		setcookie($this->name, "", time() - 3600);
	}
}

function demonstrateCookies(){
	$cookie = new Cookie("test");
	$cookie->setProperty("value", 143);
	$cookie->setProperty("expirationTime", time() + (60 * 60 * 24 * 14));
	$cookie->setCookie();

	echo $cookie->getCookieValue() . "<br/>";
	echo $cookie->timeLeft() . "<br/>";

	$cookie->deleteCookie();
}

// session must be started before any tags
class SessionManager1 {

	public static function startSession($name = "test", $lifetime = 0, $path = "/", $domain = null, $secure = null, $httpOnly = true){
		// make sure to use strict mode
		ini_set('session.use_strict_mode', 1);

		// sets the name of the session cookie
		session_name($name);

		// check if the domain is set, if not, make it be the server name
		$domain = isset($domain) ? $domain : $_SERVER['SERVER_NAME'];

		// check if secure is set
		$secure = isset($secure) ? $secure : (isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS']: null);

		// set parameters for the session cookie
		session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);
		session_start();

		// make sure the session is not expired and destroy it if it is
		if(self::isSessionValid()){
			// make sure the session is valid
			if(self::isSessionBeingHijacked()){
				$_SESSION = array();
				$_SESSION['IPAddress'] = $_SERVER['REMOTE_ADDR'];
				$_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
				self::regenerateSession();
			}

			// give a 5% chance that the session id regenerates on a request
			elseif(rand(1, 100) <= 5){
				self::regenerateSession();
			}
		}else{
			// destroy the session
			$_SESSION = array();
			session_destroy();
			session_start();
		}
	}

	// make sure the session is not being hijacked
	private static function isSessionBeingHijacked(){
		// fields are not set, session may not exist or it is being hijacked
		if(!isset($_SESSION['IPAddress']) || !isset($_SESSION['userAgent'])){
			return true;
		}

		// make sure the session is being accessed by the same ip address that started it
		if($_SESSION['IPAddress'] != $_SERVER['REMOTE_ADDR']){
			return true;
		}

		// make sure user agent is the same as the one which started the session
		if($_SESSION['userAgent'] != $_SERVER['HTTP_USER_AGENT']){
			return true;
		}

		return false;
	}

	public static function regenerateSession(){
		// if the session is obsolete that means there is a new session id
		if(isset($_SESSION['obsolete']) || $_SESSION['obsolete'] == true){
			return;
		}

		// make the session obsolete and valid for 10 seconds
		$_SESSION['obsolete'] = true;
		$_SESSION['expirationTime'] = time() + 10;

		// regenerate session id without destroying the old session
		session_regenerate_id(false);

		// get the new session id
		$sessionId = session_id();

		// store session data and end the session
		session_write_close();

		// set the new session id and restart it
		session_id($sessionId);

		// turn off strict mode so that user defined session ids could be accepted
		ini_set('session.use_strict_mode', 0);

		// start the session
		session_start();

		// switch strict mode back off
		ini_set('session.use_strict_mode', 1);

		// unset obsolete and expirationTime properties for the new session
		unset($_SESSION['obsolete']);
		unset($_SESSION['expirationTime']);
	}

	// make sure the session in not obsolete or expired
	private static function isSessionValid(){
		if(isset($_SESSION['obsolete']) && !isset($_SESSION['expirationTime'])){
			return false;
		}

		if(isset($_SESSION['expirationTime']) && $_SESSION['expirationTime'] < time()){
			return false;
		}

		return true;
	}

	/*
	*	Meaning of PHP Session functions
	*
	*	session_start() - starts new or resumes existing session
	*
	*	session_destroy() - destroys all data associated with the current session, but it 
	*		does not destroy global session variables or the session cookie, to use session
	*		again start_session() needs to be called
	*		to destroy session data you should set $_SESSION to an empty array instead of calling
	*		session_destroy()
	*
	*	session_write_close() - write session data and end the session
	*
	*	session_id() - gets the id of the current session
	*	session_id([string $id]) - set a new session id
	*
	*	session_regenerate_id() - replace the current session id with a newly generated one while
	*		keeping all session data
	*/
}

class SessionManager2 {

	public static function initialize(){
		ini_set('session.use_strict_mode', 1);
	}

	public static function setSessionName($name){
		return session_name($name);
	}

	public static function setCookieParams($lifetime = 0, $path = '/', $domain = null, $secure = null, $httpOnly = true){
		$domain = isset($domain) ? $domain : (isset($_SERVER['SERVER_NAME']) ? isset($_SERVER['SERVER_NAME']) : null);
		$secure = isset($secure) ? $secure : (isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : null);

		session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);
	}

	private static function validateClientInformation(){
		if(!isset($_SESSION['IPAddress']) || !isset($_SESSION['userAgent'])){
			return false;
		}

		if($_SESSION['IPAddress'] != $_SERVER['REMOTE_ADDR']){
			return false;
		}

		if($_SESSION['userAgent'] != $_SERVER['HTTP_USER_AGENT']){
			return false;
		}

		return true;
	}

	private function validateSession(){
		if(isset($_SESSION['expirationTime']) && $_SESSION['expirationTime'] < time()){
			return false;
		}

		return true;
	}

	public static function regenerateSessionId(){
		if(isset($_SESSION['expirationTime'])){
			return;
		}

		$_SESSION['expirationTime'] = time() + 10;

		session_regenerate_id(false);
		$newSessionId = session_id();
		session_write_close();
		session_id($newSessionId);
		session_start();

		unset($_SESSION['expirationTime']);
	}

	public static function startSession(){
		session_start();

		if(self::validateSession()){
			if(!self::validateClientInformation()){
				$_SESSION = array();
				$_SESSION['IPAddress'] = $_SERVER['REMOTE_ADDR'];
				$_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
				self::regenerateSessionId();
			}elseif(mt_rand(1, 100) <= 5){
				self::regenerateSessionId();
			}
		}else{
			$_SESSION = array();
			session_destroy();
			session_start();
		}
	}
}

function demonstrateSessions(){
	SessionManager2::setSessionName("some bullshit");
	SessionManager2::setCookieParams();
	SessionManager2::startSession();

	$_SESSION['data'] = "I will succeed at any fucking cost...";

	SessionManager2::regenerateSessionId();

	echo $_SESSION['data'];
}
// ********* End of Cookies and Sessions ********* //

?>
