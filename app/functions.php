<?php

function vd ($input)
{
    echo '<pre>'; var_dump($input); echo '</pre>';
}

function dd ($input)
{
    echo '<pre>'; var_dump($input); echo '</pre>'; die;
}
