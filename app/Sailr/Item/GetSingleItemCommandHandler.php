<?php namespace Sailr\Item;

use Laracasts\Commander\CommandHandler;

class GetSingleItemCommandHandler extends BaseItemCommandHandler implements CommandHandler {

    /**
     * Handle the command.
     *
     * @param GetSingleItemCommand $command
     * @return mixed
     */
    public function handle($command)
    {
        $item = $this->itemRepository->findOneWithPhotosAndUser($command->id);
        $this->dispatchEventsFor($this->itemRepository);
        return $this->responder->showSingleModel($item);
    }

}