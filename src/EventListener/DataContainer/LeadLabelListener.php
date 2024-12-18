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
        private readonly StringParser|null $stringParser = null,
    ) {
    }

    public function __invoke(array $row, string $label): string
    {
        $lead = $this->connection
            ->createQueryBuilder()
            ->select('l.*', 'mf.leadLabel', "IF(m.id IS NULL, '', CONCAT(m.lastname, ' ', m.firstname)) AS member_name", "IFNULL(f.title, '') AS form_title")
            ->from('tl_lead', 'l')
            ->join('l', 'tl_form', 'mf', 'l.main_id=mf.id')
            ->leftJoin('l', 'tl_member', 'm', 'l.member_id=m.id')
            ->leftJoin('l', 'tl_form', 'f', 'l.form_id=f.id')
            ->where('l.id=:id')
            ->setParameter('id', $row['id'])
            ->fetchAssociative()
        ;

        if (!$lead || empty($lead['leadLabel'])) {
            return $label;
        }

        $tokens = [
            '_id' => $row['id'],
            '_form' => $this->formatToken($lead['form_title'], $row['form_id']),
            '_created' => Date::parse(Config::get('datimFormat'), $row['created']),
            '_member' => $this->formatToken($lead['member_name'], $row['member_id']),
        ];

        $records = $this->connection->fetchAllAssociative('SELECT name, value, label FROM tl_lead_data WHERE pid=?', [$row['id']]);

        foreach ($records as $record) {
            // Try to use label over value if it is a non-empty scalar value or a non-empty array
            foreach ([$record['label'], $record['value']] as $value) {
                $value = StringUtil::deserialize($value);

                if (
                    (!\is_array($value) && '' !== (string) $value)
                    || (\is_array($value) && [] !== array_filter($value))
                ) {
                    break;
                }
            }

            if ($this->stringParser) {
                $this->stringParser->flatten($value, $record['name'], $tokens);
            } else {
                \Haste\Util\StringUtil::flatten($value, $record['name'], $tokens);
            }
        }

        if ($this->stringParser) {
            return $this->stringParser->recursiveReplaceTokensAndTags($lead['leadLabel'], $tokens);
        }

        return \Haste\Util\StringUtil::recursiveReplaceTokensAndTags($lead['leadLabel'], $tokens);
    }

    private function formatToken(string $title, int|string $value): string
    {
        if (empty($title)) {
            return (string) $value;
        }

        return \sprintf('%s [%s]', $title, $value);
    }
}
