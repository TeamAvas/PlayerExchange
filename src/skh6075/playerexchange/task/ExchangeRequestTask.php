<?php

namespace skh6075\playerexchange\task;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use skh6075\playerexchange\queue\TaskQueue;

final class ExchangeRequestTask extends Task{

    private Player $requester;

    private Player $target;

    public function __construct(Player $requester, Player $target) {
        $this->requester = $requester;
        $this->target = $target;
    }

    public function onRun(): void{
        if ($this->getHandler()->isCancelled())
            return;

        if (!$this->requester->isOnline() or !$this->target->isOnline()) {
            TaskQueue::deleteHandlerQueue($this->requester);
            $this->getHandler()->cancel();
        }
    }
}