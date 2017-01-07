<?php

class Blogs extends Eloquent
{
	protected $guarded = array('id');
	protected $softDelete = true;

	public function site(){
		return $this->hasOne('Sites','acc','acc');
	}
}