<?php

class UserSites extends Eloquent
{
	protected $table = 'user_site';
	protected $guarded = [];
	public $timestamps = false;
	public $incrementing = false;

	public function site(){
		return $this->hasOne('Sites','id','site_id');
	}

}	