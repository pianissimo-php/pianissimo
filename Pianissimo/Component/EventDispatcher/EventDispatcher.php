<?php

namespace Pianissimo\Component\EventDispatcher;

class EventDispatcher
{
    public function dispatch(string $id): void
    {
        $listeners = $this->getListeners($id);

        foreach ($listeners as $listener)
        {
            $listener();
        }
    }

    private function getListeners(string $id): array
    {
        if (isset($this->listeners[$id]))
        {
            return $this->listeners[$id];
        }
        return [];
    }
}