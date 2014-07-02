<?php namespace Sailr\Vip;

trait VipTrait {
    public function canPerformActionOnPlan($action) {
        return \VIP::canPerformAction($action, $this);
    }
}
