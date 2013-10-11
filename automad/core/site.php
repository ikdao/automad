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
 *	AUTOMAD CMS
 *
 *	(c) 2013 by Marc Anton Dahmen
 *	http://marcdahmen.de
 *
 */


/**
 *	The Site class includes all methods and properties regarding the site, structure and pages.
 */

 
class Site {
	
	
	/**
	 * 	Array holding the site's settings.
	 */
	
	public $siteData = array();
	
	
	/**
	 * 	Array holding all the site's pages and the related data. 
	 *	
	 *	To access the data for a specific page, use the url as key: $this->siteCollection['url'].
	 */
	
	public $siteCollection = array();
	
	
	/**
	 * 	Parse Site settings.
	 *
	 *	Get all sitewide settings (like site name, the theme etc.) from the main settings file 
	 *	in the root of the content directory.
	 */
	
	private function parseSiteSettings() {
		
		$this->siteData = Data::parseTxt(SITE_CONTENT_DIR . '/' . SITE_SETTINGS_FILE);
		
	}
	
	
	/**
	 *	Builds an URL out of the parent URL and the actual file system folder name.
	 *
	 *	It is important to only transform the actual folder name (slug) and not the whole path,
	 *	because of handling possible duplicate parent folder names right.
	 *	If there are for example two folders on the level above, called xxx.folder/ and yyy.folder/,
	 *	they will be transformed into folder/ and folder-1/. If the URL from yyy.folder/child/ is made from the whole path,
	 *	it will return folder/child/ instead of folder-1/child/, even if the parent URL would be folder-1/. 
	 *
	 *	The prefix for sorting (xxx.folder) will be stripped.
	 *	In case the resulting url is already in use, a suffix (-1, -2 ...) gets appende to the new url.
	 *
	 *	@param string $parentUrl
	 *	@param string $slug
	 *	@return string $url
	 */
	
	private function makeUrl($parentUrl, $slug) {
		
		// strip prefix regex replace pattern
		$pattern = '/[a-zA-Z0-9_-]+\./';
		$replacement = '';
		
		// Build URL:
		// The ltrim (/) is needed to prevent a double / in front of every url, 
		// since $parentUrl will be empty for level 0 and 1 (//path/to/page).
		// Trimming all '/' and then prependig a single '/', makes sure that there is always just one slash 
		// at the beginning of the URL. 
		// The leading slash is better to have in case of the home page where the key becomes [/] insted of just [] 
		$url = '/' . ltrim($parentUrl . '/' . preg_replace($pattern, $replacement, $slug), '/');
	
		// check if url already exists
		if (array_key_exists($url, $this->siteCollection)) {
							
			$i = 0;
			
			$newUrl = $url;
			
			while (array_key_exists($newUrl, $this->siteCollection)) {
				$i++;
				$newUrl = $url . "-" . $i;
			}
			
			$url = $newUrl;
			
		}
		
		return $url;
		
	}
	
	
	/**
	 *	Searches $relPath recursively for files with the DATA_FILE_EXTENSION and adds the parsed data to $siteCollection.
	 *
	 *	After successful indexing, the $siteCollection holds basically all information (except media files) from all pages of the whole site.
	 *	This makes searching and filtering very easy since all data is stored in one place.
	 *	To access the data of a specific page within the $siteCollection array, the page's url serves as the key: $this->siteCollection['/path/to/page']
	 *
	 *	@param string $relPath 
	 *	@param number $level 
	 *	@param string $parentRelUrl
	 */
	 
