<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Ftp;
use App\Models\Group;
use App\Models\Log;
use App\Models\MailDomain;
use App\Models\MailForward;
use App\Models\MailUser;
use App\Models\MenuItem;
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
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class StaffMailController extends Controller
{
	public function search ()
	{
		$term = Input::get ('term');
		$username = Input::get ('username');
		
		$mUsersQuery = MailUser::with ('user');
		$mFwdsQuery = MailForward::with ('user');
		$domainsQuery = MailDomain::with ('user');
		
		if (! empty ($term))
		{
			$mUsersQuery->where ('email', 'LIKE', '%' . $term . '%')
				->orWhereHas ('MailDomain', function( $query)use ($term){
					$query->where ('domain', 'LIKE' , '%' . $term . '%');
				});

			$mFwdsQuery->where ('source', 'LIKE', '%' . $term . '%')
				->orWhere ('destination', 'LIKE', '%' . $term . '%')
				->orWhereHas ('MailDomain', function( $query)use ($term){
					$query->where ('domain', 'LIKE' , '%' . $term . '%');
				});

			$domainsQuery->where('domain', 'LIKE', '%' . $term . '%');
		}
		
		if (! empty ($username))
		{
			$uid = '';
			
			$users = UserInfo::where ('username', $username);

			$user = $users->first ();
			if (! empty ($user))
			{
				$user = $user->getUser ();
				$uid = $user->uid;
			}
			
			$mUsersQuery->where ('uid', $uid);
			$mFwdsQuery->where ('uid', $uid);
			$domainsQuery->where ('uid', $uid);
		}
		
		$mUsersCount = $mUsersQuery->count ();
		$mFwdsCount = $mFwdsQuery->count ();
		$domainsCount = $domainsQuery->count ();
		
		$mUsers = $mUsersQuery->paginate ();
		$mFwds = $mFwdsQuery->paginate ();
		$domains = $domainsQuery->paginate ();
		
		$searchUrl = action ('Staff\StaffMailController@search');
		
		return view ('staff.mail.search', compact ('mUsers', 'mFwds', 'domains', 'mUsersCount', 'mFwdsCount', 'domainsCount', 'searchUrl'));
	}
}