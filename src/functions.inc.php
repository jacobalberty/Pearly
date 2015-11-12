<?php
function IsNullOrEmptyString($question)
{
    return (!isset($question) || strlen($question) == 0);
}
