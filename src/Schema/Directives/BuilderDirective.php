<?php

namespace Nuwave\Lighthouse\Schema\Directives;

use Nuwave\Lighthouse\Support\Contracts\DefinedDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgBuilderDirective;

class BuilderDirective extends BaseDirective implements ArgBuilderDirective, DefinedDirective
{
    /**
     * Name of the directive.
     *
     * @return string
     */
    public function name(): string
    {
        return 'builder';
    }

    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Use an argument to modify the query builder for a field.
"""
directive @builder(
  """
  Reference a method that is passed the query builder.
  Consists of two parts: a class name and a method name, separated by an `@` symbol.
  If you pass only a class name, the method name defaults to `__invoke`.
  """
  method: String!
) on ARGUMENT_DEFINITION
SDL;
    }

    /**
     * Dynamically call a user-defined method to enhance the builder.
     *
     * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $builder
     * @param  mixed  $value
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function handleBuilder($builder, $value)
    {
        return call_user_func(
            $this->getResolverFromArgument('method'),
            $builder,
            $value,
            $this->definitionNode
        );
    }
}
