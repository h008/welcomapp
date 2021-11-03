<?php
declare(strict_types=1);
namespace OCA\WelcomApp\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version000002Date20211030000000 extends SimpleMigrationStep {
    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` return s a `ISchemaWrapper
     * @param array $options
     * @return null|ISchemaWrapper
     */
    public function changeSchema(IOutput $output,Closure $schemaClosure,array $options){
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();
            $table = $schema->getTable('welcomapp');
        if(!$table->hasColumn('readusers')){
            $table->addColumn('readusers','string',['notnull'=>false]);
        }
        return $schema;
    }
}