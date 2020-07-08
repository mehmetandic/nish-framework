<?php
namespace Nish\Events;


interface IEventManager
{
    public function addEventListener(string $eventName, string $listenerType, callable $listener, bool $receivePrevResult, array $extraParams);
    public function unsetEventListener(string $eventName, string $listenerType);
    public function hasEventListener(string $eventName, string $listenerType);
    public function trigger(string $eventName, string $listenerType);
}