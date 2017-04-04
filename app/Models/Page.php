<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
	protected $table = 'page';
	public $timestamps = true;
	protected $softDelete = true;
	
	public function url ()
	{
		return action ('StaffPageController@edit', $this->id);
	}
	
	public function link ()
	{
		return '<a href="' . $this->url () . '">' . get_class () . '#' . $this->id . '</a>';
	}
}
