<?php namespace Sailr\Item;

use Laracasts\Commander\CommandHandler;
use Laracasts\Commander\Events\DispatchableTrait;
use Sailr\Api\Responses\ApiResponse;
use Sailr\Api\Responses\Responder;
use Sailr\Item\ItemRepository;
use Sailr\Validators\ItemsValidator;

class PostNewItemCommandHandler implements CommandHandler {
    use DispatchableTrait;
    /**
     * @var itemRepository ItemRepository
     */
    protected $itemRepository;
    protected $validator;
    protected $responder;

    function __construct(ItemRepository $itemRepository, ItemsValidator $validator, Responder $responder)
    {
        $this->itemRepository = $itemRepository;
        $this->validator = $validator;
        $this->responder = $responder;
    }

    /**
     * Handle the command.
     *
     * @param object $command
     * @return void
     */
    public function handle($command)
    {
        $isValid = $this->validator->validate((array)$command, 'create');
        $item = $this->itemRepository->post($command->title, $command->currency, $command->price, $command->user_id);
        $this->dispatchEventsFor($this->itemRepository);

        return $this->responder->createdModelResponse($item);

    }

}