<?php
namespace App\Model\Table;

use ArrayObject;
use App\Model\Entity\Category;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Reconciles Model
 *
 * @property \Cake\ORM\Association\belongsTo $Users
 * @property \Cake\ORM\Association\belongsTo $Categories
 */
class ReconcilesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
	public function initialize(array $config)
	{
        parent::initialize($config);

		$this->belongsTo('Users');
		$this->belongsTo('Categories');

		$this->addBehavior('Sluggable');
	}

	public $validate = array(
		'amount' => array(
			'notempty' => array(
				'rule' => array('notEmpty'),
				'message' => 'This field is required',
			),
			'value' => array(
				'rule' => array('decimal', 2),
				'message' => 'Value must be a decimal',
			),
		),
		'category_id' => array(
			'notempty' => array(
				'rule' => array('notEmpty'),
				'message' => 'A category is required',
			),
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
		'user_id' => array(
			'notempty' => array(
				'rule' => array('notEmpty'),
				'message' => 'This field is required',
			),
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
	);

	public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
	{
		$data['user_id'] = Configure::read('GlobalAuth.id');
		$data['amount'] = sprintf('%0.2f', $data['amount']);
	}
}
