<?php

namespace App\Service\Friend;

enum Status: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
}
