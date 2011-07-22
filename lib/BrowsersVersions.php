<?php

/*
 * Copyright (c) 2011 Sebastian Grodzicki
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * PHP library for getting latest browsers versions
 *
 * @package		BrowsersVersions
 * @author		Sebastian Grodzicki <sebastian@grodzicki.pl>
 * @copyright	Copyright (c) 2011 Sebastian Grodzicki (http://sebastian.grodzicki.pl)
 *
 */
class BrowsersVersions
{
	/** Major browsers */
	const FIREFOX	= 'Firefox';
	
	/** Operating systems */
	const WINDOWS	= 'Windows';
	const MACOSX	= 'Mac OS X';
	
	/** Default language */
	const DEFAULT_LANGUAGE = 'en';
	
	/**
	 * Holding the selected OS
	 * 
	 * @var		string
	 * @access	protected
	 */
	protected $_os;
	
	/**
	 * Holding the selected language
	 * 
	 * @var		string
	 * @access	protected
	 */
	protected $_language;

	/**
	 * Get the latest browser version
	 * 
	 * @param	string	$browser
	 * @param	string	$os [optional]
	 * @param	string	$language [optional]
	 * @return	string	Browser version
	 * @throws	Exception
	 */
	public function get($browser, $os = self::WINDOWS, $language = self::DEFAULT_LANGUAGE)
	{
		$this->_setOs($os);
		$this->_setLanguage($language);
		
		switch ($browser) {
			case self::FIREFOX:
				return $this->_getFirefoxVersion();
				
			default:
				throw new Exception('Unsupported browser');
		}
	}
	
	/**
	 * Setting operating system
	 * 
	 * @param	string	$os
	 * @return	void
	 * @throws	Exception
	 * @access	protected
	 */
	protected function _setOs($os)
	{
		switch ($os) {
			case self::WINDOWS:
			case self::MACOSX:
				$this->_os = $os;
			break;
			
			default:
				throw new Exception('Unsupported operating system');
		}
	}
	
	/**
	 * Getting selected operating system
	 * 
	 * @return	string
	 * @access	protected
	 */
	protected function _getOs()
	{
		return $this->_os;
	}
	
	/**
	 * Setting language
	 * 
	 * @param	string	$language
	 * @return	void
	 * @access	protected
	 */
	protected function _setLanguage($language)
	{
		$this->_language = $language;
	}
	
	/**
	 * Getting selected language
	 * 
	 * @return	string
	 * @access	protected
	 */
	protected function _getLanguage()
	{
		return $this->_language;
	}
	
	/**
	 * Getting latest version of Firefox
	 * 
	 * @return	string
	 * @throws	Exception
	 * @access	protected
	 */
	protected function _getFirefoxVersion()
	{
		// OS mapping
		switch ($this->_getOs()) {
			case self::WINDOWS:
				$os			= 'win32';
				$pattern	= '/Firefox Setup (([0-9]+)\.([0-9]+)(\.([0-9]+))?).exe/';
			break;
			
			case self::MACOSX:
				$os			= 'mac';
				$pattern	= '/Firefox (([0-9]+)\.([0-9]+)(\.([0-9]+))?).dmg/';
			break;
			
			default:
				throw new Exception('Unsupported operating system');
		}
		
		// FTP connection
		$host		= 'releases.mozilla.org';
		$user		= 'Anonymous';
		$pass		= '';
		
		$connection	= @ftp_connect($host);
		if (false === $connection) {
			throw new Exception('Unable to connect to Mozilla');
		}
		
		if (false === @ftp_login($connection, $user, $pass)) {
			throw new Exception('Unable to login to Mozilla');
		}
		
		// File list
		$dir   = sprintf('/pub/mozilla.org/firefox/releases/latest/%s/%s', $os, $this->_getLanguage());
		$files = ftp_nlist($connection, $dir);
		
		foreach ($files as $file) {
			preg_match($pattern, $file, $matches);
			
			if (!isset($matches[2], $matches[3])) {
				continue;
			}
			
			$major   = $matches[2];
			$minor   = $matches[3];
			
			if (isset($matches[5])) {
				$release = $matches[5];
				
				return sprintf('%d.%d.%d', $major, $minor, $release);
			} else {
				return sprintf('%d.%d', $major, $minor);
			}
		}
		
		throw new Exception('Nothing found');
	}
}

?>