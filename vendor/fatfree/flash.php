<?php
/**
 * Flash - A Flash Messages Plugin for the PHP Fat-Free Framework
 *
 * The contents of this file are subject to the terms of the GNU General
 * Public License Version 3.0. You may not use this file except in
 * compliance with the license. Any of the license terms and conditions
 * can be waived if you get permission from the copyright holder.
 *
 * Copyright (c) 2020 ~ ikkez
 * Christian Knuth <ikkez0n3@gmail.com>
 * https://github.com/ikkez/F3-Sugar/
 *
 * @version 1.0.2
 * @date: 13.12.2020
 * @since: 21.01.2015
 **/

class Flash extends \Prefab {

	/** @var \Base */
	protected $f3;

	/** @var array */
	protected $msg;

	/** @var array */
	protected $key;

	/**
	 * Flash constructor.
	 * @param string $key
	 */
	public function __construct($key='flash') {
		$this->f3 = \Base::instance();
		$this->msg = &$this->f3->ref('SESSION.'.$key.'.msg');
		$this->key = &$this->f3->ref('SESSION.'.$key.'.key');
	}

	/**
	 * add a message to the stack
	 * @param $text
	 * @param string $status
	 */
	public function addMessage($text,$status='info') {
		$this->msg[] = ['text'=>$text,'status'=>$status];
	}

	/**
	 * dump all messages and clear them
	 * @return array
	 */
	public function getMessages() {
		$out = $this->msg;
		$this->clearMessages();
		return $out ?: [];
	}

	/**
	 * reset message stack
	 */
	public function clearMessages() {
		$this->msg = [];
	}

	/**
	 * check if there messages in the stack
	 * @return bool
	 */
	public function hasMessages() {
		return !empty($this->msg);
	}

	/**
	 * set a flash key
	 * @param $key
	 * @param bool $val
	 */
	public function setKey($key,$val=TRUE) {
		$this->key[$key] = $val;
	}

	/**
	 * get and clear a flash key, if it's existing
	 * @param $key
	 * @return mixed|null
	 */
	public function getKey($key) {
		$out = NULL;
		if ($this->hasKey($key)) {
			$out = $this->key[$key];
			unset($this->key[$key]);
		}
		return $out;
	}

	/**
	 * check if there's a flash key existing
	 * @param $key
	 * @return bool
	 */
	public function hasKey($key) {
		return ($this->key && array_key_exists($key, $this->key));
	}
}
