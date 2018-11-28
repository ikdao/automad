<?php

namespace Automad\Tests;

use Automad\Core as Core;
use PHPUnit\Framework\TestCase;

defined('AUTOMAD') or die('Direct access not permitted!');


class Mock extends TestCase {
	
		
	/**
	 *	Create a mock of the Automad object with a single page.
	 * 	A template can be passed optionally to the page.
	 * 	
	 * 	@param string $template
	 * 	@return object The Automad Mock
	 */
		
	public function createAutomad($template = '') {
			
		$SharedMock = $this->createMock('\Automad\Core\Shared');
		
		$collection = array();
		$collection['/'] = new Core\Page(
			array(
				'title' => 'Test',
				'url' => AM_REQUEST,
				'theme' => '../automad/tests/templates',
				':template' => $template,
				'date' => '2018-07-21 12:00:00',
				'test' => 'Test String',
				'quoted' => '"Quoted" "Test" "String"',
				'x' => '10'
			), 
			$SharedMock
		);
		
	    $methods = array_diff(
	        get_class_methods('\Automad\Core\Automad'), 
	        array(
				'getPage',
				'getRequestedPage',
				'getFilelist',
				'getPagelist',
				'loadTemplate'
			)
	    );
	
    	$AutomadMock = $this->getMockBuilder('\Automad\Core\Automad')
    	                    ->setMethods($methods)
                            ->disableOriginalConstructor()
                            ->getMock();
							
		$AutomadMock->method('getCollection')->willReturn($collection);
		$AutomadMock->Context = new Core\Context($collection['/']);
		
		return $AutomadMock;
		
	}
	
	
}