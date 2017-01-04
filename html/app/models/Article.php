<?php

class Article extends Eloquent
{
	protected $table = 'article';
	protected $guarded = array('id');

	public function blog(){
		return $this->hasOne('Blogs','id','blogid');
	}
}