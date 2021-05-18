<?php

namespace skh6075\playerexchange;

use muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use skh6075\playerexchange\command\PlayerExchangeCommand;
use skh6075\playerexchange\queue\Queue;

final class Loader extends PluginBase{
    use SingletonTrait;

    public static string $prefix = "§l§b[Exchange]§r§7 ";

    protected function onLoad(): void{
        self::setInstance($this);
        
        Queue::createQueueCategory(Queue::CATEGORY_REQUEST);
        Queue::createQueueCategory(Queue::CATEGORY_EXCHANGE);
    }

    protected function onEnable(): void{
        if (!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }

        $this->getServer()->getCommandMap()->register(strtolower($this->getName()), new PlayerExchangeCommand($this));
    }
}
