codeigniter-mongo-pagination-library
====================================

Pagination library for MongoDB and Codeigniter

Requirements:
-------------
1. CodeIgniter 2.1.4  (http://ellislab.com/codeigniter)
2. MongoDB Library for CodeIgniter  (https://github.com/alexbilbie/codeigniter-mongodb-library)

Install
-------


1. Add MongoPagination.php file to your application/libraries folder.
	(Note that this library is to be used with https://github.com/alexbilbie/codeigniter-mongodb-library only. So make sure you already have the following files present:
	Mongo_db.php in your /application/libraries folder
	mongodb.php to your /application/config folder.)



Input Parameters:
-----------------
@param $mongo_db - MongoDB handler
@param $parameters - 
		$pagination->setParameters(array(
		  '#collection'	=>  $collection_name,
		  '#select'		=> 	array(field_names),
		  '#find'		=> 	array(where_conditions),
		  '#sort'		=>  array(field_name => 1 / -1),
		), $currentOffset, $itemsPerPage);
		
Output sample:
--------------
Array(
    [dataset] => Array(
            [0] => Array(
                    [_id]  => MongoId Object([$id] => 5230695e94d03c3cf2608c8c)
                    [name] => 'Test'
                )
        	)
    [totalPages] => 2
    [totalItems] => 10
   )

Example to use Mongo Pagination in your model file:
---------------------------------------------------

		//Pagination Sample code
		if((isset($parameters['total_rows']) && !empty($parameters['total_rows'])) && (isset($parameters['start_rows'])))
		{
			$itemsPerPage   = $parameters['total_rows'];
			$currentPage    = $parameters['start_rows'];
		}
		else {
			$currentPage = false;
			$itemsPerPage = false;
		}
		
		//$this->mongo_db is the handler of MongoDB library
		$pagination = new MongoPagination($this->mongo_db, $parameters);
		$pagination->setParameters(array(
		  '#collection'	=>  'user',
		  '#select'		=> 	array('_id'),
		  '#find'		=> 	array('age'=>'25'),
		  '#sort'		=>  array('timestamp' => -1),
		), $currentPage, $itemsPerPage);
		
		$dataSet = $pagination->Paginate();
		$result = $dataSet['dataset'];
		$output['pagination']['totalPages'] = $dataSet['totalPages'];
		$output['pagination']['totalItems'] = $dataSet['totalItems'];
		$output['pagination']['links'] = $pagination->getPageLinks();
		
