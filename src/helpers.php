<?php

/**
 * @param string $cwd
 * @return string
 */
function guessSrcDirectory(string $cwd): string
{
    if (file_exists($cwd . '/app')) {
        return 'app';
    }

    if (file_exists($cwd . '/src')) {
        return 'src';
    }

    return '.';
}
