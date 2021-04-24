<?php

namespace skh6075\playerexchange\task;

use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use skh6075\playerexchange\queue\InventoryQueue;

final class ExchangeInventoryTask extends Task{

    private Player $requester;

    private Inventory $requester_inventory;

    private Player $target;

    private Inventory $target_inventory;

    public function __construct(Player $requester, Inventory $requester_inventory, Player $target, Inventory $target_inventory) {
        $this->requester = $requester;
        $this->requester_inventory = $requester_inventory;
        $this->target = $target;
        $this->target_inventory = $target_inventory;
    }

    public function onRun(): void{
        if ($this->getHandler()->isCancelled())
            return;

        if (!$this->requester->isOnline() or !$this->target->isOnline()) {
            $this->getHandler()->cancel();

            if ($this->requester->isOnline())
                $this->requester->getNetworkSession()->getInvManager()->onCurrentWindowRemove();
            if ($this->target->isOnline())
                $this->target->getNetworkSession()->getInvManager()->onCurrentWindowRemove();

            return;
        }

        $r_inv = $this->requester_inventory;
        $t_inv = $this->target_inventory;
        if ($r_inv->getItem(InventoryQueue::STATUS_SLOT_REQUESTER)->getId() === 124 and $t_inv->getItem(InventoryQueue::STATUS_SLOT_TARGET)->getId() === 124) {
            $this->onExchangeSuccess($r_inv, $t_inv);
        }
    }

    private function onExchangeSuccess(Inventory $r_inv, Inventory $t_inv): void{
        $this->requester->getInventory()->addItem(...array_map(function (int $slot) use ($t_inv): Item{
            return $t_inv->getItem($slot);
        }, InventoryQueue::TARGET_FIELD));

        $this->target->getInventory()->addItem(...array_map(function (int $slot) use ($r_inv): Item{
            return $r_inv->getItem($slot);
        }, InventoryQueue::REQUESTER_FILED));
    }
}