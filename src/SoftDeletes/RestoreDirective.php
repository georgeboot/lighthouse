<?php

namespace Nuwave\Lighthouse\SoftDeletes;

use Illuminate\Database\Eloquent\Model;
use GraphQL\Language\AST\FieldDefinitionNode;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Nuwave\Lighthouse\Support\Contracts\DefinedDirective;
use Nuwave\Lighthouse\Support\Contracts\FieldManipulator;
use Nuwave\Lighthouse\Schema\Directives\ModifyModelExistenceDirective;

class RestoreDirective extends ModifyModelExistenceDirective implements DefinedDirective, FieldManipulator
{
    const MODEL_NOT_USING_SOFT_DELETES = 'Use the @restore directive only for Model classes that use the SoftDeletes trait.';

    /**
     * Name of the directive.
     *
     * @return string
     */
    public function name(): string
    {
        return 'restore';
    }

    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Un-delete one or more soft deleted models by their ID. 
The field must have a single non-null argument that may be a list.
"""
directive @restore(
  """
  Set to `true` to use global ids for finding the model.
  If set to `false`, regular non-global ids are used.
  """
  globalId: Boolean = false

  """
  Specify the class name of the model to use.
  This is only needed when the default model resolution does not work.
  """
  model: String
) on FIELD_DEFINITION
SDL;
    }

    /**
     * Find one or more models by id.
     *
     * @param  string|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\SoftDeletes  $modelClass
     * @param  string|int|string[]|int[]  $idOrIds
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     */
    protected function find(string $modelClass, $idOrIds)
    {
        return $modelClass::withTrashed()->find($idOrIds);
    }

    /**
     * Bring a model in or out of existence.
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\SoftDeletes  $model
     * @return void
     */
    protected function modifyExistence(Model $model): void
    {
        $model->restore();
    }

    /**
     * Manipulate the AST based on a field definition.
     *
     * @param  \Nuwave\Lighthouse\Schema\AST\DocumentAST  $documentAST
     * @param  \GraphQL\Language\AST\FieldDefinitionNode  $fieldDefinition
     * @param  \GraphQL\Language\AST\ObjectTypeDefinitionNode  $parentType
     * @return void
     */
    public function manipulateFieldDefinition(
        DocumentAST &$documentAST,
        FieldDefinitionNode &$fieldDefinition,
        ObjectTypeDefinitionNode &$parentType
    ): void {
        parent::manipulateFieldDefinition($documentAST, $fieldDefinition, $parentType);

        SoftDeletesServiceProvider::assertModelUsesSoftDeletes(
            $this->getModelClass(),
            self::MODEL_NOT_USING_SOFT_DELETES
        );
    }
}
