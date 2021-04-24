<?php

namespace skh6075\playerexchange\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use skh6075\playerexchange\Loader;
use skh6075\playerexchange\queue\Queue;
use skh6075\playerexchange\queue\TaskQueue;
use skh6075\playerexchange\task\ExchangeFieldSettingTask;
use skh6075\playerexchange\task\ExchangeRequestTask;

final class PlayerExchangeCommand extends Command{

    private Loader $plugin;

    public function __construct(Loader $plugin) {
        parent::__construct("exchange", "exchange command");
        $this->setPermission("exchange.permission");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $player, string $label, array $args): bool{
        if (!$player instanceof Player) {
            $player->sendMessage(Loader::$prefix . "Please, this command use only in-game");
            return false;
        }

        if (!$this->testPermission($player)) {
            return false;
        }

        switch ($name = array_shift($args) ?? "") {
            case "accept":
                if (!Queue::isPlayerExistsQueue(Queue::CATEGORY_REQUEST, $player)) {
                    $player->sendMessage(Loader::$prefix . "Your Exchange request could not be found.");
                    return false;
                }

                $requester = Queue::getPlayerByRequesterPlayer($player);
                if (is_null($requester) or !$requester->isOnline()) {
                    $player->sendMessage(Loader::$prefix . "The requester could not be found on the server.");
                    return false;
                }

                Queue::unsetCategoryQueue(Queue::CATEGORY_REQUEST, $player->getName());
                Queue::unsetCategoryQueue(Queue::CATEGORY_REQUEST, $requester->getName());
                Queue::setPlayerCategoryQueueValue(Queue::CATEGORY_EXCHANGE, $requester->getName(), $player->getName());
                Queue::setPlayerCategoryQueueValue(Queue::CATEGORY_EXCHANGE, $player->getName(), $requester->getName());
                TaskQueue::deleteHandlerQueue($requester);

                $this->plugin->getScheduler()->scheduleDelayedTask(new ExchangeFieldSettingTask($requester, $player), 60);
                break;
            case "reject":
                if (!Queue::isPlayerExistsQueue(Queue::CATEGORY_REQUEST, $player)) {
                    $player->sendMessage(Loader::$prefix . "Your Exchange request could not be found.");
                    return false;
                }

                $value = Queue::getPlayerCategoryQueueValue(Queue::CATEGORY_REQUEST, $player);
                Queue::unsetCategoryQueue(Queue::CATEGORY_REQUEST, $player->getName());
                Queue::unsetCategoryQueue(Queue::CATEGORY_REQUEST, $value);

                $player->sendMessage(Loader::$prefix . "Your request for exchange has been successfully declined.");
                if (($target = Server::getInstance()->getPlayerExact($value)) instanceof Player) {
                    $target->sendMessage(Loader::$prefix . $player->getName() . " declined your request for exchange");
                }

                break;
            case "help":
            case "?":
                $format = Loader::$prefix . '/' . $this->getName();

                $player->sendMessage($format . " help - view command help");
                $player->sendMessage($format . " [playerName] - request an exchange");
                $player->sendMessage($format . " accept/reject - accept or reject the exchange request.");
                if ($this->plugin->getDescription()->getVersion() === "1.0.0") {
                    $player->sendMessage(Loader::$prefix . "author \"AvasKr\" Github - https://github.com/GodVas");
                }

                break;
            default:
                if (!($target = Server::getInstance()->getPlayerByPrefix($name)) instanceof Player) {
                    $player->sendMessage(Loader::$prefix . "The user cannot be found on the server.");
                    return false;
                }

                if (Queue::isPlayerExistsQueue(Queue::CATEGORY_EXCHANGE, $target)) {
                    $_target = Queue::getPlayerCategoryQueueValue(Queue::CATEGORY_EXCHANGE, $target);
                    $player->sendMessage(Loader::$prefix . "This user is already exchanging with {$_target}.");
                    return false;
                }

                if (Queue::isPlayerExistsQueue(Queue::CATEGORY_REQUEST, $player)) {
                    $_target = Queue::getPlayerCategoryQueueValue(Queue::CATEGORY_REQUEST, $player);
                    $player->sendMessage(Loader::$prefix . "You have already requested an exchange from {$_target}.");
                    return false;
                }

                $handler = $this->plugin->getScheduler()->scheduleRepeatingTask(new ExchangeRequestTask($player, $target), 20);
                TaskQueue::addHandlerQueue($player, $handler);

                $player->sendMessage(Loader::$prefix . "You have sent a request for an exchange. If you do not accept within 5 seconds, it will be rejected automatically.");

                $target->sendMessage(Loader::$prefix . "A has received an exchange request.");
                $target->sendMessage(Loader::$prefix . "Please process the exchange request with /exchange accept or refuse.");
                break;
        }

        return true;
    }
}