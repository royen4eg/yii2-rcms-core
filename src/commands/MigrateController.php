<?php
namespace rcms\core\commands;

use rcms\core\AdminModule;
use rcms\core\Module;
use Yii;
use yii\console\controllers\BaseMigrateController;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;
use yii\helpers\Console;

/**
 * Class MigrateController
 * @package rcms\core\commands
 */
class MigrateController extends BaseMigrateController
{
    /**
     * @var Connection The database connection
     */
    public $db = 'rcmsDb';

    /**
     * @inheritdoc
     */
    public $templateFile = '@yii/views/migration.php';

    /**
     * @inheritdoc
     */
    public $migrationTable = '{{%migration_log}}';

    /**
     * @inheritdoc
     */
    public $migrationPath = '@rcms/core/migrations';

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->db = Instance::ensure($this->db, Connection::class);
        $this->migrationPath = Yii::$app->modules[AdminModule::$moduleId]->migrationPath;

        parent::init();
    }

    /**
     * @return Connection
     */
    public function getDb(): Connection
    {
        return $this->db;
    }

    /**
     * Returns the migration history.
     * @param int $limit the maximum number of records in the history to be returned. `null` for "no limit".
     * @return array the migration history
     * @throws \yii\db\Exception
     */
    protected function getMigrationHistory($limit)
    {
        if ($this->db->schema->getTableSchema($this->migrationTable, true) === null) {
            $this->createMigrationHistoryTable();
        }

        $history = (new Query())
            ->select(['apply_time'])
            ->from($this->migrationTable)
            ->orderBy(['apply_time' => SORT_DESC, 'version' => SORT_DESC])
            ->limit($limit)
            ->indexBy('version')
            ->column($this->db);

        unset($history[self::BASE_MIGRATION]);

        return $history;
    }

    /**
     * Adds new migration entry to the history.
     * @param string $version migration version name.
     * @throws \yii\db\Exception
     */
    protected function addMigrationHistory($version)
    {
        $this->db->createCommand()->insert($this->migrationTable, [
            'version' => $version,
            'apply_time' => time(),
        ])->execute();
    }

    /**
     * Removes existing migration from the history.
     * @param string $version migration version name.
     * @throws \yii\db\Exception
     */
    protected function removeMigrationHistory($version)
    {
        $this->db->createCommand()->delete($this->migrationTable, [
            'version' => $version,
        ])->execute();
    }

    /**
     * Creates the migration history table.
     * @throws \yii\db\Exception
     */
    protected function createMigrationHistoryTable()
    {
        $tableName = $this->db->schema->getRawTableName($this->migrationTable);

        $this->stdout("Creating migration history table \"$tableName\"...", Console::FG_YELLOW);

        $this->db->createCommand()->createTable($this->migrationTable, [
            'version' => 'VARCHAR(180) NOT NULL PRIMARY KEY',
            'apply_time' => 'INTEGER',
        ])->execute();

        $this->db->createCommand()->insert($this->migrationTable, [
            'version' => self::BASE_MIGRATION,
            'apply_time' => time(),
        ])->execute();

        $this->stdout("Done.\n", Console::FG_GREEN);
    }
}