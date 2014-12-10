<?php

class StaffUserLogController extends BaseController
{

	private $boekhoudingBetekenis = array
	(
		-1 => 'Niet te factureren',
		0 => 'Nog te factureren',
		1 => 'Gefactureerd'
	);

	public function index ()
	{
		$userlogs = UserLog::orderBy ('time')
		    ->with ('user_info.user')
		    ->paginate ();

		$searchUrl = action ('StaffUserLogController@search');
		$boekhoudingBetekenis = $this->boekhoudingBetekenis;
		return View::make ('staff.user.log.index', compact ('userlogs', 'searchUrl', 'boekhoudingBetekenis'));
	}

	public function search ()
	{
		$username = Input::get ('username');
		$name = Input::get ('name');
		$email = Input::get ('email');
		$schoolnr = Input::get ('schoolnr');

		$time_van = Input::get ('time_van');
		$time_tot = Input::get ('time_tot');
		$nieuw = Input::get ('nieuw');
		$boekhouding = Input::get ('boekhouding');
		$pagination = Input::get ('pagination');

		$query = UserLog::with ('user_info.user')
		    ->whereHas ('user_info', function ($q) use ($username, $name, $email, $schoolnr)
			{
				$q->where ('validated', '1')
				->where ('username', 'LIKE', '%' . $username . '%')
				->where (DB::raw ('CONCAT (fname, " ", lname)'), 'LIKE', '%' . $name . '%')
				->where ('email', 'LIKE', '%' . $email . '%')
				->where ('schoolnr', 'LIKE', '%' . $schoolnr . '%');
			}
		);

		if (!empty ($time_van) && !empty ($time_tot))
		{
			$query->whereBetween ('time', array ($time_van, $time_tot));
		}
		else
		{
			if (! empty ($time_van))
				$query->where ('time', '>', $time_van);

			if (! empty ($time_tot))
				$query->where ('time', '<', $time_tot);
		}

		if ($nieuw != 'all')
			$query->where ('nieuw', $nieuw);

		if ($boekhouding != 'all')
			$query->where ('boekhouding', $boekhouding);

		$count = $query->count ();
		if ($pagination === 'true')
		{
			$userlogs = $query->paginate ();
			$paginationOn = true;
		}
		else
		{
			$userlogs = $query->get ();
			$paginationOn = false;
		}

		$searchUrl = action ('StaffUserLogController@search');
		$boekhoudingBetekenis = $this->boekhoudingBetekenis;

		return View::make ('staff.user.log.search', compact ('count', 'userlogs', 'searchUrl', 'boekhoudingBetekenis', 'paginationOn'));
	}

	public function create ()
	{
		$users = array ();

		foreach (UserInfo::orderBy ('username')->get () as $userInfo)
			$users[$userInfo->id] = $userInfo->username . ' (' . $userInfo->getFullName () . ', ' . $userInfo->schoolnr . ')';
		
		$boekhoudingBetekenis = $this->boekhoudingBetekenis;

		return View::make ('staff.user.log.create', compact ('users', 'boekhoudingBetekenis'));
	}

	public function store ()
	{
		$validator = Validator::make
		(
			array
			(
				'user_info_id' => Input::get ('user_info_id'),
				'Datum/tijd' => Input::get ('time'),
				'Nieuw' => Input::get ('nieuw'),
				'Gefactureerd' => Input::get ('boekhouding')
			),
			array
			(
				'user_info_id' => array ('required', 'numeric'),
				'Datum/tijd' => array ('required'),
				'Nieuw' => array ('required'),
				'Gefactureerd' => array ('required')
			)
		);

		if ($validator->fails ())
			return Redirect::to ('/staff/user/log/create')->withInput ()->withErrors ($validator);

		$userLog = new UserLog ();

		$userLog->user_info_id = Input::get ('user_info_id');
		$userLog->time = Input::get ('time');
		$userLog->nieuw = Input::get ('nieuw');
		$userLog->boekhouding = Input::get ('boekhouding');

		$userLog->save ();

		return Redirect::to ('/staff/user/log')->with ('alerts', array (new Alert ('Facturatie toegevoegd', 'success')));
	}

	public function edit ($userlogid)
	{
		$userlog = UserLog::find ($userlogid);

		return View::make ('staff.user.log.edit', compact ('userlog'));
	}

	public function update ($userlogid)
	{
		$validator = Validator::make
		(
			array
			(
				'Gefactureerd' => Input::get ('boekhouding')
			),
			array
			(
				'Gefactureerd' => array ('required')
			)
		);

		if ($validator->fails ())
			return Redirect::to ('/staff/user/log/' . $userlogid . '/edit')->withInput ()->withErrors ($validator);

		$userLog = UserLog::find ($userlogid);
		$userLog->boekhouding = Input::get ('boekhouding');
		$userLog->save ();

		return Redirect::to ('/staff/user/log')->with ('alerts', array (new Alert ('Facturatie bijgewerkt', 'success')));
	}

	public function remove ($userlogid)
	{
		$userlog = UserLog::find ($userlogid);
		$userlog->delete ();
		
		return Redirect::to ('/staff/user/log')->with ('alerts', array (new Alert ('Facturatie verwijderd', 'success')));
	}

	public function editChecked ()
	{
		$userLogsIds = Input::get ('userLogId');
		$boekhouding = Input::get ('boekhouding');

		UserLog::whereIn ('id', $userLogsIds)->update (array ('boekhouding' => $boekhouding));
		
		return Redirect::to ('/staff/user/log')->with ('alerts', array (new Alert ('Facturatie(s) aangepast', 'success')));
	}

}
