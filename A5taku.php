<?php
/**
 * A5taku 5taku / 5tq file read class
 * The MIT License (MIT)
 * Copyright (c) 2017 masahiko tokita http://github.com/tokitam
 */

class A5taku {
    var $genre;
    private $fp;
    const MASK = 128;
    const SPACE = 0x20;
    const NUM_OF_GENRE = 8;

    public function __construct() {
	$this->genre = array();
    }
    
    public function load($filename) {
	$this->fp = fopen($filename, 'r');
	if (!$this->fp) {
	    throw new Exception('File not found.');
	}
	
	$this->read_all();
    }
    
    public function read_all() {
	for ($i=0; $i < self::NUM_OF_GENRE; $i++) {
	    $this->read_header($i);
	}
	for ($i=0; $i < self::NUM_OF_GENRE; $i++) {
	    $size = $this->genre[$i]['size'];
	    for ($j=0; $j < $size; $j++) {
		$this->genre[$i][$j]['question'] = $this->read_question();
		$this->genre[$i][$j]['answer'] = $this->read_answer();
	    }
	}
    }
    
    private function read_header($genre_no) {
	$title = $this->read_title();
	$pass = $this->read_pass();
	$size = $this->read_size();
	$skip = $this->read_skip();
	$this->read_player_file();
	$this->read_magic_code();
	$this->read_fill();
	
	$this->genre[$genre_no]['title'] = $title;
	$this->genre[$genre_no]['size'] = $size;
    }
    
    private function read_title() {
	return $this->read_raw_string(16);
    }

    private function read_pass() {
	$this->read_raw_skip(2);
    }

    private function read_size() {
	return ord($this->mygetc()) + ord($this->mygetc()) * 256;
    }

    private function read_skip() {
	return ord($this->mygetc())  + ord($this->mygetc()) * 256;
    }
    
    private function read_player_file() {
	$this->read_raw_skip(12);
    }
    
    private function read_magic_code() {
	$this->read_raw_skip(8);
    }
    
    private function read_fill() {
	$this->read_raw_skip(214);
    }
    
    private function read_question() {
	return $this->read_raw_string(116, true);
    }

    private function read_answer() {
	$answer_list = [];
	for ($i=0; $i < 5; $i++) {
	    $answer[$i] = $this->read_raw_string(28, true);
	}
	
	return $answer;
    }

    private function read_raw_skip($length) {
	for ($i=0; $i < $length; $i++) {
	    $this->mygetc();
	}
    }

    private function read_raw_string($length, $flg_mask=false) {
	$i = 0;
	$s = '';
	while (false !== ($char = $this->mygetc())) {
	    $char = ord($char);
	    if ($flg_mask) {
		if ($char == self::SPACE) {
		    $s .= ' ';
		} else {
		    $s .= chr($char ^ self::MASK);
		}
	    } else {
		$s .= chr($char);
	    }
	    $i++;
	    if ($length <= $i) {
		break;
	    }
	}

	return trim(mb_convert_encoding($s, 'utf8', 'sjis'));
    }
    
    private function mygetc() {
	return fgetc($this->fp);
    }
}

