<?php

namespace Zlt\LaravelApiAuth\Enums;

enum Operator
{
    case EQUAL;
    case BETWEEN;
    case IN;
    case NOTEQUAL;
    case LIKE;
    case GREATER;
    case LESS;
}
