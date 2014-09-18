<?php namespace Sailr\Item;



class PostNewItemCommandHandler extends BaseItemCommandHandler implements CommandHandler {

    /**
     * Handle the command.
     *
     * @param object $command
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle($command)
    {
        $this->validator->validate((array)$command, 'create');
        $item = $this->itemRepository->post($command->title, $command->currency, $command->price, $command->user_id);
        $this->dispatchEventsFor($this->itemRepository);

        return $this->responder->createdModelResponse($item);

    }

}