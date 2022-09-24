<?php

namespace Zlt\LaravelApiAuth\Support;

class QueryableColumn
{
    public function __construct(public string $column, public $requestParameter, public string $rule, public Operator $operator)
    {
    }

    /**
     * @throws \Exception
     */
    public function query(\Jenssegers\Mongodb\Query\Builder|\Jenssegers\Mongodb\Eloquent\Builder $query, array $values)
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
                return $values[$this->requestParameter] ?? null;
                break;
            case Operator::BETWEEN:
                if (!is_array($this->requestParameter) || !isset($this->requestParameter[0]) || !isset($this->requestParameter[1])) {
                    throw new \Exception('Invalid request parameter');
                }
                $value1 = $values[$this->requestParameter[0]] ?? null;
                $value2 = $values[$this->requestParameter[1]] ?? null;
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
                    $value = $values[$requestParameter] ?? false;
                    if ($value) {
                        $temp[] = $value;
                    }
                }
                return $temp ?? null;
                break;
        }
    }

    public static function from(string $column, $requestParameter, string $rule, Operator $operator): QueryableColumn
    {
        return new QueryableColumn($column, $requestParameter, $rule, $operator);
    }
}
