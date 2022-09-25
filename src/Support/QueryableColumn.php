<?php

namespace Zlt\LaravelApiAuth\Support;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Zlt\LaravelApiAuth\Enums\Operator;

class QueryableColumn
{
    public function __construct(public string $column, public $requestParameter, public string $rule, public Operator $operator, public ?\Closure $castRequestValue = null)
    {
    }

    /**
     * @throws \Exception
     */
    public function query(QueryBuilder|EloquentBuilder $query, array $values): EloquentBuilder|QueryBuilder
    {
        $value = $this->getValue($values);
        if (!$value) {
            return $query;
        }
        return match ($this->operator) {
            Operator::EQUAL => $query->where($this->column, $value),
            Operator::BETWEEN => $query->whereBetween($this->column, $value),
            Operator::IN => $query->whereIn($this->column, $value),
            Operator::NOTEQUAL => $query->where($this->column, '!=', $value),
            Operator::LIKE => $query->whereIn($this->column, 'like', '%' . $value . '%'),
            Operator::GREATER => $query->where($this->column, '>=', $value),
            Operator::LESS => $query->whereIn($this->column, '<=', $value),
        };
    }

    /**
     * @throws \Exception
     */
    public function getValue(array $values)
    {
        switch ($this->operator) {
            case Operator::EQUAL:
            case Operator::NOTEQUAL:
            case Operator::LIKE:
            case Operator::GREATER:
            case Operator::LESS:
                $value = $values[$this->requestParameter] ?? null;
                return $this->castRequestValue($value, $this->requestParameter);
                break;
            case Operator::BETWEEN:
                if (!is_array($this->requestParameter) || !isset($this->requestParameter[0]) || !isset($this->requestParameter[1])) {
                    throw new \Exception('Invalid request parameter');
                }
                $value1 = $this->castRequestValue($values[$this->requestParameter[0]] ?? null, $this->requestParameter[0]);
                $value2 = $this->castRequestValue($values[$this->requestParameter[1]] ?? null, $this->requestParameter[1]);
                if (!$value1 || !$value2) {
                    return null;
                }
                return [$value1, $value2];
                break;
            case Operator::IN:
                if (!is_array($this->requestParameter)) {
                    throw new \Exception('Invalid request parameter');
                };
                foreach ($this->requestParameter as $requestParameter) {
                    $value = $this->castRequestValue($values[$requestParameter] ?? false, $this->requestParameter);
                    if ($value) {
                        $temp[] = $value;
                    }
                }
                return $temp ?? null;
                break;
        }
    }

    public function castRequestValue($value, $parameter = null)
    {
        $castRequestValue = $this->castRequestValue;
        return $castRequestValue && $value ? $castRequestValue($value, $parameter) : $value;
    }

    public static function from(string $column, $requestParameter, string $rule, Operator $operator, \Closure $closure = null): QueryableColumn
    {
        return new QueryableColumn($column, $requestParameter, $rule, $operator, $closure);
    }
}