	private function collectPages($relPath = '', $level = 0, $parentRelUrl = '') {
		
		$fullPath = BASE . '/' . SITE_CONTENT_DIR . '/' . SITE_PAGES_DIR . '/' . $relPath;
				
		if ($dh = opendir($fullPath)) {
		
			while (false !== ($item = readdir($dh))) {
		
				if ($item != "." && $item != "..") {
					
					$itemFullPath = $fullPath . '/' . $item;
					
					$relUrl = $this->makeUrl($parentRelUrl, basename($relPath));
				
					// If $item is a file with the DATA_FILE_EXTENSION, $item gets added to the index.
					// In case there are more than one matching files, they get all added.
					if (is_file($itemFullPath) && strtolower(substr($item, strrpos($item, '.') + 1)) == DATA_FILE_EXTENSION) {
						
						$data = Data::parseTxt($itemFullPath);
						
						// In case the title is not set in the data file or is empty, use the slug of the URL instead.
						// In case the title is missig for the home page, use the site name instead.
						if (!array_key_exists('title', $data) || ($data['title'] == '')) {
							if ($relUrl) {
								$data['title'] = ucwords(basename($relUrl));
							} else {
								$data['title'] = $this->getSiteName();
							}
						} 
						
						// Extract tags
						$tags = Data::extractTags($data);
						
						// The relative URL ($relUrl) of the page becomes the key (in $siteCollection). 
						// That way it is impossible to create twice the same url and it is very easy to access the page's data. 	
						$P = new Page();
						$P->data = $data;
						$P->tags = $tags;
						$P->relUrl = $relUrl;
						$P->relPath = $relPath;
						$P->level = $level;
						$P->parentRelUrl = $parentRelUrl;
						$P->template = str_replace('.' . DATA_FILE_EXTENSION, '', $item);
						$this->siteCollection[$relUrl] = $P;
							
					}
					
					// If $item is a folder, $this->collectPages gets again executed for that folder (recursively).
					if (is_dir($itemFullPath)) {
						
						$this->collectPages(ltrim($relPath . '/' . $item, '/'), $level + 1, $relUrl);
						
					}
						
				}
			
			}
			
			closedir($dh);	
		
		}
			
	}
		
	
	/** 
	 *	Parse sitewide settings and create $siteCollection
	 */
	
	public function __construct() {
		
		$this->parseSiteSettings();
		$this->collectPages();
		
	}

	
	/**
	 *	Return a key from $siteData (sitename, theme, etc.).
	 *
	 *	@param string $key
	 *	@return string $this->siteData[$key]
	 */
	
	public function getSiteData($key) {
		
		if (array_key_exists($key, $this->siteData)) {
			return $this->siteData[$key];
		}
			
	}
	 
	
	/**
	 *	Return the name of the website - shortcut for $this->getSiteData('sitename').
	 *
	 *	@return string $this->getSiteData('sitename')
	 */
	
	public function getSiteName() {
		
		if ($this->getSiteData('sitename')) {
			return $this->getSiteData('sitename');
		}
		
	}
	
	
	/**
	 * 	Return the theme for the website.
	 *
	 *	@return string $this->getSiteData('theme')
	 */
	
	public function getTheme() {
		
		$theme = $this->getSiteData('theme');

		// check if theme is defined in the settings file
		if ($theme) {
			
			$themeDir = BASE . '/' . SITE_THEMES_DIR . '/' . $theme;
			
			// check if theme exists
			if (is_dir($themeDir)) {
				return $theme;
			} else {
				return SITE_DEFAULT_THEME;
			}
			
		} else {
			return SITE_DEFAULT_THEME;
		}
		
	}
	
	
	/**
	 * 	Return $siteCollection array.
	 *
	 * 	@return array $this->siteCollection
	 */
	
	public function getCollection() {
		
		return $this->siteCollection;
		
	}
		 
		 
	/**
	 * 	Return the page object for the passed relative URL.
	 * 
	 *	@param string $url
	 *	@return object $page
	 */ 

	public function getPageByUrl($url) {
		
		return $this->siteCollection[$url];
		
	} 

	 
	/**
	 * 	Return the page object for the current page.
	 *
	 *	@return object $currentPage
	 */ 
	
	public function getCurrentPage() {
		
		if (isset($_SERVER["PATH_INFO"])) {
			$url = '/' . trim($_SERVER["PATH_INFO"], '/');
		} else {
			$url = '/';
		}
			
		return $this->getPageByUrl($url);
		
	} 
	 	 
	 
}


?>
