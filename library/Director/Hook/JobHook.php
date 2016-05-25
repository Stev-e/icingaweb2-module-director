<?php

namespace Icinga\Module\Director\Hook;

use Icinga\Application\Icinga;
use Icinga\Module\Director\Db;
use Icinga\Module\Director\Objects\DirectorJob;
use Icinga\Module\Director\Web\Form\QuickForm;

abstract class JobHook
{
    private $db;

    private $output   = array();

    private $warnings = array();

    private $errors   = array();

    private $jobDefinition;

    public static function getDescription(QuickForm $form)
    {
        return false;
    }

    abstract public function run();

    public function isPending()
    {
        // TODO: Can be overridden, double-check whether this is necessary
    }

    public function setDefinition(DirectorJob $definition)
    {
        $this->jobDefinition = $definition;
        return $this;
    }

    protected function getSetting($key, $default = null)
    {
        return $this->jobDefinition->getSetting($key, $default);
    }

    public function getName()
    {
        $parts = explode('\\', get_class($this));
        $class = preg_replace('/Job$/', '', array_pop($parts));

        if (array_shift($parts) === 'Icinga' && array_shift($parts) === 'Module') {
            $module = array_shift($parts);
            if ($module !== 'Director') {
                return sprintf('%s (%s)', $class, $module);
            }
        }

        return $class;
    }

    public static function getSuggestedRunInterval(QuickForm $form)
    {
        return 900;
    }

    /**
     * Override this method if you want to extend the settings form
     *
     * @param  QuickForm $form QuickForm that should be extended
     * @return QuickForm
     */
    public static function addSettingsFormFields(QuickForm $form)
    {
        return $form;
    }

    public function setDb(Db $db)
    {
        $this->db = $db;
        return $this;
    }

    protected function db()
    {
        return $this->db;
    }

    protected function output($message)
    {
        $this->output[] = $message;
        return $this;
    }

    /**
     * printf helper method
     *
     * @param  string $message Format string
     * @param  mixed  ...$arg  Format string argument
     *
     * @return self
     */
    protected function printf($message)
    {
        $args = array_slice(func_get_args(), 1);
        return $this->output(vsprintf($message, $args));
    }

    protected function warning($message)
    {
        $this->warnings[] = $message;
        return $this;
    }

    protected function error($message)
    {
        $this->errors[] = $message;
        return $this;
    }
}
