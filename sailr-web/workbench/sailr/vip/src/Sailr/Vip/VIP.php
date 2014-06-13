<?php namespace Sailr\Vip;

use Carbon\Carbon;
use Illuminate\Support;

class VIP
{
    /*
        @property User $user The user instance to check against
    */
    protected  $user;
    protected $config;

    /**
     * Work out if thee user can perform a specific action based on their plan and activity within the last 30 days.
     *
     * @param string $actionName the action to test for
     * @param \User $user The user to test for
     * @return bool
     */

    public function canPerformAction($actionName, \User $user)
    {

        $this->config = \Config::get('vip::config.plans');
        $this->user = $user;
        $planId = '';

        if (!$this->user->subscribed()) {
            //Default
            $planId = 'default';
        } else {
            $planId = $this->user->subscription()->planId();
        }


        $rulesArray = array_get($this->config, $this->buildArrayGetString($planId, $actionName));

        $query = $rulesArray['db']();

        $maximumAmount = $rulesArray['max'];

        $today = Carbon::now();
        $todayLess30Days = Carbon::now()->subDays(30);
        $todayLess30Days->toDateTimeString();
        $today->toDateTimeString();


        $count = $query->where('user_id', '=', $this->user->getAuthIdentifier())->whereBetween('created_at', [$todayLess30Days, $today])->count();
        echo '<p> Count is::: ' . $count . '</p>';
        if ($count >= $maximumAmount) {
            return false;
        }

        else {
            return true;
        }


    }

    protected function buildArrayGetString($planID, $actionName, $type = 'count')
    {
        $type = 'count';
        return $planID . '.' . $type . '.' . $actionName;
    }
}
