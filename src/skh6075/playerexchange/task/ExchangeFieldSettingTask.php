<?php

namespace skh6075\playerexchange\task;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\item\ItemFactory;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use skh6075\playerexchange\queue\InventoryQueue;

final class ExchangeFieldSettingTask extends Task{

    private Player $requester;

    private Player $target;

    public function __construct(Player $requester, Player $target) {
        $this->requester = $requester;
        $this->target = $target;
    }

    public function onRun(): void{
        if ($this->getHandler()->isCancelled())
            return;

        if (!$this->requester->isOnline() or !$this->target->isOnline())
            return;

        $ev = new EntityTeleportEvent($this->target, $this->target->getPosition(), $this->requester->getTargetBlock(3)->getPos());
        $ev->call();
        if (!$ev->isCancelled()) {
            $ev->getEntity()->teleport($ev->getTo());
        }

        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);

        $inventory = $menu->getInventory();
        InventoryQueue::defaultFieldInventorySlots($inventory, $this->requester, $this->target);

        InventoryQueue::setQueue($this->requester, $inventory);
        InventoryQueue::setQueue($this->target, $inventory);

        $menu->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult{
            $slot = $transaction->getAction()->getSlot();
            $item = $transaction->getItemClicked();
            if (in_array($slot, InventoryQueue::BLOCKED_SLOTS)) {
                $return = [123 => 124, 124 => 123];

                switch ($slot) {
                    case InventoryQueue::STATUS_SLOT_REQUESTER:
                        if ($transaction->getPlayer()->getName() === $this->requester->getName()) {
                            $transaction->getAction()->getInventory()->setItem($slot, ItemFactory::getInstance()->get($return[$item->getId()] ?? 123));
                        }
                        break;
                    case InventoryQueue::STATUS_SLOT_TARGET:
                        if ($transaction->getPlayer()->getName() === $this->target->getName()) {
                            $transaction->getAction()->getInventory()->setItem($slot, ItemFactory::getInstance()->get($return[$item->getId()] ?? 123));
                        }
                        break;
                    default:
                        break;
                }

                return $transaction->discard();
            }

            if (in_array($slot, InventoryQueue::REQUESTER_FILED) and $transaction->getPlayer()->getName() !== $this->requester->getName())
                return $transaction->discard();

            if (in_array($slot, InventoryQueue::TARGET_FIELD) and $transaction->getPlayer()->getName() !== $this->target->getName())
                return $transaction->discard();

            return $transaction->continue();
        });

        $menu->send($this->requester, "Exchange " . $this->target->getName());
        $menu->send($this->target, "Exchange " . $this->requester->getName());
    }
}