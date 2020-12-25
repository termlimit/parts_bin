<?php
namespace App\Model\Table;

use App\Model\Entity\UserLog;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use Cake\I18n\Time;
use Cake\Core\Configure;

/**
 * User Logs Model
 *
 */
class UserLogsTable extends Table
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

        $this->table('user_logs');
        $this->displayField('id');
        $this->primaryKey('id');
        //$this->belongsTo('Users');

        //$this->addBehavior('Timestamp');
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
            ->add('user_id', 'valid', ['rule' => 'numeric'])
            ->requirePresence('user_id', 'create');

        $validator
            ->add('user_ip', 'valid', ['rule' => 'ip'])
            ->requirePresence('user_ip', 'create')
            ->notEmpty('user_ip', 'An ip address is required');

        $validator
            ->add('datetime_login', 'valid', ['rule' => 'datetime'])
            ->requirePresence('datetime_login', 'create')
            ->notEmpty('datetime_login', 'Date time in required');

        return $validator;
    }

    /**
     * Function for registering user login
     */
    public function registerLogin($user_id)
    {
        $preferences = TableRegistry::get('Preferences');
        $now = Time::now();
        $now->timezone = 'America/Los_Angeles';
        $userLog = $this->newEntity();
        $data['user_id'] = $user_id;
        $data['username'] = null;
        $data['user_ip'] = $_SERVER['REMOTE_ADDR'];
        $data['datetime_login'] = $now;
        $data['browser'] = $_SERVER['HTTP_USER_AGENT'];
        $userLog = $this->patchEntity($userLog, $data);
        $this->save($userLog);
        return $userLog->id;
    }

    /**
     * Function for registering bad user login
     */
    public function registerBadLogin($username)
    {
        $now = Time::now();
        $now->timezone = 'America/Los_Angeles';
        $userLog = $this->newEntity();
        $data['user_id'] = 0;
        $data['username'] = $username;
        $data['user_ip'] = $_SERVER['REMOTE_ADDR'];
        $data['datetime_login'] = $now;
        $data['browser'] = $_SERVER['HTTP_USER_AGENT'];
        $userLog = $this->patchEntity($userLog, $data);
        $this->save($userLog);
        return $userLog->id;
    }

    /**
     * Function for registering user logout
     */
    public function registerLogout($id)
    {
        $now = Time::now();
        $now->timezone = $this->Preferences->get('time_zone', 'America/Los_Angeles');
        $log = $this
            ->find()
            ->where(['id' => $id])
            ->first();
        if ($log != null) {
            $userLog = $this->get($id);
            $data['datetime_logout'] = $now;
            $userLog = $this->patchEntity($userLog, $data);
            $this->save($userLog);
        }
    }

    /**
     * Function for registering update on field date_time_last_action
     */
    public function updateLastAction($id)
    {
        $now = Time::now();
        $now->timezone = $this->Preferences->get('time_zone', 'America/Los_Angeles');
        $userLog = $this->get($id);
        $data['datetime_last_action'] = $now;
        $userLog = $this->patchEntity($userLog, $data);
        $this->save($userLog);
    }
}
