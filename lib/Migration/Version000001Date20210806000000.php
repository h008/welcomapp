<?php
declare(strict_types=1);
namespace OCA\WelcomApp\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version000001Date20210806000000 extends SimpleMigrationStep {
    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` return s a `ISchemaWrapper
     * @param array $options
     * @return null|ISchemaWrapper
     */
    public function changeSchema(IOutput $output,Closure $schemaClosure,array $options){
        /**@var ISchemaWrapper $schema */
        $schema = $schemaClosure();
        
        if(!$schema->hasTable('welcomapp_files')){
            $table=$schema->createTable('welcomapp_files');
        }else {
            $table=$schema->getTable('welcomapp_files');
        }
        if(!$table->hasColumn('id')){
            $table->addColumn('id','integer',[
                'autoincrement'=>true,
                'notnull'=>true,
            ]);
            $table->addColumn('announce_id','integer',[
                'notnull'=>true,
            ]);
            $table->addColumn('filename','string',[
                'notnull'=>true,
                'length'=>200,
            ]);
            $table->addColumn('fileurl','string',[
                'notnull'=>true,
                'length'=>200,
            ]);
            $table->addColumn('filetype','string',[
                'notnull'=>false,
                'length'=>200,
            ]);
            $table->addColumn('is_eyecatch','boolean',[
                'notnull'=>false,
            ]);
            $table->addColumn('user_id','string',[
                'notnull'=>true,
                'length'=>200,
            ]);
            $table->setPrimaryKey(['id']);

        }
        if(!$table->hasColumn('href')){
            $table->addColumn('href','string',[
                'notnull'=>true
            ]);
            $table->addColumn('updated','datetime',[
                'notnull'=>false
            ]);
            $table->addColumn('has_preview','boolean',[
                'notnull'=>false
            ]);
        }
        if(!$table->hasColumn('size')){
            $table->addColumn('size','integer',[
                'notnull'=>false
            ]);
            $table->addIndex(['fileurl'],'welcomapp_files_fileurl_index');
        }
        if(!$table->hasColumn('share_id')){

            $table->addColumn('share_id','integer',[
                'notnull'=>false
            ]);
        }
        if(!$schema->hasTable('welcomapp_category')){
            $table=$schema->createTable('welcomapp_category');
        }else {
            $table=$schema->getTable('welcomapp_category');
        }
        if(!$table->hasColumn('id')){
            $table->addColumn('id','integer',[
                'autoincrement'=>true,
                'notnull'=>true,
            ]);
            $table->addColumn('category_name','string',[
                'notnull'=>true,
                'length'=>200,
            ]);
            $table->addColumn('category_order','integer',[
                'notnull'=>false,
            ]);
            $table->setPrimaryKey(['id']);
                
        }
        if(!$table->hasColumn('color')){
            $table->addColumn('color','string',[
                'notnull'=>false,
                'length'=>200,
            ]);
        }
        
        if(!$schema->hasTable('welcomapp_tags')){
            $table=$schema->createTable('welcomapp_tags');
        } else {
            $table=$schema->getTable('welcomapp_tags');
        }
        if(!$table->hasColumn('id')){
            $table->addColumn('id','integer',[
                'autoincrement'=>true,
                'notnull'=>true,
            ]);
            $table->addColumn('tag_name','string',[
                'notnull'=>true,
                'length'=>200,
            ]);
            $table->addColumn('tag_order','integer',[
                'notnull'=>false,
            ]);
            $table->setPrimaryKey(['id']);
                
        }
        if(!$table->hasColumn('color')){
            $table->addColumn('color','string',[
                'notnull'=>false,
                'length'=>200,
            ]);
        }
        if(!$schema->hasTable('welcomapp')){
            $table = $schema->createTable('welcomapp');
        }else{
            $table = $schema->getTable('welcomapp');
        }
        if(!$table->hasColumn('id')){
            $table->addColumn('id','integer',[
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('category','integer',[
                'notnull'=>true,
                'default'=>0,
            ]);
            $table->addColumn('pin_flag','boolean',[
                'notnull'=>false,
            ]);
            $table->addColumn('created','datetime',[
                'notnull'=>false,
            ]);
            $table->addColumn('updated','datetime',[
                'notnull'=>false,
            ]);
            $table->addColumn('title','string',[
                'notnull'=>true,
                'length'=>200,
            ]);
            $table->addColumn('user_id','string',[
                'notnull'=>true,
                'length'=>200,
            ]);
            $table->addColumn('content','text',[
                'notnull' =>true,
                'default'=> '',
            ]);

            $table->setPrimaryKey(['id']);
            $table->addIndex(['user_id'],'welcomapp_user_id_index');

        }
        if(!$table->hasColumn('pub_flag')){
            $table->addColumn('pub_flag','boolean',['notnull'=>false,]);
            $table->addColumn('tags','string',[
                'notnull'=>false,
            ]);
        }
        if(!$table->hasColumn('uuid')){
            $table->addColumn('uuid','string',['notnull'=>true,'default'=>'n/a','length'=>200]);
            $table->addIndex(['uuid'],'welcomapp_uuid_index');
        }
        if(!$table->hasColumn('share_id')){
            $table->addColumn('share_id','integer',['notnull'=>false]);

        }
        return $schema;
    }
}