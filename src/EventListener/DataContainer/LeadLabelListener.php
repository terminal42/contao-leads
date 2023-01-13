<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener\DataContainer;

use Codefog\HasteBundle\StringParser;
use Contao\Config;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Date;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

#[AsCallback('tl_lead', 'list.label.label')]
class LeadLabelListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly StringParser $stringParser,
    ) {}

    public function __invoke(array $row, string $label): string
    {
        $mainForm = $this->connection->fetchAssociative('SELECT * FROM tl_form WHERE id=?', [$row['main_id']]);

        if (!$mainForm || empty($mainForm['leadLabel'])) {
            return $label;
        }

        $tokens = ['created' => Date::parse(Config::get('datimFormat'), $row['created'])];
        $values = $this->connection->fetchAllKeyValue('SELECT name, value FROM tl_lead_data WHERE pid=?', [$row['id']]);

        foreach ($values as $name => $value) {
            $this->stringParser->flatten(StringUtil::deserialize($value), $name, $tokens);
        }

        return $this->stringParser->recursiveReplaceTokensAndTags($mainForm['leadLabel'], $tokens);
    }
}
