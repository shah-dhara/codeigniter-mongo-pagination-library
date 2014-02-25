<?php
/**
 * 
 * CodeIgniter Library for Pagination with MongoDB
 * @category  Library
 * @package   CodeIgniter
 * @author    Dhara Shah
 * @copyright 2014 Dhara Shah.
 * @version	  Version 1.0
 *
 * @param $mongo_db - MongoDB handler
 * @param $parameters - 
 * $pagination->setParameters(array(
		  '#collection'	=>  $collection_name,
		  '#select'		=> array(field_names),
		  '#find'		=> array(where_conditions),
		  '#sort'		=>  array(field_name => 1 / -1),
		), $currentOffset, $itemsPerPage);
		
 * @output $dataset -
 * array(
    ['dataset'] => array(
            [0] => array(
                    ['_id']  => MongoId Object([$id] => 5230695e94d03c3cf2608c8c),
                    ['name'] => 'Test'
                )
        	),
    ['totalPages'] => 2,
    ['totalItems'] => 10
   )
 *
 */
class MongoPagination
{
	public function __construct($mongo_db ='', $parameters = array())
	{
		global $var;
		
		$this->query_string = http_build_query($parameters);
		$this->mongo_db = $mongo_db;
		$this->baseOffset = 0;
		$this->totalPages = 1;
	}

	public function setParameters($queryParam, $currentOffset = 0, $itemsPerPage = false)
	{
		$this->query = $queryParam;
		if(!empty($currentOffset) && is_numeric($currentOffset) && empty($itemsPerPage)) {
			$this->limitResult = $currentOffset;
		}
		else {
			$this->currentOffset = $currentOffset;
			$this->itemsPerPage = $itemsPerPage;
		}
		return true;
	}

	/**
	 * 	Paginate MongoResults
	 */
	public function Paginate() {
		global $var;

		$collection = (!empty($this->query['#collection']))?$this->query['#collection']:die('MongoPagination: no collection found');
		$select = (!empty($this->query['#select']))?$this->query['#select']:array();
		$find = (!empty($this->query['#find']))?$this->query['#find']:array();
		$sort = (!empty($this->query['#sort']))?$this->query['#sort']:array();

		//  Get total results count
		$this->totalItemCount = $this->mongo_db->where($find)->count($collection);
		
		/*	Enable Limit based Query	*/
		if(!empty($this->limitResult)) {
			$resultSet = $this->mongo_db
			->select($select)
			->where($find)
			->order_by($sort)
			->limit($this->limitResult)
			->get($collection);
			 
			return array(
	        'dataset'		=>    $resultSet,
	        'totalItems'	=>    $this->totalItemCount
			);
		}
		/*	Enable Pagination based Query	*/
		else 
		{
			if(!$this->itemsPerPage)
				$this->totalPages = 1;
			else 
				$this->totalPages = floor($this->totalItemCount / $this->itemsPerPage);
			
			// Is the page offset beyond the result range?
			// If so we show the last page
			if ($this->currentOffset > $this->totalItemCount)
			{
				$this->currentOffset = ($this->totalPages - 1) * $this->itemsPerPage;
			}
		
			$resultSet = $this->mongo_db
			->select($select)
			->where($find)
			->order_by($sort)
			->limit($this->itemsPerPage)
			->offset($this->currentOffset)
			->get($collection);

			return array(
		        'dataset'		=>    $resultSet,
		        'totalPages'	=>    $this->totalPages,
		        'totalItems'	=>    $this->totalItemCount
			);
		}
	}
	 
	/**
	 * 	Generate HTML Based Page Links
	 */
	public function getPageLinks($setVisiblePagelinkCount = 3, $type = 'HTML') {
		global $var;

		$html = '';
		// If our item count or per-page total is zero there is no need to continue.
		if ($this->totalItemCount == 0 OR (isset($this->itemsPerPage) && $this->itemsPerPage == 0))
		{
			return $html;
		}
		if($this->totalPages <= 1) {
			return $html;
		}

		$html = '<div class="MongoPagination">';
		$html .= '<span><a href="'.$this->preparePageLink($this->baseOffset).'">First</a></span><br/>';
		if(0 != $this->currentOffset) {
			$html .= '<span><a href="'.$this->preparePageLink($this->currentOffset - $this->itemsPerPage).'">previous</a></span><br/>';
		}
		$VisiblePagelinkCount = 1;
		$current_page = floor(($this->currentOffset )/ $this->itemsPerPage)+1; 
		
		// Calculate the start and end numbers. These determine
		// which number to start and end the digit links with
		$start = (($current_page - $setVisiblePagelinkCount) > 0) ? $current_page - ($setVisiblePagelinkCount - 1) : 1;
		$end   = (($current_page + $setVisiblePagelinkCount) < $this->totalPages) ? $current_page + $setVisiblePagelinkCount : $this->totalPages;
	
		
		for($i=$current_page ; $i <= $end; $i++) { // Shows page numbers from current page
		//for($i=$start ; $i <= $end; $i++) {
			
			if($VisiblePagelinkCount <= $setVisiblePagelinkCount) {
				if($current_page == $i) {
					$html .= '<span><a class="active" href="'.$this->preparePageLink($this->currentOffset).'">'.($i).'</a></span><br/>';
				}
				else {
					$html .= '<span><a href="'.$this->preparePageLink(( $i * $this->itemsPerPage) - ($this->itemsPerPage)).'">'.($i).'</a></span><br/>';
				}
			}
			$VisiblePagelinkCount++;
		}
		if($this->totalPages > $current_page) {
			$html .= '<span><a href="'.$this->preparePageLink($this->currentOffset + $this->itemsPerPage).'">next &raquo;</a></span><br/>';
		}
		$html .= '<span><a href="'.$this->preparePageLink(($this->totalPages -1) * $this->itemsPerPage).'">Last</a></span>';
		$html .= '</div>';
		
		return $html;
	}

	private function preparePageLink($currentPageIndex = 1) {
		global $var;
		$pageUrl = current_url();
	  	
		parse_str($this->query_string, $queryString);
	  	$queryString['start_rows'] = $currentPageIndex;
	  	$pageUrl .= "?".http_build_query($queryString);
		
		return $pageUrl;
	}
}
