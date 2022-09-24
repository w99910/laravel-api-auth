<?php

namespace Zlt\LaravelApiAuth\Support;

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
