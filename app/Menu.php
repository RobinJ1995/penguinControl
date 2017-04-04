<?php

namespace App;

use App\Models\Group;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Support\Facades\Auth;

class Menu
{
	public static function getControl ()
	{
		$user = Auth::user ();
		$items = array ();
		$gid;
		
		if (empty ($user))
		{
			$gid = 1200; // gid == 1200 ? Gebruiker is niet ingelogd //
			
			$items = MenuItem::where ('parent', 0)
				->whereNull ('gid_access')
				->orderBy ('order', 'asc')
				->get ();
		}
		else
		{
			$gid = $user->getLowestGid ();
			
			$items = MenuItem::where ('parent', 0)
				->where ('id', '!=', 5) // menu 5 = staff //
				->where
				(
					function ($query) use ($gid)
					{
						$query->whereNull ('gid_access')
							->orWhere ('gid_access', '>=', $gid);
					}
				)
				->orderBy ('order', 'asc')
				->get ();
		}
		
		foreach ($items as $item)
		{
			$children = MenuItem::where ('parent', $item->id)
				->where
				(
					function ($query) use ($gid)
					{
						$query->whereNull ('gid_access')
							->orWhere ('gid_access', '>=', $gid);
					}
				)
				->orderBy ('order', 'asc');
			
			if ($children->count () > 0)
			{
				$item->hasChildren = true;
				$item->children = $children->get ();
			}
		}
		
		return $items;
	}
	
	public static function getSite ()
	{
		return Page::where ('published', '1')
			->orderBy ('weight')
			->get ();
	}
	
	public static function getStaff ()
	{
		$user = Auth::user ();
		$items = array ();
		$gid;
		
		if (! empty ($user))
		{
			$gid = $user->getLowestGid ();
			$administratorGroup = Group::where ('name', 'administrator')->first ();
			
			if ($administratorGroup && $gid <= $administratorGroup->gid)
			{
				$items = MenuItem::where ('gid_access', '<=', $administratorGroup->gid)
					->where ('parent', 5) // menu 5 = staff //
					->where ('gid_access', '>=', $gid)
					->orderBy ('order', 'asc')
					->get ();
			}
		}
		else
		{
			$gid = 1200;
		}
		
		foreach ($items as $item)
		{
			$children = MenuItem::where ('parent', $item->id)
				->where
				(
					function ($query) use ($gid)
					{
						$query->whereNull ('gid_access')
						->orWhere ('gid_access', '>=', $gid);
					}
				)
				->orderBy ('order', 'asc');
			
			if ($children->count () > 0)
			{
				$item->hasChildren = true;
				$item->children = $children->get ();
			}
		}
		
		return $items;
	}
}