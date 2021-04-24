<?php

namespace skh6075\playerexchange\queue;

use pocketmine\player\Player;
use pocketmine\scheduler\TaskHandler;

final class TaskQueue{

    /** @var TaskHandler[] */
    private static array $handler = [];

    public static function addHandlerQueue(Player $player, TaskHandler $handler): void{
        self::$handler[spl_object_id($player)] = $handler;
    }

    public static function deleteHandlerQueue(Player $player): void{
        if (isset(self::$handler[spl_object_id($player)])) {
            unset(self::$handler[spl_object_id($player)]);
        }
    }

    public static function isExistsHandlerQueue(Player $player): bool{
        return isset(self::$handler[spl_object_id($player)]);
    }
}