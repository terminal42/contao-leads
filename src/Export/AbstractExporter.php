<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Export;

use Codefog\HasteBundle\StringParser;
use Contao\Config;
use Contao\Date;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractExporter implements ExporterInterface
{
    private const CHUNK_SIZE = 100;

    private array $config;
    private array|null $ids;
    private array|null $columns = null;

    public function __construct(
        private readonly ServiceLocator $formatters,
        private readonly Connection $connection,
        private readonly TranslatorInterface $translator,
        private readonly StringParser $parser,
    ) {
    }

    public function getResponse(array $config, array $ids = null): Response
    {
        $this->init($config, $ids);

        $response = new StreamedResponse(
            function (): void {
                $fp = fopen('php://output', 'w');
                $this->doExport($fp);
                fclose($fp);
            }
        );

        $response->headers->set('Content-Disposition', HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $this->getFilename(),
        ));

        $this->finish($config);

        return $response;
    }

    public function writeToFile(array $config, string $filename, array $ids = null): void
    {
        $this->init($config, $ids);

        $fp = fopen($filename, 'w');
        $this->doExport($fp);
        fclose($fp);

        $this->finish($config);
    }

    abstract protected function doExport($stream): void;

    protected function getConfig(): array
    {
        return $this->config;
    }

    protected function includeHeaderFields(): bool
    {
        return (bool) ($this->config['headerFields'] ?? false);
    }

    protected function iterateRows(bool $skipHeaders = false): \Generator
    {
        $columns = $this->getColumns();

        if (!$skipHeaders && $this->includeHeaderFields()) {
            yield array_column($columns, 'name');
        }

        foreach ($this->iterateLeads() as $lead) {
            $row = [];
            $i = 0;
            $data = array_combine(array_column($lead['data'], 'main_id'), $lead['data']);

            foreach ($columns as $column) {
                $value = (string) (isset($column['value']) ? $column['value']($lead) : $data[$column['id']]['value']);
                $label = (string) (isset($column['label']) ? $column['label']($lead) : $data[$column['id']]['label']);

                if (!empty($column['format'])) {
                    $value = $this->format($value, $column['format']);
                }

                $row[$column['targetColumn'] ?? $i] = $this->getOutput($value, $label, $column['output'] ?? self::OUTPUT_BOTH);
                ++$i;
            }

            yield $row;
        }
    }

    protected function iterateLeads(): \Generator
    {
        $mainFormId = (int) $this->config['pid'];

        $countQuery = $this->connection->createQueryBuilder();
        $countQuery
            ->select('COUNT(*)')
            ->from('tl_lead', 'l')
            ->where('l.main_id=:mainId')
            ->setParameter('mainId', $mainFormId)
        ;

        $selectQuery = $this->connection->createQueryBuilder();
        $selectQuery
            ->select([
                'l.*',
                "IF(m.id IS NULL, '', CONCAT(m.lastname, ' ', m.firstname)) AS member_name",
                "IFNULL(f.title, '') AS form_title",
            ])
            ->from('tl_lead', 'l')
            ->leftJoin('l', 'tl_member', 'm', 'l.member_id=m.id')
            ->leftJoin('l', 'tl_form', 'f', 'l.form_id=f.id')
            ->where('l.main_id=:mainId')
            ->setParameter('mainId', $mainFormId)
            ->orderBy('l.created', 'ASC')
            ->addOrderBy('l.id', 'ASC')
            ->setFirstResult(0)
            ->setMaxResults(self::CHUNK_SIZE)
        ;

        if (null !== $this->ids) {
            $countQuery->andWhere('l.id IN (:leadIds)')->setParameter('leadIds', $this->ids, Connection::PARAM_INT_ARRAY);
            $selectQuery->andWhere('l.id IN (:leadIds)')->setParameter('leadIds', $this->ids, Connection::PARAM_INT_ARRAY);
        } elseif ($this->config['skipLastRun'] && $this->config['lastRun']) {
            $countQuery->andWhere('l.created > :lastRun')->setParameter('lastRun', $this->config['lastRun']);
            $selectQuery->andWhere('l.created > :lastRun')->setParameter('lastRun', $this->config['lastRun']);
        }

        $total = (int) $countQuery->fetchOne();

        // Split leads fetching into chunks to prevent memory issues. We cannot use
        // Connection::iterateAssociative because it would lock the connection for additional queries.
        do {
            foreach ($selectQuery->fetchAllAssociative() as $lead) {
                $cols = $this->connection->fetchAllAssociative(
                    'SELECT * FROM tl_lead_data WHERE pid=? ORDER BY sorting',
                    [$lead['id']]
                );

                $data = $lead + ['data' => $cols];

                yield $data;
            }

            $selectQuery->setFirstResult($selectQuery->getFirstResult() + self::CHUNK_SIZE);
        } while ($selectQuery->getFirstResult() < $total);
    }

    /**
     * @noinspection PhpTranslationKeyInspection
     */
    protected function getColumns(): array
    {
        if (null !== $this->columns) {
            return $this->columns;
        }

        $columns = array_merge(
            [
                [
                    'id' => '_form',
                    'name' => $this->translator->trans('tl_lead_export._form', [], 'contao_tl_lead_export'),
                    'output' => self::OUTPUT_BOTH,
                    'value' => static fn ($lead) => $lead['form_id'],
                    'label' => static fn ($lead) => $lead['form_title'],
                ],
                [
                    'id' => '_created',
                    'name' => $this->translator->trans('tl_lead_export._created', [], 'contao_tl_lead_export'),
                    'output' => self::OUTPUT_VALUE,
                    'format' => 'datim',
                    'value' => static fn ($lead) => $lead['created'],
                    'label' => static fn () => '',
                ],
                [
                    'id' => '_member',
                    'name' => $this->translator->trans('tl_lead_export._member', [], 'contao_tl_lead_export'),
                    'output' => self::OUTPUT_BOTH,
                    'value' => static fn ($lead) => $lead['member_id'],
                    'label' => static fn ($lead) => $lead['member_name'],
                ],
            ],
            $this->connection->fetchAllAssociative(
                <<<'SQL'
                        SELECT d.main_id AS id, IF(ff.label, ff.label, d.name) AS name
                        FROM tl_lead_data d
                            JOIN tl_lead l ON l.id = d.pid
                            LEFT JOIN tl_form_field ff ON ff.id=d.main_id
                        WHERE l.main_id = ?
                        GROUP BY d.main_id, ff.label, d.name
                        ORDER BY MIN(d.sorting)
                    SQL,
                [$this->config['pid']]
            )
        );

        $this->columns = $columns = array_combine(array_column($columns, 'id'), $columns);

        if (self::EXPORT_FIELDS === $this->config['export']) {
            $this->columns = [];

            foreach (StringUtil::deserialize($this->config['fields'], true) as $config) {
                if (!isset($columns[$config['field']])) {
                    continue;
                }

                $col = $columns[$config['field']];
                $col['output'] = $config['output'];
                $col['format'] = $config['format'];

                if (!empty($config['name'])) {
                    $col['name'] = $config['name'];
                }

                if ('_skip' === ($config['id'] ?? null)) {
                    $col['value'] = static fn () => '';
                    $col['label'] = static fn () => '';
                    unset($col['output'], $col['format']);
                }

                $this->columns[] = $col;
            }
        } elseif (self::EXPORT_TOKENS === $this->config['export']) {
            $this->columns = [];

            foreach (StringUtil::deserialize($this->config['tokenFields'], true) as $config) {
                $this->columns[] = $config + [
                    'value' => fn ($lead) => $this->replaceTokens($config['tokensValue'], $lead),
                    'label' => static fn () => '',
                    'output' => 'value',
                ];
            }
        }

        return $this->columns;
    }

    protected function getFilename(): string
    {
        if (empty($this->config['filename'])) {
            return 'export_'.md5(uniqid('', false)).$this->getFileExtension();
        }

        $filename = $this->config['filename'];

        $tokens = [
            'time' => Date::parse(Config::get('timeFormat')),
            'date' => Date::parse(Config::get('dateFormat')),
            'datim' => Date::parse(Config::get('datimFormat')),
        ];

        $filename = $this->parser->recursiveReplaceTokensAndTags(
            $filename,
            $tokens,
            StringParser::NO_TAGS & StringParser::NO_BREAKS & StringParser::NO_ENTITIES
        );

        if (!str_contains($filename, '.')) {
            return $filename.$this->getFileExtension();
        }

        return $filename;
    }

    protected function getFileExtension(): string
    {
        return '.'.$this->config['type'];
    }

    protected function getOutput(string $value, string $label, string $format): string
    {
        switch ($format) {
            case self::OUTPUT_VALUE:
                return $value;

            case self::OUTPUT_LABEL:
                return $label ?: $value;

            case self::OUTPUT_BOTH:
                if ('' !== $label && '' !== $value && $label !== $value) {
                    return sprintf('%s [%s]', $label, $value);
                }

                return '' === $value ? $label : $value;
        }

        throw new \RuntimeException(sprintf('Unknown output format "%s"', $format));
    }

    protected function format(mixed $value, string $type): int|string
    {
        return $this->formatters->get($type)->format($value, $type);
    }

    protected function replaceTokens(string $text, array $lead): string
    {
        $tokens = [];

        foreach ($lead['data'] as $data) {
            $this->parser->flatten(StringUtil::deserialize($data['value']), $data['name'], $tokens);
        }

        // TODO: should we also make the leads meta data available for tokens?
        //unset($lead['data']);
        //$parser->flatten($lead, 'lead', $tokens);

        return $this->parser->recursiveReplaceTokensAndTags($text, $tokens);
    }

    private function init(array $config, array|null $ids): void
    {
        $this->config = $config;
        $this->ids = $ids;
        $this->columns = null;
    }

    private function finish(array $config): void
    {
        $this->connection->update('tl_lead_export', ['lastRun' => time()], ['id' => $config['id']]);
    }
}
