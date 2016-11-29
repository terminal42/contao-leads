<?php

namespace Terminal42\LeadsBundle;

use Terminal42\LeadsBundle\Exception\InvalidExportTargetException;
use Terminal42\LeadsBundle\ExportTarget\TargetInterface;

class ExportTargetManager
{
    /**
     * Targets
     * @var array
     */
    private $targets = [];

    /**
     * Add the target
     * 
     * @param TargetInterface $target
     * @param string          $alias
     */
    public function addTarget(TargetInterface $target, $alias)
    {
        $this->targets[$alias] = $target;
    }

    /**
     * Get the target
     *
     * @param string $alias
     *
     * @return TargetInterface
     */
    public function getTarget($alias)
    {
        if (!isset($this->targets[$alias])) {
            throw new InvalidExportTargetException('The target "%s" does not exist', $alias);
        }

        return $this->targets[$alias];
    }

    /**
     * Get the target aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return array_keys($this->targets);
    }
}
