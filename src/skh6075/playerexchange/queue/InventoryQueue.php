<?php

namespace skh6075\playerexchange\queue;

use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\inventory\Inventory;
use pocketmine\item\ItemFactory;
use pocketmine\player\Player;

final class InventoryQueue{

    public const BARRIER_FIELD = [
        0, 1, 3, 4, 5, 7, 8,
        9, 13, 17,
        18, 22, 26,
        27, 31, 35,
        36, 37, 38, 39, 40, 41, 42, 43, 44,
        45, 49, 53
    ];

    public const REQUESTER_FILED = [
        10, 11, 12,
        19, 20, 21,
        28, 29, 30
    ];

    public const TARGET_FIELD = [
        14, 15, 16,
        23, 24, 25,
        32, 33, 34
    ];

    public const BLOCKED_SLOTS = self::BARRIER_FIELD + [2, 6, 47, 53];

    public const STATUS_SLOT_REQUESTER = 47;
    public const STATUS_SLOT_TARGET = 51;

    /** @var Inventory[] */
    private static array $queue = [];

    public static function setQueue(Player $player, Inventory $inventory): void{
        self::$queue[$player->getName()] = $inventory;
    }

    public static function defaultFieldInventorySlots(Inventory $inventory, Player $requester, Player $target): void{
        foreach (self::BARRIER_FIELD as $barrierSlot) {
            $inventory->setItem($barrierSlot, ItemFactory::getInstance()->get(BlockLegacyIds::SIGN_POST));
        }

        $inventory->setItem(2, ItemFactory::getInstance()->get(BlockLegacyIds::MOB_HEAD_BLOCK, 3)->setCustomName("§r§f" . $requester->getName() . " Slots"));
        $inventory->setItem(6, ItemFactory::getInstance()->get(BlockLegacyIds::MOB_HEAD_BLOCK, 3)->setCustomName("§r§f" . $target->getName() . " Slots"));

        $inventory->setItem(47, ItemFactory::getInstance()->get(BlockLegacyIds::REDSTONE_LAMP)->setCustomName("§r§f" . $requester->getName() . " Exchange Status"));
        $inventory->setItem(51, ItemFactory::getInstance()->get(BlockLegacyIds::REDSTONE_LAMP)->setCustomName("§r§f" . $target->getName() . " Exchange Status"));
    }

    public static function getQueue(Player $player): ?Inventory{
        return self::$queue[$player->getName()] ?? null;
    }
}
