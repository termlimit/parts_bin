<td>
<?php
if ($n === 1) {
	$type = isset($transaction->_matchingData['TransactionTypes']) ? $transaction->_matchingData['TransactionTypes']['type'] : $transaction->transaction_type['type'];
	$c_or_d = $transaction->transactions[0]['credit'] != 0 ? $transaction->transactions[0]['credit'] : $transaction->transactions[0]['debit'];
	$amount = $this->Journal->correctAmountByType($type, $c_or_d);
?>
<span class="<?=$amount < 0 ? 'text-danger' : 'text-success'?>"><?=$this->Number->currency($amount, $transaction->transaction_currency['code'])?></span><br />
<?php
} else {
// 4 different transaction types;
for ($x = count($transaction->transactions)-1; $x >= 0; $x--) {
$type = isset($transaction->_matchingData['TransactionTypes']) ? $transaction->_matchingData['TransactionTypes']['type'] : $transaction->transaction_type['type'];
$c_or_d = $transaction->transactions[$x]['credit'] != 0 ? $transaction->transactions[$x]['credit'] : $transaction->transactions[$x]['debit'];
$amount = $this->Journal->correctAmountByType($type, $c_or_d);
?>
<span class="<?=$amount < 0 ? 'text-danger' : 'text-success'?>"><?=$this->Number->currency($amount, $transaction->transaction_currency['code'])?></span><br />
<?php } } ?>
</td>