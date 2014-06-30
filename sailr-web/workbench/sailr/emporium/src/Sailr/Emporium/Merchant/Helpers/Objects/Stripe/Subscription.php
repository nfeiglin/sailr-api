<?php namespace Sailr\Emporium\Merchant\Helpers\Objects\Stripe;

use Illuminate\Support\Contracts\JsonableInterface;
use Illuminate\Support\Contracts\ArrayableInterface;
use Sailr\Emporium\Merchant\Helpers\Objects\DataObjectInterface;

class Subscription implements DataObjectInterface, JsonableInterface, ArrayableInterface {

    public $data = [];
    protected $stripeSubscription;

    public function ___construct($object) {
        $this->createSimpleArrayFromObject($object);
    }

    public static function make($object) {
        $instance = new Subscription;
        $instance->stripeSubscription = $object;
        return $instance;
    }

    public function build() {
        $stripeSubscription = $this->stripeSubscription;
        $stripePlan = $stripeSubscription->plan;

        $this->data = [
            'id' => $stripePlan->id,
            'name' => $stripePlan->name,
            'interval' => $stripePlan->interval,
            'created' => $stripePlan->created,
            'amount' => $stripePlan->amount,
            'currency' => $stripePlan->currency,
            'formatted_amount' => $this->formatMoney($stripePlan->amount, $stripePlan->currency),
            'statement_description' => $stripePlan->statement_description,
            'status' => $stripeSubscription->status,
            'current_period_start' => $stripeSubscription->current_period_start,
            'current_period_end' => $stripeSubscription->current_period_end,
            'formatted_period_start' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start)->toFormattedDateString(),
            'formatted_period_end' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end)->toFormattedDateString(),
            'cancel_at_period_end' => $stripeSubscription->cancel_at_period_end,
        ];

        return $this;
    }

    public function getData() {
        return $this->data;
    }

    protected function handleDiscounts() {
        $this->data['discounts'] = [

        ];
    }

    protected function formatMoney($amount, $currencyCode = 'aud') {
        $currencyCode = strtoupper($currencyCode);
        $formattedAmount = floatval($amount) / 100;
        return "$currencyCode $formattedAmount";
    }

    public function toArray() {
        return $this->data;
    }

    public function toJson($options = 0) {
        return json_encode($this->data, $options);
    }
} 