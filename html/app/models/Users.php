<?php

class Users extends Eloquent
{
	protected $guarded = array('id');
	protected $softDelete = true;

	public function sites(){
		return $this->hasMany('UserSites')->orderBy('site_id');
	}
}	