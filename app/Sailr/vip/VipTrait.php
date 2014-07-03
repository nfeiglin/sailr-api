<?php namespace Sailr\Vip;


trait VipTrait {
    public function canPerformActionOnPlan($action) {

        $vip = new VIP;
        return $vip->canPerformAction($action, $this);
    }
}
