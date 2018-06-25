<?php

namespace Statamic\Addons\Spock;

use ReflectionClass;
use Statamic\Contracts\Data\Users\User;

class Git
{
    protected $config;
    protected $event;
    protected $user;

    /**
     * @param array $config  The Spock config.
     * @param mixed $event   The event class that Spock listened for.
     * @param User  $user    The user that triggered the event.
     */
    public function __construct($config, $event, User $user)
    {
        $this->config = $config;
        $this->event = $event;
        $this->user = $user;
    }

    /**
     * Get the commands to be executed.
     *
     * @return array
     */
    public function commands()
    {
        $commands = [];

        foreach ($this->event->affectedPaths() as $path) {
            $commands[] = "git add {$path}";
        }

        $commands[] = sprintf("git commit -m '%s by %s'", $this->label(), $this->user->username());

        if (array_get($this->config, 'git_push', false)) {
            $commands[] = 'git push';
        }

        return $commands;
    }

    /**
     * Get the label of the class, which is the action name.
     *
     * eg. "Statamic\Events\Data\DataSaved" becomes "Data saved"
     *
     * @return string
     */
    protected function label()
    {
        $class = (new ReflectionClass($this->event))->getShortName();

        return ucfirst(str_replace('_', ' ', snake_case($class)));
    }
}
