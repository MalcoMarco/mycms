<?php

namespace App\Enums;

enum PostType: int
{
    case Page = 1;
    case Post = 2;
    case Layout = 3;
    case Component = 4;
    // case EmailTemplate = 5;
}
