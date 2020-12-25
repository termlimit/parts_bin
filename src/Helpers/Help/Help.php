<?php
namespace App\Helpers\Help;

use Cake\Core\App;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use League\CommonMark\CommonMarkConverter;
use Log;

/**
 * Class Help
 *
 * @package src\Helpers\Help
 */
class Help
{

	public $routes = [
		'none' => 'Please use an existing route',
		'accounts.add' => 'Add a new account',
		'bills.add' => 'Add a new bill',
		'bills.index' => 'View current bills',
		'categories.add' => 'Add a new category',
	];

    /**
     * @codeCoverageIgnore
     *
     * @param $key
     *
     * @return string
     */
	public function getRoute($route)
	{
		if (isset($this->routes[$route])) {
			return $this->routes[$route];
		} else {
			return 'none';
		}
	}

    /**
     * @codeCoverageIgnore
     *
     * @param $key
     *
     * @return string
     */
    public function getFromCache($key)
    {
        return Cache::get($key);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param $route
     *
     * @return array
     */
    public function getFromLocal($route)
    {
		$folder		= App::path('Help')[0];
        $file       = $folder . h($route) . '.md';
		$exists		= new File($file);
        $title      = $this->getRoute($route);
        $content    = [
            'text'  => '<p>There is no help for this route!</p>',
            'title' => $title,
        ];
        try {
			if ($exists->exists()) {
				$content['text'] = file_get_contents($file);
			}
        } catch (ErrorException $e) {
            Log::write('error', trim($e->getMessage()));
        }
        if (strlen(trim($content['text'])) == 0) {
            $content['text'] = '<p>There is no help for this route.</p>';
        }
        $converter       = new CommonMarkConverter();
        $content['text'] = $converter->convertToHtml($content['text']);

        return $content;

    }

    /**
     * @codeCoverageIgnore
     *
     * @param $route
     *
     * @return bool
     */
    public function hasRoute($route)
    {
        return Route::has($route);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param $route
     *
     * @return bool
     */
    public function inCache($route)
    {
        return Cache::has('help.' . $route . '.title') && Cache::has('help.' . $route . '.text');
    }

    /**
     * @codeCoverageIgnore
     *
     * @param       $route
     * @param array $content
     *
     * @internal param $title
     */
    public function putInCache($route, array $content)
    {
        Cache::put('help.' . $route . '.text', $content['text'], 10080); // a week.
        Cache::put('help.' . $route . '.title', $content['title'], 10080);
    }
}
