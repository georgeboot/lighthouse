<?php

namespace Nuwave\Lighthouse\Schema\Directives;

use Nuwave\Lighthouse\Support\Contracts\DefinedDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgBuilderDirective;

class WhereBetweenDirective extends BaseDirective implements ArgBuilderDirective, DefinedDirective
{
    const NAME = 'whereBetween';

    /**
     * Name of the directive.
     *
     * @return string
     */
    public function name(): string
    {
        return self::NAME;
    }

    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Verify that a column\'s value is between two values.
The type of the input value this is defined upon should be
an `input` object with two fields.
"""
directive @whereBetween(
  """
  Specify the database column to compare. 
  Only required if database column has a different name than the attribute in your schema.
  """
  key: String
) on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION
SDL;
    }

    /**
     * Apply a "WHERE BETWEEN" clause.
     *
     * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $builder
     * @param  mixed  $values
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function handleBuilder($builder, $values)
    {
        return $builder->whereBetween(
            $this->directiveArgValue('key', $this->definitionNode->name->value),
            $values
        );
    }
}
