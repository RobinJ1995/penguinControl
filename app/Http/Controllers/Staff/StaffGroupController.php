<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Ftp;
use App\Models\Group;
use App\Models\Log;
use App\Models\MailDomain;
use App\Models\MailForward;
use App\Models\MailUser;
use App\Models\Page;
use App\Models\SystemTask;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\UserInfo;
use App\Models\UserLimit;
use App\Models\UserLog;
use App\Models\Vhost;
use App\Alert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class StaffGroupController extends Controller
{
	public function index ()
	{
		$groups = Group::all ();
		
		return view ('staff.user.group.index', compact ('groups'));
	}
	
	public function create ()
	{
		$gids = array ();
		foreach (Group::all () as $group)
			$gids[] = $group->gid;
		
		return view ('staff.user.group.create', compact ('gids'));
	}

	public function store ()
	{
		$reservedGroups = array ();
		$reservedGids = array ();
		$etcGroup = explode (PHP_EOL, file_get_contents ('/etc/group'));
		
		foreach ($etcGroup as $entry)
		{
			if (! empty ($entry))
			{
				$fields = explode (':', $entry, 4);

				$reservedGroups[] = $fields[0];
				$reservedGids[] = $fields[2];
			}
		}
		
		$strReservedGroups = implode (',', $reservedGroups);
		$strReservedGids = implode (',', $reservedGids);
		
		$validator = Validator::make
		(
			array
			(
				'GID' => request ('gid'),
				'Name' => request ('name')
			),
			array
			(
				'GID' => array ('required', 'unique:group,gid', 'integer', 'not_in:' . $strReservedGids),
				'Name' => array ('required', 'unique:group,name', 'alpha', 'max:30', 'not_in:' . $strReservedGroups)
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/user/group/create')->withInput ()->withErrors ($validator);
		
		$group = new Group ();
		$group->gid = request ('gid');
		$group->name = strtolower (request ('name'));
		
		$group->save ();
		
		Log::log ('User group created', NULL, $group);
		
		return Redirect::to ('/staff/user/group')->with ('alerts', array (new Alert ('Group created: ' . $group->name, Alert::TYPE_SUCCESS)));
	}
	
	public function remove ($group)
	{
		$group->delete ();
		
		Log::log ('User group removed', NULL, $group);
		
		return Redirect::to ('/staff/user/group')->with ('alerts', array (new Alert ('Group removed: ' . $group->name, Alert::TYPE_SUCCESS)));
	}

}