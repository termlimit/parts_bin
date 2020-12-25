<?php
namespace App\Support;

use App\Repositories\Journal\JournalRepository;
use App\Support\Navigation;
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\Network\Request;
use Cake\Network\Session;
use Cake\ORM\TableRegistry;
use Cake\View\ViewVarsTrait;

/**
 * Range class
 *
 */
class Range
{
    use ViewVarsTrait;

    /**
     * Create a new range instance.
     *
     */
    public function __construct(Request $request, $auth, $PreferencesComponent)
    {
        $this->request 		= $request;
        $this->PreferencesComponent = $PreferencesComponent;
    }

    // Execute any other additional setup for your component.
    public function initialize(array $config)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle()
    {
        if (Configure::check('GlobalAuth.id') && Configure::read('GlobalAuth.id') > 0) {

            // Check existing session, always create start and end if not existing
            // ignore preference. set the range to be the current month:
            if (!$this->request->session()->check('start') && !$this->request->session()->check('end')) {
                $viewRange = '1M';
                $start     = new Time;
                $start     = Navigation::updateStartDate($viewRange, $start);
                $end       = Navigation::updateEndDate($viewRange, $start);

                Configure::write('calendar.start', $start);
                Configure::write('calendar.end', $end);
                $this->request->session()->write('start', $start);
                $this->request->session()->write('end', $end);
            }

            // Check daterangepicker, only triggered when new range is set
            if ($this->request->is(['patch', 'post', 'put']) && isset($this->request->data['_chartstart'], $this->request->data['_chartend'])) {
                $start = Time::createFromFormat('Y-m-d', urldecode($this->request->data['_chartstart']), $this->PreferencesComponent->get('time_zone', 'America/Los_Angeles'));
                $end = Time::createFromFormat('Y-m-d', urldecode($this->request->data['_chartend']), $this->PreferencesComponent->get('time_zone', 'America/Los_Angeles'));

                Configure::write('calendar.start', $start);
                Configure::write('calendar.end', $end);
                $this->request->session()->write('start', $start);
                $this->request->session()->write('end', $end);
            }

            if (!$this->request->session()->check('first')) {
                $repository = new JournalRepository;
                $journal    = $repository->first();
                if ($journal) {
                    $journal->entered->timezone = $this->PreferencesComponent->get('time_zone', 'America/Los_Angeles');
                    Configure::write('calendar.first', $journal->entered);
                    $this->request->session()->write('first', $journal->entered);
                } else {
                    $first = Time::now()->startOfYear();
                    #$first->timezone = $this->Preferences->get('time_zone', 'America/Los_Angeles');
                    Configure::write('calendar.first', $first);
                    $this->request->session()->write('first', $first);
                }
            }

            if (!Configure::check('calendar.start') || !Configure::check('calendar.end')) {
                Configure::write('calendar.start', $this->request->session()->read('start'));
                Configure::write('calendar.end', $this->request->session()->read('end'));
            }

            $current = Time::now()->format('%B %Y');
            $next    = Time::now()->endOfMonth()->addDay()->format('%B %Y');
            $prev    = Time::now()->startOfMonth()->subDay()->format('%B %Y');
            $this->set('currentMonthName', $current);#View::share('currentMonthName', $current);
            $this->set('previousMonthName', $prev);#View::share('previousMonthName', $prev);
            $this->set('nextMonthName', $next);#View::share('nextMonthName', $next);
        }
    }
}
