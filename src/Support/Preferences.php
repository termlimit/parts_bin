<?php
namespace App\Support;

use App\Model\Table\PreferencesTable;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;

/**
 * Class Preferences
 *
 * @package App\Support
 */
class Preferences
{

	/**
	 * @param      string $name
	 * @param string      $default
	 *
	 * @return null|App\Model\Table\PreferencesTable
	 */
	public static function get($title, $default = null)
	{
		$fullName = 'Preference' . Configure::read('GlobalAuth.id') . $title;
/* 		if (Cache::has($fullName)) {
			return Cache::get($fullName);
		} */

		$preference = TableRegistry::get('Preferences')
			->find()
			->where([
				'user_id' => Configure::read('GlobalAuth.id'),
				'title'   => $title
			])
			->first();

		if ($preference) {
			// Cache::forever($fullName, $preference);

			return $preference->value;
		}
		// no preference found and default is null:
		if (is_null($default)) {
			// return NULL
			return null;
		}

		return $default;
		#return $this->set($title, $default);
	}

	/**
	 * @return string
	 */
	public function lastActivity()
	{
		$preference = $this->get('last_activity', Time::now()->format('Y-m-d H:i:s'));

		return $preference;
	}

	/**
	 * @return bool
	 */
	public static function mark()
	{
		Preferences::set('last_activity', Time::now()->format('Y-m-d H:i:s'));

		return true;
	}

	/**
	 * @param        $name
	 * @param string $value
	 *
	 * @return Preference
	 */
	public static function set($name, $value)
	{
		#$fullName = 'preference' . Auth::user()->id . $name;
		#Cache::forget($fullName);
		$Preferences = TableRegistry::get('Preferences');
		$query = $Preferences->query();
		$query->update()
			->set(['value' => $value])
			->where([
				'title' => $name,
				'user_id' => Configure::read('GlobalAuth.id')
			])
			->execute();

		if ($query) {
			return true;
		}
		return false;
	}
}
