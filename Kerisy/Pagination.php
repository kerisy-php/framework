<?php

/*
* This file is part of the Kerisy package.
*
* (c) Brian Zou <zoujiaqing@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

class Kerisy_Pagination
{	
	private $base_url			= ''; // The page we are linking to

	private $total_rows			=  0; // Total number of items (database results)
	private $per_page			= 10; // Max number of items you want shown per page
	private $num_links			=  3; // Number of "digit" links to show before/after the currently viewed page
	private $cur_page			=  1; // The current page being viewed
	private $next_link			= '下一页';
	private $prev_link			= '上一页';
	
	private $link_formate = '<li><a href="[base_url]?page=[page_digit]">[page]</a></li>';
	private $current_page_link_formate = '<li><strong>[page_digit]</strong></li>';
	private $hide_link_formate = '<li><span>...</span></li>';
	
	
	public function  __construct(array $params)
	{
		foreach ($params as $key => $value) 
		{
			$this->$key = $value;
		}
	}
	
	public function getLinks()
	{
		if ($this->total_rows == 0 || $this->per_page == 0)
		{
			return '';
		}
		if ($this->cur_page < 1)
		{
			$this->cur_page = 1;
		}
		$num_pages = ceil($this->total_rows / $this->per_page);
		
		$base_page = 0;
		// Calculate the start and end numbers. These determine
		// which number to start and end the digit links with
		$start = (($this->cur_page - $this->num_links) > 0) ? $this->cur_page - $this->num_links : 1;
		$end   = (($this->cur_page + $this->num_links) < $num_pages) ? $this->cur_page + $this->num_links : $num_pages;
		
	// And here we go...
		$output = '';

		// Render the "First" link
		if ($this->cur_page == 1)
		{
			$output .= str_replace('[page_digit]', $this->prev_link, $this->current_page_link_formate);
		}
		else 
		{
			$output .= str_replace(array('[page_digit]', '[base_url]', '[page]'), array($this->cur_page - 1, $this->base_url, $this->prev_link), $this->link_formate);
		}
		

		if ($start > 2)
		{
			$output .= str_replace(array('[page_digit]', '[base_url]', '[page]'), array(1, $this->base_url, 1), $this->link_formate);
			$output .= $this->hide_link_formate;
		}
		else if ($start == 2 ) 
		{
			$output .= str_replace(array('[page_digit]', '[base_url]', '[page]'), array(1, $this->base_url, 1), $this->link_formate);
		}
		//$start ==1
		for ($i = $start; $i <= $end; $i++)
		{
			if ($i == $this->cur_page)
			{
				$output .= str_replace('[page_digit]', $i, $this->current_page_link_formate);
			}
			else 
			{
				$output .= str_replace(array('[page_digit]', '[base_url]', '[page]'), array($i, $this->base_url, $i), $this->link_formate);
			}
		}
		
		if ($end < $num_pages - 2 )
		{
			$output .= $this->hide_link_formate;
			$output .= str_replace(array('[page_digit]', '[base_url]', '[page]'), array($num_pages - 1, $this->base_url, $num_pages - 1), $this->link_formate);
		}
		else if ($end == $num_pages -2)
		{
			$output .= str_replace(array('[page_digit]', '[base_url]', '[page]'), array($num_pages - 1, $this->base_url, $num_pages - 1), $this->link_formate);
		}
		
		if ($this->cur_page == $num_pages)
		{
			$output .= str_replace('[page_digit]', $this->next_link, $this->current_page_link_formate);
		}
		else 
		{
			$output .= str_replace(array('[page_digit]', '[base_url]', '[page]'), array($this->cur_page + 1, $this->base_url, $this->next_link), $this->link_formate);
		}

		return $output;
		
	}
}