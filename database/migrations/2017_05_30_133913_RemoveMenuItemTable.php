<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveMenuItemTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up ()
	{
		Schema::dropIfExists ('menuitem');
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down ()
	{
		\Illuminate\Support\Facades\DB::statement ("CREATE TABLE IF NOT EXISTS `menuitem` (
  `id` int(4) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `parent` int(4) NOT NULL COMMENT '-1= not in menu, 0=menu header, else id of header',
  `name` varchar(32) NOT NULL COMMENT 'menu name',
  `url` varchar(128) DEFAULT NULL COMMENT 'url',
  `gid_access` int(11) DEFAULT '25',
  `order` tinyint(1) NOT NULL DEFAULT '0',
  `help` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`),
  KEY `parent` (`parent`),
  KEY `menuitem_ibfk_1_idx` (`gid_access`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=latin1;");
	}
}
