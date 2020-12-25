<?php foreach ($transaction->category_transaction_journal as $category):?>
	<?=$this->Html->link(__(h($category['category']['title'] . ' ('.$this->Number->currency($category['amount'], $transaction->transaction_currency['code']).')')), ['controller' => 'categories', 'action' => 'view', $category['category']['id']]) . '<br>'?>
<?php endforeach;?>