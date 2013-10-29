<?php

/**
 * Nette\Diagnostics\Debugger::barDump shortcut.
 * @author Jáchym Toušek
 * @param mixed $var
 */
function bd($value) {
    $bt = debug_backtrace();
    $caller = array_shift($bt);
    $title = pathinfo($caller['file'], PATHINFO_BASENAME) . ':' . $caller['line'];
    foreach (func_get_args() as $var) {
        if (is_array($var) && empty($var)) {
            \Nette\Diagnostics\Debugger::barDump($var, $title . ' [empty]');
        } else {
            \Nette\Diagnostics\Debugger::barDump($var, $title);
        }
    }
    return $value;
}