<?php

class PaginationPresenter extends Illuminate\Pagination\Presenter
{
	public function getActivePageWrapper ($text)
	{
		return '<li class="current"><a href="">' . $text . '</a></li>';
	}

	public function getDisabledTextWrapper ($text)
	{
		return '<li class="unavailable"><a href="">' . $text . '</a></li>';
	}

	public function getPageLinkWrapper ($url, $page, $rel = null)
	{
		return '<li><a href="' . $url . '">' . $page . '</a></li>';
	}
}
