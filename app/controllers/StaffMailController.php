<?php

class StaffMailController extends BaseController
{
	public function search ()
	{
		$term = Input::get ('term');
		$username = Input::get ('username');
		
		$mUsersQuery = MailUserVirtual::where ('email', 'LIKE', '%' . $term . '%');
		$mFwdsQuery = MailForwardingVirtual::where
		(
			function ($query) use ($term)
			{
				$query->where ('source', 'LIKE', '%' . $term . '%')
					->orWhere ('destination', 'LIKE', '%' . $term . '%');
			}
		);
		$domainsQuery = MailDomainVirtual::where ('domain', 'LIKE', '%' . $term . '%');
		
		
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
			
			$mUsersQuery = $mUsersQuery->where ('uid', $uid);
			$mFwdsQuery = $mFwdsQuery->where ('uid', $uid);
			$domainsQuery = $domainsQuery->where ('uid', $uid);
		}
		
		$mUsersCount = $mUsersQuery->count ();
		$mFwdsCount = $mFwdsQuery->count ();
		$domainsCount = $domainsQuery->count ();
		
		$mUsers = $mUsersQuery->paginate ();
		$mFwds = $mFwdsQuery->paginate ();
		$domains = $domainsQuery->paginate ();
		
		$searchUrl = action ('StaffMailController@search');
		
		return View::make ('staff.mail.search', compact ('mUsers', 'mFwds', 'domains', 'mUsersCount', 'mFwdsCount', 'domainsCount', 'searchUrl'));
	}
}