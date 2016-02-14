<?php 
/*
 *	                  ....
 *	                .:   '':.
 *	                ::::     ':..
 *	                ::.         ''..
 *	     .:'.. ..':.:::'    . :.   '':.
 *	    :.   ''     ''     '. ::::.. ..:
 *	    ::::.        ..':.. .''':::::  .
 *	    :::::::..    '..::::  :. ::::  :
 *	    ::'':::::::.    ':::.'':.::::  :
 *	    :..   ''::::::....':     ''::  :
 *	    :::::.    ':::::   :     .. '' .
 *	 .''::::::::... ':::.''   ..''  :.''''.
 *	 :..:::'':::::  :::::...:''        :..:
 *	 ::::::. '::::  ::::::::  ..::        .
 *	 ::::::::.::::  ::::::::  :'':.::   .''
 *	 ::: '::::::::.' '':::::  :.' '':  :
 *	 :::   :::::::::..' ::::  ::...'   .
 *	 :::  .::::::::::   ::::  ::::  .:'
 *	  '::'  '':::::::   ::::  : ::  :
 *	            '::::   ::::  :''  .:
 *	             ::::   ::::    ..''
 *	             :::: ..:::: .:''
 *	               ''''  '''''
 *	
 *
 *	AUTOMAD
 *
 *	Copyright (c) 2016 by Marc Anton Dahmen
 *	http://marcdahmen.de
 *
 *	Licensed under the MIT license.
 *	http://automad.org/license
 */


namespace Automad\GUI;


defined('AUTOMAD') or die('Direct access not permitted!');


/**
 *	The Text class provides all methods related to the text modules used in the GUI. 
 *
 *	@author Marc Anton Dahmen <hello@marcdahmen.de>
 *	@copyright Copyright (c) 2016 Marc Anton Dahmen <hello@marcdahmen.de>
 *	@license MIT license - http://automad.org/license
 */

class Text {
	
	
	/**
	 *	Array of GUI text modules.
	 */
	
	private static $modules = array();
	
	
	/**
	 *	Parse the text modules file and store all modules in Text::$modules.
	 */
	
	public static function parseModules() {
		
		Text::$modules = \Automad\Core\Parse::textFile(AM_FILE_GUI_TEXT_MODULES);
		
		array_walk(Text::$modules, function(&$item) {
			$item = \Automad\Core\String::markdown($item, true);
		});
			
	}
	
	
	/**
	 *	Return the requested text module.
	 *
	 *	@param string $key
	 *	@return The requested text module
	 */
	
	public static function get($key) {
		
		if (isset(Text::$modules[$key])) {
			return Text::$modules[$key];
		}
		
	}
	
	
}


?>