<?php
namespace App\Repositories\Location;

use App\Model\Entity\Location;
use App\Model\Table\LocationsTable;
use Cake\Core\Configure;
use Cake\I18n\Date;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;

/**
 * Class LocationRepository
 *
 * @package App\Repositories\Location
 */
class LocationRepository
{
	/** @var  App\Controller\Component\Preferences */
	protected $Preferences;

	/**
	 * Create a new LocationRepository instance.
	 */
	public function __construct()
	{
	}

	/**
	 * @param Location $location
	 *
	 * @return boolean|null
	 */
	public function destroy(Location $location)
	{
		return TableRegistry::get('Locations')->delete($location);
	}

    /**
     * @param $user_id
	 * @param $search
     *
     * @return location
     */
    public function findParents(array $user, $search = null)
    {
		$locations = TableRegistry::get('Locations')
            ->find()
			->order(['Locations.lft' => 'ASC']);

		$locations = $locations
			->where([
				'parent_id IS' => null
			]);

		$locations = $locations
            ->find('ownedBy',
				['user' => $user]
			);

        return $this->search($locations, $search);
    }

    /**
     * @param $user_id
	 * @param $search
     *
     * @return location
     */
    public function findChildren($locations, array $user)
    {
		$Locations = TableRegistry::get('Locations');
		$counter = 0;
		$rows = [];

		foreach($locations as $location) {
			$none = false;
			$rows[$counter] = $location;
			$rows[$counter]['children'] = $Locations
				->find('children', ['for' => $location->id])
				->find('threaded')
				->find('ownedBy',
					['user' => $user]
				)
				->order(['lft' => 'ASC']);
			$counter++;
		}
		return $rows;
    }

	/**
	 * @param QueryObject $query
	 * @pram array $search
	 *
	 * @return QueryObject
	 */
	public function search($query, array $search = null)
	{
		if ($search === null) {
			return $query;
		}

		$query = $query
			->where([
				'Locations.name LIKE' => '%' . $search . '%'
			]);

		return $query;
	}

	/**
	 * @param array $data
	 *
	 * @return Location
	 */
	public function store(array $data)
	{
		$Locations = TableRegistry::get('Locations');
		$location = $Locations->newEntity();
		$location = $Locations->patchEntity($location, $data);

		#### Hard coding for now #####################
		$location->user_id = 2;
		##############################################

		$Locations->save($location);

		return $location;
	}

	/**
	 * @param	Category	$category
	 * @access	public
	 * @return	array
	 */
	public function titlesNotAccounts($category)
	{
		// limit to identified account types
		$types = Configure::read('lf.accountTypesByIdentifier.budget');

		$accounts = TableRegistry::get('Accounts')
			->getAccounts($types, $category->user_id);

		if ($accounts->count() > 0) {
			return $accounts
				->extract('title')
				->toArray();
		}
		return [];
	}

	/**
	 * @param Category $category
	 * @param array    $data
	 *
	 * @return Category
	 */
	public function update(Category $category, array $data)
	{
		$Categories = TableRegistry::get('Categories');
        $data['expiration'] = $data['expiration']['year'] == '' ? null : Time::now()
								->year($data['expiration']['year'])
								->month($data['expiration']['month'])
								->day($data['expiration']['day']);

		$category = $Categories->patchEntity($category, $data);
		if ($Categories->save($category)) {
			// recover TreeBehavior
			$Categories->recover();
		} else {
			Log::write('debug', 'File: ' . __FILE__ . '. Line: ' . __LINE__ . '. update() failed to save category: '.json_encode($category->errors()));
		}
		return $category;
	}
}
