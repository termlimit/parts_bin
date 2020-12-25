<?php
namespace App\Model\Table;

use App\Model\Entity\User;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\I18n\Time;
use Cake\Mailer\Email;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use SoftDelete\Model\Table\SoftDeleteTrait;

/**
 * Users Model
 *
 * @property \Cake\ORM\Association\HasMany $Bookmarks
 */
class UsersTable extends Table
{
	use SoftDeleteTrait;
	protected $softDeleteField = 'deleted_date';

	/**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('users');
        $this->displayField('id');
        $this->primaryKey('id');
		$this->addBehavior('Timestamp');

		$this->hasMany('Accounts');
		$this->hasMany('AccountTypes');
		$this->hasMany('Bills');
		$this->hasMany('Budgets');
		$this->hasMany('Categories');
		$this->hasMany('TransactionJournals');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('id', 'create');

        $validator
			->add('username', 'valid', ['rule' => 'alphaNumeric'])
			->add('username', 'minLength', [
				'rule' => ['minLength', 3],
				'message' => 'Usernames must be at least 3 characters long'
			])
			->requirePresence('username', 'create')
            ->notEmpty('username', 'A username is required');

		$validator
            ->add('email', 'valid', ['rule' => 'email'])
			->requirePresence('email', 'create')
			->notEmpty('email', 'An email is required');

        $validator
			->requirePresence('password', 'create')
			->add('password', 'minLength', [
				'rule' => ['minLength',  7],
				'message' => 'Passwords must be at least 7 characters long'
			])
            ->notEmpty('password', 'A password is required');

		$validator
			->notEmpty('confirm_password', 'Please confirm your password')
			->add('confirm_password', 'isEqualTo', [
				'rule' => ['isEqualTo', 'password'],
				'message' => 'Both passwords must match.'
            ]);

		$validator
			->add('role', 'valid', ['rule' => 'numeric'])
			->allowEmpty('role', 'create');

        return $validator;
    }

	/**
	 * Determine if two fields are of equal value
	 *
	 * @param string $check the first field to compare
	 * @param string $otherfield the second field to compare
	 * @return boolean true on equal value
	 */
	public function isEqualTo($check, $otherfield)
	{
		//get name of field
		$fname = '';
		foreach ($check as $key => $value){
			$fname = $key;
			break;
		}
		return $this->data[$this->name][$otherfield] === $this->data[$this->name][$fname];
	}

	/**
	 * Notify admin of new user registration
	 *
	 * @param Event  $event
	 * @param Entity $entity
	 * @param array  $options
	 * @return null
	 */
	public function afterSave($event, $entity, $options)
	{
		if ($entity->isNew()) {

			$ev = new Event('Model.User.afterAdd', $this, [
				'user' => $entity
			]);
			EventManager::instance()->dispatch($ev);
		}
	}

	/**
	 * Notify admin of user deletion
	 *
	 * @param Event  $event
	 * @param Entity $entity
	 * @param array  $options
	 * @return null
	 */
	public function afterDelete($event, $entity, $options)
	{
		$ev = new Event('Model.User.afterDelete', $this, [
			'user' => $entity
		]);
		EventManager::instance()->dispatch($ev);
	}

	/**
	 * findAuth allows a greater level of control authenticating users
	 *
	 * @param \Cake\ORM\Query $query
	 * @param array $options
	 * @return \Cake\ORM\Query $query
	 */
	public function findAuth(\Cake\ORM\Query $query, array $options)
	{
		$query
			->select(['id', 'username', 'password'])
			->where([
				'Users.active' => 1,
				'Users.deleted_date IS NULL'
			]);

		return $query;
	}

	/**
	 * findAuth allows a greater level of control authenticating users
	 *
	 * @param \Cake\ORM\Query $query
	 * @param array $options
	 * @return \Cake\ORM\Query $query
	 */
	public function findOwnedBy(Query $query, array $options)
	{
		return $query->where(['id' => Configure::read('GlobalAuth.id')]);
	}

	/**
	 * Generate an activation key
	 *
	 * @return string $md5String
	 */
	public function activationKey()
	{
		return md5(uniqid());
	}

	/**
	 * save default user preferences
	 *
	 * @param entity $user
	 */
	public function savePreferences(User $user)
	{
		$now = (new Time('now'))->format('Y-m-d');
		$Preferences = TableRegistry::get('Preferences');

		# Default value arrays
		$prefs	= ['last_activity', 'locale', 'setup', 'time_zone', 'tour'];
		$values	= [$now, 'en_US', 'true', 'America/Los_Angeles', 'true'];
		$descs	= ['Last activity for a user', 'Default location', 'New user walkthrough for initial data', 'Your timezone', 'Tour LoadedFinance'];

		foreach ($prefs as $index => $title) {
			// create preference:
			$preference					= $Preferences->newEntity();
			$preference->user_id		= $user->id;
			$preference->title			= $title;
			$preference->value			= $values[$index];
			$preference->description	= $descs[$index];
			$preference->modified		= $now;
			$preference->display 		= 1;
			$Preferences->save($preference);
		}
	}

	/**
	 * Send a confirmation email to the user
	 *
	 * @param object $user
	 */
	public function sendConfirmation($user)
	{
		$email = new Email();
		$email->template('register', 'default')
			->emailFormat('html')
			->to($user['email'])
			->from(['register@loadedfinance.com' => 'LoadedFinance.com'])
			->subject('[Loaded Finance] Please activate your account')
			->viewVars([
				'username' => $user['username'],
				'activation_key' => $user['activation_key']
			])
			->send();
	}

	/**
	 * Send a confirmation email to the user
	 *
	 * @param object $user
	 */
	public function sendValidationEmail($user)
	{
		$email = new Email();
		$email->template('validate_email', 'default')
			->emailFormat('html')
			->to($user['email'])
			->from(['register@loadedfinance.com' => 'LoadedFinance.com'])
			->subject('[Loaded Finance] Please validate your email address change')
			->viewVars([
				'username' => $user['username'],
				'email_token' => $user['email_token']
			])
			->send();
	}

	public function getIdByUsername($username)
	{
		$users = TableRegistry::get('Users');
		$user = $users->findByUsername($username)->first();
		return $user->id;
	}

	/**
	 * Soft delete a user
	 *
	 * @param int $id
	 * @return boolean true on success
	 */
	public function deactivate($user) {
		$user->active = false;
		$user->activation_key = $this->activationKey();
		$this->save($user);
		return $this->delete($user);
	}

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
		$rules->add($rules->isUnique(['email']));
        $rules->add($rules->isUnique(['username']));
        return $rules;
    }
}
