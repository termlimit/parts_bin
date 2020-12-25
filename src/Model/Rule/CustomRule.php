<?php
namespace App\Model\Rule;

use Cake\Datasource\EntityInterface;

class CustomRule
{
	public function __invoke(EntityInterface $entity, array $options)
	{
		// Do work
		return false;
	}
}

// Add the custom rule
use App\Model\Rule\CustomRule;

$rules->add(new CustomRule(...), 'ruleName');

http://book.cakephp.org/3.0/en/orm/validation.html#creating-custom-rule-objects
