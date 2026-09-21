<?php
declare(strict_types=1);

namespace App\Command;

use App\Utility\Uuid;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use DateTimeInterface;

/**
 * Fills in `uuid` for rows created before the column existed.
 *
 *     bin/cake backfill_uuids                  # users, teachers and lessons
 *     bin/cake backfill_uuids --table Lessons  # just one table (Users, Teachers or Lessons)
 *     bin/cake backfill_uuids --dry-run        # only report what's missing
 *
 * Each UUIDv7 embeds the row's `created` time (or now, when that's empty),
 * so backfilled UUIDs keep the rows' chronological order. Safe to re-run:
 * rows that already have a UUID are never touched.
 */
class BackfillUuidsCommand extends Command
{
    /**
     * Tables that carry a `uuid` column.
     */
    public const TABLES = ['Users', 'Teachers', 'Lessons'];

    /**
     * @return string
     */
    public static function defaultName(): string
    {
        return 'backfill_uuids';
    }

    /**
     * @return string
     */
    public static function getDescription(): string
    {
        return 'Assign UUIDv7 values to users, teachers and lessons that have none.';
    }

    /**
     * @param \Cake\Console\ConsoleOptionParser $parser Parser.
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return $parser
            ->setDescription(static::getDescription())
            ->addOption('table', [
                'short' => 't',
                'help' => 'Only backfill this table.',
                'choices' => self::TABLES,
            ])
            ->addOption('batch', [
                'short' => 'b',
                'help' => 'Rows per batch (each batch is one transaction).',
                'default' => '500',
            ])
            ->addOption('dry-run', [
                'short' => 'd',
                'help' => 'Report how many rows are missing a UUID without changing anything.',
                'boolean' => true,
            ]);
    }

    /**
     * @param \Cake\Console\Arguments $args Arguments.
     * @param \Cake\Console\ConsoleIo $io Console IO.
     * @return int
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $batch = (int)$args->getOption('batch');
        if ($batch < 1) {
            $io->error('--batch must be a positive integer.');

            return static::CODE_ERROR;
        }

        $table = $args->getOption('table');
        $tables = $table ? [(string)$table] : self::TABLES;
        $dryRun = (bool)$args->getOption('dry-run');

        foreach ($tables as $alias) {
            $done = $dryRun ? $this->countMissing($alias) : $this->backfill($alias, $batch);
            $io->out(sprintf(
                '%s: %d row(s) %s',
                $alias,
                $done,
                $dryRun ? 'missing a UUID' : 'backfilled',
            ));
        }

        return static::CODE_SUCCESS;
    }

    /**
     * @param string $alias Table alias.
     * @return int
     */
    protected function countMissing(string $alias): int
    {
        return $this->fetchTable($alias)->find()->where(['uuid IS' => null])->count();
    }

    /**
     * Update rows without a UUID in batches, oldest first.
     *
     * Writes go through updateAll() so callbacks, validation and the
     * `modified` timestamp are left alone.
     *
     * @param string $alias Table alias.
     * @param int $batch Rows per batch.
     * @return int Rows updated.
     */
    protected function backfill(string $alias, int $batch): int
    {
        $table = $this->fetchTable($alias);
        $total = 0;

        do {
            $rows = $table->find()
                ->select(['id', 'created'])
                ->where(['uuid IS' => null])
                ->orderBy(['id' => 'ASC'])
                ->limit($batch)
                ->disableHydration()
                ->all()
                ->toList();
            $fetched = count($rows);

            $table->getConnection()->transactional(function () use ($table, $rows, &$total): void {
                foreach ($rows as $row) {
                    $created = $row['created'] instanceof DateTimeInterface ? $row['created'] : null;
                    // `uuid IS NULL` guards against a concurrent writer that got there first.
                    $total += $table->updateAll(
                        ['uuid' => Uuid::v7($created)],
                        ['id' => $row['id'], 'uuid IS' => null],
                    );
                }
            });
        } while ($fetched === $batch);

        return $total;
    }
}
