<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 18/09/2014
 * Time: 3:44 PM
 */

namespace Sailr\Item;

use Laracasts\Commander\CommandHandler;
use Laracasts\Commander\Events\DispatchableTrait;
use Sailr\Api\Responses\ApiResponse;
use Sailr\Api\Responses\Responder;
use Sailr\Item\ItemRepository;
use Sailr\Validators\ItemsValidator;

abstract class BaseItemCommandHandler {
    use DispatchableTrait;

    /**
     * @var itemRepository ItemRepository
     * @var ItemsValidator
     * @var Responder
     */
    protected $itemRepository;
    protected $validator;
    protected $responder;

    public function __construct(ItemRepository $itemRepository, ItemsValidator $validator, Responder $responder)
    {
        $this->itemRepository = $itemRepository;
        $this->validator = $validator;
        $this->responder = $responder;
    }



} 