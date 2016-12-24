<?php

class Users extends Eloquent
{
	protected $guarded = array('id');
	protected $softDelete = true;
}	