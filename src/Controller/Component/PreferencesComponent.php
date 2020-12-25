<?php
/**
 * Preferences component
 */
namespace App\Controller\Component;

use App\Model\Table\PreferencesTable;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;

/**
 * Preferences component
 */
class PreferencesComponent extends Component
{

	/**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

	/**
     * Logged in user id
     *
     * @var int
     */
    protected $user_id;

	/**
     * Preferences table
     *
     * @var PreferencesTable
     */
    protected $Preferences;

    /**
     * initialize
     *
     * @param array $options Options.
     * @return void
     */
    public function initialize(array $options)
    {
		$this->user_id = $this->config('user_id');
		$this->Preferences = TableRegistry::get('Preferences');
    }

	/**
	 * @param		string $title
	 * @param		string $default
	 *
	 * @return null|App\Model\Table\PreferencesTable
	 */
	public function get($title, $default = null)
	{
		$preference = $this->Preferences
			->find()
			->where([
				'user_id' => $this->user_id,
				'title'   => $title
			])
			->first();

		if ($preference) {
			return $preference->value;
		}
		// no preference found and default is null:
		if (is_null($default)) {
			// return NULL
			return null;
		}

		return $default;
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
	public function mark()
	{
		return $this->set('last_activity', Time::now()->format('Y-m-d H:i:s'));
	}

	/**
	 * @param        $title
	 * @param string $value
	 *
	 * @return Preference
	 */
	public function set($title, $value)
	{
		$query = $this->Preferences->query();
		$query->update()
			->set(['value' => $value])
			->where([
				'title' => $title,
				'user_id' => $this->user_id
			])
			->execute();

		if ($query) {
			return true;
		}
		return false;
	}
}