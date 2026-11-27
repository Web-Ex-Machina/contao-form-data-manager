<?php

declare(strict_types=1);

/**
 * Form Data Manager for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-data-manager
 * @license    https://www.gnu.org/licenses/lgpl+gpl-3.0.txt
 */

namespace WEM\ContaoFormDataManagerBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\StringType;

/**
 * Converts tl_form.storeViaFormDataManager from char(1) to a boolean column.
 */
class StoreViaFormDataManagerBooleanMigration extends AbstractMigration
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (! $schemaManager->tablesExist(['tl_form'])) {
            return false;
        }

        $columns = $schemaManager->listTableColumns('tl_form');

        return isset($columns['storeviaformdatamanager']) && $columns['storeviaformdatamanager']->getType() instanceof StringType;
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement("UPDATE tl_form SET storeViaFormDataManager = '0' WHERE storeViaFormDataManager != '1'");
        $this->connection->executeStatement('ALTER TABLE tl_form CHANGE storeViaFormDataManager storeViaFormDataManager TINYINT(1) NOT NULL DEFAULT 0');

        return $this->createResult(true);
    }
}
