<?php

namespace App\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends BaseModel
{
	// Was `protected $softDelete = true`, which is Laravel 4 syntax and has been
	// a silent no-op since 5.0, so removing a page was a hard delete //
	use SoftDeletes;

	protected $table = 'page';
	public $timestamps = true;

	public function url ()
	{
		return route ('staff.page.edit', $this->name);
	}
	
	public function link ()
	{
		return '<a href="' . $this->url () . '">' . class_basename (static::class) . '#' . $this->id . '</a>';
	}
}
