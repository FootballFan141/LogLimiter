<?php
/* This file is really *free* software, not like FSF ones.
*  Do what you want with this piece of code, I just enjoyed coding, don't care.
*/

/**
* Provides a simple way to implement a limitator for login attempts, including a logger.
* @author Francesco Cirac. <sydarex@gmail.com>
* @link http://sydarex.org
* @version 0.2
* @copyright Copyleft (c) 2009/2010 Francesco Cirac.
*/

/**
* LogLimiter class.
*
* Provides a simple way to implement a limitator for login attempts, including a logger.
* @author Francesco Cirac. <sydarex@gmail.com>
* @copyright Copyleft (c) 2009, Francesco Cirac.
*/
class LogLimiter {

	/**
	 * Max attempts concessed before blocking.
	 *
	 * @access private
	 * @var integer
	 */
	private $attempts = 0;

	/**
	 * Time of blocking (minutes).
	 *
	 * @access private
	 * @var integer
	 */
	private $delay = 0;

	/**
	 * Validity attempts in attempts counting (minutes)
	 *
	 * @access private
	 * @var integer
	 */
	private $validity = 0;

	/**
	 * MySQL connection handler.
	 *
	 * @access private
	 * @var resource
	 */
	private $db = null;

	/**
	 * Client IP.
	 *
	 * @access private
	 * @var string
	 */
	private $ip = null;

	/**
	 * Class constructor. Sets class vars and deletes expired attempts.
	 *
	 * @param resource $dbc database connection.
	 * @param integer $attempts max attempts concessed before blocking.
	 * @param integer $delay time of blocking (minutes).
	 * @param integer $validity validity attempts in attempts counting (minutes).
	 */
	function __construct($dbc, $attempts, $delay, $validity) {
		$this->db = $dbc;
		$this->ip = $_SERVER['REMOTE_ADDR'];
		$this->attempts = $attempts;
		$this->delay = $delay;
		$this->validity = $validity;
		$this->delExpired();
	}

	/**
	 * Deletes expired blocks and attempts from database.
	 * 
	 * @access private
	 */
	private function delExpired() {
		@mysqli_query($this->db,"DELETE FROM login_blocks WHERE expire<=".time());
		$t = time()-($this->delay*60);
		@mysqli_query($this->db,"DELETE FROM login_attempts WHERE date<=".$t);
	}

	/**
	 * Generates the cookie block.
	 */
	function ckGen() {
		$expire = ($this->delay*60)+time();
		setcookie("ll_block",md5(rand()), $expire);
	}

	/**
	 * Checks if there is a cookie block.
	 * 
	 * @return bool
	 */
	function ckBlock() {
		if (isset($_COOKIE['ll_block'])) return true;
		return false;
	}

	/**
	 * Generates the database block.
	 */
	function dbGen() {
		$expire = ($this->delay*60)+time();
		$q = @mysqli_query($this->db,"INSERT INTO login_blocks (ip, expire) VALUES ('".$this->ip."', ".$expire.")");
	}

	/**
	 * Checks if there is a database block.
	 * 
	 * @return bool
	 */
	function dbBlock() {
		$q = @mysqli_query($this->db,"SELECT * FROM login_blocks WHERE ip='".$this->ip."'");
		$rows = @mysqli_num_rows($q);
		if ($rows>0) return true;
		return false;
	}

	/**
	 * Logs a possible cracking attempt.
	 */
	function logCrack() {
		@mysqli_query($this->db,"INSERT INTO login_log (ip, date) VALUES ('".$this->ip."', ".time().")");
	}

	/**
	* Logs a failed login attempt.
	*/
	function logAttempt() {
		@mysqli_query($this->db,"INSERT INTO login_attempts (ip, date) VALUES ('".$this->ip."', ".time().")");
	}

	/**
	* Counts how many attempts from this IP.
	*/
	function countAttempt() {
		$res = @mysqli_query($this->db,"SELECT * FROM login_attempts WHERE ip='".$this->ip."'");
		return @mysqli_num_rows($res);
	}

	/**
	* Call this method when a login fails. Logs the attempt and checks if a block is needed. If is, does it.
	*/
	function fail() {
		$this->logAttempt();
		if ($this->countAttempt() >= $this->attempts) {
			$this->logCrack();
			$this->dbGen();
			$this->ckGen();
		}
	}

	/**
	* Call this method when a login goes right. Deletes the attempts from this IP.
	*/
	function login() {
		@mysqli_query($this->db,"DELETE FROM login_attempts WHERE ip='".$this->ip."'");
	}
 }
 ?>
