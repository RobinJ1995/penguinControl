<?php

class Page extends Eloquent
{
	protected $table = 'page';
	public $timestamps = true;
	protected $softDelete = true;
}
