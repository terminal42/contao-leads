<?php

declare(strict_types=1);

/*
 * leads Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2011-2018, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-leads
 */

namespace Terminal42\LeadsBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class ExportFileEvent extends Event
{
    /**
     * @var \stdClass
     */
    private $config;

    /**
     * @var array
     */
    private $tokens;

    /**
     * @var string
     */
    private $filename;

    public function __construct(\stdClass $config, array $tokens)
    {
        $this->config = $config;
        $this->tokens = $tokens;
    }

    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function setTokens(array $tokens): self
    {
        $this->tokens = $tokens;

        return $this;
    }

    public function addToken(string $token, string $replacement): self
    {
        $this->tokens[$token] = $replacement;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        $this->stopPropagation();

        return $this;
    }
}
