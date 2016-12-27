<?php

class Article2 extends Eloquent
{
	protected $table = 'article2';
	protected $guarded = array('id');

	public function blog(){
		return $this->hasOne('Blogs','id','blogid');
	}
}
