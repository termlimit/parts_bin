<?php
namespace App\Repositories\Part;

use App\Model\Entity\Part;
use App\Model\Table\PartsTable;
use Cake\Collection\Collection;
use Cake\Collection\Iterator\SortIterator;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\Date;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;

/**
 * Class PartRepository
 *
 * @package App\Repositories\Part
 */
class PartRepository
{
	/** @var  App\Controller\Component\Preferences */
	protected $Preferences;

	/**
	 * Create a new PartRepository instance.
	 */
	public function __construct()
	{
	}

	/**
	 * @return boolean|null
	 */
	public function add()
	{
        $parts = TableRegistry::get('Parts')
            ->find('all',
				['contain' => [
					'Packaging',
					'PartTypes',
					'Locations',
					'Attachments'
			]])
			->order(['Parts.part_number' => 'ASC']);

			return $parts;
	}

	/**
	 * @param Part $part
	 *
	 * @return boolean|null
	 */
	public function destroy(Part $part)
	{
		return TableRegistry::get('Parts')->delete($part);
	}

    /**
     * @param $user_id
	 * @param $search
     *
     * @return part
     */
    public function find(array $user, $search = null)
    {
        $parts = TableRegistry::get('Parts')
            ->find('all',
				['contain' => [
					'Packaging',
					'PartTypes',
					'Locations',
					'Attachments',
					'PartPurchases'
			]])
			->order(['Parts.part_number' => 'ASC']);

		$parts = $parts
            ->find('ownedBy',
				['user' => $user]
			);

        return $this->search($parts, $search);
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
				'Parts.description LIKE' => '%' . $search . '%'
			]);

		return $query;
	}

	/**
	 * @param array $data
	 *
	 * @return Part
	 */
	public function store(array $data)
	{
		$Parts = TableRegistry::get('Parts');
		$part = $Parts->newEntity();
		$part = $Parts->patchEntity($part, $data);

		#### Hard coding for now #####################
		$part->user_id = 2;
		$part->attachment_id = 1;
		##############################################

		$Parts->save($part);

		return $part;
	}

	/**
	 * @param Part  $part
	 * @param array $data
	 *
	 * @return Part
	 */
	public function update(Part $part, array $data)
	{
		$Parts = TableRegistry::get('Parts');

		$part->packaging_id			= $data['packaging_id'];
		$part->part_type_id			= $data['part_type_id'];
		$part->location_id			= $data['location_id'];
		$part->attachment_id		= $data['attachment_id'];
		$part->part_number			= $data['part_number'];
		$part->description			= $data['description'];
		$part->price				= $data['price'];
		$part->link					= $data['link'];

		$Parts->save($part);
		return $part;
	}
}
