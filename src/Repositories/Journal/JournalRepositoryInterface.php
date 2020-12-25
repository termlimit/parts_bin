<?php
/**
 * JournalRepositoryInterface.php
 */
declare(strict_types=1);

namespace App\Repositories\Journal;

use App\Log\Engine\DatabaseLog;
use App\Model\Entity\TransactionJournal;
use App\Model\Entity\TransactionType;
use App\Model\Table\Account;
use App\Model\Table\AccountTypesTable;
use App\Model\Table\BudgetsTable;
use App\Model\Table\CategoriesTable;
use App\Model\Table\CategoryTransactionJournalTable;
use App\Model\Table\TransactionsTable;
use App\Model\Table\TransactionJournalsTable;
use App\Model\Table\TransactionTypesTable;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;

/**
 * Interface JournalRepositoryInterface.
 */
interface JournalRepositoryInterface
{
	/**
	 * Check totals for the transaction match
	 *
	 * @param array
	 *
	 * @return bool true on no errors
	 */
	public function checkTotalsMatch(array $data): bool;

	/**
	 * Check if the transaction is split, require existing accounts
	 *
	 * @param array
	 *
	 * @return bool true on no errors
	 */
	public function checkAccountsExist(array $data): bool;

    /**
     * Deletes a journal.
     *
     * @param TransactionJournal $journal
     *
     * @return bool
     */
    public function delete(TransactionJournal $journal): bool;

    /**
     * Find a specific journal.
     *
     * @param int $journalId
     *
     * @return TransactionJournal
    public function find(int $journalId): TransactionJournal;
     */

    /**
     * @param int $transactionid
     *
     * @return Transaction|null
    public function findTransaction(int $transactionid): ?Transaction;
     */

    /**
     * Get users very first transaction journal.
     *
     * @return TransactionJournal
     */
    public function first(): TransactionJournal;

    /**
     * @param TransactionJournal $journal
     * @param Transaction        $transaction
     *
     * @return int
     */
    public function getAmountBefore(TransactionJournal $journal, Transaction $transaction): int;

    /**
     * @param TransactionType $dbType
     *
     * @return Collection
     */
    public function getJournalsOfType(TransactionType $dbType): Collection;

    /**
	 * How is this different from getJournalsOfType?
	 *
     * @param array $types
	 * @param int $offset
	 * @param int $page
	 * @param string $search
	 *
     * @return Collection
     */
	public function getJournalsOfTypes(array $types, $offset = 0, $page = 0, $search = null): Collection;

	/**
	 * @param array $data
	 *
	 * @return TransactionJournal
	 */
	public function store(array $data): TransactionJournal;

	/**
	 * @param array $data
	 *
	 * @return Transaction
	 */
	protected function storeTransaction(array $data): Transaction;

	/**
	 * @param TransactionJournal $journal
	 * @param array              $data
	 *
	 * @return TransactionJournal
	 */
	public function update(TransactionJournal $journal, array $data): TransactionJournal;

	/**
	 * @param TransactionType $type
	 * @param array           $data
	 *
	 * @return array
	 */
	protected function storeAccounts(TransactionType $type, array $data): array;

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	protected function storeWithdrawalAccounts(array $data): array;

	/**
	 * @param array $data
	 *
	 * @return Entity	$account
	 */
	protected function storeExpressWithdrawalAccount(array $data): Account;

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	protected function storeExpressDepositAccount(array $data): array;

	/**
	 * @param array $data
	 *
	 * @return Entity Account
	 */
	protected function storeDepositAccounts(array $data): Account;
}
