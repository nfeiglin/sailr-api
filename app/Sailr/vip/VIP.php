<?php namespace Sailr\Vip;

use Carbon\Carbon;
use Illuminate\Support;

class VIP
{
    /*
        @property User $user The user instance to check against
    */
    protected $user;
    protected $config;
    protected $planId = '';

    /**
     * Work out if thee user can perform a specific action based on their plan and activity within the last 30 days.
     *
     * @param string $actionName the action to test for
     * @param \User $user The user to test for
     * @return bool
     */

    public function canPerformAction($actionName, \User $user)
    {

        $this->config = \Config::get('vip.plans');
        $this->user = $user;

        if (!$this->user->subscribed()) {
            //Default
            $this->planId = 'default';
        } else {
            $this->planId = $this->user->subscription()->planId();
        }


        //dd($this->buildArrayGetString($this->planId, $actionName));

        $rulesArray = array_get($this->config, $this->buildArrayGetString($this->planId, $actionName));

        /* Support for closures and pure variables.. */
        if ($rulesArray['db'] instanceof Closure) {
            $query = $rulesArray['db']();
        } else {
            $query = $rulesArray['db'];
        }


        $maximumAmount = $rulesArray['max'];

        $today = Carbon::now();
        $todayLess30Days = Carbon::now()->subDays(30);
        $todayLess30Days->toDateTimeString();
        $today->toDateTimeString();


        $count = $query->where('user_id', '=', $this->user->getAuthIdentifier())->whereBetween('created_at', [$todayLess30Days, $today])->count();

        if ($count == 0) {
            return true;
        }
        return !$count >= $maximumAmount;

    }

    protected function buildArrayGetString($planID, $actionName, $type = 'count')
    {
        $type = 'count';
        return $planID . '.' . $type . '.' . $actionName;
    }
}
