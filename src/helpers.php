<?php

function guessSrcDirectory($cwd)
{
    if (file_exists($cwd . '/app')) {
        return 'app';
    }

    if (file_exists($cwd . '/src')) {
        return 'src';
    }

    return '.';
}
