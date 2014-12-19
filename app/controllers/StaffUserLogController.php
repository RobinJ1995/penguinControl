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
			->whereHas ('user_info',
				function ($q) use ($username, $name, $email, $schoolnr)
				{
					$q->where ('validated', '1')
					->where ('username', 'LIKE', '%' . $username . '%')
					->where (DB::raw ('CONCAT (fname, " ", lname)'), 'LIKE', '%' . $name . '%')
					->where ('email', 'LIKE', '%' . $email . '%')
					->where ('schoolnr', 'LIKE', '%' . $schoolnr . '%');
				}
			);

		if (! empty ($time_van) && !empty ($time_tot))
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

	public function editChecked ()
	{
		if (! empty (Input::get ('userLogId')))
		{
			$userLogsIds = Input::get ('userLogId');
			$boekhouding = Input::get ('boekhouding');
			
			$userLogs = UserLog::whereIn ('id', $userLogsIds);
		}
		else
			$alert = 'Geen wijzigingen doorgevoerd';

		if (isset ($userLogs))
		{
			if(! empty (Input::get ('facturatie')))
			{
				$userLogs->update (array ('boekhouding' => $boekhouding));
				$alert = 'Facturatie(s) gewijzigd';
			}
			
			if(! empty (Input::get ('export')))
			{
				$boekhoudingBetekenis = $this->boekhoudingBetekenis;
				return View::make ('staff.user.log.export', compact ('userLogsIds', 'boekhoudingBetekenis'));
			}
		}
		
		return Redirect::to ('/staff/user/log')->with ('alerts', array (new Alert ($alert, 'success')));
		
	}
	
	public function export ()
	{
		if (! empty (Input::get ('userLogId')))
		{
			$userLogsIds = json_decode (Input::get ('userLogId'));
			$userLogs = UserLog::whereIn ('id', $userLogsIds);
		}
		
		$boekhouding = Input::get ('boekhouding');
		$exportSeperator = Input::get ('seperator');
		
		$csvHeader = array
			(
				'user_info.id'=>'id',
				'user_info.username'=>'gebruikersnaam',
				'user_info.fname'=>'voornaam',
				'user_info.lname'=>'achternaam',
				'user_info.email'=>'e-mail',
				'user_info.schoolnr'=>'r-nummer',
				'user_info.lastchange'=>'lastchange',
				'user_info.validated'=>'validated',
				'user_log.id'=>'user_log_id',
				'user_log.time'=>'datum/tijd',
				'user_log.nieuw'=>'nieuw',
				'user_log.boekhouding'=>'boekhouding'
			);
		
		
		$fields = Input::get ('exportFields');

		$output = array ();
		$userLogsUserInfo = $userLogs->with ('user_info')->get ();

		if ($boekhouding != 'unchanged')
			$userLogs->update (array ('boekhouding' => $boekhouding));
		
		switch ($exportSeperator)
		{
			case 0:
				$seperator = ',';
				break;
			case 1:
				$seperator = ';';
				break;
			default :
				$seperator = '';
				break;
		}

		foreach ($fields as $field)
			$userOutput[]= $csvHeader[$field];
		
		$output[]= implode($seperator, $userOutput);

		foreach ($userLogsUserInfo as $user_log)
		{
			$userOutput = array ();
			$user_info = $user_log->user_info;

			foreach ($fields as $field)
			{
				if ( array_key_exists ($field, $csvHeader))
				{
					$arr =  explode('.', $field);
					$table = $arr[0];
					$tableField = $arr[1];
					// escape values with double quotes
					$userOutput[] = '"'.${$table}->{$tableField}.'"';
				}
			}

			$output[]= implode($seperator, $userOutput);
		}

		$csvOutput = rtrim (implode (PHP_EOL, $output), "\n");
		$fileName = 'export/sin_facturatie_'.date('Y_m_d_H_i_s').'.csv';
		$fileHandle = fopen($fileName, 'w');
		fwrite($fileHandle, $csvOutput);
		fclose($fileHandle);
		$fileLink='<a href="/'.$fileName.'" target="_blank">'.$fileName.'</a>';
		$alert = 'Facturatie(s) gewijzigd en ge&euml;xporteerd';
		
		return Redirect::to ('/staff/user/log')->with ('alerts', array (
		    new Alert ($alert, 'success'),
		    new Alert ($fileLink, 'info')
		    ));
			
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

	public function edit ($userlog)
	{
		$boekhoudingBetekenis = $this->boekhoudingBetekenis;
		
		return View::make ('staff.user.log.edit', compact ('userlog', 'boekhoudingBetekenis'));
	}

	public function update ($userlog)
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
			return Redirect::to ('/staff/user/log/' . $userlog->id . '/edit')->withInput ()->withErrors ($validator);
		
		$userlog->boekhouding = Input::get ('boekhouding');
		$userlog->save ();

		return Redirect::to ('/staff/user/log')->with ('alerts', array (new Alert ('Facturatie bijgewerkt', 'success')));
	}

	public function remove ($userlog)
	{
		$userlog->delete ();

		return Redirect::to ('/staff/user/log')->with ('alerts', array (new Alert ('Facturatie verwijderd', 'success')));
	}

}
