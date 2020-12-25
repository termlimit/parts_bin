<?php
namespace App\Support;

use App\Model\Table\BudgetsTable;
use App\Model\Table\CategoriesTable;
use App\Model\Table\TransactionJournalsTable;
use App\Repositories\Journal\JournalRepository;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;

/**
 * Class Search
 *
 * @package App\Support\Search
 */
class Search
{

	/**
	 * @param repository 	$repository
	 */
	public function __construct($repository)
	{
		$this->repository = $repository;
	}

    /**
     * @param array $words
     *
     * @return Collection
     */
    public function searchAccounts(array $words)
    {
        return Auth::user()->accounts()->with('accounttype')->where(
            function (EloquentBuilder $q) use ($words) {
                foreach ($words as $word) {
                    $q->orWhere('name', 'LIKE', '%' . e($word) . '%');
                }
            }
        )->get();
    }

    /**
     * @param array $words
     *
     * @return Collection
     */
	public function searchBills(array $words)
	{
		
	}

    /**
     * @param array $words
     *
     * @return Collection
     */
    public function searchBudgets(array $words)
    {
        /** @var Collection $set */
        $set    = Auth::user()->budgets()->get();
        $newSet = $set->filter(
            function (Budget $b) use ($words) {
                $found = 0;
                foreach ($words as $word) {
                    if (!(strpos(strtolower($b->name), strtolower($word)) === false)) {
                        $found++;
                    }
                }

                return $found > 0;
            }
        );

        return $newSet;
    }

    /**
     * @param array $words
     *
     * @return Collection
     */
    public function searchCategories(array $words)
    {
        /** @var Collection $set */
        $set    = Auth::user()->categories()->get();
        $newSet = $set->filter(
            function (Category $c) use ($words) {
                $found = 0;
                foreach ($words as $word) {
                    if (!(strpos(strtolower($c->name), strtolower($word)) === false)) {
                        $found++;
                    }
                }

                return $found > 0;
            }
        );

        return $newSet;
    }

    /**
     *
     * @param array $words
     *
     * @return Collection
     */
    public function searchTags(array $words)
    {
        return new Collection;
    }

    /**
     * @param array $words
     *
     * @return Collection
     */
    public function searchTransactions(array $words)
    {
		$search				= isset($this->request->data['search']) && !empty($this->request->data['search']) ? $this->request->data['search'] : null;
		$transactions		= $this->repository->getJournalsOfTypes($types, 0, 0, $search);

        // decrypted transaction journals:
        $decrypted = Auth::user()->transactionjournals()->withRelevantData()->where('encrypted', 0)->where(
            function (EloquentBuilder $q) use ($words) {
                foreach ($words as $word) {
                    $q->orWhere('description', 'LIKE', '%' . e($word) . '%');
                }
            }
        )->get();

        // encrypted
        $all      = Auth::user()->transactionjournals()->withRelevantData()->where('encrypted', 1)->get();
        $set      = $all->filter(
            function (TransactionJournal $journal) use ($words) {
                foreach ($words as $word) {
                    $haystack = strtolower($journal->description);
                    $word     = strtolower($word);
                    if (!(strpos($haystack, $word) === false)) {
                        return $journal;
                    }
                }

                return null;

            }
        );
        $filtered = $set->merge($decrypted);
        $filtered = $filtered->sortBy(
            function (TransactionJournal $journal) {
                return intval($journal->date->format('U'));
            }
        );

        $filtered = $filtered->reverse();

        return $filtered;
    }
} 
