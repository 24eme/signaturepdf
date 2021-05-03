<?php

/*

	Copyright (c) 2009-2019 F3::Factory/Bong Cosca, All rights reserved.

	This file is part of the Fat-Free Framework (http://fatfreeframework.com).

	This is free software: you can redistribute it and/or modify it under the
	terms of the GNU General Public License as published by the Free Software
	Foundation, either version 3 of the License, or later.

	Fat-Free Framework is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
	General Public License for more details.

	You should have received a copy of the GNU General Public License along
	with Fat-Free Framework.  If not, see <http://www.gnu.org/licenses/>.

*/

namespace DB\Jig;

//! Jig-managed session handler
class Session extends Mapper {

	protected
		//! Session ID
		$sid,
		//! Anti-CSRF token
		$_csrf,
		//! User agent
		$_agent,
		//! IP,
		$_ip,
		//! Suspect callback
		$onsuspect;

	/**
	*	Open session
	*	@return TRUE
	*	@param $path string
	*	@param $name string
	**/
	function open($path,$name) {
		return TRUE;
	}

	/**
	*	Close session
	*	@return TRUE
	**/
	function close() {
		$this->reset();
		$this->sid=NULL;
		return TRUE;
	}

	/**
	*	Return session data in serialized format
	*	@return string
	*	@param $id string
	**/
	function read($id) {
		$this->load(['@session_id=?',$this->sid=$id]);
		if ($this->dry())
			return '';
		if ($this->get('ip')!=$this->_ip || $this->get('agent')!=$this->_agent) {
			$fw=\Base::instance();
			if (!isset($this->onsuspect) ||
				$fw->call($this->onsuspect,[$this,$id])===FALSE) {
				// NB: `session_destroy` can't be called at that stage;
				// `session_start` not completed
				$this->destroy($id);
				$this->close();
				unset($fw->{'COOKIE.'.session_name()});
				$fw->error(403);
			}
		}
		return $this->get('data');
	}

	/**
	*	Write session data
	*	@return TRUE
	*	@param $id string
	*	@param $data string
	**/
	function write($id,$data) {
		$this->set('session_id',$id);
		$this->set('data',$data);
		$this->set('ip',$this->_ip);
		$this->set('agent',$this->_agent);
		$this->set('stamp',time());
		$this->save();
		return TRUE;
	}

	/**
	*	Destroy session
	*	@return TRUE
	*	@param $id string
	**/
	function destroy($id) {
		$this->erase(['@session_id=?',$id]);
		return TRUE;
	}

	/**
	*	Garbage collector
	*	@return TRUE
	*	@param $max int
	**/
	function cleanup($max) {
		$this->erase(['@stamp+?<?',$max,time()]);
		return TRUE;
	}

	/**
	 *	Return session id (if session has started)
	 *	@return string|NULL
	 **/
	function sid() {
		return $this->sid;
	}

	/**
	 *	Return anti-CSRF token
	 *	@return string
	 **/
	function csrf() {
		return $this->_csrf;
	}

	/**
	 *	Return IP address
	 *	@return string
	 **/
	function ip() {
		return $this->_ip;
	}

	/**
	*	Return Unix timestamp
	*	@return string|FALSE
	**/
	function stamp() {
		if (!$this->sid)
			session_start();
		return $this->dry()?FALSE:$this->get('stamp');
	}

	/**
	*	Return HTTP user agent
	*	@return string|FALSE
	**/
	function agent() {
		return $this->_agent;
	}

	/**
	*	Instantiate class
	*	@param $db \DB\Jig
	*	@param $file string
	*	@param $onsuspect callback
	*	@param $key string
	**/
	function __construct(\DB\Jig $db,$file='sessions',$onsuspect=NULL,$key=NULL) {
		parent::__construct($db,$file);
		$this->onsuspect=$onsuspect;
		session_set_save_handler(
			[$this,'open'],
			[$this,'close'],
			[$this,'read'],
			[$this,'write'],
			[$this,'destroy'],
			[$this,'cleanup']
		);
		register_shutdown_function('session_commit');
		$fw=\Base::instance();
		$headers=$fw->HEADERS;
		$this->_csrf=$fw->hash($fw->SEED.
			extension_loaded('openssl')?
				implode(unpack('L',openssl_random_pseudo_bytes(4))):
				mt_rand()
			);
		if ($key)
			$fw->$key=$this->_csrf;
		$this->_agent=isset($headers['User-Agent'])?$headers['User-Agent']:'';
		$this->_ip=$fw->IP;
	}

}
