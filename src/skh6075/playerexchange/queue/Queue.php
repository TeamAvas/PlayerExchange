<?php

namespace skh6075\playerexchange\queue;

use pocketmine\player\Player;
use pocketmine\Server;

final class Queue{

    public const CATEGORY_REQUEST = "request";
    public const CATEGORY_EXCHANGE = "exchange";

    private static array $queue = [];

    public static function createQueueCategory(string $category): void{
        self::$queue[$category] = [];
    }

    public static function isPlayerExistsQueue(string $category, Player $player): bool{
        return isset(self::$queue[$category]) and isset(self::$queue[$category][$player->getName()]);
    }

    /** @return mixed|null */
    public static function getPlayerCategoryQueueValue(string $category, Player $player) {
        return self::$queue[$category][$player->getName()] ?? null;
    }

    public static function setPlayerCategoryQueueValue(string $category, string $key, $value): void{
        self::$queue[$category][$key] = $value;
    }

    public static function getPlayerByRequesterPlayer(Player $player): ?Player{
        $res = null;
        if (self::isPlayerExistsQueue(self::CATEGORY_REQUEST, $player))
            $res = Server::getInstance()->getPlayerExact(self::$queue[self::CATEGORY_REQUEST][$player->getName()]);

        return $res;
    }

    public static function unsetCategoryQueue(string $category, string $key): void{
        if (!isset(self::$queue[$category]))
            return;

        unset(self::$queue[$category][$key]);
        self::$queue[$category] = array_values(self::$queue[$category]);
    }
}